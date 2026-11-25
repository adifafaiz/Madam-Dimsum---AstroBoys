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
        // 1. Akun Pengguna (Users) sesuai Frame 8
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

        User::updateOrCreate(
            ['username' => 'adifa123'],
            [
                'name' => 'Adifa Faiz',
                'email' => 'adifa@madamdimsum.com',
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

        $dani = User::updateOrCreate(
            ['username' => 'dani'],
            [
                'name' => 'Dani',
                'email' => 'dani@madamdimsum.com',
                'role' => 'kasir',
                'password' => bcrypt('password123'),
                'is_active' => true,
            ]
        );

        $agus = User::updateOrCreate(
            ['username' => 'agus'],
            [
                'name' => 'Agus',
                'email' => 'agus@madamdimsum.com',
                'role' => 'dapur',
                'password' => bcrypt('password123'),
                'is_active' => true,
            ]
        );

        // 2. Pemasok (Suppliers) sesuai Frame 5
        $pemasokA = Supplier::updateOrCreate(
            ['nama' => 'Pemasok A'],
            ['kontak' => '0812-3456-7890']
        );

        $pemasokB = Supplier::updateOrCreate(
            ['nama' => 'Pemasok B'],
            ['kontak' => '0819-8765-4321']
        );

        $pemasokC = Supplier::updateOrCreate(
            ['nama' => 'Pemasok C'],
            ['kontak' => '0856-1234-5678']
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
            [
                'nama' => 'Dimsum Mentai',
                'kategori' => 'Dimsum Kukus',
                'satuan' => 'Porsi',
                'harga' => 20000.00,
                'stok' => 0, // Habis sesuai Mockup Dapur Frame 3
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

        // 4. Transaksi Contoh (Orders) untuk Owner & Antrian Dapur (Frame 1)
        $ordersData = [
            [
                'kode' => 'TRX-0065',
                'waktu' => Carbon::today()->setHour(14)->setMinute(40),
                'user_id' => $rina->id,
                'total' => 48000.00,
                'dibayar' => 50000.00,
                'kembalian' => 2000.00,
                'status' => 'baru',
            ],
            [
                'kode' => 'TRX-0064',
                'waktu' => Carbon::today()->setHour(14)->setMinute(30),
                'user_id' => $rina->id,
                'total' => 84000.00,
                'dibayar' => 100000.00,
                'kembalian' => 16000.00,
                'status' => 'siap',
            ],
            [
                'kode' => 'TRX-0063',
                'waktu' => Carbon::today()->setHour(14)->setMinute(25),
                'user_id' => $rina->id,
                'total' => 48000.00,
                'dibayar' => 50000.00,
                'kembalian' => 2000.00,
                'status' => 'diproses',
            ],
            [
                'kode' => 'TRX-0062',
                'waktu' => Carbon::today()->setHour(13)->setMinute(50),
                'user_id' => $dani->id,
                'total' => 32000.00,
                'dibayar' => 50000.00,
                'kembalian' => 18000.00,
                'status' => 'selesai',
            ],
            [
                'kode' => 'TRX-0061',
                'waktu' => Carbon::today()->setHour(14)->setMinute(10),
                'user_id' => $rina->id,
                'total' => 42000.00,
                'dibayar' => 50000.00,
                'kembalian' => 8000.00,
                'status' => 'baru',
            ],
            [
                'kode' => 'TRX-0060',
                'waktu' => Carbon::today()->setHour(12)->setMinute(15),
                'user_id' => $dani->id,
                'total' => 28000.00,
                'dibayar' => 30000.00,
                'kembalian' => 2000.00,
                'status' => 'selesai',
            ],
        ];

        foreach ($ordersData as $oData) {
            $order = Order::updateOrCreate(['kode' => $oData['kode']], $oData);

            if ($order->kode === 'TRX-0065') {
                OrderItem::firstOrCreate(
                    ['order_id' => $order->id, 'product_id' => $createdProducts['Siomay Ayam']->id],
                    ['qty' => 2, 'harga_satuan' => 15000.00, 'subtotal' => 30000.00]
                );
                OrderItem::firstOrCreate(
                    ['order_id' => $order->id, 'product_id' => $createdProducts['Hakau Udang']->id],
                    ['qty' => 1, 'harga_satuan' => 18000.00, 'subtotal' => 18000.00]
                );
            } elseif ($order->kode === 'TRX-0061') {
                OrderItem::firstOrCreate(
                    ['order_id' => $order->id, 'product_id' => $createdProducts['Bakpao Coklat']->id],
                    ['qty' => 3, 'harga_satuan' => 14000.00, 'subtotal' => 42000.00]
                );
            } elseif ($order->kode === 'TRX-0063') {
                OrderItem::firstOrCreate(
                    ['order_id' => $order->id, 'product_id' => $createdProducts['Pangsit Goreng']->id],
                    ['qty' => 2, 'harga_satuan' => 14000.00, 'subtotal' => 28000.00]
                );
                OrderItem::firstOrCreate(
                    ['order_id' => $order->id, 'product_id' => $createdProducts['Siomay Ayam']->id],
                    ['qty' => 1, 'harga_satuan' => 15000.00, 'subtotal' => 15000.00]
                );
            } elseif ($order->kode === 'TRX-0064') {
                OrderItem::firstOrCreate(
                    ['order_id' => $order->id, 'product_id' => $createdProducts['Dimsum Mentai']->id],
                    ['qty' => 1, 'harga_satuan' => 20000.00, 'subtotal' => 20000.00]
                );
            } else {
                OrderItem::firstOrCreate(
                    ['order_id' => $order->id, 'product_id' => $createdProducts['Siomay Ayam']->id],
                    ['qty' => 2, 'harga_satuan' => 15000.00, 'subtotal' => 30000.00]
                );
            }
        }

        // Sample Riwayat Produksi Dapur (Frame 4)
        $riwayatProduksiData = [
            ['nama' => 'Siomay Ayam', 'qty' => 20, 'sesi' => 'pagi', 'ket' => null, 'menit' => 20],
            ['nama' => 'Hakau Udang', 'qty' => 20, 'sesi' => 'sore', 'ket' => null, 'menit' => 10],
            ['nama' => 'Lumpia Udang', 'qty' => 15, 'sesi' => 'sore', 'ket' => 'Tambahan stok', 'menit' => 0],
            ['nama' => 'Bakpao Coklat', 'qty' => 20, 'sesi' => 'sore', 'ket' => null, 'menit' => 50],
        ];

        foreach ($riwayatProduksiData as $rp) {
            StockMovement::firstOrCreate(
                [
                    'product_id' => $createdProducts[$rp['nama']]->id,
                    'jenis' => 'produksi',
                    'sesi' => $rp['sesi'],
                    'qty' => $rp['qty'],
                ],
                [
                    'user_id' => $agus->id,
                    'tanggal' => Carbon::today()->setHour(15)->setMinute($rp['menit']),
                    'saldo_setelah' => $createdProducts[$rp['nama']]->stok,
                    'keterangan' => $rp['ket'],
                ]
            );
        }

        // 5. Data Pencadangan & Pengaturan Sesuai Frame 9
        \App\Models\Setting::updateOrCreate(
            ['kunci' => 'backup_frekuensi'],
            ['nilai' => 'Harian']
        );
        \App\Models\Setting::updateOrCreate(
            ['kunci' => 'backup_jam'],
            ['nilai' => '02:00']
        );

        \App\Models\Backup::updateOrCreate(
            ['file_path' => 'backups/backup_2026_11_24_020000.sqlite'],
            [
                'waktu' => Carbon::today()->setHour(2)->setMinute(0),
                'ukuran_kb' => 1200,
                'jenis' => 'otomatis',
                'status' => 'berhasil',
            ]
        );
        \App\Models\Backup::updateOrCreate(
            ['file_path' => 'backups/backup_2026_11_23_020000.sqlite'],
            [
                'waktu' => Carbon::yesterday()->setHour(2)->setMinute(0),
                'ukuran_kb' => 1200,
                'jenis' => 'otomatis',
                'status' => 'berhasil',
            ]
        );
        \App\Models\Backup::updateOrCreate(
            ['file_path' => 'backups/backup_2026_11_22_144500.sqlite'],
            [
                'waktu' => Carbon::today()->subDays(2)->setHour(14)->setMinute(45),
                'ukuran_kb' => 1200,
                'jenis' => 'manual',
                'status' => 'berhasil',
            ]
        );
    }
}
