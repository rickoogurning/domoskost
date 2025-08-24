<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class PembayaranController extends Controller
{
    /**
     * Display a listing of pembayaran
     */
    public function index(Request $request): JsonResponse
    {
        $query = Pembayaran::with(['tagihan.penghuni.user', 'tagihan.penghuni.kamar', 'verifikator']);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('tagihan.penghuni.user', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%");
            })->orWhereHas('tagihan.penghuni.kamar', function ($q) use ($search) {
                $q->where('kode_kamar', 'like', "%{$search}%");
            });
        }

        // Filter by verification status
        if ($request->has('status_verifikasi')) {
            $query->where('status_verifikasi', $request->status_verifikasi);
        }

        // Filter by payment method
        if ($request->has('metode_bayar')) {
            $query->where('metode_bayar', $request->metode_bayar);
        }

        // Filter by date range
        if ($request->has('tanggal_mulai')) {
            $query->whereDate('tanggal_bayar', '>=', $request->tanggal_mulai);
        }

        if ($request->has('tanggal_selesai')) {
            $query->whereDate('tanggal_bayar', '<=', $request->tanggal_selesai);
        }

        // Filter by tagihan
        if ($request->has('tagihan_id')) {
            $query->where('tagihan_id', $request->tagihan_id);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'tanggal_bayar');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $pembayaran = $query->paginate($perPage);

        // Transform data
        $pembayaran->getCollection()->transform(function ($item) {
            return [
                'id' => $item->id,
                'tagihan' => [
                    'id' => $item->tagihan->id,
                    'periode' => $item->tagihan->periode_string,
                    'total_tagihan' => $item->tagihan->total_tagihan,
                    'penghuni' => [
                        'nama_lengkap' => $item->tagihan->penghuni->user->nama_lengkap,
                        'kamar' => $item->tagihan->penghuni->kamar->kode_kamar,
                    ],
                ],
                'tanggal_bayar' => $item->tanggal_bayar,
                'jumlah_bayar' => $item->jumlah_bayar,
                'metode_bayar' => $item->metode_bayar,
                'bukti_bayar' => $item->bukti_bayar,
                'status_verifikasi' => $item->status_verifikasi,
                'verifikator' => $item->verifikator ? $item->verifikator->nama_lengkap : null,
                'tanggal_verifikasi' => $item->tanggal_verifikasi,
                'catatan_verifikasi' => $item->catatan_verifikasi,
                'created_at' => $item->created_at,
            ];
        });

        return $this->paginatedResponse($pembayaran, 'Data pembayaran berhasil diambil');
    }

    /**
     * Store a newly created pembayaran
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tagihan_id' => 'required|exists:tagihan,id',
            'tanggal_bayar' => 'required|date|before_or_equal:today',
            'jumlah_bayar' => 'required|numeric|min:1',
            'metode_bayar' => 'required|in:Tunai,Transfer Bank,E-Wallet',
            'bukti_bayar' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $tagihan = Tagihan::with('penghuni.user')->find($request->tagihan_id);

        // Validate payment amount
        if ($request->jumlah_bayar > $tagihan->sisa_tagihan) {
            return $this->errorResponse('Jumlah pembayaran melebihi sisa tagihan', 400);
        }

        try {
            $pembayaran = Pembayaran::create([
                'tagihan_id' => $request->tagihan_id,
                'tanggal_bayar' => $request->tanggal_bayar,
                'jumlah_bayar' => $request->jumlah_bayar,
                'metode_bayar' => $request->metode_bayar,
                'bukti_bayar' => $request->bukti_bayar,
                'status_verifikasi' => $request->metode_bayar === 'Tunai' ? 'Terverifikasi' : 'Menunggu',
                'verifikasi_oleh' => $request->metode_bayar === 'Tunai' ? auth()->id() : null,
                'tanggal_verifikasi' => $request->metode_bayar === 'Tunai' ? now() : null,
            ]);

            // Update tagihan status if cash payment (auto-verified)
            if ($request->metode_bayar === 'Tunai') {
                $tagihan->updateStatus();
            }

            // Create notification
            $tagihan->penghuni->user->notifikasi()->create([
                'judul' => 'Pembayaran Diterima',
                'isi_notifikasi' => "Pembayaran sebesar Rp " . number_format($pembayaran->jumlah_bayar, 0, ',', '.') . " untuk tagihan {$tagihan->periode_string} telah diterima dan " . ($pembayaran->isVerified() ? 'terverifikasi' : 'menunggu verifikasi') . ".",
                'tipe_notifikasi' => 'Pembayaran',
                'link_terkait' => "/penghuni/tagihan/{$tagihan->id}",
            ]);

            return $this->successResponse([
                'id' => $pembayaran->id,
                'tagihan_id' => $pembayaran->tagihan_id,
                'jumlah_bayar' => $pembayaran->jumlah_bayar,
                'metode_bayar' => $pembayaran->metode_bayar,
                'status_verifikasi' => $pembayaran->status_verifikasi,
                'sisa_tagihan' => $tagihan->fresh()->sisa_tagihan,
            ], 'Pembayaran berhasil dicatat', 201);

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Gagal mencatat pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified pembayaran
     */
    public function show($id): JsonResponse
    {
        $pembayaran = Pembayaran::with([
            'tagihan.penghuni.user', 
            'tagihan.penghuni.kamar',
            'tagihan.detailTagihan',
            'verifikator'
        ])->find($id);

        if (!$pembayaran) {
            return $this->notFoundResponse('Pembayaran tidak ditemukan');
        }

        return $this->successResponse([
            'id' => $pembayaran->id,
            'tagihan' => [
                'id' => $pembayaran->tagihan->id,
                'periode' => $pembayaran->tagihan->periode_string,
                'total_tagihan' => $pembayaran->tagihan->total_tagihan,
                'total_dibayar' => $pembayaran->tagihan->total_dibayar,
                'sisa_tagihan' => $pembayaran->tagihan->sisa_tagihan,
                'penghuni' => [
                    'id' => $pembayaran->tagihan->penghuni->id,
                    'nama_lengkap' => $pembayaran->tagihan->penghuni->user->nama_lengkap,
                    'email' => $pembayaran->tagihan->penghuni->user->email,
                    'no_telp' => $pembayaran->tagihan->penghuni->user->no_telp,
                    'kamar' => $pembayaran->tagihan->penghuni->kamar->kode_kamar,
                ],
            ],
            'tanggal_bayar' => $pembayaran->tanggal_bayar,
            'jumlah_bayar' => $pembayaran->jumlah_bayar,
            'metode_bayar' => $pembayaran->metode_bayar,
            'bukti_bayar' => $pembayaran->bukti_bayar,
            'status_verifikasi' => $pembayaran->status_verifikasi,
            'verifikator' => $pembayaran->verifikator ? [
                'id' => $pembayaran->verifikator->id,
                'nama_lengkap' => $pembayaran->verifikator->nama_lengkap,
            ] : null,
            'tanggal_verifikasi' => $pembayaran->tanggal_verifikasi,
            'catatan_verifikasi' => $pembayaran->catatan_verifikasi,
            'created_at' => $pembayaran->created_at,
            'updated_at' => $pembayaran->updated_at,
        ], 'Detail pembayaran berhasil diambil');
    }

    /**
     * Update the specified pembayaran
     */
    public function update(Request $request, $id): JsonResponse
    {
        $pembayaran = Pembayaran::find($id);

        if (!$pembayaran) {
            return $this->notFoundResponse('Pembayaran tidak ditemukan');
        }

        if ($pembayaran->status_verifikasi === 'Terverifikasi') {
            return $this->errorResponse('Pembayaran yang sudah terverifikasi tidak dapat diubah', 400);
        }

        $validator = Validator::make($request->all(), [
            'tanggal_bayar' => 'sometimes|date|before_or_equal:today',
            'jumlah_bayar' => 'sometimes|numeric|min:1',
            'metode_bayar' => 'sometimes|in:Tunai,Transfer Bank,E-Wallet',
            'bukti_bayar' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        // Validate payment amount if changed
        if ($request->has('jumlah_bayar')) {
            $tagihan = $pembayaran->tagihan;
            $totalPembayaranLain = $tagihan->pembayaran()
                                          ->where('id', '!=', $pembayaran->id)
                                          ->where('status_verifikasi', 'Terverifikasi')
                                          ->sum('jumlah_bayar');
            
            if (($totalPembayaranLain + $request->jumlah_bayar) > $tagihan->total_tagihan) {
                return $this->errorResponse('Total pembayaran akan melebihi tagihan', 400);
            }
        }

        try {
            $pembayaran->update($request->only([
                'tanggal_bayar', 'jumlah_bayar', 'metode_bayar', 'bukti_bayar'
            ]));

            return $this->successResponse(
                $pembayaran->fresh(['tagihan', 'verifikator']),
                'Pembayaran berhasil diperbarui'
            );

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Gagal memperbarui pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Verify payment
     */
    public function verify(Request $request, $id): JsonResponse
    {
        $pembayaran = Pembayaran::with('tagihan.penghuni.user')->find($id);

        if (!$pembayaran) {
            return $this->notFoundResponse('Pembayaran tidak ditemukan');
        }

        if ($pembayaran->status_verifikasi === 'Terverifikasi') {
            return $this->errorResponse('Pembayaran sudah terverifikasi', 400);
        }

        $validator = Validator::make($request->all(), [
            'catatan_verifikasi' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $pembayaran->verify(auth()->user(), $request->catatan_verifikasi);

            // Create notification
            $pembayaran->tagihan->penghuni->user->notifikasi()->create([
                'judul' => 'Pembayaran Terverifikasi',
                'isi_notifikasi' => "Pembayaran sebesar Rp " . number_format($pembayaran->jumlah_bayar, 0, ',', '.') . " untuk tagihan {$pembayaran->tagihan->periode_string} telah terverifikasi.",
                'tipe_notifikasi' => 'Pembayaran',
                'link_terkait' => "/penghuni/tagihan/{$pembayaran->tagihan->id}",
            ]);

            return $this->successResponse([
                'id' => $pembayaran->id,
                'status_verifikasi' => $pembayaran->fresh()->status_verifikasi,
                'verifikator' => auth()->user()->nama_lengkap,
                'tanggal_verifikasi' => $pembayaran->fresh()->tanggal_verifikasi,
                'tagihan_status' => $pembayaran->tagihan->fresh()->status_tagihan,
            ], 'Pembayaran berhasil diverifikasi');

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Gagal memverifikasi pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Reject payment
     */
    public function reject(Request $request, $id): JsonResponse
    {
        $pembayaran = Pembayaran::with('tagihan.penghuni.user')->find($id);

        if (!$pembayaran) {
            return $this->notFoundResponse('Pembayaran tidak ditemukan');
        }

        if ($pembayaran->status_verifikasi === 'Terverifikasi') {
            return $this->errorResponse('Pembayaran yang sudah terverifikasi tidak dapat ditolak', 400);
        }

        $validator = Validator::make($request->all(), [
            'catatan_verifikasi' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $pembayaran->reject(auth()->user(), $request->catatan_verifikasi);

            // Create notification
            $pembayaran->tagihan->penghuni->user->notifikasi()->create([
                'judul' => 'Pembayaran Ditolak',
                'isi_notifikasi' => "Pembayaran sebesar Rp " . number_format($pembayaran->jumlah_bayar, 0, ',', '.') . " untuk tagihan {$pembayaran->tagihan->periode_string} ditolak. Alasan: {$request->catatan_verifikasi}",
                'tipe_notifikasi' => 'Pembayaran',
                'link_terkait' => "/penghuni/tagihan/{$pembayaran->tagihan->id}",
            ]);

            return $this->successResponse([
                'id' => $pembayaran->id,
                'status_verifikasi' => $pembayaran->fresh()->status_verifikasi,
                'verifikator' => auth()->user()->nama_lengkap,
                'tanggal_verifikasi' => $pembayaran->fresh()->tanggal_verifikasi,
                'catatan_verifikasi' => $pembayaran->fresh()->catatan_verifikasi,
            ], 'Pembayaran berhasil ditolak');

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Gagal menolak pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Create payment for authenticated penghuni
     */
    public function createPayment(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->penghuni) {
            return $this->errorResponse('Data penghuni tidak ditemukan', 404);
        }

        $validator = Validator::make($request->all(), [
            'tagihan_id' => 'required|exists:tagihan,id',
            'jumlah_bayar' => 'required|numeric|min:1',
            'metode_bayar' => 'required|in:Transfer Bank,E-Wallet',
            'bukti_bayar' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        // Verify tagihan belongs to this penghuni
        $tagihan = Tagihan::where('id', $request->tagihan_id)
                         ->where('penghuni_id', $user->penghuni->id)
                         ->first();

        if (!$tagihan) {
            return $this->errorResponse('Tagihan tidak ditemukan atau bukan milik Anda', 404);
        }

        if ($tagihan->status_tagihan === 'Lunas') {
            return $this->errorResponse('Tagihan sudah lunas', 400);
        }

        // Check if payment amount is valid
        if ($request->jumlah_bayar > $tagihan->sisa_tagihan) {
            return $this->errorResponse('Jumlah pembayaran melebihi sisa tagihan', 400);
        }

        try {
            $pembayaran = Pembayaran::create([
                'tagihan_id' => $request->tagihan_id,
                'tanggal_bayar' => now(),
                'jumlah_bayar' => $request->jumlah_bayar,
                'metode_bayar' => $request->metode_bayar,
                'bukti_bayar' => $request->bukti_bayar,
                'status_verifikasi' => 'Menunggu',
            ]);

            return $this->successResponse([
                'id' => $pembayaran->id,
                'tagihan_id' => $pembayaran->tagihan_id,
                'jumlah_bayar' => $pembayaran->jumlah_bayar,
                'metode_bayar' => $pembayaran->metode_bayar,
                'status_verifikasi' => $pembayaran->status_verifikasi,
                'message' => 'Pembayaran berhasil diupload dan menunggu verifikasi admin.',
            ], 'Bukti pembayaran berhasil diupload', 201);

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Gagal mengupload pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Get payment history for authenticated penghuni
     */
    public function myPembayaran(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->penghuni) {
            return $this->errorResponse('Data penghuni tidak ditemukan', 404);
        }

        $query = Pembayaran::with(['tagihan'])
                          ->whereHas('tagihan', function ($q) use ($user) {
                              $q->where('penghuni_id', $user->penghuni->id);
                          });

        // Filter by status
        if ($request->has('status_verifikasi')) {
            $query->where('status_verifikasi', $request->status_verifikasi);
        }

        // Filter by year
        if ($request->has('tahun')) {
            $query->whereYear('tanggal_bayar', $request->tahun);
        }

        $pembayaran = $query->orderBy('tanggal_bayar', 'desc')
                           ->paginate($request->get('per_page', 15));

        $pembayaran->getCollection()->transform(function ($item) {
            return [
                'id' => $item->id,
                'tagihan' => [
                    'id' => $item->tagihan->id,
                    'periode' => $item->tagihan->periode_string,
                    'total_tagihan' => $item->tagihan->total_tagihan,
                ],
                'tanggal_bayar' => $item->tanggal_bayar,
                'jumlah_bayar' => $item->jumlah_bayar,
                'metode_bayar' => $item->metode_bayar,
                'status_verifikasi' => $item->status_verifikasi,
                'tanggal_verifikasi' => $item->tanggal_verifikasi,
                'catatan_verifikasi' => $item->catatan_verifikasi,
            ];
        });

        return $this->paginatedResponse($pembayaran, 'Riwayat pembayaran berhasil diambil');
    }
}
