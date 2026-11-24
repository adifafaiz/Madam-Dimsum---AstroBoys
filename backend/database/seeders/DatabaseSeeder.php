<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database based on PRD Final DimsumKu v2.
     */
    public function run(): void
    {
        // 1. Akun Pengguna (Users)
        $owner = User::updateOrCreate(
            ['username' => 'owner'],
            [
                'name' => 'Madam Owner',
                'email' => 'owner@madamdimsum.com',
                'role' => 'owner',
                'password' => bcrypt('password123'),
                'is_active' => true,
            ]
        );

        $rina = User::updateOrCreate(
            ['username' => 'rina'],
            [
                'name' => 'Rina',
                'email' => 'rina@madamdimsum.com',
                'role' => 'kasir',
                'password' => bcrypt('password123'),
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['username' => 'kasir'],
            [
                'name' => 'Kasir Utama',
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

        // Akun Nonaktif untuk UAT-10
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

        // 3. Produk Contoh dari Mockup Owner (Products)
        $productsData = [
            [
                'nama' => 'Siomay Ayam',
                'kategori' => 'Dimsum Kukus',
                'satuan' => 'Porsi',
                'harga' => 15000.00,
                'stok' => 48,
                'stok_minimum' => 10,
                'supplier_id' => $pemasokA->id,
                'is_active' => true,
            ],
            [
                'nama' => 'Hakau Udang',
                'kategori' => 'Dimsum Kukus',
                'satuan' => 'Porsi',
                'harga' => 18000.00,
                'stok' => 4, // Menipis
                'stok_minimum' => 10,
                'supplier_id' => $pemasokA->id,
                'is_active' => true,
            ],
            [
                'nama' => 'Lumpia Udang',
                'kategori' => 'Dimsum Goreng',
                'satuan' => 'Porsi',
                'harga' => 16000.00,
                'stok' => 2, // Menipis
                'stok_minimum' => 10,
                'supplier_id' => $pemasokA->id,
                'is_active' => true,
            ],
            [
                'nama' => 'Bakpao Coklat',
                'kategori' => 'Bakpao',
                'satuan' => 'Porsi',
                'harga' => 14000.00,
                'stok' => 6, // Menipis
                'stok_minimum' => 10,
                'supplier_id' => $pemasokA->id,
                'is_active' => true,
            ],
            [
                'nama' => 'Pangsit Goreng',
                'kategori' => 'Dimsum Goreng',
                'satuan' => 'Porsi',
                'harga' => 14000.00,
                'stok' => 24, // Aman
                'stok_minimum' => 10,
                'supplier_id' => $pemasokA->id,
                'is_active' => true,
            ],
            [
                'nama' => 'Dimsum Mozza',
                'kategori' => 'Dimsum Kukus',
                'satuan' => 'Porsi',
                'harga' => 20000.00,
                'stok' => 30, // Aman
                'stok_minimum' => 10,
                'supplier_id' => $pemasokA->id,
                'is_active' => true,
            ],
        ];

        $createdProducts = [];
        foreach ($productsData as $data) {
            $product = Product::updateOrCreate(['nama' => $data['nama']], $data);
            $createdProducts[$data['nama']] = $product;

            // Catat stok awal jika belum ada (BR-02)
            StockMovement::firstOrCreate(
                [
                    'product_id' => $product->id,
                    'jenis' => 'stok_awal',
                ],
                [
                    'user_id' => $owner->id,
                    'tanggal' => Carbon::now()->subDays(7),
                    'qty' => $product->stok,
                    'saldo_setelah' => $product->stok,
                    'keterangan' => 'Stok awal pembukaan sistem',
                ]
            );
        }

        // 4. Transaksi Contoh (Orders) sesuai Mockup
        $ordersData = [
            [
                'kode' => 'TRX-0062',
                'waktu' => Carbon::today()->setHour(14)->setMinute(32),
                'user_id' => $rina->id,
                'total' => 48000.00,
                'dibayar' => 50000.00,
                'kembalian' => 2000.00,
                'status' => 'selesai',
            ],
            [
                'kode' => 'TRX-0061',
                'waktu' => Carbon::today()->setHour(14)->setMinute(20),
                'user_id' => $rina->id,
                'total' => 32000.00,
                'dibayar' => 50000.00,
                'kembalian' => 18000.00,
                'status' => 'selesai',
            ],
            [
                'kode' => 'TRX-0060',
                'waktu' => Carbon::today()->setHour(13)->setMinute(50),
                'user_id' => $rina->id,
                'total' => 64000.00,
                'dibayar' => 100000.00,
                'kembalian' => 36000.00,
                'status' => 'selesai',
            ],
            [
                'kode' => 'TRX-0059',
                'waktu' => Carbon::today()->setHour(12)->setMinute(15),
                'user_id' => $rina->id,
                'total' => 28000.00,
                'dibayar' => 30000.00,
                'kembalian' => 2000.00,
                'status' => 'selesai',
            ],
        ];

        foreach ($ordersData as $oData) {
            $order = Order::updateOrCreate(['kode' => $oData['kode']], $oData);

            // Item pesanan
            OrderItem::firstOrCreate(
                [
                    'order_id' => $order->id,
                    'product_id' => $createdProducts['Siomay Ayam']->id,
                ],
                [
                    'qty' => 2,
                    'harga_satuan' => 15000.00,
                    'subtotal' => 30000.00,
                ]
            );
        }
    }
}
