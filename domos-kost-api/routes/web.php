<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return response()->json([
        'app' => 'Domos Kost API',
        'version' => '1.0.0',
        'description' => 'Backend API for Domos Kost Group Management System',
        'status' => 'running',
        'timestamp' => now()->toISOString(),
        'endpoints' => [
            'api' => '/api',
            'health' => '/api/health',
            'docs' => '/docs',
        ]
    ]);
});
