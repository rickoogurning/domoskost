<?php

namespace App\Http\Controllers;

use App\Models\Tagihan;
use App\Models\DetailTagihan;
use App\Models\Penghuni;
use App\Models\Kamar;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TagihanController extends Controller
{
    /**
     * Display a listing of tagihan
     */
    public function index(Request $request): JsonResponse
    {
        $query = Tagihan::with(['penghuni.user', 'penghuni.kamar', 'detailTagihan', 'pembayaran']);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('penghuni.user', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%");
            })->orWhereHas('penghuni.kamar', function ($q) use ($search) {
                $q->where('kode_kamar', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status_tagihan', $request->status);
        }

        // Filter by period
        if ($request->has('periode_bulan')) {
            $query->where('periode_bulan', $request->periode_bulan);
        }

        if ($request->has('periode_tahun')) {
            $query->where('periode_tahun', $request->periode_tahun);
        }

        // Filter overdue bills
        if ($request->has('overdue') && $request->overdue === 'true') {
            $query->where('tanggal_jatuh_tempo', '<', now())
                  ->whereNotIn('status_tagihan', ['Lunas']);
        }

        // Filter by penghuni
        if ($request->has('penghuni_id')) {
            $query->where('penghuni_id', $request->penghuni_id);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'periode_tahun');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if ($sortBy === 'periode') {
            $query->orderBy('periode_tahun', $sortOrder)
                  ->orderBy('periode_bulan', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $tagihan = $query->paginate($perPage);

        // Transform data
        $tagihan->getCollection()->transform(function ($item) {
            return [
                'id' => $item->id,
                'penghuni' => [
                    'id' => $item->penghuni->id,
                    'nama_lengkap' => $item->penghuni->user->nama_lengkap,
                    'kamar' => $item->penghuni->kamar->kode_kamar,
                ],
                'periode_bulan' => $item->periode_bulan,
                'periode_tahun' => $item->periode_tahun,
                'periode_string' => $item->periode_string,
                'tanggal_terbit' => $item->tanggal_terbit,
                'tanggal_jatuh_tempo' => $item->tanggal_jatuh_tempo,
                'total_tagihan' => $item->total_tagihan,
                'total_dibayar' => $item->total_dibayar,
                'sisa_tagihan' => $item->sisa_tagihan,
                'denda' => $item->denda,
                'status_tagihan' => $item->status_tagihan,
                'sisa_hari' => $item->sisa_hari,
                'is_overdue' => $item->isOverdue(),
                'created_at' => $item->created_at,
            ];
        });

        return $this->paginatedResponse($tagihan, 'Data tagihan berhasil diambil');
    }

    /**
     * Store a newly created tagihan
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'penghuni_id' => 'required|exists:penghuni,id',
            'periode_bulan' => 'required|integer|between:1,12',
            'periode_tahun' => 'required|integer|min:2020',
            'tanggal_terbit' => 'required|date',
            'tanggal_jatuh_tempo' => 'required|date|after:tanggal_terbit',
            'catatan' => 'nullable|string',
            'detail_tagihan' => 'required|array|min:1',
            'detail_tagihan.*.jenis_tagihan' => 'required|in:Sewa Kamar,Laundry,Denda,Lainnya',
            'detail_tagihan.*.deskripsi' => 'required|string',
            'detail_tagihan.*.quantity' => 'required|integer|min:1',
            'detail_tagihan.*.harga_satuan' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        // Check if tagihan already exists for this period
        $exists = Tagihan::where('penghuni_id', $request->penghuni_id)
                         ->where('periode_bulan', $request->periode_bulan)
                         ->where('periode_tahun', $request->periode_tahun)
                         ->exists();

        if ($exists) {
            return $this->errorResponse('Tagihan untuk periode ini sudah ada', 400);
        }

        try {
            DB::beginTransaction();

            // Calculate total
            $totalTagihan = 0;
            foreach ($request->detail_tagihan as $detail) {
                $totalTagihan += $detail['quantity'] * $detail['harga_satuan'];
            }

            // Create tagihan
            $tagihan = Tagihan::create([
                'penghuni_id' => $request->penghuni_id,
                'periode_bulan' => $request->periode_bulan,
                'periode_tahun' => $request->periode_tahun,
                'tanggal_terbit' => $request->tanggal_terbit,
                'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo,
                'total_tagihan' => $totalTagihan,
                'status_tagihan' => 'Belum Dibayar',
                'catatan' => $request->catatan,
                'created_by' => auth()->id(),
            ]);

            // Create detail tagihan
            foreach ($request->detail_tagihan as $detail) {
                $tagihan->detailTagihan()->create([
                    'jenis_tagihan' => $detail['jenis_tagihan'],
                    'deskripsi' => $detail['deskripsi'],
                    'quantity' => $detail['quantity'],
                    'harga_satuan' => $detail['harga_satuan'],
                    'subtotal' => $detail['quantity'] * $detail['harga_satuan'],
                ]);
            }

            DB::commit();

            // Create notification for penghuni
            $penghuni = Penghuni::with('user')->find($request->penghuni_id);
            $penghuni->user->notifikasi()->create([
                'judul' => 'Tagihan Baru',
                'isi_notifikasi' => "Tagihan {$tagihan->periode_string} sebesar Rp " . number_format($tagihan->total_tagihan, 0, ',', '.') . " telah diterbitkan. Jatuh tempo: " . $tagihan->tanggal_jatuh_tempo->format('d/m/Y'),
                'tipe_notifikasi' => 'Tagihan',
                'link_terkait' => "/penghuni/tagihan/{$tagihan->id}",
            ]);

            $tagihan->load(['penghuni.user', 'detailTagihan']);

            return $this->successResponse([
                'id' => $tagihan->id,
                'penghuni' => $tagihan->penghuni->user->nama_lengkap,
                'periode' => $tagihan->periode_string,
                'total_tagihan' => $tagihan->total_tagihan,
                'jatuh_tempo' => $tagihan->tanggal_jatuh_tempo,
                'status' => $tagihan->status_tagihan,
            ], 'Tagihan berhasil dibuat', 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->serverErrorResponse('Gagal membuat tagihan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified tagihan
     */
    public function show($id): JsonResponse
    {
        $tagihan = Tagihan::with([
            'penghuni.user', 
            'penghuni.kamar', 
            'detailTagihan', 
            'pembayaran.verifikator',
            'createdBy'
        ])->find($id);

        if (!$tagihan) {
            return $this->notFoundResponse('Tagihan tidak ditemukan');
        }

        return $this->successResponse([
            'id' => $tagihan->id,
            'penghuni' => [
                'id' => $tagihan->penghuni->id,
                'nama_lengkap' => $tagihan->penghuni->user->nama_lengkap,
                'email' => $tagihan->penghuni->user->email,
                'no_telp' => $tagihan->penghuni->user->no_telp,
                'kamar' => [
                    'id' => $tagihan->penghuni->kamar->id,
                    'kode_kamar' => $tagihan->penghuni->kamar->kode_kamar,
                    'lantai' => $tagihan->penghuni->kamar->lantai,
                    'tarif_bulanan' => $tagihan->penghuni->kamar->tarif_bulanan,
                ],
            ],
            'periode_bulan' => $tagihan->periode_bulan,
            'periode_tahun' => $tagihan->periode_tahun,
            'periode_string' => $tagihan->periode_string,
            'tanggal_terbit' => $tagihan->tanggal_terbit,
            'tanggal_jatuh_tempo' => $tagihan->tanggal_jatuh_tempo,
            'total_tagihan' => $tagihan->total_tagihan,
            'total_dibayar' => $tagihan->total_dibayar,
            'sisa_tagihan' => $tagihan->sisa_tagihan,
            'denda' => $tagihan->denda,
            'status_tagihan' => $tagihan->status_tagihan,
            'sisa_hari' => $tagihan->sisa_hari,
            'is_overdue' => $tagihan->isOverdue(),
            'catatan' => $tagihan->catatan,
            'detail_tagihan' => $tagihan->detailTagihan->map(function ($detail) {
                return [
                    'id' => $detail->id,
                    'jenis_tagihan' => $detail->jenis_tagihan,
                    'deskripsi' => $detail->deskripsi,
                    'quantity' => $detail->quantity,
                    'harga_satuan' => $detail->harga_satuan,
                    'subtotal' => $detail->subtotal,
                ];
            }),
            'pembayaran' => $tagihan->pembayaran->map(function ($bayar) {
                return [
                    'id' => $bayar->id,
                    'tanggal_bayar' => $bayar->tanggal_bayar,
                    'jumlah_bayar' => $bayar->jumlah_bayar,
                    'metode_bayar' => $bayar->metode_bayar,
                    'status_verifikasi' => $bayar->status_verifikasi,
                    'bukti_bayar' => $bayar->bukti_bayar,
                    'verifikator' => $bayar->verifikator ? $bayar->verifikator->nama_lengkap : null,
                    'tanggal_verifikasi' => $bayar->tanggal_verifikasi,
                    'catatan_verifikasi' => $bayar->catatan_verifikasi,
                ];
            }),
            'created_by' => $tagihan->createdBy ? $tagihan->createdBy->nama_lengkap : null,
            'created_at' => $tagihan->created_at,
            'updated_at' => $tagihan->updated_at,
        ], 'Detail tagihan berhasil diambil');
    }

    /**
     * Update the specified tagihan
     */
    public function update(Request $request, $id): JsonResponse
    {
        $tagihan = Tagihan::with('pembayaran')->find($id);

        if (!$tagihan) {
            return $this->notFoundResponse('Tagihan tidak ditemukan');
        }

        if ($tagihan->status_tagihan === 'Lunas') {
            return $this->errorResponse('Tagihan yang sudah lunas tidak dapat diubah', 400);
        }

        $validator = Validator::make($request->all(), [
            'tanggal_jatuh_tempo' => 'sometimes|date',
            'catatan' => 'nullable|string',
            'denda' => 'sometimes|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $tagihan->update($request->only(['tanggal_jatuh_tempo', 'catatan', 'denda']));

            // Recalculate status if needed
            $tagihan->updateStatus();

            return $this->successResponse(
                $tagihan->fresh(['penghuni.user', 'detailTagihan']),
                'Tagihan berhasil diperbarui'
            );

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Gagal memperbarui tagihan: ' . $e->getMessage());
        }
    }

    /**
     * Generate monthly bills for all active tenants
     */
    public function generateBulanan($bulan, $tahun): JsonResponse
    {
        $validator = Validator::make([
            'bulan' => $bulan,
            'tahun' => $tahun,
        ], [
            'bulan' => 'required|integer|between:1,12',
            'tahun' => 'required|integer|min:2020',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            // Get all active penghuni with kamar
            $penghuniAktif = Penghuni::with(['kamar', 'user'])
                                   ->where('status_penghuni', 'Aktif')
                                   ->whereNotNull('kamar_id')
                                   ->get();

            $created = 0;
            $skipped = 0;
            $errors = [];

            foreach ($penghuniAktif as $penghuni) {
                // Check if tagihan already exists
                $exists = Tagihan::where('penghuni_id', $penghuni->id)
                                ->where('periode_bulan', $bulan)
                                ->where('periode_tahun', $tahun)
                                ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                try {
                    // Create tagihan
                    $tanggalTerbit = Carbon::create($tahun, $bulan, 1);
                    $tanggalJatuhTempo = Carbon::create($tahun, $bulan, 10);

                    $tagihan = Tagihan::create([
                        'penghuni_id' => $penghuni->id,
                        'periode_bulan' => $bulan,
                        'periode_tahun' => $tahun,
                        'tanggal_terbit' => $tanggalTerbit,
                        'tanggal_jatuh_tempo' => $tanggalJatuhTempo,
                        'total_tagihan' => $penghuni->kamar->tarif_bulanan,
                        'status_tagihan' => 'Belum Dibayar',
                        'created_by' => auth()->id(),
                    ]);

                    // Create detail tagihan for room rent
                    $tagihan->detailTagihan()->create([
                        'jenis_tagihan' => 'Sewa Kamar',
                        'deskripsi' => "Sewa Kamar {$penghuni->kamar->kode_kamar} " . $tagihan->periode_string,
                        'quantity' => 1,
                        'harga_satuan' => $penghuni->kamar->tarif_bulanan,
                        'subtotal' => $penghuni->kamar->tarif_bulanan,
                    ]);

                    // Create notification
                    $penghuni->user->notifikasi()->create([
                        'judul' => 'Tagihan Baru',
                        'isi_notifikasi' => "Tagihan {$tagihan->periode_string} sebesar Rp " . number_format($tagihan->total_tagihan, 0, ',', '.') . " telah diterbitkan. Jatuh tempo: " . $tagihan->tanggal_jatuh_tempo->format('d/m/Y'),
                        'tipe_notifikasi' => 'Tagihan',
                        'link_terkait' => "/penghuni/tagihan/{$tagihan->id}",
                    ]);

                    $created++;

                } catch (\Exception $e) {
                    $errors[] = "Error untuk {$penghuni->user->nama_lengkap}: " . $e->getMessage();
                }
            }

            DB::commit();

            $message = "Berhasil generate tagihan untuk {$created} penghuni.";
            if ($skipped > 0) {
                $message .= " {$skipped} tagihan dilewati karena sudah ada.";
            }
            if (!empty($errors)) {
                $message .= " " . count($errors) . " error terjadi.";
            }

            return $this->successResponse([
                'created' => $created,
                'skipped' => $skipped,
                'errors' => $errors,
                'total_penghuni' => $penghuniAktif->count(),
            ], $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->serverErrorResponse('Gagal generate tagihan bulanan: ' . $e->getMessage());
        }
    }

    /**
     * Get tagihan by penghuni
     */
    public function byPenghuni($penghuniId): JsonResponse
    {
        $tagihan = Tagihan::with(['detailTagihan', 'pembayaran'])
                         ->where('penghuni_id', $penghuniId)
                         ->orderBy('periode_tahun', 'desc')
                         ->orderBy('periode_bulan', 'desc')
                         ->get()
                         ->map(function ($item) {
                             return [
                                 'id' => $item->id,
                                 'periode_string' => $item->periode_string,
                                 'total_tagihan' => $item->total_tagihan,
                                 'total_dibayar' => $item->total_dibayar,
                                 'sisa_tagihan' => $item->sisa_tagihan,
                                 'status_tagihan' => $item->status_tagihan,
                                 'tanggal_jatuh_tempo' => $item->tanggal_jatuh_tempo,
                                 'is_overdue' => $item->isOverdue(),
                                 'sisa_hari' => $item->sisa_hari,
                             ];
                         });

        return $this->successResponse($tagihan, 'Tagihan penghuni berhasil diambil');
    }

    /**
     * Get tagihan for authenticated penghuni
     */
    public function myTagihan(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->penghuni) {
            return $this->errorResponse('Data penghuni tidak ditemukan', 404);
        }

        $query = Tagihan::with(['detailTagihan', 'pembayaran'])
                       ->where('penghuni_id', $user->penghuni->id);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status_tagihan', $request->status);
        }

        // Filter by year
        if ($request->has('tahun')) {
            $query->where('periode_tahun', $request->tahun);
        }

        $tagihan = $query->orderBy('periode_tahun', 'desc')
                        ->orderBy('periode_bulan', 'desc')
                        ->paginate($request->get('per_page', 12));

        $tagihan->getCollection()->transform(function ($item) {
            return [
                'id' => $item->id,
                'periode_bulan' => $item->periode_bulan,
                'periode_tahun' => $item->periode_tahun,
                'periode_string' => $item->periode_string,
                'tanggal_terbit' => $item->tanggal_terbit,
                'tanggal_jatuh_tempo' => $item->tanggal_jatuh_tempo,
                'total_tagihan' => $item->total_tagihan,
                'total_dibayar' => $item->total_dibayar,
                'sisa_tagihan' => $item->sisa_tagihan,
                'denda' => $item->denda,
                'status_tagihan' => $item->status_tagihan,
                'sisa_hari' => $item->sisa_hari,
                'is_overdue' => $item->isOverdue(),
                'detail_count' => $item->detailTagihan->count(),
                'payment_count' => $item->pembayaran->count(),
            ];
        });

        return $this->paginatedResponse($tagihan, 'Tagihan saya berhasil diambil');
    }

    /**
     * Get detail tagihan for authenticated penghuni
     */
    public function myTagihanDetail($id, Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->penghuni) {
            return $this->errorResponse('Data penghuni tidak ditemukan', 404);
        }

        $tagihan = Tagihan::with(['detailTagihan', 'pembayaran.verifikator'])
                         ->where('id', $id)
                         ->where('penghuni_id', $user->penghuni->id)
                         ->first();

        if (!$tagihan) {
            return $this->notFoundResponse('Tagihan tidak ditemukan');
        }

        return $this->successResponse([
            'id' => $tagihan->id,
            'periode_string' => $tagihan->periode_string,
            'tanggal_terbit' => $tagihan->tanggal_terbit,
            'tanggal_jatuh_tempo' => $tagihan->tanggal_jatuh_tempo,
            'total_tagihan' => $tagihan->total_tagihan,
            'total_dibayar' => $tagihan->total_dibayar,
            'sisa_tagihan' => $tagihan->sisa_tagihan,
            'denda' => $tagihan->denda,
            'status_tagihan' => $tagihan->status_tagihan,
            'sisa_hari' => $tagihan->sisa_hari,
            'is_overdue' => $tagihan->isOverdue(),
            'catatan' => $tagihan->catatan,
            'detail_tagihan' => $tagihan->detailTagihan->map(function ($detail) {
                return [
                    'jenis_tagihan' => $detail->jenis_tagihan,
                    'deskripsi' => $detail->deskripsi,
                    'quantity' => $detail->quantity,
                    'harga_satuan' => $detail->harga_satuan,
                    'subtotal' => $detail->subtotal,
                ];
            }),
            'pembayaran' => $tagihan->pembayaran->map(function ($bayar) {
                return [
                    'id' => $bayar->id,
                    'tanggal_bayar' => $bayar->tanggal_bayar,
                    'jumlah_bayar' => $bayar->jumlah_bayar,
                    'metode_bayar' => $bayar->metode_bayar,
                    'status_verifikasi' => $bayar->status_verifikasi,
                    'bukti_bayar' => $bayar->bukti_bayar,
                    'tanggal_verifikasi' => $bayar->tanggal_verifikasi,
                    'catatan_verifikasi' => $bayar->catatan_verifikasi,
                ];
            }),
        ], 'Detail tagihan berhasil diambil');
    }
}
