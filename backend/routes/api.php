<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DapurController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\OwnerController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Login publik, dibatasi 5 percobaan/menit per username + IP (lihat AppServiceProvider)
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

// Daftar akun demo: hanya terdaftar saat APP_ENV=local, tanpa password (NFR: tidak ada
// endpoint publik yang menampilkan password atau data akun di lingkungan lain)
if (app()->environment('local')) {
    Route::get('/demo-accounts', function () {
        return response()->json([
            'success' => true,
            'message' => 'Daftar akun demo (hanya tersedia di lingkungan lokal).',
            'data' => User::where('is_active', true)
                ->orderBy('role')->orderBy('name')
                ->get(['name', 'username', 'role']),
        ]);
    });
}

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'message' => 'Data pengguna yang sedang login.',
            'data' => $user->only(['id', 'name', 'username', 'role']),
        ]);
    })->middleware('role:owner,kasir,dapur');

    // Owner API Endpoints (untuk Frontend Standalone)
    Route::middleware('role:owner')->prefix('owner')->group(function () {
        Route::get('/dashboard', [OwnerController::class, 'dashboard']);
        Route::get('/produk', [OwnerController::class, 'produk']);
        Route::post('/produk', [OwnerController::class, 'storeProduk']);
        Route::post('/produk/{product}', [OwnerController::class, 'updateProduk']);
        Route::delete('/produk/{product}', [OwnerController::class, 'destroyProduk']);
        Route::get('/produk/{product}/riwayat', [OwnerController::class, 'riwayatStok']);
        Route::get('/stok-masuk', [OwnerController::class, 'stokMasuk']);
        Route::post('/stok-masuk', [OwnerController::class, 'storeStokMasuk']);
        Route::get('/pemasok', [OwnerController::class, 'pemasok']);
        Route::post('/pemasok', [OwnerController::class, 'storePemasok']);
        Route::get('/laporan', [OwnerController::class, 'laporan']);
    });

    // Dapur API Endpoints (untuk Frontend Standalone)
    Route::middleware('role:dapur')->prefix('dapur')->group(function () {
        Route::get('/antrian', [DapurController::class, 'antrian']);
        Route::post('/antrian/{order}', [DapurController::class, 'updateStatusPesanan']);
        Route::get('/catat-produksi', [DapurController::class, 'catatProduksi']);
        Route::post('/catat-produksi', [DapurController::class, 'storeProduksi']);
        Route::get('/stok', [DapurController::class, 'stok']);
        Route::get('/riwayat-produksi', [DapurController::class, 'riwayatProduksi']);
    });

    // Kasir API Endpoints (untuk Frontend Standalone)
    Route::middleware('role:kasir')->prefix('kasir')->group(function () {
        Route::get('/transaksi', [KasirController::class, 'transaksi']);
        Route::post('/transaksi', [KasirController::class, 'storeTransaksi']);
        Route::get('/cek-stok', [KasirController::class, 'cekStok']);
        Route::get('/riwayat', [KasirController::class, 'riwayat']);
    });
});
