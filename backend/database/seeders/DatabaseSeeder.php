<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database based on PRD Final DimsumKu v2 + mockup Figma.
     *
     * Data dibuat konsisten dengan aturan bisnis:
     * - BR-01: setiap perubahan stok punya baris stock_movements dengan saldo_setelah yang benar.
     * - BR-02/BR-05/BR-09: stok_awal (7 hari lalu), produksi dapur (2 hari lalu), terjual (kemarin & hari ini).
     * - BR-07: total = jumlah subtotal, kembalian = dibayar - total, kode TRX berurutan sesuai waktu.
     * - BR-08: harga_satuan disalin dari harga produk.
     *
     * Stok AKHIR produk disesuaikan dengan skenario UAT (Hakau Udang 4, Bakpao Coklat 6,
     * Dimsum Mentai 0 = Habis). Stok awal dihitung mundur = stok akhir - produksi + terjual.
     *
     * Kode TRX dimulai dari TRX-0060 untuk order id 1 karena KasirController membuat kode
     * berikutnya dari (id terakhir + 60). Dengan begitu transaksi baru kasir langsung
     * melanjutkan nomor terakhir seeder tanpa lompat.
     *
     * Tabel backups sengaja tidak diisi: belum ada fitur yang membuat file backup sungguhan,
     * jadi baris contoh akan menunjuk ke file yang tidak ada.
     *
     * Seeder aman dijalankan ulang: item & pergerakan stok dibuat ulang,
     * dan stok produk selalu kembali ke nilai akhir di bawah.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Akun Pengguna (Users)
            $owner = $this->user('owner', 'Madam Owner', 'owner');
            $this->user('adifa123', 'Adifa Faiz', 'owner');
            $rina = $this->user('rina', 'Rina', 'kasir');
            $this->user('kasir', 'Kasir Utama', 'kasir');
            $dani = $this->user('dani', 'Dani', 'kasir'); // aktif; dinonaktifkan owner saat demo UAT-10
            $agus = $this->user('agus', 'Agus', 'dapur');

            // 2. Pemasok (Suppliers) sesuai mockup Figma
            $pemasokA = Supplier::updateOrCreate(['nama' => 'Pemasok A'], ['kontak' => '0812-3456-7890']);
            $pemasokB = Supplier::updateOrCreate(['nama' => 'Pemasok B'], ['kontak' => '0819-8765-4321']);
            $pemasokC = Supplier::updateOrCreate(['nama' => 'Pemasok C'], ['kontak' => '0856-1234-5678']);

            // 3. Produk sesuai mockup Figma. 'stok' = stok AKHIR setelah produksi & transaksi contoh.
            $productsData = [
                'Siomay Ayam'    => ['kategori' => 'Dimsum Kukus',  'harga' => 15000, 'stok' => 48, 'supplier_id' => $pemasokA->id], // Aman
                'Hakau Udang'    => ['kategori' => 'Dimsum Kukus',  'harga' => 18000, 'stok' => 4,  'supplier_id' => $pemasokA->id], // Menipis (UAT-02, UAT-05)
                'Lumpia Udang'   => ['kategori' => 'Dimsum Goreng', 'harga' => 16000, 'stok' => 2,  'supplier_id' => $pemasokB->id], // Menipis
                'Bakpao Coklat'  => ['kategori' => 'Bakpao',        'harga' => 12000, 'stok' => 6,  'supplier_id' => $pemasokA->id], // Menipis (UAT-07)
                'Pangsit Goreng' => ['kategori' => 'Dimsum Goreng', 'harga' => 14000, 'stok' => 24, 'supplier_id' => $pemasokB->id], // Aman
                'Dimsum Mozza'   => ['kategori' => 'Dimsum Kukus',  'harga' => 20000, 'stok' => 30, 'supplier_id' => $pemasokC->id], // Aman
                'Dimsum Mentai'  => ['kategori' => 'Dimsum Kukus',  'harga' => 22000, 'stok' => 0,  'supplier_id' => $pemasokC->id], // Habis
            ];

            // 4. Riwayat produksi dapur oleh Agus, 2 hari lalu (BR-09)
            $hariProduksi = Carbon::today()->subDays(2);
            $produksiData = [
                ['nama' => 'Siomay Ayam',   'qty' => 20, 'sesi' => 'pagi', 'jam' => [7, 30],  'ket' => null],
                ['nama' => 'Hakau Udang',   'qty' => 20, 'sesi' => 'sore', 'jam' => [15, 0],  'ket' => null],
                ['nama' => 'Lumpia Udang',  'qty' => 15, 'sesi' => 'sore', 'jam' => [15, 10], 'ket' => 'Tambahan stok'],
                ['nama' => 'Bakpao Coklat', 'qty' => 20, 'sesi' => 'sore', 'jam' => [15, 50], 'ket' => null],
            ];

            // 5. Transaksi contoh, urut waktu = urut kode. Kemarin semua selesai;
            //    hari ini status campuran agar antrian dapur terisi (UAT-04).
            $kemarin = Carbon::yesterday();
            $hariIni = Carbon::today();
            $ordersData = [
                ['kasir' => $rina, 'waktu' => $kemarin->copy()->setTime(10, 15), 'dibayar' => 100000, 'status' => 'selesai',
                    'items' => ['Hakau Udang' => 3, 'Siomay Ayam' => 2]],                      // 84.000
                ['kasir' => $dani, 'waktu' => $kemarin->copy()->setTime(11, 40), 'dibayar' => 100000, 'status' => 'selesai',
                    'items' => ['Bakpao Coklat' => 5, 'Lumpia Udang' => 2]],                   // 92.000
                ['kasir' => $rina, 'waktu' => $kemarin->copy()->setTime(13, 5),  'dibayar' => 100000, 'status' => 'selesai',
                    'items' => ['Hakau Udang' => 4, 'Pangsit Goreng' => 2]],                   // 100.000
                ['kasir' => $dani, 'waktu' => $kemarin->copy()->setTime(16, 20), 'dibayar' => 150000, 'status' => 'selesai',
                    'items' => ['Lumpia Udang' => 6, 'Bakpao Coklat' => 3]],                   // 132.000
                ['kasir' => $rina, 'waktu' => $kemarin->copy()->setTime(17, 45), 'dibayar' => 150000, 'status' => 'selesai',
                    'items' => ['Hakau Udang' => 5, 'Dimsum Mozza' => 2]],                     // 130.000
                ['kasir' => $dani, 'waktu' => $kemarin->copy()->setTime(19, 10), 'dibayar' => 200000, 'status' => 'selesai',
                    'items' => ['Hakau Udang' => 4, 'Lumpia Udang' => 4, 'Bakpao Coklat' => 4]], // 184.000

                ['kasir' => $dani, 'waktu' => $hariIni->copy()->setTime(12, 15), 'dibayar' => 30000,  'status' => 'selesai',
                    'items' => ['Siomay Ayam' => 2]],                                          // 30.000
                ['kasir' => $rina, 'waktu' => $hariIni->copy()->setTime(13, 10), 'dibayar' => 30000,  'status' => 'selesai',
                    'items' => ['Siomay Ayam' => 1, 'Pangsit Goreng' => 1]],                   // 29.000
                ['kasir' => $dani, 'waktu' => $hariIni->copy()->setTime(13, 50), 'dibayar' => 100000, 'status' => 'selesai',
                    'items' => ['Siomay Ayam' => 2, 'Lumpia Udang' => 2]],                     // 62.000
                ['kasir' => $rina, 'waktu' => $hariIni->copy()->setTime(14, 10), 'dibayar' => 50000,  'status' => 'baru',
                    'items' => ['Bakpao Coklat' => 3]],                                        // 36.000
                ['kasir' => $rina, 'waktu' => $hariIni->copy()->setTime(14, 25), 'dibayar' => 50000,  'status' => 'diproses',
                    'items' => ['Pangsit Goreng' => 2, 'Siomay Ayam' => 1]],                   // 43.000
                ['kasir' => $rina, 'waktu' => $hariIni->copy()->setTime(14, 30), 'dibayar' => 50000,  'status' => 'siap',
                    'items' => ['Dimsum Mentai' => 2]],                                        // 44.000
                ['kasir' => $rina, 'waktu' => $hariIni->copy()->setTime(14, 40), 'dibayar' => 50000,  'status' => 'baru',
                    'items' => ['Siomay Ayam' => 2, 'Hakau Udang' => 1]],                      // 48.000
            ];

            // Hitung stok awal mundur: stok akhir - produksi + terjual
            $produksi = array_fill_keys(array_keys($productsData), 0);
            foreach ($produksiData as $p) {
                $produksi[$p['nama']] += $p['qty'];
            }
            $terjual = array_fill_keys(array_keys($productsData), 0);
            foreach ($ordersData as $o) {
                foreach ($o['items'] as $nama => $qty) {
                    $terjual[$nama] += $qty;
                }
            }

            $products = [];
            $saldo = [];
            foreach ($productsData as $nama => $data) {
                $stokAwal = $data['stok'] - $produksi[$nama] + $terjual[$nama];
                if ($stokAwal < 0) {
                    throw new \RuntimeException("Stok awal {$nama} negatif ({$stokAwal}): periksa data seeder.");
                }

                $product = Product::updateOrCreate(['nama' => $nama], [
                    'kategori' => $data['kategori'],
                    'satuan' => 'Porsi',
                    'harga' => $data['harga'],
                    'stok' => $data['stok'],
                    'stok_minimum' => 10,
                    'supplier_id' => $data['supplier_id'],
                    'is_active' => true,
                ]);

                // BR-02: stok awal pembukaan sistem
                StockMovement::updateOrCreate(
                    ['product_id' => $product->id, 'jenis' => 'stok_awal'],
                    [
                        'user_id' => $owner->id,
                        'tanggal' => Carbon::today()->subDays(7)->setHour(8),
                        'qty' => $stokAwal,
                        'saldo_setelah' => $stokAwal,
                        'keterangan' => 'Stok awal pembukaan sistem',
                    ]
                );

                $products[$nama] = $product;
                $saldo[$nama] = $stokAwal;
            }

            // Produksi: stok naik, saldo_setelah mengikuti (BR-01, BR-09)
            StockMovement::where('jenis', 'produksi')
                ->where('user_id', $agus->id)
                ->whereIn('product_id', collect($products)->pluck('id'))
                ->delete();

            foreach ($produksiData as $p) {
                $saldo[$p['nama']] += $p['qty'];
                StockMovement::create([
                    'product_id' => $products[$p['nama']]->id,
                    'user_id' => $agus->id,
                    'tanggal' => $hariProduksi->copy()->setTime($p['jam'][0], $p['jam'][1]),
                    'jenis' => 'produksi',
                    'qty' => $p['qty'],
                    'saldo_setelah' => $saldo[$p['nama']],
                    'sesi' => $p['sesi'],
                    'keterangan' => $p['ket'],
                ]);
            }

            // Transaksi: stok berkurang, tercatat sebagai terjual (BR-01, BR-05)
            foreach ($ordersData as $i => $o) {
                $kode = 'TRX-' . str_pad((string) (60 + $i), 4, '0', STR_PAD_LEFT);

                $total = 0;
                foreach ($o['items'] as $nama => $qty) {
                    $total += $products[$nama]->harga * $qty;
                }
                if ($o['dibayar'] < $total) {
                    throw new \RuntimeException("Uang dibayar {$kode} kurang dari total: periksa data seeder.");
                }

                $order = Order::updateOrCreate(['kode' => $kode], [
                    'waktu' => $o['waktu'],
                    'user_id' => $o['kasir']->id,
                    'total' => $total,
                    'dibayar' => $o['dibayar'],
                    'kembalian' => $o['dibayar'] - $total, // BR-07
                    'status' => $o['status'],
                    'alasan_batal' => null,
                ]);

                // Buat ulang item & pergerakan stok agar seeder idempoten
                $order->items()->delete();
                $order->stockMovements()->delete();

                foreach ($o['items'] as $nama => $qty) {
                    $product = $products[$nama];

                    $order->items()->create([
                        'product_id' => $product->id,
                        'qty' => $qty,
                        'harga_satuan' => $product->harga, // BR-08
                        'subtotal' => $product->harga * $qty,
                    ]);

                    $saldo[$nama] -= $qty;
                    if ($saldo[$nama] < 0) {
                        throw new \RuntimeException("Saldo {$nama} negatif setelah {$kode}: periksa urutan data seeder.");
                    }

                    StockMovement::create([
                        'product_id' => $product->id,
                        'user_id' => $o['kasir']->id,
                        'order_id' => $order->id,
                        'tanggal' => $o['waktu'],
                        'jenis' => 'terjual',
                        'qty' => -$qty,
                        'saldo_setelah' => $saldo[$nama],
                        'keterangan' => "Penjualan {$kode}",
                    ]);
                }
            }

            // Pastikan saldo akhir pergerakan = stok produk (BR-01)
            foreach ($products as $nama => $product) {
                if ($saldo[$nama] !== $product->stok) {
                    throw new \RuntimeException("Saldo stok {$nama} tidak konsisten: {$saldo[$nama]} vs {$product->stok}");
                }
            }

            // 6. Pengaturan sistem (jadwal backup otomatis); nilai mengikuti opsi di OwnerController
            Setting::updateOrCreate(['kunci' => 'backup_frekuensi'], ['nilai' => 'Harian']);
            Setting::updateOrCreate(['kunci' => 'backup_jam'], ['nilai' => '02:00']);
        });
    }

    private function user(string $username, string $name, string $role): User
    {
        return User::updateOrCreate(
            ['username' => $username],
            [
                'name' => $name,
                'email' => "{$username}@madamdimsum.com",
                'role' => $role,
                'password' => 'password123', // di-hash oleh cast 'hashed' di model User
                'is_active' => true,
            ]
        );
    }
}
