<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PenghuniController;
use App\Http\Controllers\KamarController;
use App\Http\Controllers\TagihanController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\LaundryController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\LaporanController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'message' => 'Domos Kost API is running',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0',
    ]);
});

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::put('/change-password', [AuthController::class, 'changePassword']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
    });

    // Dashboard routes
    Route::prefix('dashboard')->group(function () {
        Route::get('/admin/stats', [DashboardController::class, 'adminStats'])
             ->middleware('role:Admin,Koordinator,Pengawas');
        Route::get('/admin/activities', [DashboardController::class, 'recentActivities'])
             ->middleware('role:Admin,Koordinator,Pengawas');
        Route::get('/penghuni', [DashboardController::class, 'penghuniDashboard'])
             ->middleware('role:Penghuni');
        Route::get('/laundry', [DashboardController::class, 'laundryDashboard'])
             ->middleware('role:Admin,Koordinator,Pengawas,Petugas Laundry');
    });

    // Admin & Staff routes
    Route::middleware('role:Admin,Koordinator,Pengawas')->group(function () {
        
        // Penghuni management
        Route::apiResource('penghuni', PenghuniController::class);
        Route::post('penghuni/{id}/activate', [PenghuniController::class, 'activate']);
        Route::post('penghuni/{id}/deactivate', [PenghuniController::class, 'deactivate']);
        
        // Kamar management
        Route::apiResource('kamar', KamarController::class);
        Route::get('kamar/status/summary', [KamarController::class, 'statusSummary']);
        
        // Tagihan management
        Route::apiResource('tagihan', TagihanController::class);
        Route::post('tagihan/generate/{bulan}/{tahun}', [TagihanController::class, 'generateBulanan']);
        Route::get('tagihan/penghuni/{penghuniId}', [TagihanController::class, 'byPenghuni']);
        
        // Pembayaran management
        Route::apiResource('pembayaran', PembayaranController::class);
        Route::post('pembayaran/{id}/verify', [PembayaranController::class, 'verify']);
        Route::post('pembayaran/{id}/reject', [PembayaranController::class, 'reject']);
        
        // Laporan
        Route::prefix('laporan')->group(function () {
            Route::get('/pendapatan', [LaporanController::class, 'pendapatan']);
            Route::get('/pendapatan/export', [LaporanController::class, 'exportPendapatan']);
            Route::get('/penghuni', [LaporanController::class, 'penghuni']);
            Route::get('/laundry', [LaporanController::class, 'laundry']);
        });

        // Notifikasi admin
        Route::post('notifikasi', [NotifikasiController::class, 'store']);
        Route::post('notifikasi/broadcast', [NotifikasiController::class, 'broadcast']);
    });

    // Laundry staff routes
    Route::middleware('role:Admin,Koordinator,Pengawas,Petugas Laundry')->group(function () {
        Route::prefix('laundry')->group(function () {
            Route::get('/', [LaundryController::class, 'index']);
            Route::post('/', [LaundryController::class, 'store']);
            Route::get('/{id}', [LaundryController::class, 'show']);
            Route::put('/{id}', [LaundryController::class, 'update']);
            Route::post('/{id}/update-status', [LaundryController::class, 'updateStatus']);
            Route::get('/penghuni/{penghuniId}', [LaundryController::class, 'byPenghuni']);
            Route::get('/status/{status}', [LaundryController::class, 'byStatus']);
        });
        
        Route::get('jenis-layanan', [LaundryController::class, 'jenisLayanan']);
    });

    // Penghuni routes
    Route::middleware('role:Penghuni')->group(function () {
        Route::prefix('my')->group(function () {
            Route::get('/tagihan', [TagihanController::class, 'myTagihan']);
            Route::get('/tagihan/{id}', [TagihanController::class, 'myTagihanDetail']);
            Route::post('/pembayaran', [PembayaranController::class, 'createPayment']);
            Route::get('/pembayaran', [PembayaranController::class, 'myPembayaran']);
            Route::get('/laundry', [LaundryController::class, 'myLaundry']);
            Route::post('/laundry', [LaundryController::class, 'createOrder']);
        });
    });

    // Notifikasi routes (all authenticated users)
    Route::prefix('notifikasi')->group(function () {
        Route::get('/', [NotifikasiController::class, 'index']);
        Route::get('/unread-count', [NotifikasiController::class, 'unreadCount']);
        Route::get('/latest', [NotifikasiController::class, 'latest']);
        Route::post('/{id}/mark-read', [NotifikasiController::class, 'markAsRead']);
        Route::post('/mark-all-read', [NotifikasiController::class, 'markAllAsRead']);
        Route::delete('/{id}', [NotifikasiController::class, 'destroy']);
    });
});

// Fallback route
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found',
        'available_endpoints' => [
            'health' => 'GET /api/health',
            'auth' => 'POST /api/auth/login',
            'dashboard' => 'GET /api/dashboard/{admin|penghuni|laundry}',
            'documentation' => 'https://docs.domoskost.com/api',
        ]
    ], 404);
});