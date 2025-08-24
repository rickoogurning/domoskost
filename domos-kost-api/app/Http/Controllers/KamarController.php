<?php

namespace App\Http\Controllers;

use App\Models\Kamar;
use App\Models\Penghuni;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class KamarController extends Controller
{
    /**
     * Display a listing of kamar
     */
    public function index(Request $request): JsonResponse
    {
        $query = Kamar::with(['penghuniAktif.user']);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_kamar', 'like', "%{$search}%")
                  ->orWhere('tipe_kamar', 'like', "%{$search}%")
                  ->orWhere('fasilitas', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status_kamar', $request->status);
        }

        // Filter by floor
        if ($request->has('lantai')) {
            $query->where('lantai', $request->lantai);
        }

        // Filter by type
        if ($request->has('tipe')) {
            $query->where('tipe_kamar', $request->tipe);
        }

        // Filter by price range
        if ($request->has('min_price')) {
            $query->where('tarif_bulanan', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('tarif_bulanan', '<=', $request->max_price);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'kode_kamar');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $kamar = $query->paginate($perPage);

        // Transform data
        $kamar->getCollection()->transform(function ($item) {
            return [
                'id' => $item->id,
                'kode_kamar' => $item->kode_kamar,
                'lantai' => $item->lantai,
                'tipe_kamar' => $item->tipe_kamar,
                'tarif_bulanan' => $item->tarif_bulanan,
                'formatted_price' => $item->formatted_price,
                'fasilitas' => $item->fasilitas,
                'status_kamar' => $item->status_kamar,
                'foto_kamar' => $item->foto_kamar,
                'penghuni' => $item->penghuniAktif ? [
                    'id' => $item->penghuniAktif->id,
                    'nama_lengkap' => $item->penghuniAktif->user->nama_lengkap,
                    'tanggal_masuk' => $item->penghuniAktif->tanggal_masuk,
                ] : null,
                'is_available' => $item->isAvailable(),
                'is_occupied' => $item->isOccupied(),
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];
        });

        return $this->paginatedResponse($kamar, 'Data kamar berhasil diambil');
    }

    /**
     * Store a newly created kamar
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'kode_kamar' => 'required|string|max:10|unique:kamar',
            'lantai' => 'required|integer|min:1',
            'tipe_kamar' => 'required|in:Single,Double,Triple',
            'tarif_bulanan' => 'required|numeric|min:0',
            'fasilitas' => 'nullable|string',
            'foto_kamar' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $kamar = Kamar::create([
                'kode_kamar' => $request->kode_kamar,
                'lantai' => $request->lantai,
                'tipe_kamar' => $request->tipe_kamar,
                'tarif_bulanan' => $request->tarif_bulanan,
                'fasilitas' => $request->fasilitas,
                'foto_kamar' => $request->foto_kamar,
                'status_kamar' => 'Tersedia',
            ]);

            return $this->successResponse([
                'id' => $kamar->id,
                'kode_kamar' => $kamar->kode_kamar,
                'lantai' => $kamar->lantai,
                'tipe_kamar' => $kamar->tipe_kamar,
                'tarif_bulanan' => $kamar->tarif_bulanan,
                'status_kamar' => $kamar->status_kamar,
            ], 'Kamar berhasil ditambahkan', 201);

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Gagal menambahkan kamar: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified kamar
     */
    public function show($id): JsonResponse
    {
        $kamar = Kamar::with(['penghuni.user', 'penghuni' => function ($query) {
            $query->latest();
        }])->find($id);

        if (!$kamar) {
            return $this->notFoundResponse('Kamar tidak ditemukan');
        }

        return $this->successResponse([
            'id' => $kamar->id,
            'kode_kamar' => $kamar->kode_kamar,
            'lantai' => $kamar->lantai,
            'tipe_kamar' => $kamar->tipe_kamar,
            'tarif_bulanan' => $kamar->tarif_bulanan,
            'formatted_price' => $kamar->formatted_price,
            'fasilitas' => $kamar->fasilitas,
            'status_kamar' => $kamar->status_kamar,
            'foto_kamar' => $kamar->foto_kamar,
            'is_available' => $kamar->isAvailable(),
            'is_occupied' => $kamar->isOccupied(),
            'penghuni_history' => $kamar->penghuni->map(function ($penghuni) {
                return [
                    'id' => $penghuni->id,
                    'nama_lengkap' => $penghuni->user->nama_lengkap,
                    'tanggal_masuk' => $penghuni->tanggal_masuk,
                    'tanggal_keluar' => $penghuni->tanggal_keluar,
                    'status_penghuni' => $penghuni->status_penghuni,
                    'lama_tinggal' => $penghuni->lama_tinggal,
                ];
            }),
            'current_penghuni' => $kamar->penghuniAktif ? [
                'id' => $kamar->penghuniAktif->id,
                'nama_lengkap' => $kamar->penghuniAktif->user->nama_lengkap,
                'email' => $kamar->penghuniAktif->user->email,
                'no_telp' => $kamar->penghuniAktif->user->no_telp,
                'tanggal_masuk' => $kamar->penghuniAktif->tanggal_masuk,
                'pekerjaan' => $kamar->penghuniAktif->pekerjaan,
                'lama_tinggal' => $kamar->penghuniAktif->lama_tinggal,
            ] : null,
            'created_at' => $kamar->created_at,
            'updated_at' => $kamar->updated_at,
        ], 'Detail kamar berhasil diambil');
    }

    /**
     * Update the specified kamar
     */
    public function update(Request $request, $id): JsonResponse
    {
        $kamar = Kamar::find($id);

        if (!$kamar) {
            return $this->notFoundResponse('Kamar tidak ditemukan');
        }

        $validator = Validator::make($request->all(), [
            'kode_kamar' => 'sometimes|string|max:10|unique:kamar,kode_kamar,' . $id,
            'lantai' => 'sometimes|integer|min:1',
            'tipe_kamar' => 'sometimes|in:Single,Double,Triple',
            'tarif_bulanan' => 'sometimes|numeric|min:0',
            'fasilitas' => 'nullable|string',
            'status_kamar' => 'sometimes|in:Tersedia,Terisi,Maintenance',
            'foto_kamar' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            // Check if trying to set status to 'Tersedia' when occupied
            if ($request->status_kamar === 'Tersedia' && $kamar->isOccupied()) {
                $hasActivePenghuni = Penghuni::where('kamar_id', $id)
                                           ->where('status_penghuni', 'Aktif')
                                           ->exists();
                if ($hasActivePenghuni) {
                    return $this->errorResponse('Tidak dapat mengubah status kamar menjadi tersedia saat masih ada penghuni aktif', 400);
                }
            }

            $kamar->update($request->only([
                'kode_kamar', 'lantai', 'tipe_kamar', 'tarif_bulanan',
                'fasilitas', 'status_kamar', 'foto_kamar'
            ]));

            return $this->successResponse(
                $kamar->fresh(),
                'Kamar berhasil diperbarui'
            );

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Gagal memperbarui kamar: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified kamar
     */
    public function destroy($id): JsonResponse
    {
        $kamar = Kamar::find($id);

        if (!$kamar) {
            return $this->notFoundResponse('Kamar tidak ditemukan');
        }

        // Check if kamar is occupied
        if ($kamar->isOccupied()) {
            return $this->errorResponse('Tidak dapat menghapus kamar yang sedang dihuni', 400);
        }

        // Check if kamar has historical data
        $hasPenghuni = Penghuni::where('kamar_id', $id)->exists();
        if ($hasPenghuni) {
            return $this->errorResponse('Tidak dapat menghapus kamar yang memiliki riwayat penghuni', 400);
        }

        try {
            $kamar->delete();

            return $this->successResponse(null, 'Kamar berhasil dihapus');

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Gagal menghapus kamar: ' . $e->getMessage());
        }
    }

    /**
     * Get room status summary
     */
    public function statusSummary(): JsonResponse
    {
        $summary = [
            'total' => Kamar::count(),
            'tersedia' => Kamar::where('status_kamar', 'Tersedia')->count(),
            'terisi' => Kamar::where('status_kamar', 'Terisi')->count(),
            'maintenance' => Kamar::where('status_kamar', 'Maintenance')->count(),
        ];

        $summary['occupancy_rate'] = $summary['total'] > 0 
            ? round(($summary['terisi'] / $summary['total']) * 100, 2) 
            : 0;

        // Get breakdown by floor
        $byFloor = Kamar::selectRaw('lantai, status_kamar, count(*) as count')
                       ->groupBy('lantai', 'status_kamar')
                       ->orderBy('lantai')
                       ->get()
                       ->groupBy('lantai')
                       ->map(function ($floor) {
                           return $floor->mapWithKeys(function ($item) {
                               return [$item->status_kamar => $item->count];
                           })->toArray();
                       });

        // Get breakdown by type
        $byType = Kamar::selectRaw('tipe_kamar, status_kamar, count(*) as count')
                      ->groupBy('tipe_kamar', 'status_kamar')
                      ->orderBy('tipe_kamar')
                      ->get()
                      ->groupBy('tipe_kamar')
                      ->map(function ($type) {
                          return $type->mapWithKeys(function ($item) {
                              return [$item->status_kamar => $item->count];
                          })->toArray();
                      });

        // Get available rooms list
        $availableRooms = Kamar::where('status_kamar', 'Tersedia')
                              ->orderBy('lantai')
                              ->orderBy('kode_kamar')
                              ->get(['id', 'kode_kamar', 'lantai', 'tipe_kamar', 'tarif_bulanan'])
                              ->map(function ($kamar) {
                                  return [
                                      'id' => $kamar->id,
                                      'kode_kamar' => $kamar->kode_kamar,
                                      'lantai' => $kamar->lantai,
                                      'tipe_kamar' => $kamar->tipe_kamar,
                                      'tarif_bulanan' => $kamar->tarif_bulanan,
                                      'formatted_price' => $kamar->formatted_price,
                                  ];
                              });

        return $this->successResponse([
            'summary' => $summary,
            'breakdown' => [
                'by_floor' => $byFloor,
                'by_type' => $byType,
            ],
            'available_rooms' => $availableRooms,
        ], 'Ringkasan status kamar berhasil diambil');
    }
}
