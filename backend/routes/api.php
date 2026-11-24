<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// API Auth Endpoints
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

// Demo credentials list for frontend convenience
Route::get('/demo-accounts', function () {
    return response()->json([
        'accounts' => [
            ['role' => 'Owner', 'username' => 'owner', 'password' => 'password123', 'name' => 'Madam Owner'],
            ['role' => 'Kasir', 'username' => 'kasir', 'password' => 'password123', 'name' => 'Siti Kasir'],
            ['role' => 'Dapur', 'username' => 'agus', 'password' => 'password123', 'name' => 'Agus Dapur'],
        ]
    ]);
});

// Owner API Endpoints (untuk Frontend Standalone)
Route::prefix('owner')->group(function () {
    Route::get('/dashboard', [OwnerController::class, 'dashboard']);
    Route::get('/produk', [OwnerController::class, 'produk']);
    Route::post('/produk', [OwnerController::class, 'storeProduk']);
    Route::get('/produk/{product}/riwayat', [OwnerController::class, 'riwayatStok']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

