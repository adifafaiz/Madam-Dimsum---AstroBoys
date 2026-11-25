<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DapurController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\OwnerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Halaman Khusus Owner (Mockup Frame 1 s.d. 9)
Route::middleware('auth')->prefix('owner')->group(function () {
    Route::get('/dashboard', [OwnerController::class, 'dashboard'])->name('owner.dashboard');
    Route::get('/produk', [OwnerController::class, 'produk'])->name('owner.produk');
    Route::post('/produk', [OwnerController::class, 'storeProduk'])->name('owner.produk.store');
    Route::post('/produk/{product}', [OwnerController::class, 'updateProduk'])->name('owner.produk.update');
    Route::post('/produk/{product}/delete', [OwnerController::class, 'destroyProduk'])->name('owner.produk.delete');
    Route::delete('/produk/{product}', [OwnerController::class, 'destroyProduk']);
    Route::get('/produk/{product}/riwayat', [OwnerController::class, 'riwayatStok'])->name('owner.produk.riwayat');

    // Frame 4: Catat Stok Masuk
    Route::get('/stok-masuk', [OwnerController::class, 'stokMasuk'])->name('owner.stok-masuk');
    Route::post('/stok-masuk', [OwnerController::class, 'storeStokMasuk'])->name('owner.stok-masuk.store');

    // Frame 5: Pemasok & Kebutuhan Stok
    Route::get('/pemasok', [OwnerController::class, 'pemasok'])->name('owner.pemasok');
    Route::post('/pemasok', [OwnerController::class, 'storePemasok'])->name('owner.pemasok.store');

    // Frame 6: Laporan Penjualan & Ekspor
    Route::get('/laporan', [OwnerController::class, 'laporan'])->name('owner.laporan');
    Route::get('/laporan/export-excel', [OwnerController::class, 'exportExcel'])->name('owner.laporan.excel');

    // Frame 7: Riwayat Transaksi & Pembatalan
    Route::get('/riwayat-transaksi', [OwnerController::class, 'riwayatTransaksi'])->name('owner.riwayat-transaksi');
    Route::post('/transaksi/{order}/batal', [OwnerController::class, 'batalkanTransaksi'])->name('owner.transaksi.batal');

    // Frame 8: Pengguna & Hak Akses
    Route::get('/pengguna', [OwnerController::class, 'pengguna'])->name('owner.pengguna');
    Route::post('/pengguna', [OwnerController::class, 'storePengguna'])->name('owner.pengguna.store');
    Route::post('/pengguna/{user}/toggle', [OwnerController::class, 'toggleStatusPengguna'])->name('owner.pengguna.toggle');
    Route::post('/pengguna/{user}/reset', [OwnerController::class, 'resetPasswordPengguna'])->name('owner.pengguna.reset');

    // Frame 9: Pencadangan Data
    Route::get('/pencadangan', [OwnerController::class, 'pencadangan'])->name('owner.pencadangan');
    Route::post('/pencadangan', [OwnerController::class, 'createBackup'])->name('owner.pencadangan.store');
    Route::post('/pencadangan/schedule', [OwnerController::class, 'updateBackupSchedule'])->name('owner.pencadangan.schedule');
});

// Halaman Khusus Dapur (Mockup Dapur Frame 1 s.d. 4)
Route::middleware('auth')->prefix('dapur')->group(function () {
    // Frame 1: Antrian Pesanan
    Route::get('/antrian', [DapurController::class, 'antrian'])->name('dapur.antrian');
    Route::post('/antrian/{order}', [DapurController::class, 'updateStatusPesanan'])->name('dapur.antrian.update');

    // Frame 2: Catat Produksi
    Route::get('/catat-produksi', [DapurController::class, 'catatProduksi'])->name('dapur.catat-produksi');
    Route::post('/catat-produksi', [DapurController::class, 'storeProduksi'])->name('dapur.catat-produksi.store');

    // Frame 3: Stok Produk Dapur
    Route::get('/stok', [DapurController::class, 'stok'])->name('dapur.stok');

    // Frame 4: Riwayat Produksi
    Route::get('/riwayat-produksi', [DapurController::class, 'riwayatProduksi'])->name('dapur.riwayat-produksi');
});

// Halaman Khusus Kasir (Mockup Kasir Frame 1 s.d. 4)
Route::middleware('auth')->prefix('kasir')->group(function () {
    // Frame 1 & 2: Pencatatan Transaksi & Modal Sukses
    Route::get('/transaksi', [KasirController::class, 'transaksi'])->name('kasir.transaksi');
    Route::post('/transaksi', [KasirController::class, 'storeTransaksi'])->name('kasir.transaksi.store');

    // Frame 3: Cek Stok Kasir
    Route::get('/cek-stok', [KasirController::class, 'cekStok'])->name('kasir.cek-stok');

    // Frame 4: Riwayat Transaksi Saya
    Route::get('/riwayat', [KasirController::class, 'riwayat'])->name('kasir.riwayat');

    // Cetak & Cetak Ulang Struk
    Route::get('/struk/{order}', [KasirController::class, 'cetakStruk'])->name('kasir.struk');
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
    if (strtolower($role) === 'dapur') {
        return redirect()->route('dapur.antrian');
    }
    if (strtolower($role) === 'kasir') {
        return redirect()->route('kasir.transaksi');
    }
    return app(AuthController::class)->dashboard(request(), $role);
})->middleware('auth')->name('dashboard');
