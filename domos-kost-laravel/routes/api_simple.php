<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Simple API Routes for Testing
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

// Protected routes - simplified without role middleware for now
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::put('/change-password', [AuthController::class, 'changePassword']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
    });

    // Dashboard routes - no role middleware for testing
    Route::prefix('dashboard')->group(function () {
        Route::get('/admin/stats', [DashboardController::class, 'adminStats']);
        Route::get('/admin/activities', [DashboardController::class, 'recentActivities']);
        Route::get('/penghuni', [DashboardController::class, 'penghuniDashboard']);
        Route::get('/laundry', [DashboardController::class, 'laundryDashboard']);
    });

    // Basic test route
    Route::get('/test', function (Request $request) {
        return response()->json([
            'success' => true,
            'message' => 'API working correctly',
            'user' => $request->user()->only(['id', 'nama_lengkap', 'role']),
            'timestamp' => now()->toISOString(),
        ]);
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
            'test' => 'GET /api/test (authenticated)',
            'dashboard' => 'GET /api/dashboard/{admin|penghuni|laundry}',
        ]
    ], 404);
});
