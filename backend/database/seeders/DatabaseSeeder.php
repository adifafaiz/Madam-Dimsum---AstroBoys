<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database based on PRD Final DimsumKu v2.
     */
    public function run(): void
    {
        // 1. Akun Pengguna (Users)
        User::updateOrCreate(
            ['username' => 'owner'],
            [
                'name' => 'Madam Owner',
                'email' => 'owner@madamdimsum.com',
                'role' => 'owner',
                'password' => bcrypt('password123'),
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['username' => 'kasir'],
            [
                'name' => 'Siti Kasir',
                'email' => 'kasir@madamdimsum.com',
                'role' => 'kasir',
                'password' => bcrypt('password123'),
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['username' => 'agus'],
            [
                'name' => 'Agus Dapur',
                'email' => 'agus@madamdimsum.com',
                'role' => 'dapur',
                'password' => bcrypt('password123'),
                'is_active' => true,
            ]
        );

        // Akun Nonaktif untuk UAT-10 (Dani Kasir dinonaktifkan)
        User::updateOrCreate(
            ['username' => 'dani'],
            [
                'name' => 'Dani Kasir (Nonaktif)',
                'email' => 'dani@madamdimsum.com',
                'role' => 'kasir',
                'password' => bcrypt('password123'),
                'is_active' => false,
            ]
        );

        // 2. Pemasok (Suppliers)
        $pemasokA = Supplier::updateOrCreate(
            ['nama' => 'Pemasok Dimsum Jaya'],
            ['kontak' => '081234567890']
        );

        $pemasokB = Supplier::updateOrCreate(
            ['nama' => 'Pemasok Kemasan Astro'],
            ['kontak' => '089876543210']
        );

        // 3. Produk Contoh dari PRD UAT (Products)
        Product::updateOrCreate(
            ['nama' => 'Siomay Ayam'],
            [
                'kategori' => 'Dimsum Kukus',
                'satuan' => 'Porsi',
                'harga' => 24000.00,
                'stok' => 48,
                'stok_minimum' => 10,
                'supplier_id' => $pemasokA->id,
                'is_active' => true,
            ]
        );

        Product::updateOrCreate(
            ['nama' => 'Hakau Udang'],
            [
                'kategori' => 'Dimsum Kukus',
                'satuan' => 'Porsi',
                'harga' => 28000.00,
                'stok' => 4, // Menipis (< stok_minimum 10)
                'stok_minimum' => 10,
                'supplier_id' => $pemasokA->id,
                'is_active' => true,
            ]
        );

        Product::updateOrCreate(
            ['nama' => 'Bakpao Coklat'],
            [
                'kategori' => 'Bakpao',
                'satuan' => 'Porsi',
                'harga' => 20000.00,
                'stok' => 6, // Menipis (< stok_minimum 10)
                'stok_minimum' => 10,
                'supplier_id' => $pemasokA->id,
                'is_active' => true,
            ]
        );
    }
}
