<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotifikasiController extends Controller
{
    /**
     * Display a listing of notifications for authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $query = Notifikasi::where('user_id', $user->id);

        // Filter by read status
        if ($request->has('is_read')) {
            $isRead = $request->is_read === 'true' || $request->is_read === '1';
            $query->where('is_read', $isRead);
        }

        // Filter by type
        if ($request->has('tipe')) {
            $query->where('tipe_notifikasi', $request->tipe);
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('isi_notifikasi', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 20);
        $notifikasi = $query->paginate($perPage);

        // Transform data
        $notifikasi->getCollection()->transform(function ($item) {
            return [
                'id' => $item->id,
                'judul' => $item->judul,
                'isi_notifikasi' => $item->isi_notifikasi,
                'tipe_notifikasi' => $item->tipe_notifikasi,
                'is_read' => $item->is_read,
                'tanggal_baca' => $item->tanggal_baca,
                'link_terkait' => $item->link_terkait,
                'created_at' => $item->created_at,
                'time_ago' => $item->created_at->diffForHumans(),
            ];
        });

        return $this->paginatedResponse($notifikasi, 'Notifikasi berhasil diambil');
    }

    /**
     * Get unread notification count
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $count = Notifikasi::where('user_id', $user->id)
                          ->where('is_read', false)
                          ->count();

        // Get breakdown by type
        $breakdown = Notifikasi::where('user_id', $user->id)
                              ->where('is_read', false)
                              ->selectRaw('tipe_notifikasi, count(*) as count')
                              ->groupBy('tipe_notifikasi')
                              ->get()
                              ->mapWithKeys(function ($item) {
                                  return [$item->tipe_notifikasi => $item->count];
                              });

        return $this->successResponse([
            'total_unread' => $count,
            'breakdown' => $breakdown,
        ], 'Jumlah notifikasi belum dibaca berhasil diambil');
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id, Request $request): JsonResponse
    {
        $user = $request->user();
        
        $notifikasi = Notifikasi::where('id', $id)
                               ->where('user_id', $user->id)
                               ->first();

        if (!$notifikasi) {
            return $this->notFoundResponse('Notifikasi tidak ditemukan');
        }

        if ($notifikasi->is_read) {
            return $this->errorResponse('Notifikasi sudah dibaca', 400);
        }

        try {
            $notifikasi->markAsRead();

            return $this->successResponse([
                'id' => $notifikasi->id,
                'is_read' => true,
                'tanggal_baca' => $notifikasi->fresh()->tanggal_baca,
            ], 'Notifikasi berhasil ditandai sebagai dibaca');

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Gagal menandai notifikasi: ' . $e->getMessage());
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            $updated = Notifikasi::where('user_id', $user->id)
                                ->where('is_read', false)
                                ->update([
                                    'is_read' => true,
                                    'tanggal_baca' => now(),
                                ]);

            return $this->successResponse([
                'updated_count' => $updated,
            ], "Berhasil menandai {$updated} notifikasi sebagai dibaca");

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Gagal menandai semua notifikasi: ' . $e->getMessage());
        }
    }

    /**
     * Delete notification
     */
    public function destroy($id, Request $request): JsonResponse
    {
        $user = $request->user();
        
        $notifikasi = Notifikasi::where('id', $id)
                               ->where('user_id', $user->id)
                               ->first();

        if (!$notifikasi) {
            return $this->notFoundResponse('Notifikasi tidak ditemukan');
        }

        try {
            $notifikasi->delete();

            return $this->successResponse(null, 'Notifikasi berhasil dihapus');

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Gagal menghapus notifikasi: ' . $e->getMessage());
        }
    }

    /**
     * Get latest notifications (for real-time updates)
     */
    public function latest(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Get notifications from last 5 minutes or since last check
        $since = $request->get('since', now()->subMinutes(5)->toISOString());
        
        $notifikasi = Notifikasi::where('user_id', $user->id)
                               ->where('created_at', '>', $since)
                               ->orderBy('created_at', 'desc')
                               ->limit(10)
                               ->get()
                               ->map(function ($item) {
                                   return [
                                       'id' => $item->id,
                                       'judul' => $item->judul,
                                       'isi_notifikasi' => $item->isi_notifikasi,
                                       'tipe_notifikasi' => $item->tipe_notifikasi,
                                       'is_read' => $item->is_read,
                                       'link_terkait' => $item->link_terkait,
                                       'created_at' => $item->created_at,
                                       'time_ago' => $item->created_at->diffForHumans(),
                                   ];
                               });

        return $this->successResponse([
            'notifications' => $notifikasi,
            'count' => $notifikasi->count(),
            'timestamp' => now()->toISOString(),
        ], 'Notifikasi terbaru berhasil diambil');
    }

    /**
     * Create notification (for admin/system use)
     */
    public function store(Request $request): JsonResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'judul' => 'required|string|max:255',
            'isi_notifikasi' => 'required|string',
            'tipe_notifikasi' => 'required|in:Tagihan,Pembayaran,Laundry,Umum',
            'link_terkait' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $notifikasi = Notifikasi::create([
                'user_id' => $request->user_id,
                'judul' => $request->judul,
                'isi_notifikasi' => $request->isi_notifikasi,
                'tipe_notifikasi' => $request->tipe_notifikasi,
                'link_terkait' => $request->link_terkait,
                'is_read' => false,
            ]);

            return $this->successResponse([
                'id' => $notifikasi->id,
                'judul' => $notifikasi->judul,
                'tipe_notifikasi' => $notifikasi->tipe_notifikasi,
                'created_at' => $notifikasi->created_at,
            ], 'Notifikasi berhasil dibuat', 201);

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Gagal membuat notifikasi: ' . $e->getMessage());
        }
    }

    /**
     * Broadcast notification to multiple users
     */
    public function broadcast(Request $request): JsonResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'judul' => 'required|string|max:255',
            'isi_notifikasi' => 'required|string',
            'tipe_notifikasi' => 'required|in:Tagihan,Pembayaran,Laundry,Umum',
            'link_terkait' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $notifications = [];
            $timestamp = now();

            foreach ($request->user_ids as $userId) {
                $notifications[] = [
                    'user_id' => $userId,
                    'judul' => $request->judul,
                    'isi_notifikasi' => $request->isi_notifikasi,
                    'tipe_notifikasi' => $request->tipe_notifikasi,
                    'link_terkait' => $request->link_terkait,
                    'is_read' => false,
                    'created_at' => $timestamp,
                ];
            }

            Notifikasi::insert($notifications);

            return $this->successResponse([
                'sent_to' => count($request->user_ids),
                'timestamp' => $timestamp,
            ], 'Notifikasi berhasil dikirim ke ' . count($request->user_ids) . ' pengguna', 201);

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Gagal mengirim notifikasi broadcast: ' . $e->getMessage());
        }
    }
}
