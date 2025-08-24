<?php

namespace App\Http\Controllers;

use App\Models\OrderLaundry;
use App\Models\JenisLayananLaundry;
use App\Models\Penghuni;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LaundryController extends Controller
{
    /**
     * Display a listing of laundry orders
     */
    public function index(Request $request): JsonResponse
    {
        $query = OrderLaundry::with(['penghuni.user', 'penghuni.kamar', 'jenisLayanan', 'petugasTerima', 'petugasSelesai']);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_order', 'like', "%{$search}%")
                  ->orWhereHas('penghuni.user', function ($subQ) use ($search) {
                      $subQ->where('nama_lengkap', 'like', "%{$search}%");
                  })
                  ->orWhereHas('penghuni.kamar', function ($subQ) use ($search) {
                      $subQ->where('kode_kamar', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status_order', $request->status);
        }

        // Filter by payment status
        if ($request->has('status_bayar')) {
            $query->where('status_bayar', $request->status_bayar);
        }

        // Filter by date range
        if ($request->has('tanggal_mulai')) {
            $query->whereDate('tanggal_terima', '>=', $request->tanggal_mulai);
        }

        if ($request->has('tanggal_selesai')) {
            $query->whereDate('tanggal_terima', '<=', $request->tanggal_selesai);
        }

        // Filter by service type
        if ($request->has('jenis_layanan_id')) {
            $query->where('jenis_layanan_id', $request->jenis_layanan_id);
        }

        // Filter by penghuni
        if ($request->has('penghuni_id')) {
            $query->where('penghuni_id', $request->penghuni_id);
        }

        // Filter overdue orders
        if ($request->has('overdue') && $request->overdue === 'true') {
            $query->where('tanggal_estimasi_selesai', '<', now())
                  ->whereNotIn('status_order', ['Selesai', 'Dibatalkan']);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'tanggal_terima');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $orders = $query->paginate($perPage);

        // Transform data
        $orders->getCollection()->transform(function ($order) {
            return [
                'id' => $order->id,
                'kode_order' => $order->kode_order,
                'penghuni' => [
                    'id' => $order->penghuni->id,
                    'nama_lengkap' => $order->penghuni->user->nama_lengkap,
                    'kamar' => $order->penghuni->kamar->kode_kamar,
                ],
                'jenis_layanan' => [
                    'id' => $order->jenisLayanan->id,
                    'nama_layanan' => $order->jenisLayanan->nama_layanan,
                    'harga_per_kg' => $order->jenisLayanan->harga_per_kg,
                ],
                'tanggal_terima' => $order->tanggal_terima,
                'tanggal_estimasi_selesai' => $order->tanggal_estimasi_selesai,
                'tanggal_selesai' => $order->tanggal_selesai,
                'berat_kg' => $order->berat_kg,
                'total_biaya' => $order->total_biaya,
                'status_order' => $order->status_order,
                'status_bayar' => $order->status_bayar,
                'progress_percentage' => $order->progress_percentage,
                'is_overdue' => $order->is_overdue,
                'next_status' => $order->getNextStatus(),
                'petugas_terima' => $order->petugasTerima ? $order->petugasTerima->nama_lengkap : null,
                'petugas_selesai' => $order->petugasSelesai ? $order->petugasSelesai->nama_lengkap : null,
                'catatan_order' => $order->catatan_order,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
            ];
        });

        return $this->paginatedResponse($orders, 'Data order laundry berhasil diambil');
    }

    /**
     * Store a newly created laundry order
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'penghuni_id' => 'required|exists:penghuni,id',
            'jenis_layanan_id' => 'required|exists:jenis_layanan_laundry,id',
            'berat_kg' => 'required|numeric|min:0.1|max:50',
            'catatan_order' => 'nullable|string',
            'foto_bukti_terima' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            // Get service details
            $jenisLayanan = JenisLayananLaundry::find($request->jenis_layanan_id);
            if (!$jenisLayanan->is_active) {
                return $this->errorResponse('Jenis layanan tidak aktif', 400);
            }

            // Calculate total cost
            $totalBiaya = $jenisLayanan->calculatePrice($request->berat_kg);

            // Calculate estimated completion date
            $estimasiSelesai = Carbon::parse($request->tanggal_terima ?? now())
                                   ->addDays($jenisLayanan->estimasi_hari);

            // Create order
            $order = OrderLaundry::create([
                'penghuni_id' => $request->penghuni_id,
                'jenis_layanan_id' => $request->jenis_layanan_id,
                'petugas_terima_id' => auth()->id(),
                'tanggal_terima' => $request->tanggal_terima ?? now(),
                'tanggal_estimasi_selesai' => $estimasiSelesai,
                'berat_kg' => $request->berat_kg,
                'total_biaya' => $totalBiaya,
                'status_order' => 'Diterima',
                'status_bayar' => 'Belum Dibayar',
                'catatan_order' => $request->catatan_order,
                'foto_bukti_terima' => $request->foto_bukti_terima,
            ]);

            // Log initial status
            $order->statusLog()->create([
                'status_sebelum' => null,
                'status_sesudah' => 'Diterima',
                'diubah_oleh' => auth()->id(),
                'catatan' => 'Order laundry dibuat',
            ]);

            // Create notification for penghuni
            $penghuni = Penghuni::with('user')->find($request->penghuni_id);
            $penghuni->user->notifikasi()->create([
                'judul' => 'Order Laundry Diterima',
                'isi_notifikasi' => "Order laundry Anda dengan kode {$order->kode_order} telah diterima dan sedang diproses.",
                'tipe_notifikasi' => 'Laundry',
                'link_terkait' => "/penghuni/laundry/{$order->id}",
            ]);

            DB::commit();

            $order->load(['penghuni.user', 'penghuni.kamar', 'jenisLayanan']);

            return $this->successResponse([
                'id' => $order->id,
                'kode_order' => $order->kode_order,
                'penghuni' => $order->penghuni->user->nama_lengkap,
                'jenis_layanan' => $order->jenisLayanan->nama_layanan,
                'berat_kg' => $order->berat_kg,
                'total_biaya' => $order->total_biaya,
                'status_order' => $order->status_order,
                'tanggal_estimasi_selesai' => $order->tanggal_estimasi_selesai,
            ], 'Order laundry berhasil dibuat', 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->serverErrorResponse('Gagal membuat order laundry: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified laundry order
     */
    public function show($id): JsonResponse
    {
        $order = OrderLaundry::with([
            'penghuni.user', 
            'penghuni.kamar', 
            'jenisLayanan', 
            'petugasTerima', 
            'petugasSelesai',
            'statusLog.user'
        ])->find($id);

        if (!$order) {
            return $this->notFoundResponse('Order laundry tidak ditemukan');
        }

        return $this->successResponse([
            'id' => $order->id,
            'kode_order' => $order->kode_order,
            'penghuni' => [
                'id' => $order->penghuni->id,
                'nama_lengkap' => $order->penghuni->user->nama_lengkap,
                'no_telp' => $order->penghuni->user->no_telp,
                'kamar' => [
                    'id' => $order->penghuni->kamar->id,
                    'kode_kamar' => $order->penghuni->kamar->kode_kamar,
                    'lantai' => $order->penghuni->kamar->lantai,
                ],
            ],
            'jenis_layanan' => [
                'id' => $order->jenisLayanan->id,
                'nama_layanan' => $order->jenisLayanan->nama_layanan,
                'harga_per_kg' => $order->jenisLayanan->harga_per_kg,
                'estimasi_hari' => $order->jenisLayanan->estimasi_hari,
                'deskripsi' => $order->jenisLayanan->deskripsi,
            ],
            'tanggal_terima' => $order->tanggal_terima,
            'tanggal_estimasi_selesai' => $order->tanggal_estimasi_selesai,
            'tanggal_selesai' => $order->tanggal_selesai,
            'berat_kg' => $order->berat_kg,
            'total_biaya' => $order->total_biaya,
            'status_order' => $order->status_order,
            'status_bayar' => $order->status_bayar,
            'progress_percentage' => $order->progress_percentage,
            'is_overdue' => $order->is_overdue,
            'next_status' => $order->getNextStatus(),
            'can_update_status' => $order->canUpdateToNextStatus(),
            'petugas_terima' => $order->petugasTerima ? [
                'id' => $order->petugasTerima->id,
                'nama_lengkap' => $order->petugasTerima->nama_lengkap,
            ] : null,
            'petugas_selesai' => $order->petugasSelesai ? [
                'id' => $order->petugasSelesai->id,
                'nama_lengkap' => $order->petugasSelesai->nama_lengkap,
            ] : null,
            'catatan_order' => $order->catatan_order,
            'foto_bukti_terima' => $order->foto_bukti_terima,
            'foto_bukti_selesai' => $order->foto_bukti_selesai,
            'status_history' => $order->statusLog->map(function ($log) {
                return [
                    'id' => $log->id,
                    'status_sebelum' => $log->status_sebelum,
                    'status_sesudah' => $log->status_sesudah,
                    'diubah_oleh' => $log->user->nama_lengkap,
                    'catatan' => $log->catatan,
                    'created_at' => $log->created_at,
                ];
            }),
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,
        ], 'Detail order laundry berhasil diambil');
    }

    /**
     * Update the specified laundry order
     */
    public function update(Request $request, $id): JsonResponse
    {
        $order = OrderLaundry::find($id);

        if (!$order) {
            return $this->notFoundResponse('Order laundry tidak ditemukan');
        }

        if ($order->status_order === 'Selesai') {
            return $this->errorResponse('Order yang sudah selesai tidak dapat diubah', 400);
        }

        $validator = Validator::make($request->all(), [
            'berat_kg' => 'sometimes|numeric|min:0.1|max:50',
            'catatan_order' => 'nullable|string',
            'status_bayar' => 'sometimes|in:Belum Dibayar,Sudah Dibayar',
            'foto_bukti_selesai' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            // Recalculate cost if weight changes
            if ($request->has('berat_kg') && $request->berat_kg != $order->berat_kg) {
                $jenisLayanan = $order->jenisLayanan;
                $order->total_biaya = $jenisLayanan->calculatePrice($request->berat_kg);
            }

            $order->update($request->only([
                'berat_kg', 'total_biaya', 'catatan_order', 'status_bayar', 'foto_bukti_selesai'
            ]));

            return $this->successResponse(
                $order->fresh(['penghuni.user', 'jenisLayanan']),
                'Order laundry berhasil diperbarui'
            );

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Gagal memperbarui order laundry: ' . $e->getMessage());
        }
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $order = OrderLaundry::find($id);

        if (!$order) {
            return $this->notFoundResponse('Order laundry tidak ditemukan');
        }

        $validator = Validator::make($request->all(), [
            'status_baru' => 'required|in:Dicuci,Dikeringkan,Disetrika,Siap Diambil,Selesai,Dibatalkan',
            'catatan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        if (!$order->canUpdateToNextStatus() && $request->status_baru !== 'Dibatalkan') {
            return $this->errorResponse('Status order tidak dapat diubah', 400);
        }

        try {
            $user = auth()->user();
            $order->updateStatus($request->status_baru, $user, $request->catatan);

            return $this->successResponse([
                'id' => $order->id,
                'kode_order' => $order->kode_order,
                'status_order' => $order->fresh()->status_order,
                'progress_percentage' => $order->fresh()->progress_percentage,
                'updated_at' => $order->fresh()->updated_at,
            ], 'Status order berhasil diperbarui');

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Gagal memperbarui status: ' . $e->getMessage());
        }
    }

    /**
     * Get orders by status
     */
    public function byStatus($status): JsonResponse
    {
        $orders = OrderLaundry::with(['penghuni.user', 'penghuni.kamar', 'jenisLayanan'])
                             ->where('status_order', $status)
                             ->orderBy('tanggal_terima', 'asc')
                             ->get()
                             ->map(function ($order) {
                                 return [
                                     'id' => $order->id,
                                     'kode_order' => $order->kode_order,
                                     'penghuni' => $order->penghuni->user->nama_lengkap,
                                     'kamar' => $order->penghuni->kamar->kode_kamar,
                                     'jenis_layanan' => $order->jenisLayanan->nama_layanan,
                                     'berat_kg' => $order->berat_kg,
                                     'total_biaya' => $order->total_biaya,
                                     'tanggal_terima' => $order->tanggal_terima,
                                     'tanggal_estimasi_selesai' => $order->tanggal_estimasi_selesai,
                                     'is_overdue' => $order->is_overdue,
                                 ];
                             });

        return $this->successResponse($orders, "Order dengan status {$status} berhasil diambil");
    }

    /**
     * Get orders by penghuni
     */
    public function byPenghuni($penghuniId): JsonResponse
    {
        $orders = OrderLaundry::with(['jenisLayanan', 'statusLog.user'])
                             ->where('penghuni_id', $penghuniId)
                             ->orderBy('tanggal_terima', 'desc')
                             ->get()
                             ->map(function ($order) {
                                 return [
                                     'id' => $order->id,
                                     'kode_order' => $order->kode_order,
                                     'jenis_layanan' => $order->jenisLayanan->nama_layanan,
                                     'berat_kg' => $order->berat_kg,
                                     'total_biaya' => $order->total_biaya,
                                     'status_order' => $order->status_order,
                                     'status_bayar' => $order->status_bayar,
                                     'tanggal_terima' => $order->tanggal_terima,
                                     'tanggal_selesai' => $order->tanggal_selesai,
                                     'progress_percentage' => $order->progress_percentage,
                                 ];
                             });

        return $this->successResponse($orders, 'Riwayat order penghuni berhasil diambil');
    }

    /**
     * Get laundry orders for authenticated penghuni
     */
    public function myLaundry(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->penghuni) {
            return $this->errorResponse('Data penghuni tidak ditemukan', 404);
        }

        $query = OrderLaundry::with(['jenisLayanan', 'statusLog.user'])
                            ->where('penghuni_id', $user->penghuni->id);

        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->whereNotIn('status_order', ['Selesai', 'Dibatalkan']);
            } else {
                $query->where('status_order', $request->status);
            }
        }

        $orders = $query->orderBy('tanggal_terima', 'desc')
                       ->paginate($request->get('per_page', 10));

        $orders->getCollection()->transform(function ($order) {
            return [
                'id' => $order->id,
                'kode_order' => $order->kode_order,
                'jenis_layanan' => [
                    'nama_layanan' => $order->jenisLayanan->nama_layanan,
                    'estimasi_hari' => $order->jenisLayanan->estimasi_hari,
                ],
                'berat_kg' => $order->berat_kg,
                'total_biaya' => $order->total_biaya,
                'status_order' => $order->status_order,
                'status_bayar' => $order->status_bayar,
                'progress_percentage' => $order->progress_percentage,
                'tanggal_terima' => $order->tanggal_terima,
                'tanggal_estimasi_selesai' => $order->tanggal_estimasi_selesai,
                'tanggal_selesai' => $order->tanggal_selesai,
                'is_overdue' => $order->is_overdue,
                'catatan_order' => $order->catatan_order,
            ];
        });

        return $this->paginatedResponse($orders, 'Riwayat laundry berhasil diambil');
    }

    /**
     * Create order for authenticated penghuni
     */
    public function createOrder(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->penghuni) {
            return $this->errorResponse('Data penghuni tidak ditemukan', 404);
        }

        $validator = Validator::make($request->all(), [
            'jenis_layanan_id' => 'required|exists:jenis_layanan_laundry,id',
            'berat_kg' => 'required|numeric|min:0.1|max:50',
            'catatan_order' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        // Check if penghuni has unpaid laundry orders
        $unpaidOrders = OrderLaundry::where('penghuni_id', $user->penghuni->id)
                                  ->where('status_bayar', 'Belum Dibayar')
                                  ->where('status_order', 'Selesai')
                                  ->count();

        if ($unpaidOrders > 3) {
            return $this->errorResponse('Anda memiliki terlalu banyak tagihan laundry yang belum dibayar', 400);
        }

        // Use the same logic as store method but set penghuni_id from auth
        $storeRequest = $request->merge(['penghuni_id' => $user->penghuni->id]);
        return $this->store($storeRequest);
    }

    /**
     * Get available laundry services
     */
    public function jenisLayanan(): JsonResponse
    {
        $services = JenisLayananLaundry::active()
                                     ->orderBy('nama_layanan')
                                     ->get()
                                     ->map(function ($service) {
                                         return [
                                             'id' => $service->id,
                                             'nama_layanan' => $service->nama_layanan,
                                             'harga_per_kg' => $service->harga_per_kg,
                                             'formatted_price' => $service->formatted_price,
                                             'estimasi_hari' => $service->estimasi_hari,
                                             'deskripsi' => $service->deskripsi,
                                         ];
                                     });

        return $this->successResponse($services, 'Jenis layanan laundry berhasil diambil');
    }
}
