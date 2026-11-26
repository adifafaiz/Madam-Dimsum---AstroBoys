# Checklist Kebutuhan Backend — DimsumKu (Madam Dimsum / AstroBoys)

> Acuan: `CLAUDE.md` di root repo (dibuat ulang 24 November 2026; ringkasan PRD/laporan capstone) · Diperiksa: 24 November 2026 · Commit: `ac5ecd6`
> Diperbarui: 26 November 2026. Bagian 1 diperiksa setelah `git pull` ke `a59ff99` + penggabungan seeder. **Bagian 2–8 diaudit ulang 26 November 2026 terhadap commit `22ccb02`** (`AuthController`, `KasirController`, `DapurController`, `OwnerController`, `routes/web.php`, `routes/api.php`, `bootstrap/app.php`, model, view Blade, dan test).
>
> Status: **Sudah** = terpenuhi sesuai kebutuhan · **Sebagian** = ada tetapi belum lengkap atau ada cacat · **Belum** = belum ada kode
>
> Tanda **★** di kolom `#` = termasuk tugas Fatih: keamanan API (Sanctum, middleware peran), backup riil + scheduler, edit pemasok/pengguna, WhatsApp stok, batal transaksi, ekspor PDF, generator kode TRX, dan test UAT-01 s.d. UAT-10.
>
> Catatan lingkup:
> - Yang diperiksa adalah folder `backend/` di repo utama.
> - Test terakhir dijalankan (`php artisan test`, 26 November 2026, setelah pekerjaan keamanan API): **55 test lulus, 230 assertion, 0 gagal**. Test baru: `ApiSecurityTest` (19 test). Test fitur lainnya masih memakai route **web** (session).
> - **Pembaruan 26 November 2026 (keamanan API, belum di-commit):** Sanctum, middleware `role`, proteksi grup `/api/*`, pesan login umum, throttle login, dan pembatasan `/api/demo-accounts` sudah dikerjakan. Baris yang berubah: 2.5–2.9, 2.11, 7.5, 7.7, 7.8, 7.10–7.12, 8.1, 8.3, 8.7, UAT-03, UAT-10, 5.7, 5.16. Baris lain tetap hasil audit `22ccb02`.

---

## 1. Database (9 tabel ERD v2, relasi, seeder)

| # | Kebutuhan | Status | File / Endpoint | Yang masih kurang |
|---|-----------|--------|-----------------|-------------------|
| 1.1 | Tabel `users` (username unik, role owner/kasir/dapur, is_active) | Sudah | `database/migrations/0001_01_01_000000_create_users_table.php` | — |
| 1.2 | Tabel `settings` (kunci–nilai) | Sudah | `database/migrations/2026_11_24_000001_create_dimsumku_erd_v2_tables.php` | Belum dipakai di kode mana pun |
| 1.3 | Tabel `activity_logs` (aksi, tabel, data_lama/baru JSON, waktu datetime) | Sudah | migration ERD v2 | FK `user_id` memakai `onDelete('cascade')`: jika user terhapus, log ikut hilang. Lebih aman `restrictOnDelete` (sejalan dengan BR-10) |
| 1.4 | Tabel `backups` (waktu, ukuran_kb, jenis, status, file_path) | Sudah | migration ERD v2 | — |
| 1.5 | Tabel `suppliers` | Sudah | migration ERD v2 | — |
| 1.6 | Tabel `products` (harga, stok, stok_minimum, supplier_id, is_active) | Sudah | migration ERD v2 | Tidak ada constraint di DB agar stok ≥ 0 (lihat BR-01) |
| 1.7 | Tabel `orders` (kode unik, total/dibayar/kembalian, status enum, alasan_batal) | Sudah | migration ERD v2 | — |
| 1.8 | Tabel `order_items` (qty, harga_satuan, subtotal) | Sudah | migration ERD v2 | — |
| 1.9 | Tabel `stock_movements` (jenis enum, qty ±, saldo_setelah, sesi, order_id, supplier_id) | Sudah | migration ERD v2 | — |
| 1.10 | Relasi Eloquent di semua model | Sebagian | `app/Models/*.php` | Relasi Product, Order, OrderItem, StockMovement, Supplier, ActivityLog sudah ada. `User` belum punya `orders()`, `stockMovements()`, `activityLogs()` |
| 1.11 | Seeder akun (owner, kasir, dapur) | Sudah | `database/seeders/DatabaseSeeder.php` | 6 akun, semua aktif: `owner` & `adifa123` (owner), `rina`, `kasir`, `dani` (kasir), `agus` (dapur). Password `password123`, di-hash oleh cast `hashed`. Dani sengaja dibiarkan aktif agar demo UAT-10 bisa dimulai dari awal. Nama belum dicek ulang terhadap `/api/demo-accounts` |
| 1.12 | Seeder supplier & produk + stok awal (sesuai mockup Figma) | Sudah | `DatabaseSeeder.php` | — Terverifikasi lewat `migrate:fresh --seed` (26 Nov): Pemasok A/B/C dengan kontak lengkap; 7 produk dengan harga mockup (Siomay 15.000, Hakau 18.000, Lumpia 16.000, Bakpao Coklat 12.000, Pangsit 14.000, Dimsum Mozza 20.000, Dimsum Mentai 22.000). Ketiga status ada: Aman (Siomay 48, Pangsit 24, Mozza 30), Menipis (Hakau 4, Lumpia 2, Bakpao 6), Habis (Mentai 0). Setiap produk punya 1 movement `stok_awal` (7 hari lalu), dihitung mundur = stok akhir − produksi + terjual |
| 1.13 | Seeder transaksi & produksi contoh yang konsisten | Sudah | `DatabaseSeeder.php` | — Terverifikasi (26 Nov): 13 transaksi TRX-0060 s.d. 0072, kode urut sesuai waktu. 6 transaksi kemarin (semua `selesai`) dan 7 hari ini (4 `selesai`, 2 `baru`, 1 `diproses`, 1 `siap`, jadi antrian dapur terisi). Rina dan Dani masing-masing punya transaksi. Semua total = jumlah subtotal, kembalian = dibayar − total ≥ 0, `harga_satuan` = harga produk, dan ada 24 movement `terjual` (1 per item). 4 movement `produksi` oleh Agus 2 hari lalu (Siomay +20 pagi; Hakau +20, Lumpia +15, Bakpao +20 sore) dengan `saldo_setelah` yang naik. Untuk tiap produk, stok = `saldo_setelah` terakhir = jumlah qty movement (BR-01). Seeder dibungkus `DB::transaction`, menolak saldo negatif, mengecek konsistensi sendiri, dan aman dijalankan ulang (hasil run ke-1 dan ke-3 identik). Kode mulai TRX-0060 karena `KasirController.php:126` membuat kode dari `id + 60` (lihat catatan BR-07) |
| 1.14 | Seeder `settings` & contoh `backups` | Sudah | `DatabaseSeeder.php` | — `backup_frekuensi = Harian` (sama dengan opsi di `OwnerController@pencadangan` dan `owner/pencadangan.blade.php`), `backup_jam = 02:00`. Tabel `backups` sengaja kosong: `OwnerController@createBackup` hanya membuat baris tanpa file sungguhan, jadi baris contoh akan menunjuk ke file yang tidak ada |
| 1.15 | Factory untuk model domain (dipakai test) | Belum | `database/factories/` | Baru ada `UserFactory` |

## 2. Login & hak akses per peran

| # | Kebutuhan | Status | File / Endpoint | Yang masih kurang |
|---|-----------|--------|-----------------|-------------------|
| 2.1 | Login dengan username + password + pilihan peran | Sudah | `AuthController@login`, `POST /api/login`, `POST /login` | — |
| 2.2 | Tolak akun nonaktif (BR-10, FR-29) | Sudah | `AuthController@login` (HTTP 403) | — |
| 2.3 | Tolak peran yang tidak cocok dengan akun | Sudah | `AuthController@login` (HTTP 422) | — |
| 2.4 | Login web (session) + redirect sesuai peran | Sudah | `routes/web.php`, `/dashboard/{role}` → `owner.dashboard` / `dapur.antrian` / `kasir.transaksi` | — |
| 2.5 ★ | Sanctum terpasang & login API mengembalikan token | Sudah | `composer.json` (`laravel/sanctum` ^4.5), `config/sanctum.php`, migration `personal_access_tokens`, `User` (`HasApiTokens`), `AuthController@login` | — Dikerjakan 26 Nov. `POST /api/login` mengembalikan `data.token` (Bearer, ability = peran), `data.user`, `data.redirect_url`. Token kedaluwarsa 720 menit (`SANCTUM_EXPIRATION`). Kolom `email` (tidak ada di ERD) dihapus dari respons. Test: `ApiSecurityTest::test_login_api_mengembalikan_token_yang_bisa_dipakai` |
| 2.6 ★ | `GET /api/user` (cek sesi token) | Sudah | `routes/api.php` | — Di dalam grup `auth:sanctum`, format `{success, message, data}`, hanya `id`, `name`, `username`, `role` |
| 2.7 ★ | Logout API mencabut token | Sudah | `AuthController@logout`, `POST /api/logout` (grup `auth:sanctum`) | — Token yang sedang dipakai dihapus; token itu langsung ditolak (401). Logout web tetap lewat session. Test: `test_logout_api_mencabut_token` |
| 2.8 ★ | Middleware peran (`role:kasir` / `role:dapur` / `role:owner`) per grup route | Sudah | `app/Http/Middleware/EnsureRole.php`, alias `role` di `bootstrap/app.php` | — Dipasang di grup API dan grup web `/owner`, `/kasir`, `/dapur`. Peran salah → 403 (JSON di API). Akun yang sudah dinonaktifkan: token dicabut (API 401) atau session dikeluarkan ke `/login` (web). `check*Access()` di controller dibiarkan sebagai lapisan kedua |
| 2.9 ★ | Grup route API (`/api/owner`, `/api/kasir`, `/api/dapur`) terlindungi auth | Sudah | `routes/api.php` | — Ketiga grup dibungkus `auth:sanctum` + `role:{peran}`. Tamu → 401 JSON (bukan redirect), peran lain → 403 JSON, peran sendiri → 200. Catatan fitur (bukan keamanan): batal transaksi, pengguna, pencadangan, ekspor belum punya route API (lihat 5.7, 5.12, 5.14, 5.15) |
| 2.10 | Halaman "Akses Ditolak" (FR-02) untuk web | Sudah | `resources/views/errors/akses-ditolak.blade.php` | Hanya dipakai di `/dashboard/{role}`. Akses lintas modul (misal kasir → `/owner/produk`) memakai `abort(403)` biasa, jadi tampil halaman 403 bawaan Laravel |
| 2.11 ★ | Pesan gagal login umum ("Username atau password salah"), termasuk saat peran salah, supaya username/peran tidak bocor | Sudah | `AuthController@login`, `AuthController::loginGagal()` | — Username tidak ada, password salah, dan peran salah → **401 "Username atau password salah."** (web: satu pesan di form). Status aktif dicek **setelah** password benar → 403 "Akun Anda telah dinonaktifkan…". Akun nonaktif + password salah tetap mendapat pesan umum. `AuthTest` disesuaikan |

## 3. API Kasir

| # | Kebutuhan | Status | File / Endpoint | Yang masih kurang |
|---|-----------|--------|-----------------|-------------------|
| 3.1 | Daftar produk aktif + stok + status stok | Sebagian | `KasirController@transaksi` & `@cekStok`; `GET /kasir/transaksi`, `GET /kasir/cek-stok`, `GET /api/kasir/cek-stok` | Jalan di web: produk aktif, cari, filter kategori, status Aman/Menipis/Habis di Blade. Di JSON, `status_stok` tidak ikut karena `Product` belum `$appends`. Respons memakai kunci `products`, bukan `data` |
| 3.2 | Simpan transaksi (keranjang, total, uang diterima, kembalian; tolak jika stok kurang atau uang kurang) | Sebagian | `KasirController@storeTransaksi`; `POST /kasir/transaksi`, `POST /api/kasir/transaksi` | Sudah benar: validasi item, `lockForUpdate()`, tolak stok kurang & uang kurang, harga dari DB (BR-08), movement `terjual` qty negatif + `saldo_setelah`, status `baru`, semua dalam 1 transaksi DB. **Cacat:** (a) jika produk yang sama muncul 2× di `items`, tiap baris dicek terhadap stok awal yang sama dan disimpan dari instance model terpisah, sehingga stok bisa terjual melebihi persediaan dan nilai stok akhir salah. Gabungkan qty per `product_id` dulu, atau tolak duplikat. (b) produk nonaktif tetap bisa dijual (`exists:products,id` tanpa cek `is_active`). (c) pesan `$e->getMessage()` dikirim ke klien (7.6) |
| 3.3 ★ | Generator kode `TRX-XXXX` (4 digit, berurutan) | Sebagian | `KasirController.php:125-127` | Kode = `id order terakhir + 60`, bukan nomor kode terbesar + 1. Bergantung pada id dan isi seeder, melompat kalau ada order terhapus, dan tidak dikunci terhadap 2 kasir yang menyimpan bersamaan (bisa gagal karena kolom unik). Ganti dengan nomor terbesar dari `kode` + 1 di dalam transaksi DB dengan lock |
| 3.4 | Riwayat transaksi milik kasir sendiri | Sudah | `KasirController@riwayat`; `GET /kasir/riwayat`, `GET /api/kasir/riwayat` | Filter `user_id = Auth::id()`, filter tanggal, cari kode/nama produk. Kurang (kecil): tanpa pagination |
| 3.5 | Detail transaksi milik sendiri (item, dibayar, kembalian) | Sebagian | `KasirController@riwayat` (`?selected=`), `KasirController@cetakStruk`, `GET /kasir/struk/{order}` | Panel detail di riwayat hanya memilih dari order milik sendiri. **Cacat:** `cetakStruk` tidak mengecek pemilik, jadi kasir mana pun bisa membuka struk transaksi kasir lain lewat `/kasir/struk/{id}`. Belum ada endpoint detail JSON tersendiri |

## 4. API Dapur

| # | Kebutuhan | Status | File / Endpoint | Yang masih kurang |
|---|-----------|--------|-----------------|-------------------|
| 4.1 | Antrian pesanan (status baru/diproses/siap) | Sudah | `DapurController@antrian`; `GET /dapur/antrian`, `GET /api/dapur/antrian` | Tiga daftar terpisah, urut waktu, dengan item. Kunci JSON `baru`/`diproses`/`siap` di luar `data` |
| 4.2 | Ubah status pesanan satu arah | Sebagian | `DapurController@updateStatusPesanan`; `POST /dapur/antrian/{order}`, `POST /api/dapur/antrian/{order}` | Status tersimpan dan dicatat di `activity_logs`. **Cacat berat:** tidak ada validasi transisi. Request bisa memundurkan status (`siap` → `baru`), melompat (`baru` → `selesai`), atau menghidupkan lagi order `batal`. Perlu cek bahwa status baru tepat satu langkah setelah status lama, dan order `batal`/`selesai` ditolak. Ini membuat UAT-04 gagal |
| 4.3 | Catat produksi (tanggal, sesi, daftar produk + jumlah, catatan) | Sudah | `DapurController@storeProduksi`; `POST /dapur/catat-produksi`, `POST /api/dapur/catat-produksi` | Multi-produk, tiap produk 1 movement `produksi` + `sesi` + `keterangan`, `lockForUpdate()`, 1 transaksi DB. Kurang (kecil): produk nonaktif tidak ditolak, `getMessage()` bocor (7.6) |
| 4.4 | Riwayat produksi milik sendiri | Sebagian | `DapurController@riwayatProduksi`; `GET /dapur/riwayat-produksi` | Filter `jenis = produksi` dan tanggal sudah ada, tetapi **tidak** difilter `user_id = Auth::id()`, jadi tampil produksi semua petugas dapur |
| 4.5 | Lihat stok + jumlah perlu diproduksi | Sebagian | `DapurController@stok`; `GET /dapur/stok`, `GET /api/dapur/stok` | Blade menampilkan `status_stok` & `perlu_diproduksi` dan ada filter "hanya perlu produksi". Di JSON kedua nilai tidak ikut (belum `$appends`) |

## 5. API Owner

| # | Kebutuhan | Status | File / Endpoint | Yang masih kurang |
|---|-----------|--------|-----------------|-------------------|
| 5.1 | Dashboard (penjualan hari ini + % vs kemarin, pendapatan bulan ini + jumlah transaksi, jumlah stok menipis, produk terlaris hari ini, penjualan 7 hari, 5 transaksi terbaru, daftar restock), semua dari query | Sebagian | `OwnerController@dashboard`, `GET /owner/dashboard`, `GET /api/owner/dashboard` | Tidak berubah: angka cadangan hard-coded (1.075.000; 38.420.000 / 1.240 transaksi) jika hasil query 0, produk terlaris tetap "Siomay Ayam/74", grafik 7 hari statis, belum ada "% vs kemarin", `take(6)` (seharusnya 5), `whereMonth` tanpa tahun, daftar restock tanpa jumlah restock (BR-04). Yang sudah dari query: penjualan hari ini, pendapatan bulan ini, jumlah stok menipis, daftar restock, transaksi terbaru |
| 5.2 | Daftar produk + cari + filter kategori | Sebagian | `OwnerController@produk`, `GET /owner/produk`, `GET /api/owner/produk` | Cari & filter jalan. Kunci JSON `products`/`categories`, tanpa `status_stok` (belum `$appends`). Produk nonaktif tidak bisa dilihat atau diaktifkan kembali |
| 5.3 | Tambah produk + stok awal + log | Sebagian | `OwnerController@storeProduk`, `POST /owner/produk`, `POST /api/owner/produk` | Validasi, movement `stok_awal`, `activity_logs` sudah ada. Kurang: `kategori` tidak divalidasi terhadap daftar, `getMessage()` bocor ke klien, format respons (`product`, bukan `data`) |
| 5.4 | Edit data produk (+ log BR-11) | Sudah | `OwnerController@updateProduk`, `POST /owner/produk/{product}`, `POST /api/owner/produk/{product}` | `data_lama`/`data_baru` di `activity_logs`, dalam transaksi DB. Kurang (kecil): memakai `POST`, bukan `PUT`; `getMessage()` bocor. Bagian stok lihat 5.9 |
| 5.5 | Hapus/nonaktifkan produk | Sudah | `OwnerController@destroyProduk`, `POST /owner/produk/{product}/delete`, `DELETE /owner/produk/{product}`, `DELETE /api/owner/produk/{product}` | Soft-disable `is_active = false` + log. Kurang (kecil): pesan masih berbunyi "dihapus" |
| 5.6 ★ | CRUD supplier | Sebagian | `OwnerController@pemasok` & `@storePemasok`, `GET/POST /owner/pemasok`, `GET/POST /api/owner/pemasok` | Baru lihat + tambah. **Edit dan hapus pemasok belum ada** (method, route, dan tombol di `owner/pemasok.blade.php`). `kontak` diwajibkan, padahal di ERD kolomnya nullable. Tambah pemasok belum dicatat di log |
| 5.7 ★ | CRUD user (nonaktifkan, bukan hapus) | Sebagian | `OwnerController@pengguna`, `@storePengguna`, `@toggleStatusPengguna`; `/owner/pengguna`, `/owner/pengguna/{user}/toggle` | Sudah: lihat, tambah (password di-hash, username unik, log), nonaktif/aktifkan dengan pencegahan menonaktifkan diri sendiri + log. **Edit pengguna (nama, username, peran) belum ada.** Log `ubah_status_pengguna` tidak menyimpan id/username user yang diubah. Hanya route web (tanpa JSON/API). Session/token akun yang dinonaktifkan kini langsung ditolak (2.8) |
| 5.8 | Stok masuk dari supplier | Sudah | `OwnerController@storeStokMasuk`, `POST /owner/stok-masuk`, `POST /api/owner/stok-masuk` | Multi-produk, movement `masuk` + `supplier_id`, `lockForUpdate()`, transaksi DB, log. Kurang (kecil): `getMessage()` bocor |
| 5.9 | Ubah stok lewat edit produk = `penyesuaian`, keterangan wajib | Sebagian | `OwnerController@updateProduk` | Selisih stok dicatat sebagai movement `penyesuaian` (qty ±, `saldo_setelah`) dalam transaksi DB + log. Kurang: `alasan_koreksi` **tidak wajib** (`nullable`, diisi default "Koreksi stok oleh Owner"); harus `required_if` stok berubah. Produk tidak di-`lockForUpdate()` |
| 5.10 | Riwayat stok per produk | Sebagian | `OwnerController@riwayatStok`, `GET /owner/produk/{product}/riwayat`, `GET /api/owner/produk/{product}/riwayat` | Sekarang ada route web, tetapi hasilnya tetap JSON (bukan halaman). Tanpa pagination/filter tanggal. Kunci `product`/`movements` di luar `data` |
| 5.11 | Riwayat semua transaksi (filter tanggal/status/kasir) + detail | Sebagian | `OwnerController@riwayatTransaksi`, `GET /owner/riwayat-transaksi` | Filter tanggal + cari kode/produk + panel detail (item, dibayar, kembalian, alasan batal) sudah ada, jadi UAT-08 bisa dijalankan di web. Kurang: filter status & kasir, pagination, route API. Default terpilih hard-coded `TRX-0064` |
| 5.12 ★ | Batalkan transaksi | Sebagian | `OwnerController@batalkanTransaksi`, `POST /owner/transaksi/{order}/batal`; modal di `owner/riwayat-transaksi.blade.php` | Sudah: `alasan_batal` wajib, status `batal`, stok kembali lewat movement `penyesuaian` per item, log, dalam transaksi DB, tolak jika sudah batal. Kurang: produk tidak di-`lockForUpdate()`; `data_lama.status` hard-coded `'selesai'` (seharusnya status asli); tidak ada respons JSON maupun route API; `getMessage()` bocor |
| 5.13 | Laporan penjualan per periode (total, jumlah transaksi, rata-rata, tren harian, produk terlaris, perbandingan periode lalu) | Belum | `OwnerController@laporan`, `GET /owner/laporan`, `GET /api/owner/laporan` | Halaman & endpoint ada, tetapi **semua angka hard-coded** (38.420.000, 1.264 transaksi, tren, 5 produk terlaris, +12,6%). Parameter `periode`/`range` tidak dipakai di query. Perlu agregasi SQL nyata, kecualikan `batal` (7.13) |
| 5.14 ★ | Backup: riwayat, buat manual, jadwal otomatis, pulihkan | Sebagian | `OwnerController@pencadangan`, `@createBackup`, `@updateBackupSchedule`; `/owner/pencadangan` | Riwayat dari tabel `backups` dan penyimpanan jadwal ke `settings` sudah ada. Kurang: `createBackup` **tidak membuat file** (hanya baris DB dengan `ukuran_kb = 1200` tetap dan status selalu `berhasil`); `updateBackupSchedule` tanpa validasi; jadwal tidak dijalankan scheduler mana pun (7.9); tombol Pulihkan hanya `alert()`, tidak ada restore + log BR-11. Hanya route web |
| 5.15 ★ | Ekspor laporan ke PDF & Excel | Sebagian | `OwnerController@exportExcel`, `GET /owner/laporan/export-excel`; tombol PDF di `owner/laporan.blade.php:41-51` | Excel: berkas **CSV** berisi semua order sepanjang masa (termasuk `batal`), tanpa filter periode dan tanpa ringkasan total / jumlah transaksi / produk terlaris (UAT-09). PDF: hanya `window.print()` di browser, belum ada file PDF dari server. Belum ada paket `barryvdh/laravel-dompdf` / `phpoffice/phpspreadsheet` |
| 5.16 | Reset password user | Sebagian | `OwnerController@resetPasswordPengguna`, `POST /owner/pengguna/{user}/reset` | Password di-hash + log. Kurang: tanpa validasi (password kosong → default `password123`), password baru ditampilkan di pesan flash, belum mencabut token Sanctum milik user tersebut (`$user->tokens()->delete()`) |
| 5.17 ★ | Unduh file backup | Belum | Tombol Unduh di `owner/pencadangan.blade.php:135` | Tombol hanya `alert()`. Belum ada route, method, atau file. Simpan di `storage/app/private`, kirim lewat `response()->download()`, hanya owner |
| 5.18 ★ | Kirim daftar restock ke pemasok via WhatsApp (baris baru 26 Nov) | Sebagian | `owner/pemasok.blade.php:204-226` (`kirimWhatsApp()`, `salinDaftarPesanan()`) | Tombol ada, tetapi pesan **hard-coded** (Hakau/Lumpia/Bakpao @20 porsi) dan nomor **hard-coded** `6281234567890`. Seharusnya dibangun dari `kebutuhan_stok` (jumlah = 2 × stok_minimum, BR-04) dan nomor `kontak` pemasok yang dipilih (dinormalisasi ke format 62…) |

## 6. Aturan bisnis BR-01 s.d. BR-11

| # | Kebutuhan | Status | File / Endpoint | Yang masih kurang |
|---|-----------|--------|-----------------|-------------------|
| BR-01 | Setiap perubahan stok = 1 baris `stock_movements` dalam 1 transaksi DB; stok tidak negatif | Sebagian | `storeProduk`, `updateProduk`, `storeStokMasuk`, `batalkanTransaksi`, `KasirController@storeTransaksi`, `DapurController@storeProduksi` | Keenam titik perubahan stok sekarang menulis 1 movement dalam transaksi DB. Kurang: belum ada service terpusat; `updateProduk` dan `batalkanTransaksi` tanpa `lockForUpdate()`; bug item ganda di transaksi kasir (3.2) bisa membuat stok tidak sesuai jumlah movement; tidak ada constraint DB stok ≥ 0 |
| BR-02 | Jenis pergerakan dipakai sesuai sumbernya | Sudah | `stok_awal` (`storeProduk`), `masuk` (`storeStokMasuk`), `produksi` (`storeProduksi`), `terjual` (`storeTransaksi`), `penyesuaian` (`updateProduk`, `batalkanTransaksi`) | Kelima jenis dipakai sesuai sumbernya |
| BR-03 | Status stok dihitung: Habis / Menipis / Aman | Sebagian | `Product::getStatusStokAttribute()` | Accessor benar dan dipakai di Blade ketiga peran. Belum `$appends`, jadi tidak ada di JSON |
| BR-04 | Perlu diproduksi = 2 × stok_minimum (hanya Menipis/Habis) | Sebagian | `Product::getPerluDiproduksiAttribute()`, `dapur/stok.blade.php` | Dipakai di halaman stok Dapur. Belum `$appends`; dashboard owner dan daftar WhatsApp (5.18) belum memakai nilai ini |
| BR-05 | Stok berkurang saat kasir simpan transaksi (status `baru`) | Sudah | `KasirController@storeTransaksi` | — (lihat cacat item ganda di 3.2) |
| BR-06 ★ | Status satu arah; batal wajib alasan, stok kembali lewat movement `penyesuaian` | Sebagian | `DapurController@updateStatusPesanan`, `OwnerController@batalkanTransaksi` | Bagian batal sudah jalan (5.12, tugas ★). Bagian status satu arah **belum ditegakkan** sama sekali (4.2, bukan tugas ★) |
| BR-07 ★ | `kembalian = dibayar − total`, tidak boleh negatif; kode TRX berurutan | Sebagian | `KasirController.php:111-127` | `dibayar < total` ditolak, kembalian dihitung di server. Kode TRX belum aman (3.3) |
| BR-08 | `harga_satuan` disalin dari harga produk saat transaksi | Sudah | `KasirController@storeTransaksi` | Harga diambil dari DB, bukan dari request |
| BR-09 | Produksi dapur = movement `produksi` + sesi; tanpa tabel produksi | Sudah | `DapurController@storeProduksi` | — |
| BR-10 | User tidak dihapus, hanya dinonaktifkan | Sudah | `OwnerController@toggleStatusPengguna`, `AuthController@login` | Tidak ada kode penghapusan user. Sisa risiko di tingkat DB: FK cascade di `activity_logs` (1.3) |
| BR-11 | Activity log: tambah/edit produk, penyesuaian stok, batal transaksi, ubah user, pulihkan backup | Sebagian | `storeProduk`, `updateProduk`, `destroyProduk`, `batalkanTransaksi`, `storePengguna`, `toggleStatusPengguna`, `resetPasswordPengguna`, dll. | Sudah dicatat: tambah/edit/nonaktif produk, penyesuaian (lewat edit produk), batal, tambah/status/reset user. Kurang: edit user (fitur belum ada, 5.7), pulihkan backup (belum ada, 5.14). Belum ada helper log terpusat dan belum ada halaman/endpoint untuk melihat log |

## 7. Non-fungsional

| # | Kebutuhan | Status | File / Endpoint | Yang masih kurang |
|---|-----------|--------|-----------------|-------------------|
| 7.1 | Password di-hash bcrypt | Sudah | `User::casts()` (`'password' => 'hashed'`); `storePengguna` & `resetPasswordPengguna` memakai `bcrypt()` | — |
| 7.2 | Password & token tersembunyi di JSON | Sudah | `User::$hidden` | — |
| 7.3 | Validasi **semua** input di server dengan pesan error Bahasa Indonesia | Sebagian | Semua `store*`/`update*` di ketiga controller | Hampir semua aksi tulis sudah `validate()` dengan sebagian pesan Indonesia. Tanpa validasi: `updateBackupSchedule`, `resetPasswordPengguna`, parameter filter `tanggal`. Belum FormRequest. `.env.example` masih `APP_LOCALE=en` dan belum ada `lang/id/validation.php`, jadi pesan bawaan (`exists`, `integer`, `in`) masih bahasa Inggris |
| 7.4 | Format respons konsisten `{success, message, data}` | Sebagian | Semua controller | Sebagian endpoint sudah `data` (`storeTransaksi`, `storeProduksi`, `storeStokMasuk`, `dashboard`, `laporan`). Lainnya memakai `products`, `orders`, `baru/diproses/siap`, `product`, `movements`, `users`, `backups`, `user`. Error 403/404/500 bawaan Laravel tidak memakai format ini |
| 7.5 | Error 403 (akses ditolak) konsisten untuk API & web | Sebagian | `bootstrap/app.php` → `withExceptions`, `EnsureRole`, `errors/akses-ditolak.blade.php` | API sudah seragam: 401/403/404/429 dalam format `{success:false, message, data:null}` Bahasa Indonesia. Web: halaman khusus "Akses Ditolak" masih hanya di `/dashboard/{role}`; akses lintas modul (misal kasir → `/owner/produk`) tetap halaman 403 bawaan Laravel |
| 7.6 | Pesan error asli / stack trace tidak dikirim ke klien | Belum | Semua blok `catch` di `KasirController`, `DapurController`, `OwnerController`; `.env.example` | Sekarang ada **8 tempat** yang mengirim `$e->getMessage()` ke klien (JSON dan flash web). `.env.example` masih `APP_DEBUG=true`. Perlu pesan umum + `report($e)` |
| 7.7 ★ | Pembatasan percobaan login (throttle) | Sudah | `RateLimiter::for('login')` di `AppServiceProvider`; `throttle:login` di `POST /api/login` & `POST /login` | — 5 percobaan/menit per username + IP. API → 429 JSON; web → kembali ke form dengan pesan. Test: `test_login_dibatasi_5_percobaan_per_menit` |
| 7.8 | CORS untuk frontend standalone | Sudah | `config/cors.php` (default `api/*`) | — API memakai token Bearer (bukan cookie SPA), jadi `supports_credentials` & `SANCTUM_STATEFUL_DOMAINS` tidak perlu diubah |
| 7.9 ★ | Backup otomatis harian (default 02:00) + manual, dan bisa dipulihkan | Belum | `routes/console.php` (hanya `inspire`), `OwnerController@createBackup` | Tidak ada artisan command backup, tidak ada `Schedule::command(...)->dailyAt(...)` yang membaca `settings`, tidak ada file yang benar-benar dibuat (5.14), dan tidak ada restore (SQLite: salin file; MySQL: dump/impor) |
| 7.10 | Dependensi terpasang & bisa dijalankan | Sebagian | `composer.json`, `.env` | `composer install`, `migrate`, `migrate:fresh --seed`, `php artisan test` berjalan; Sanctum sudah masuk dependensi. Kurang: paket ekspor PDF/Excel (5.15). Setelah `git pull`, anggota tim perlu `composer install` + `php artisan migrate` |
| 7.11 ★ | Autentikasi token (Sanctum) + middleware peran di server | Sudah | `composer.json`, `bootstrap/app.php`, `routes/api.php`, `routes/web.php` | — Lihat 2.5, 2.8, 2.9 |
| 7.12 ★ | Tidak ada endpoint publik yang menampilkan password atau data akun | Sudah | `routes/api.php` (`/api/demo-accounts`) | — Route hanya didaftarkan saat `APP_ENV=local` (di `testing`/`production` → 404). Isinya dibaca dari akun aktif di DB (nama, username, peran) **tanpa password**. Test: `test_demo_accounts_tidak_tersedia_di_luar_lingkungan_local` |
| 7.13 | Laporan 1 bulan tampil < 5 detik | Belum | `OwnerController@laporan` | Laporan masih hard-coded (5.13), jadi belum bisa diukur. Gunakan agregasi SQL + index `orders(waktu, status)` |

## 8. Pengujian (skenario UAT & test lintas peran)

### 8a. Test otomatis umum

| # | Kebutuhan | Status | File / Endpoint | Yang masih kurang |
|---|-----------|--------|-----------------|-------------------|
| 8.1 | Test login (sukses, peran salah, password salah, web redirect, halaman login, akun nonaktif) | Sudah | `tests/Feature/AuthTest.php` (7 test) | — Disesuaikan 26 Nov: login API mengecek `data.token`; peran salah & password salah sama-sama 401 + pesan umum |
| 8.2 | Test halaman Owner (dashboard, produk, tambah/edit/hapus produk, stok masuk, pemasok, laporan, ekspor) | Sudah | `tests/Feature/OwnerTest.php` (6 test), `OwnerExtendedTest.php` (6 test) | Lulus (26 Nov). Hanya route web. Test dashboard & laporan hanya mengecek teks Blade, sehingga angka hard-coded (5.1, 5.13) tidak terdeteksi. Belum ada test untuk riwayat transaksi, pengguna, pencadangan |
| 8.3 ★ | Test API (JSON + token) untuk ketiga peran | Sebagian | `tests/Feature/ApiSecurityTest.php` (19 test) | Sudah: login token, pesan login umum, akses tiap grup dengan token asli, tamu 401, logout, token akun nonaktif, throttle, demo-accounts. Kurang: test fungsi endpoint lewat API (misal `POST /api/kasir/transaksi`, `POST /api/dapur/catat-produksi`, `POST /api/owner/stok-masuk`) dan isi JSON-nya |
| 8.4 | Test alur kasir: simpan transaksi → stok berkurang, kembalian, harga snapshot, stok/uang kurang ditolak, kode TRX berurutan | Sebagian | `tests/Feature/KasirTest.php` (8 test) | Sudah: stok berkurang + movement `terjual`, stok kurang ditolak, uang kurang ditolak, riwayat, struk. Kurang: harga snapshot (ubah harga setelah transaksi), kode TRX berurutan, item ganda (3.2), struk milik kasir lain (3.5) |
| 8.5 | Test alur dapur: transisi status valid/invalid, produksi menambah stok + sesi | Sebagian | `tests/Feature/DapurTest.php` (7 test) | Sudah: transisi maju, produksi + sesi, stok, riwayat. Kurang: transisi mundur/lompat **ditolak** (akan gagal karena 4.2), riwayat produksi hanya milik sendiri (4.4) |
| 8.6 ★ | Test pembatalan transaksi: stok kembali, alasan wajib, log tercatat | Belum | — | Fitur ada (5.12), test belum |
| 8.7 ★ | Matriks lintas peran (kasir→dapur/owner, dapur→kasir/owner, tamu→semua) harus 401/403 | Sudah | `ApiSecurityTest::test_matriks_akses_api_lintas_peran` (9 kombinasi), `test_tamu_tanpa_token_mendapat_401_json_bukan_redirect`; web: `AuthTest`, `OwnerTest`, `KasirTest`, `DapurTest` | — API: 3 peran × 3 grup (200 untuk peran sendiri, 403 lainnya) + tamu 401. Web: tiap modul menolak peran lain |
| 8.8 | Test unit BR-03/BR-04 (accessor status & perlu diproduksi) | Belum | `tests/Unit/` (hanya ExampleTest) | `DapurTest` sekilas mengecek `status_stok = Aman`, tetapi uji batas (stok 0, minimum−1, minimum) belum ada |
| 8.9 ★ | Test backup & pulihkan | Belum | — | Fitur backup riil belum ada (5.14, 7.9) |
| 8.10 | Data seeder cocok dengan angka di skenario UAT | Sudah | `database/seeders/DatabaseSeeder.php` | — (lihat 1.12–1.13) |

### 8b. Skenario UAT (wajib lulus)

Penilaian: **Sudah** = fitur jalan dan ada test yang memakai angka skenario. **Sebagian** = fitur jalan di web, tetapi test belum ada atau memakai angka lain, atau ada cacat. API sudah bisa dipakai dengan token (2.9), tetapi selain UAT-03 semua test UAT masih memakai route web.

| # | Skenario | Status | File / Endpoint | Yang masih kurang |
|---|----------|--------|-----------------|-------------------|
| UAT-01 ★ | Kasir: Siomay Ayam ×2, Hakau Udang ×1, Bakpao Coklat ×3, bayar 100.000 → total 84.000, kembalian 16.000, stok berkurang, status `baru` | Sebagian | `KasirController@storeTransaksi`; `KasirTest::test_kasir_can_process_transaction…` | Fitur jalan di web. Test hanya Siomay ×2 bayar 50.000; belum ada test 3 produk dengan total 84.000 / kembalian 16.000 |
| UAT-02 ★ | Kasir: pesan Hakau Udang 5 saat stok 4 → ditolak | Sebagian | `KasirTest::test_kasir_cannot_process_transaction_if_stock_insufficient` | Fitur jalan. Test memakai qty 10 (bukan 5) dan belum mengecek bahwa tidak ada order tersimpan |
| UAT-03 ★ | Kasir memanggil API owner → 403 | Sudah | `/api/owner/*` (`auth:sanctum` + `role:owner`); `ApiSecurityTest::test_uat_03_kasir_memanggil_api_owner_ditolak_403` | — Token kasir hasil login ke `GET /api/owner/dashboard`, `GET /api/owner/produk`, dan `POST /api/owner/produk` → 403 JSON, dan produk tidak tersimpan. Token owner ke endpoint yang sama → 200 (matriks 8.7) |
| UAT-04 ★ | Dapur: `baru → diproses → siap → selesai`; status mundur ditolak | Sebagian | `DapurTest::test_dapur_can_update_status_pesanan` | Jalur maju jalan & dites. Status mundur **diterima** (4.2), jadi bagian kedua gagal. Perlu perbaikan + test |
| UAT-05 ★ | Dapur: produksi sore Hakau Udang +20 → stok 4 jadi 24, jenis `produksi` | Sudah | `DapurTest::test_dapur_can_store_produksi_and_update_stock` | — Test memakai angka persis (sore, Hakau 4 → 24, jenis `produksi`) dan lulus. Versi API belum dites |
| UAT-06 ★ | Owner: tambah produk stok awal 48, minimum 10 → Aman, tercatat `stok_awal` 48 | Sebagian | `OwnerTest::test_owner_can_store_new_product_with_initial_stock_movement` | Fitur jalan. Test memakai angka 30 (bukan 48) dan tanpa cek status "Aman" |
| UAT-07 ★ | Owner: stok masuk Pemasok A, Bakpao Coklat +24 → stok 6 jadi 30, jenis `masuk` | Sebagian | `OwnerExtendedTest::test_owner_can_store_stok_masuk_and_update_inventory` | Fitur jalan. Test memakai produk stok 4 +20 → 24, bukan Bakpao 6 +24 → 30 |
| UAT-08 ★ | Owner: cari transaksi per tanggal → detail item, dibayar, kembalian tampil | Sebagian | `OwnerController@riwayatTransaksi`, `GET /owner/riwayat-transaksi?tanggal=…` | Fitur jalan di web (filter tanggal + panel detail). Belum ada test |
| UAT-09 ★ | Owner: ekspor laporan November ke Excel → berisi total, jumlah transaksi, produk terlaris | Belum | `OwnerController@exportExcel` | Berkas CSV ada, tetapi tanpa filter periode dan **tanpa** total, jumlah transaksi, atau produk terlaris. Laporan sumbernya juga hard-coded (5.13). Test hanya mengecek header `text/csv` |
| UAT-10 ★ | Owner menonaktifkan akun Dani → Dani tidak bisa login, transaksinya tetap ada | Sebagian | `OwnerController@toggleStatusPengguna`, `EnsureRole`; `AuthTest::test_deactivated_user_cannot_login_uat_10`, `ApiSecurityTest::test_token_akun_yang_dinonaktifkan_langsung_ditolak`, `test_session_web_akun_nonaktif_dikeluarkan` | Login ditolak, token lama langsung dicabut, session web dikeluarkan (semuanya dites). Kurang: test yang menonaktifkan lewat `toggle` oleh owner dan mengecek bahwa transaksi Dani tetap ada |

---

## Ringkasan

Cara hitung: Sudah = 1, Sebagian = 0,5, Belum = 0. Total 98 baris (termasuk 5.18 WhatsApp).

| Bagian | Baris | Sudah | Sebagian | Belum | Progres | Audit 26 Nov | 24 Nov |
|--------|------:|------:|---------:|------:|--------:|-------------:|-------:|
| 1. Database | 15 | 13 | 1 | 1 | 90% | 90% | 90% |
| 2. Login & hak akses | 11 | 11 | 0 | 0 | 100% | 59% | 55% |
| 3. API Kasir | 5 | 1 | 4 | 0 | 60% | 60% | 0% |
| 4. API Dapur | 5 | 2 | 3 | 0 | 70% | 70% | 0% |
| 5. API Owner | 18 | 3 | 13 | 2 | 53% | 53% | 12% |
| 6. Aturan bisnis | 11 | 5 | 6 | 0 | 73% | 73% | 27% |
| 7. Non-fungsional | 13 | 6 | 4 | 3 | 62% | 38% | 38% |
| 8. Pengujian (8a + UAT) | 20 | 6 | 10 | 4 | 55% | 45% | 23% |
| **Total** | **98** | **47** | **41** | **10** | **≈ 69%** | ≈ 59% | ≈ 35% |

UAT saja: **2 dari 10 lulus penuh** (UAT-03, UAT-05), 7 sebagian, 1 belum (UAT-09).

### Tugas Fatih (baris ★)

| Kelompok | Baris | Sudah · Sebagian · Belum |
|----------|-------|--------------------------|
| Keamanan API (Sanctum, middleware peran, login, throttle, demo-accounts) | 2.5, 2.6, 2.7, 2.8, 2.9, 2.11, 7.7, 7.11, 7.12, 8.3, 8.7 | 10 · 1 · 0 |
| Backup riil + scheduler | 5.14, 5.17, 7.9, 8.9 | 0 · 1 · 3 |
| Edit pemasok / pengguna | 5.6, 5.7 | 0 · 2 · 0 |
| WhatsApp stok | 5.18 | 0 · 1 · 0 |
| Batal transaksi | 5.12, BR-06 (bagian batal), 8.6 | 0 · 2 · 1 |
| Ekspor PDF | 5.15 | 0 · 1 · 0 |
| Generator kode TRX | 3.3, BR-07 | 0 · 2 · 0 |
| Test UAT-01 s.d. UAT-10 | UAT-01 … UAT-10 | 2 · 7 · 1 |
| **Jumlah** | **34 baris** | **12 Sudah · 17 Sebagian · 5 Belum ≈ 60%** |

2.11, 7.7, dan 7.12 kini ditandai ★ karena dikerjakan bersama keamanan API (26 Nov). Baris keamanan yang masih tanpa pemilik: **7.6** (8 blok `catch` mengirim `$e->getMessage()` ke klien, `APP_DEBUG=true` di `.env.example`).

Kesimpulan: fitur ketiga peran berjalan lewat web, dan sejak 26 November **API juga bisa dipakai** dengan token Sanctum dan dibatasi per peran. Progres ≈ 69%. Celah terbesar sekarang: (1) **status pesanan dapur bisa mundur** (UAT-04 gagal), (2) **laporan dan sebagian dashboard masih angka hard-coded** (UAT-09 gagal), (3) backup hanya baris palsu tanpa file, (4) beberapa cacat data: item ganda di keranjang, struk kasir lain bisa dibuka, kode TRX dari `id + 60`, dan (5) pesan error asli masih bocor ke klien (7.6).

## 5 pekerjaan prioritas berikutnya

1. **★ Kode TRX + batal transaksi.** Kode = nomor terbesar + 1 dengan lock (3.3, BR-07). Batal: `lockForUpdate()`, `data_lama` status asli, respons JSON + route API `role:owner`, test 8.6. *(5.12, BR-06)*
2. **★ Backup riil + scheduler.** Artisan command `backup:run` yang menyalin DB ke `storage/app/private/backups`, mengisi `ukuran_kb`/`status` sebenarnya; `Schedule` membaca `backup_frekuensi`/`backup_jam`; route unduh & pulihkan (+ log BR-11); test 8.9. *(5.14, 5.17, 7.9)*
3. **★ Fitur Owner yang kurang.** Edit/hapus pemasok, edit pengguna (+ log, cabut token saat peran diubah), WhatsApp dari data restock nyata + nomor pemasok, ekspor PDF dari server. *(5.6, 5.7, 5.18, 5.15)*
4. **★ Test UAT-01 s.d. UAT-10 dengan angka persis skenario**, sebaiknya lewat API dengan token (sekaligus melengkapi 8.3), lalu laporkan ke tim cacat di luar tugas ★ yang membuat UAT gagal: transisi status dapur (4.2, UAT-04), laporan & ekspor Excel hard-coded (5.13, 5.15, UAT-09), item ganda di keranjang (3.2), dan struk kasir lain (3.5).
5. **Sepakati pemilik 7.6** (pesan error umum + `report($e)` di semua `catch`, `APP_DEBUG=false` untuk demo), dan minta tim menjalankan `composer install` + `php artisan migrate` setelah pull.
