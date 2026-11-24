<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for DimsumKu ERD v2.
     */
    public function up(): void
    {
        // 1. Settings (Pengguna & Sistem)
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('kunci')->unique();
            $table->text('nilai')->nullable();
            $table->timestamps();
        });

        // 2. Activity Logs (Pengguna & Sistem)
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('aksi');
            $table->string('tabel');
            $table->json('data_lama')->nullable();
            $table->json('data_baru')->nullable();
            $table->dateTime('waktu');
            $table->timestamps();
        });

        // 3. Backups (Pengguna & Sistem)
        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->dateTime('waktu');
            $table->integer('ukuran_kb')->default(0);
            $table->enum('jenis', ['otomatis', 'manual'])->default('manual');
            $table->enum('status', ['berhasil', 'gagal'])->default('berhasil');
            $table->string('file_path');
            $table->timestamps();
        });

        // 4. Suppliers (Produk & Pemasok)
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('kontak')->nullable();
            $table->timestamps();
        });

        // 5. Products (Produk & Pemasok)
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('kategori')->default('Dimsum Kukus');
            $table->string('satuan')->default('Porsi');
            $table->decimal('harga', 12, 2)->default(0);
            $table->integer('stok')->default(0);
            $table->integer('stok_minimum')->default(10);
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 6. Orders (Pesanan & Penjualan)
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique(); // contoh TRX-0001
            $table->dateTime('waktu');
            $table->foreignId('user_id')->constrained('users'); // Kasir
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('dibayar', 12, 2)->default(0);
            $table->decimal('kembalian', 12, 2)->default(0);
            $table->enum('status', ['baru', 'diproses', 'siap', 'selesai', 'batal'])->default('baru');
            $table->text('alasan_batal')->nullable();
            $table->timestamps();
        });

        // 7. Order Items (Pesanan & Penjualan)
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products');
            $table->integer('qty');
            $table->decimal('harga_satuan', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });

        // 8. Stock Movements (Stok & Produksi)
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->dateTime('tanggal');
            $table->enum('jenis', ['stok_awal', 'masuk', 'produksi', 'terjual', 'penyesuaian']);
            $table->integer('qty'); // positif = bertambah, negatif = berkurang
            $table->integer('saldo_setelah');
            $table->enum('sesi', ['pagi', 'sore'])->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('products');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('backups');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('settings');
    }
};

