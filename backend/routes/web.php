<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OwnerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Halaman Khusus Owner (Mockup Frame 1, 2, 3)
Route::middleware('auth')->prefix('owner')->group(function () {
    Route::get('/dashboard', [OwnerController::class, 'dashboard'])->name('owner.dashboard');
    Route::get('/produk', [OwnerController::class, 'produk'])->name('owner.produk');
    Route::post('/produk', [OwnerController::class, 'storeProduk'])->name('owner.produk.store');
    Route::get('/produk/{product}/riwayat', [OwnerController::class, 'riwayatStok'])->name('owner.produk.riwayat');
});

// Redirect dashboard role umum
Route::get('/dashboard/{role}', function ($role) {
    $user = Auth::user();
    if ($user && strtolower($user->role) !== strtolower($role)) {
        return response()->view('errors.akses-ditolak', [
            'userRole' => strtolower($user->role),
            'attemptedRole' => strtolower($role),
        ], 403);
    }

    if (strtolower($role) === 'owner') {
        return redirect()->route('owner.dashboard');
    }
    return app(AuthController::class)->dashboard(request(), $role);
})->middleware('auth')->name('dashboard');
