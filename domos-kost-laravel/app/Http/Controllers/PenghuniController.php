<?php

namespace App\Http\Controllers;

use App\Models\Penghuni;
use App\Models\User;
use App\Models\Role;
use App\Models\Kamar;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PenghuniController extends Controller
{
    /**
     * Display a listing of penghuni
     */
    public function index(Request $request): JsonResponse
    {
        $query = Penghuni::with(['user', 'kamar']);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('no_ktp', 'like', "%{$search}%")
              ->orWhereHas('kamar', function ($q) use ($search) {
                  $q->where('kode_kamar', 'like', "%{$search}%");
              });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status_penghuni', $request->status);
        }

        // Filter by gender
        if ($request->has('jenis_kelamin')) {
            $query->where('jenis_kelamin', $request->jenis_kelamin);
        }

        // Filter by floor
        if ($request->has('lantai')) {
            $query->whereHas('kamar', function ($q) use ($request) {
                $q->where('lantai', $request->lantai);
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if ($sortBy === 'nama') {
            $query->join('users', 'penghuni.user_id', '=', 'users.id')
                  ->orderBy('users.nama_lengkap', $sortOrder)
                  ->select('penghuni.*');
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $penghuni = $query->paginate($perPage);

        // Transform data
        $penghuni->getCollection()->transform(function ($item) {
            return [
                'id' => $item->id,
                'user' => [
                    'id' => $item->user->id,
                    'username' => $item->user->username,
                    'email' => $item->user->email,
                    'nama_lengkap' => $item->user->nama_lengkap,
                    'no_telp' => $item->user->no_telp,
                    'is_active' => $item->user->is_active,
                ],
                'kamar' => $item->kamar ? [
                    'id' => $item->kamar->id,
                    'kode_kamar' => $item->kamar->kode_kamar,
                    'lantai' => $item->kamar->lantai,
                    'tipe_kamar' => $item->kamar->tipe_kamar,
                    'tarif_bulanan' => $item->kamar->tarif_bulanan,
                ] : null,
                'no_ktp' => $item->no_ktp,
                'tempat_lahir' => $item->tempat_lahir,
                'tanggal_lahir' => $item->tanggal_lahir,
                'jenis_kelamin' => $item->jenis_kelamin,
                'pekerjaan' => $item->pekerjaan,
                'tanggal_masuk' => $item->tanggal_masuk,
                'tanggal_keluar' => $item->tanggal_keluar,
                'status_penghuni' => $item->status_penghuni,
                'umur' => $item->umur,
                'lama_tinggal' => $item->lama_tinggal,
                'unpaid_bills_count' => $item->unpaid_bills_count,
                'active_laundry_count' => $item->active_laundry_count,
                'created_at' => $item->created_at,
            ];
        });

        return $this->paginatedResponse($penghuni, 'Data penghuni berhasil diambil');
    }

    /**
     * Store a newly created penghuni
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'no_telp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'kamar_id' => 'nullable|exists:kamar,id',
            'no_ktp' => 'required|string|unique:penghuni',
            'tempat_lahir' => 'nullable|string|max:50',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'pekerjaan' => 'nullable|string|max:100',
            'nama_kontak_darurat' => 'nullable|string|max:100',
            'telp_kontak_darurat' => 'nullable|string|max:20',
            'tanggal_masuk' => 'required|date',
            'status_penghuni' => 'in:Aktif,Non-Aktif,Pending',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        // Check if room is available
        if ($request->kamar_id) {
            $kamar = Kamar::find($request->kamar_id);
            if (!$kamar || !$kamar->isAvailable()) {
                return $this->errorResponse('Kamar tidak tersedia', 400);
            }
        }

        try {
            DB::beginTransaction();

            // Get penghuni role
            $penghuniRole = Role::where('nama_role', 'Penghuni')->first();
            if (!$penghuniRole) {
                return $this->errorResponse('Role penghuni tidak ditemukan', 400);
            }

            // Create user
            $user = User::create([
                'role_id' => $penghuniRole->id,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'nama_lengkap' => $request->nama_lengkap,
                'no_telp' => $request->no_telp,
                'alamat' => $request->alamat,
                'is_active' => true,
            ]);

            // Create penghuni
            $penghuni = Penghuni::create([
                'user_id' => $user->id,
                'kamar_id' => $request->kamar_id,
                'no_ktp' => $request->no_ktp,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'jenis_kelamin' => $request->jenis_kelamin,
                'pekerjaan' => $request->pekerjaan,
                'nama_kontak_darurat' => $request->nama_kontak_darurat,
                'telp_kontak_darurat' => $request->telp_kontak_darurat,
                'tanggal_masuk' => $request->tanggal_masuk,
                'status_penghuni' => $request->status_penghuni ?? 'Aktif',
            ]);

            // Update room status if assigned
            if ($request->kamar_id && $request->status_penghuni === 'Aktif') {
                Kamar::where('id', $request->kamar_id)->update(['status_kamar' => 'Terisi']);
            }

            DB::commit();

            $penghuni->load(['user', 'kamar']);

            return $this->successResponse([
                'id' => $penghuni->id,
                'user' => [
                    'id' => $penghuni->user->id,
                    'username' => $penghuni->user->username,
                    'email' => $penghuni->user->email,
                    'nama_lengkap' => $penghuni->user->nama_lengkap,
                ],
                'kamar' => $penghuni->kamar ? [
                    'id' => $penghuni->kamar->id,
                    'kode_kamar' => $penghuni->kamar->kode_kamar,
                ] : null,
                'status_penghuni' => $penghuni->status_penghuni,
            ], 'Penghuni berhasil ditambahkan', 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->serverErrorResponse('Gagal menambahkan penghuni: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified penghuni
     */
    public function show($id): JsonResponse
    {
        $penghuni = Penghuni::with(['user', 'kamar', 'tagihan' => function ($query) {
            $query->latest()->take(5);
        }, 'orderLaundry' => function ($query) {
            $query->latest()->take(5);
        }])->find($id);

        if (!$penghuni) {
            return $this->notFoundResponse('Penghuni tidak ditemukan');
        }

        return $this->successResponse([
            'id' => $penghuni->id,
            'user' => [
                'id' => $penghuni->user->id,
                'username' => $penghuni->user->username,
                'email' => $penghuni->user->email,
                'nama_lengkap' => $penghuni->user->nama_lengkap,
                'no_telp' => $penghuni->user->no_telp,
                'alamat' => $penghuni->user->alamat,
                'is_active' => $penghuni->user->is_active,
                'last_login' => $penghuni->user->last_login,
            ],
            'kamar' => $penghuni->kamar ? [
                'id' => $penghuni->kamar->id,
                'kode_kamar' => $penghuni->kamar->kode_kamar,
                'lantai' => $penghuni->kamar->lantai,
                'tipe_kamar' => $penghuni->kamar->tipe_kamar,
                'tarif_bulanan' => $penghuni->kamar->tarif_bulanan,
                'fasilitas' => $penghuni->kamar->fasilitas,
            ] : null,
            'no_ktp' => $penghuni->no_ktp,
            'tempat_lahir' => $penghuni->tempat_lahir,
            'tanggal_lahir' => $penghuni->tanggal_lahir,
            'jenis_kelamin' => $penghuni->jenis_kelamin,
            'pekerjaan' => $penghuni->pekerjaan,
            'nama_kontak_darurat' => $penghuni->nama_kontak_darurat,
            'telp_kontak_darurat' => $penghuni->telp_kontak_darurat,
            'tanggal_masuk' => $penghuni->tanggal_masuk,
            'tanggal_keluar' => $penghuni->tanggal_keluar,
            'status_penghuni' => $penghuni->status_penghuni,
            'umur' => $penghuni->umur,
            'lama_tinggal' => $penghuni->lama_tinggal,
            'foto_ktp' => $penghuni->foto_ktp,
            'catatan' => $penghuni->catatan,
            'unpaid_bills_count' => $penghuni->unpaid_bills_count,
            'active_laundry_count' => $penghuni->active_laundry_count,
            'recent_bills' => $penghuni->tagihan->map(function ($tagihan) {
                return [
                    'id' => $tagihan->id,
                    'periode' => $tagihan->periode_string,
                    'total' => $tagihan->total_tagihan,
                    'status' => $tagihan->status_tagihan,
                    'jatuh_tempo' => $tagihan->tanggal_jatuh_tempo,
                ];
            }),
            'recent_laundry' => $penghuni->orderLaundry->map(function ($order) {
                return [
                    'id' => $order->id,
                    'kode_order' => $order->kode_order,
                    'status' => $order->status_order,
                    'tanggal_terima' => $order->tanggal_terima,
                    'total_biaya' => $order->total_biaya,
                ];
            }),
            'created_at' => $penghuni->created_at,
            'updated_at' => $penghuni->updated_at,
        ], 'Detail penghuni berhasil diambil');
    }

    /**
     * Update the specified penghuni
     */
    public function update(Request $request, $id): JsonResponse
    {
        $penghuni = Penghuni::with('user')->find($id);

        if (!$penghuni) {
            return $this->notFoundResponse('Penghuni tidak ditemukan');
        }

        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'sometimes|string|max:100',
            'username' => 'sometimes|string|max:50|unique:users,username,' . $penghuni->user->id,
            'email' => 'sometimes|email|unique:users,email,' . $penghuni->user->id,
            'no_telp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'kamar_id' => 'nullable|exists:kamar,id',
            'no_ktp' => 'sometimes|string|unique:penghuni,no_ktp,' . $penghuni->id,
            'tempat_lahir' => 'nullable|string|max:50',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'sometimes|in:Laki-laki,Perempuan',
            'pekerjaan' => 'nullable|string|max:100',
            'nama_kontak_darurat' => 'nullable|string|max:100',
            'telp_kontak_darurat' => 'nullable|string|max:20',
            'tanggal_masuk' => 'sometimes|date',
            'tanggal_keluar' => 'nullable|date|after:tanggal_masuk',
            'status_penghuni' => 'sometimes|in:Aktif,Non-Aktif,Pending',
            'catatan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            $oldKamarId = $penghuni->kamar_id;
            $newKamarId = $request->kamar_id;

            // Update user data
            $penghuni->user->update($request->only([
                'nama_lengkap', 'username', 'email', 'no_telp', 'alamat'
            ]));

            // Update penghuni data
            $penghuni->update($request->only([
                'kamar_id', 'no_ktp', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin',
                'pekerjaan', 'nama_kontak_darurat', 'telp_kontak_darurat',
                'tanggal_masuk', 'tanggal_keluar', 'status_penghuni', 'catatan'
            ]));

            // Handle room changes
            if ($oldKamarId !== $newKamarId) {
                // Free old room
                if ($oldKamarId) {
                    $hasOtherOccupant = Penghuni::where('kamar_id', $oldKamarId)
                                               ->where('id', '!=', $penghuni->id)
                                               ->where('status_penghuni', 'Aktif')
                                               ->exists();
                    if (!$hasOtherOccupant) {
                        Kamar::where('id', $oldKamarId)->update(['status_kamar' => 'Tersedia']);
                    }
                }

                // Occupy new room
                if ($newKamarId && $penghuni->status_penghuni === 'Aktif') {
                    $newKamar = Kamar::find($newKamarId);
                    if (!$newKamar->isAvailable()) {
                        DB::rollBack();
                        return $this->errorResponse('Kamar tidak tersedia', 400);
                    }
                    $newKamar->update(['status_kamar' => 'Terisi']);
                }
            }

            DB::commit();

            return $this->successResponse(
                $penghuni->fresh(['user', 'kamar']),
                'Penghuni berhasil diperbarui'
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->serverErrorResponse('Gagal memperbarui penghuni: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified penghuni
     */
    public function destroy($id): JsonResponse
    {
        $penghuni = Penghuni::with('user')->find($id);

        if (!$penghuni) {
            return $this->notFoundResponse('Penghuni tidak ditemukan');
        }

        // Check if penghuni has unpaid bills
        if ($penghuni->unpaid_bills_count > 0) {
            return $this->errorResponse('Tidak dapat menghapus penghuni yang memiliki tagihan belum dibayar', 400);
        }

        try {
            DB::beginTransaction();

            $kamarId = $penghuni->kamar_id;

            // Soft delete: set status to non-aktif instead of hard delete
            $penghuni->update([
                'status_penghuni' => 'Non-Aktif',
                'tanggal_keluar' => now(),
            ]);

            $penghuni->user->update(['is_active' => false]);

            // Free room if no other active occupant
            if ($kamarId) {
                $hasOtherOccupant = Penghuni::where('kamar_id', $kamarId)
                                           ->where('id', '!=', $penghuni->id)
                                           ->where('status_penghuni', 'Aktif')
                                           ->exists();
                if (!$hasOtherOccupant) {
                    Kamar::where('id', $kamarId)->update(['status_kamar' => 'Tersedia']);
                }
            }

            DB::commit();

            return $this->successResponse(null, 'Penghuni berhasil dinonaktifkan');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->serverErrorResponse('Gagal menghapus penghuni: ' . $e->getMessage());
        }
    }

    /**
     * Activate penghuni
     */
    public function activate($id): JsonResponse
    {
        $penghuni = Penghuni::with('user')->find($id);

        if (!$penghuni) {
            return $this->notFoundResponse('Penghuni tidak ditemukan');
        }

        $penghuni->update(['status_penghuni' => 'Aktif']);
        $penghuni->user->update(['is_active' => true]);

        // Update room status if assigned
        if ($penghuni->kamar_id) {
            Kamar::where('id', $penghuni->kamar_id)->update(['status_kamar' => 'Terisi']);
        }

        return $this->successResponse(null, 'Penghuni berhasil diaktifkan');
    }

    /**
     * Deactivate penghuni
     */
    public function deactivate($id): JsonResponse
    {
        $penghuni = Penghuni::with('user')->find($id);

        if (!$penghuni) {
            return $this->notFoundResponse('Penghuni tidak ditemukan');
        }

        $penghuni->update(['status_penghuni' => 'Non-Aktif']);
        $penghuni->user->update(['is_active' => false]);

        // Free room if no other active occupant
        if ($penghuni->kamar_id) {
            $hasOtherOccupant = Penghuni::where('kamar_id', $penghuni->kamar_id)
                                       ->where('id', '!=', $penghuni->id)
                                       ->where('status_penghuni', 'Aktif')
                                       ->exists();
            if (!$hasOtherOccupant) {
                Kamar::where('id', $penghuni->kamar_id)->update(['status_kamar' => 'Tersedia']);
            }
        }

        return $this->successResponse(null, 'Penghuni berhasil dinonaktifkan');
    }
}
