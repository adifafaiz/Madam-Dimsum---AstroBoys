# Checklist Kebutuhan Backend — DimsumKu (Madam Dimsum / AstroBoys)

> Acuan: `CLAUDE.md` di root repo (dibuat ulang 24 November 2026; ringkasan PRD/laporan capstone) · Diperiksa: 24 November 2026 · Commit: `ac5ecd6`
> Diperbarui: 26 November 2026, setelah `git pull` ke `a59ff99` dan penggabungan `DatabaseSeeder.php` (belum di-commit). **Hanya baris seeder (1.11–1.14, 7.1, 8.10) dan hasil test yang diperiksa ulang.**
>
> Status: **Sudah** = terpenuhi sesuai kebutuhan · **Sebagian** = ada tetapi belum lengkap atau ada cacat · **Belum** = belum ada kode
>
> Catatan lingkup:
> - Yang diperiksa adalah folder `backend/` di repo utama.
> - Test terakhir dijalankan (`php artisan test`, 26 November 2026, commit `a59ff99` + seeder gabungan): **36 test lulus, 146 assertion, 0 gagal** (20,5 detik). Test baru dari pull: `KasirTest`, `DapurTest`, `OwnerExtendedTest`.
> - **Peringatan: bagian 2–8 belum diaudit ulang terhadap kode dari pull.** Commit `c794bf2` dan `a59ff99` menambah `KasirController`, `DapurController`, perluasan `OwnerController` (laporan, ekspor, pemasok, stok masuk, pengguna, pencadangan), route web/API, dan view. Banyak baris "Belum" di bagian 3, 4, 5, dan 8 kemungkinan sudah berubah status. Angka di Ringkasan masih angka 24 November.

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
| 2.4 | Login web (session) + redirect sesuai peran | Sudah | `routes/web.php`, `/dashboard/{role}` | — |
| 2.5 | Sanctum terpasang & login API mengembalikan token | Belum | `composer.json`, `app/Models/User.php` | `laravel/sanctum` tidak ada di `composer.json` repo utama. Tidak ada `config/sanctum.php`, migration `personal_access_tokens`, atau trait `HasApiTokens`. Respons login tidak berisi token |
| 2.6 | `GET /api/user` (cek sesi token) | Sebagian | `routes/api.php` | Route sudah ada, tetapi guard `sanctum` belum ada, jadi akan error |
| 2.7 | Logout API mencabut token | Sebagian | `AuthController@logout`, `POST /api/logout` | Baru logout session. Tidak ada pencabutan token dan route tidak dilindungi auth |
| 2.8 | Middleware peran (`role:kasir` / `role:dapur` / `role:owner`) per grup route | Belum | `bootstrap/app.php` | Belum ada middleware terdaftar. Pengecekan masih manual (`checkOwnerAccess()` di `OwnerController`, closure di `web.php`) |
| 2.9 | Grup route API Owner terlindungi auth | Belum | `routes/api.php` (`prefix('owner')`) | Tidak memakai middleware `auth`. Selain itu **`use App\Http\Controllers\OwnerController;` tidak ada** di `api.php`, sehingga semua `/api/owner/*` gagal (class tidak ditemukan). Jika import diperbaiki, `Auth::user()` di API stateless tetap `null`, jadi hasilnya selalu 403 |
| 2.10 | Halaman "Akses Ditolak" (FR-02) untuk web | Sudah | `resources/views/errors/akses-ditolak.blade.php` | — |
| 2.11 | Pesan gagal login umum ("Username atau password salah"), termasuk saat peran salah, supaya username/peran tidak bocor | Belum | `AuthController@login` | Saat ini setiap kegagalan punya pesan & kode sendiri: username tidak ada → 404 "Username … tidak ditemukan"; peran salah → 422 "Akun ini terdaftar sebagai peran Kasir…" (membocorkan peran); password salah → 401. Ketiganya harus memakai satu pesan + satu kode HTTP yang sama. Cek `is_active` sebaiknya dilakukan **setelah** password benar, agar pesan "dinonaktifkan" tidak membocorkan keberadaan akun. `AuthTest` perlu disesuaikan. (Endpoint `/api/demo-accounts` dipindah ke 7.12) |

## 3. API Kasir

| # | Kebutuhan | Status | File / Endpoint | Yang masih kurang |
|---|-----------|--------|-----------------|-------------------|
| 3.1 | Daftar produk aktif + stok + status stok | Belum | `GET /api/kasir/produk` | Controller & route belum ada |
| 3.2 | Simpan transaksi (keranjang, total, uang diterima, kembalian; tolak jika stok kurang atau uang kurang) | Belum | `POST /api/kasir/transaksi` | Perlu validasi item/qty, tolak stok kurang (UAT-02) & uang kurang (BR-07), `DB::transaction`, salin harga (BR-08), movement `terjual` (BR-01/05), status awal `baru` (UAT-01) |
| 3.3 | Generator kode `TRX-XXXX` (4 digit, berurutan) | Belum | — | Harus aman dari duplikat (dalam transaksi + kolom unik). Seeder sudah memakai TRX-0059 s.d. 0062 |
| 3.4 | Riwayat transaksi milik kasir sendiri | Belum | `GET /api/kasir/transaksi` | Filter `user_id = auth()->id()`, pagination/filter tanggal |
| 3.5 | Detail transaksi milik sendiri (item, dibayar, kembalian) | Belum | `GET /api/kasir/transaksi/{id}` (belum ada di rencana route) | Wajib menurut CLAUDE.md ("riwayat … + detail"). Kasir hanya boleh membuka transaksinya sendiri (selain itu 403) |

## 4. API Dapur

| # | Kebutuhan | Status | File / Endpoint | Yang masih kurang |
|---|-----------|--------|-----------------|-------------------|
| 4.1 | Antrian pesanan (status baru/diproses/siap) | Belum | `GET /api/dapur/pesanan` | Controller & route belum ada |
| 4.2 | Ubah status pesanan satu arah | Belum | `PATCH /api/dapur/pesanan/{id}/status` | Validasi transisi baru→diproses→siap→selesai (BR-06) |
| 4.3 | Catat produksi (tanggal, sesi, daftar produk + jumlah, catatan) | Belum | `POST /api/dapur/produksi` | Satu request berisi beberapa produk. Tiap produk = 1 movement `produksi` + `sesi` + `keterangan`, tambah stok dalam transaksi (BR-01, BR-09, UAT-05) |
| 4.4 | Riwayat produksi milik sendiri | Belum | `GET /api/dapur/produksi` | Filter `jenis=produksi` & `user_id` sendiri |
| 4.5 | Lihat stok + jumlah perlu diproduksi | Belum | `GET /api/dapur/stok` | Tampilkan `status_stok` & `perlu_diproduksi` (BR-03/04) |

## 5. API Owner

| # | Kebutuhan | Status | File / Endpoint | Yang masih kurang |
|---|-----------|--------|-----------------|-------------------|
| 5.1 | Dashboard (penjualan hari ini + % vs kemarin, pendapatan bulan ini + jumlah transaksi, jumlah stok menipis, produk terlaris hari ini, penjualan 7 hari, 5 transaksi terbaru, daftar restock), semua dari query | Sebagian | `OwnerController@dashboard`, `GET /owner/dashboard`, `GET /api/owner/dashboard` | Ada **angka mockup hard-coded**: penjualan 1.075.000 & pendapatan 38.420.000 jika nol, produk terlaris "Siomay Ayam/74", grafik 7 hari statis. Belum ada "% vs kemarin". Transaksi terbaru mengambil 6, seharusnya 5. Daftar restock belum menyertakan jumlah restock (BR-04). `whereMonth` tanpa filter tahun. Route API rusak (lihat 2.9) |
| 5.2 | Daftar produk + cari + filter kategori | Sebagian | `OwnerController@produk`, `GET /owner/produk`, `GET /api/owner/produk` | Respons JSON belum `{success, message, data}`. `status_stok`/`perlu_diproduksi` tidak ikut di JSON (belum `$appends`). Produk nonaktif tidak bisa dilihat |
| 5.3 | Tambah produk + stok awal + log | Sebagian | `OwnerController@storeProduk`, `POST /owner/produk`, `POST /api/owner/produk` | Validasi, movement `stok_awal`, dan `activity_logs` sudah ada. Kurang: `kategori` tidak divalidasi terhadap daftar, pesan exception mentah dikirim ke klien, format respons belum sesuai konvensi |
| 5.4 | Edit data produk (+ log BR-11) | Belum | `PUT /api/owner/produk/{id}` | Simpan `data_lama`/`data_baru` di `activity_logs`. Jika stok ikut diubah, ikuti 5.9 |
| 5.5 | Hapus/nonaktifkan produk | Belum | `DELETE /api/owner/produk/{id}` | Sebaiknya soft-disable (`is_active=false`) agar riwayat aman |
| 5.6 | CRUD supplier | Belum | `/api/owner/supplier` | — |
| 5.7 | CRUD user (nonaktifkan, bukan hapus) | Belum | `/api/owner/user` | `DELETE` harus menonaktifkan (BR-10), hash password, catat log (BR-11), cegah owner menonaktifkan dirinya sendiri |
| 5.8 | Stok masuk dari supplier | Belum | `POST /api/owner/stok-masuk` | Movement `masuk` + `supplier_id` dalam transaksi |
| 5.9 | Ubah stok lewat edit produk = `penyesuaian`, keterangan wajib | Belum | `PUT /api/owner/produk/{id}` | Selisih stok lama→baru dicatat sebagai movement `penyesuaian` (qty ±, `keterangan` wajib), dalam transaksi DB, + `activity_logs` (BR-01, BR-02, BR-11) |
| 5.10 | Riwayat stok per produk | Sebagian | `OwnerController@riwayatStok`, `GET /api/owner/produk/{product}/riwayat` | Path berbeda dari rencana (`/api/owner/stok/{product_id}/riwayat`). Tidak ada pagination/filter tanggal. Route API rusak (2.9). Tidak ada route web padanannya, hanya JSON |
| 5.11 | Riwayat semua transaksi (filter tanggal/status/kasir) + detail | Belum | `GET /api/owner/transaksi`, `GET /api/owner/transaksi/{id}` | Detail menampilkan item, dibayar, kembalian (UAT-08) |
| 5.12 | Batalkan transaksi | Belum | `POST /api/owner/transaksi/{id}/batal` | Wajib `alasan_batal`, kembalikan stok, catat log (BR-06, BR-11) |
| 5.13 | Laporan penjualan per periode (total, jumlah transaksi, rata-rata, tren harian, produk terlaris, perbandingan periode lalu) | Belum | `GET /api/owner/laporan/penjualan` | Kecualikan order `batal`. Laporan 1 bulan harus tampil < 5 detik (7.13) |
| 5.14 | Backup: riwayat, buat manual, jadwal otomatis, pulihkan | Belum | `GET/POST /api/owner/backup`, `POST /api/owner/backup/{id}/pulihkan` | Baru ada model `Backup`. Jadwal dibaca dari `settings` (`backup_frekuensi`, `backup_jam`). Pulihkan dicatat di `activity_logs` (BR-11). Lihat 7.9 |
| 5.15 | Ekspor laporan ke PDF & Excel | Belum | — (belum ada di rencana route), misal `GET /api/owner/laporan/penjualan/ekspor?format=pdf\|xlsx` | Belum ada paket ekspor di `composer.json` (misal `maatwebsite/excel` / `phpoffice/phpspreadsheet` dan `barryvdh/laravel-dompdf`). File Excel wajib berisi total, jumlah transaksi, produk terlaris (UAT-09) |
| 5.16 | Reset password user | Belum | — (belum ada di rencana route), misal `POST /api/owner/user/{id}/reset-password` | Password baru di-hash, catat `activity_logs` (BR-11), cabut token lama user tersebut |
| 5.17 | Unduh file backup | Belum | — (belum ada di rencana route), misal `GET /api/owner/backup/{id}/unduh` | Hanya owner. File di luar folder publik (`storage/app/private`), dikirim lewat `response()->download()` |

## 6. Aturan bisnis BR-01 s.d. BR-11

| # | Kebutuhan | Status | File / Endpoint | Yang masih kurang |
|---|-----------|--------|-----------------|-------------------|
| BR-01 | Setiap perubahan stok = 1 baris `stock_movements` dalam 1 transaksi DB; stok tidak negatif | Sebagian | `OwnerController@storeProduk` | Baru dipakai saat tambah produk. Belum ada service terpusat (misal `StockService::adjust()`) dengan `lockForUpdate()` dan penolakan stok negatif. Seeder sudah mengikuti aturan ini (1.13) |
| BR-02 | Jenis pergerakan dipakai sesuai sumbernya: `stok_awal` (tambah produk), `masuk` (pemasok, oleh owner), `produksi` (dapur), `terjual` (transaksi kasir), `penyesuaian` (edit stok owner / batal transaksi) | Sebagian | Enum di migration ERD v2; `OwnerController@storeProduk`, `DatabaseSeeder` | Kelima jenis sudah ada di enum, tetapi baru `stok_awal` yang dipakai (tambah produk & seeder). `masuk` (5.8), `produksi` (4.3), `terjual` (3.2), dan `penyesuaian` (5.4 edit stok, 5.12 batal) belum punya kode. Seeder sudah memakai `stok_awal`, `produksi`, dan `terjual` dengan benar (1.13) |
| BR-03 | Status stok dihitung: Habis / Menipis / Aman | Sebagian | `Product::getStatusStokAttribute()` | Accessor benar dan dipakai di Blade, tetapi tidak masuk `$appends`, sehingga tidak muncul di respons JSON |
| BR-04 | Perlu diproduksi = 2 × stok_minimum (hanya Menipis/Habis) | Sebagian | `Product::getPerluDiproduksiAttribute()` | Logika benar. Belum di-`$appends` dan belum dipakai di endpoint mana pun |
| BR-05 | Stok berkurang saat kasir simpan transaksi (status `baru`) | Belum | — | Tergantung 3.2. Skema sudah siap |
| BR-06 | Status satu arah; batal wajib alasan, stok kembali lewat movement `penyesuaian` | Belum | — | Tergantung 4.2 & 5.12. Enum status sudah ada |
| BR-07 | `kembalian = dibayar − total`, tidak boleh negatif; kode TRX berurutan | Belum | `KasirController.php:125-127` (dari pull, belum diaudit penuh) | Validasi `dibayar >= total` di 3.2. **Temuan 26 Nov:** kode baru dibuat dari `id order terakhir + 60`, bukan dari nomor kode terbesar. Kode jadi bergantung pada id dan seeder, akan melompat kalau ada order terhapus atau seeder berubah, dan tidak dikunci terhadap dua kasir yang menyimpan bersamaan. Seharusnya nomor terbesar + 1 di dalam transaksi DB dengan lock (3.3). Seeder sementara disesuaikan (kode = id + 59) agar tetap berurutan |
| BR-08 | `harga_satuan` disalin dari harga produk saat transaksi | Belum | — | Harga diambil dari DB (bukan dari request) di 3.2 |
| BR-09 | Produksi dapur = movement `produksi` + sesi; tanpa tabel produksi | Belum | — | Tergantung 4.3. Kolom `sesi` sudah ada |
| BR-10 | User tidak dihapus, hanya dinonaktifkan | Sebagian | `AuthController@login` | Login sudah menolak akun nonaktif. Belum ada endpoint nonaktifkan (5.7). FK cascade di `activity_logs` (1.3) |
| BR-11 | Activity log: tambah/edit produk, penyesuaian stok, batal transaksi, ubah user, pulihkan backup | Sebagian | `storeProduk` | Baru "tambah produk" yang dicatat. Belum ada helper log terpusat dan belum ada endpoint owner untuk melihat log |

## 7. Non-fungsional

| # | Kebutuhan | Status | File / Endpoint | Yang masih kurang |
|---|-----------|--------|-----------------|-------------------|
| 7.1 | Password di-hash bcrypt | Sudah | `User::casts()` (`'password' => 'hashed'`); seeder mengirim password polos yang di-hash oleh cast | — (nama kolom `name`/`password` sudah sama dengan CLAUDE.md) |
| 7.2 | Password & token tersembunyi di JSON | Sudah | `User::$hidden` | — |
| 7.3 | Validasi **semua** input di server dengan pesan error Bahasa Indonesia | Sebagian | `AuthController@login`, `OwnerController@storeProduk` | Baru 2 endpoint yang divalidasi. Belum memakai FormRequest. `.env.example` masih `APP_LOCALE=en` dan belum ada `lang/id/validation.php`, jadi pesan bawaan (misal `numeric`, `exists`) masih bahasa Inggris |
| 7.4 | Format respons konsisten `{success, message, data}` | Sebagian | `AuthController`, `OwnerController` | Login memakai `user`, produk memakai `products`/`categories`, riwayat memakai `product`/`movements`. Belum dibungkus `data` |
| 7.5 | Error 403 (akses ditolak) konsisten untuk API & web | Sebagian | `errors/akses-ditolak.blade.php`, `abort(403)` | Web sudah. Untuk API, `abort(403)` menghasilkan JSON bawaan Laravel (`{"message":"Forbidden"}`, bahasa Inggris). Perlu handler di `bootstrap/app.php` → `withExceptions` untuk 401/403/404/422 |
| 7.6 | Pesan error asli / stack trace tidak dikirim ke klien | Belum | `OwnerController@storeProduk`, `.env.example` | `storeProduk` mengirim `$e->getMessage()` ke klien. `.env.example` memakai `APP_DEBUG=true` (stack trace tampil). Perlu `APP_DEBUG=false` untuk demo/produksi + handler JSON umum di `bootstrap/app.php` yang mengembalikan pesan Indonesia dan mencatat detail ke log |
| 7.7 | Pembatasan percobaan login (throttle) | Belum | `routes/api.php`, `routes/web.php` | Tambah `throttle:5,1` atau `RateLimiter` pada route login |
| 7.8 | CORS untuk frontend standalone (`frontend/`, `http://127.0.0.1:8000/api`) | Sudah | `config/cors.php` (default `api/*`) | Jika memakai Sanctum mode SPA/cookie, atur `supports_credentials` & `SANCTUM_STATEFUL_DOMAINS` |
| 7.9 | Backup otomatis harian (default 02:00) + manual, dan bisa dipulihkan | Belum | `app/Models/Backup.php` saja | Belum ada artisan command, entri jadwal harian 02:00 di scheduler, jadwal (`routes/console.php` hanya `inspire`), folder penyimpanan, atau proses restore (SQLite: salin file `.sqlite`; MySQL: dump) |
| 7.10 | Dependensi terpasang & bisa dijalankan | Sebagian | `composer.json`, `.env` | `composer install` berhasil, `.env` lokal + `database/database.sqlite` sudah dibuat, `migrate:fresh --seed` dan `php artisan test` berjalan tanpa error. Yang kurang: Sanctum belum masuk dependensi (2.5) |
| 7.11 | Autentikasi token (Sanctum) + middleware peran di server | Belum | `composer.json`, `bootstrap/app.php`, `routes/api.php` | Sama dengan 2.5 dan 2.8: Sanctum belum terpasang, belum ada middleware peran, dan grup API Owner tanpa auth (2.9) |
| 7.12 | Tidak ada endpoint publik yang menampilkan password atau data akun | Belum | `GET /api/demo-accounts` (`routes/api.php`) | Endpoint tanpa auth ini mengembalikan username + password + nama semua peran. Hapus, atau aktifkan hanya saat `APP_ENV=local`. Lihat juga 2.11 (pesan login) |
| 7.13 | Laporan 1 bulan tampil < 5 detik | Belum | `GET /api/owner/laporan/penjualan` | Endpoint laporan belum ada (5.13), jadi belum bisa diukur. Gunakan agregasi SQL (`SUM`/`COUNT`/`GROUP BY`), bukan loop di PHP. Tambah index `orders(waktu, status)`. Uji dengan data 1 bulan |

## 8. Pengujian (skenario UAT & test lintas peran)

### 8a. Test otomatis umum

| # | Kebutuhan | Status | File / Endpoint | Yang masih kurang |
|---|-----------|--------|-----------------|-------------------|
| 8.1 | Test login (sukses, peran salah, password salah, web redirect, halaman login, akun nonaktif) | Sudah | `tests/Feature/AuthTest.php` (7 test) | Ketujuh test lulus (run 24 November 2026). Saat 2.11 diperbaiki, assertion kode HTTP/pesan (422 peran salah, 401 password salah) harus diganti ke pesan umum |
| 8.2 | Test halaman Owner (dashboard, produk, tambah produk + stok_awal) | Sudah | `tests/Feature/OwnerTest.php` (4 test) | Keempat test lulus (run 24 November 2026). Hanya route web. Assertion dashboard bergantung pada teks Blade, bukan angka |
| 8.3 | Test API Owner (JSON) | Belum | — | Test ini akan langsung menemukan bug import di 2.9 |
| 8.4 | Test alur kasir: simpan transaksi → stok berkurang, kembalian, harga snapshot, stok/uang kurang ditolak, kode TRX berurutan | Belum | — | Mencakup BR-01/05/07/08 |
| 8.5 | Test alur dapur: transisi status valid/invalid, produksi menambah stok + sesi | Belum | — | Mencakup BR-06/09 |
| 8.6 | Test pembatalan transaksi: stok kembali, alasan wajib, log tercatat | Belum | — | Mencakup BR-06/11 |
| 8.7 | Matriks lintas peran (kasir→dapur/owner, dapur→kasir/owner, tamu→semua) harus 401/403 | Sebagian | `AuthTest::test_cross_role_access…`, `OwnerTest::test_non_owner_forbidden…` | Baru 2 kombinasi di web (dapur→/dashboard/owner, kasir→/owner/*). Belum ada untuk API dan belum ada untuk tamu (tanpa login) |
| 8.8 | Test unit BR-03/BR-04 (accessor status & perlu diproduksi) | Belum | `tests/Unit/` (hanya ExampleTest) | Uji batas: stok 0, stok = minimum−1, stok = minimum |
| 8.9 | Test backup & pulihkan | Belum | — | — |
| 8.10 | Data seeder cocok dengan angka di skenario UAT | Sudah | `database/seeders/DatabaseSeeder.php` | — Terverifikasi (26 Nov): total UAT-01 dengan harga seeder = 30.000 + 18.000 + 36.000 = **84.000**. Hakau Udang stok 4 (UAT-02/05), Bakpao Coklat stok 6 + "Pemasok A" ada (UAT-07), stok akhir Siomay Ayam 48 / minimum 10 (Aman, sama dengan angka UAT-06), ada pesanan `baru`/`diproses`/`siap` untuk UAT-04, Dani **aktif** dan punya 5 transaksi, jadi UAT-10 bisa didemokan langsung |

### 8b. Skenario UAT (wajib lulus)

| # | Skenario | Status | File / Endpoint | Yang masih kurang |
|---|----------|--------|-----------------|-------------------|
| UAT-01 | Kasir: Siomay Ayam ×2, Hakau Udang ×1, Bakpao Coklat ×3, bayar 100.000 → total 84.000, kembalian 16.000, stok berkurang, status `baru` | Belum | `POST /api/kasir/transaksi` | Endpoint belum ada (3.2). Data seeder sudah cocok (8.10). Belum ada test |
| UAT-02 | Kasir: pesan Hakau Udang 5 saat stok 4 → ditolak | Belum | `POST /api/kasir/transaksi` | Endpoint belum ada (3.2). Pastikan stok tidak berubah & tidak ada order tersimpan. Belum ada test |
| UAT-03 | Kasir memanggil API owner → 403 | Belum | `/api/owner/*` | Saat ini `/api/owner/*` error karena import hilang (2.9), dan tanpa token/middleware peran (2.5, 2.8). Test yang ada hanya untuk route **web** `/owner/*` |
| UAT-04 | Dapur: `baru → diproses → siap → selesai`; status mundur ditolak | Belum | `PATCH /api/dapur/pesanan/{id}/status` | Endpoint belum ada (4.2). Belum ada test |
| UAT-05 | Dapur: produksi sore Hakau Udang +20 → stok 4 jadi 24, jenis `produksi` | Belum | `POST /api/dapur/produksi` | Endpoint belum ada (4.3). Belum ada test |
| UAT-06 | Owner: tambah produk stok awal 48, minimum 10 → Aman, tercatat `stok_awal` 48 | Sebagian | `OwnerController@storeProduk`, `POST /owner/produk`; `OwnerTest::test_owner_can_store_new_product_with_initial_stock_movement` | Fitur jalan di web dan test mengecek `stok_awal`, tetapi dengan angka 30 (bukan 48) dan tanpa cek status "Aman". Versi API rusak (2.9) |
| UAT-07 | Owner: stok masuk Pemasok A, Bakpao Coklat +24 → stok 6 jadi 30, jenis `masuk` | Belum | `POST /api/owner/stok-masuk` | Endpoint belum ada (5.8). Belum ada test |
| UAT-08 | Owner: cari transaksi per tanggal → detail item, dibayar, kembalian tampil | Belum | `GET /api/owner/transaksi`, `GET /api/owner/transaksi/{id}` | Endpoint belum ada (5.11). Belum ada test |
| UAT-09 | Owner: ekspor laporan November ke Excel → berisi total, jumlah transaksi, produk terlaris | Belum | ekspor laporan (5.15) | Laporan (5.13) & ekspor (5.15) belum ada, paket Excel belum terpasang. Belum ada test |
| UAT-10 | Owner menonaktifkan akun Dani → Dani tidak bisa login, transaksinya tetap ada | Sebagian | `AuthController@login`; `AuthTest::test_deactivated_user_cannot_login_uat_10` | Bagian "tidak bisa login" sudah jalan & dites. Endpoint nonaktifkan user belum ada (5.7). Test belum mengecek bahwa transaksi Dani tetap ada. Seeder sudah punya transaksi Dani (8.10) |

---

## Ringkasan

Cara hitung: Sudah = 1, Sebagian = 0,5, Belum = 0.

| Bagian | Baris | Sudah | Sebagian | Belum | Progres |
|--------|------:|------:|---------:|------:|--------:|
| 1. Database | 15 | 13 | 1 | 1 | 90% |
| 2. Login & hak akses | 11 | 5 | 2 | 4 | 55% |
| 3. API Kasir | 5 | 0 | 0 | 5 | 0% |
| 4. API Dapur | 5 | 0 | 0 | 5 | 0% |
| 5. API Owner | 17 | 0 | 4 | 13 | 12% |
| 6. Aturan bisnis | 11 | 0 | 6 | 5 | 27% |
| 7. Non-fungsional | 13 | 3 | 4 | 6 | 38% |
| 8. Pengujian (8a + UAT) | 20 | 3 | 3 | 14 | 23% |
| **Total** | **97** | **24** | **20** | **53** | **≈ 35%** |

UAT saja: 0 dari 10 lulus penuh, 2 sebagian (UAT-06, UAT-10).

Kesimpulan: fondasi sudah kuat (skema 9 tabel, model, login, halaman Owner awal). Namun **seluruh API Kasir dan Dapur belum ada**, API Owner belum bisa dipakai (rusak dan tanpa auth), sebagian besar aturan bisnis yang menyentuh stok dan transaksi belum diterapkan, dan belum ada satu pun UAT yang lulus penuh.

## 5 pekerjaan prioritas berikutnya

1. **Perbaiki auth API & keamanan login.** Tambah `laravel/sanctum` ke `composer.json`, `php artisan install:api`, trait `HasApiTokens`, login API mengembalikan token, dan logout mencabut token. Buat middleware `role:{peran}` di `bootstrap/app.php`, lalu bungkus `/api/kasir`, `/api/dapur`, `/api/owner` dengan `auth:sanctum` + `role`. Perbaiki import `OwnerController` di `routes/api.php`. Samakan pesan gagal login, dan hapus atau batasi `/api/demo-accounts`. *(2.5–2.11, 7.11, 7.12, UAT-03)*
2. **Buat `StockService` terpusat untuk BR-01/BR-02.** Satu fungsi `catat(product, jenis, qty, …)` yang memakai `DB::transaction` + `lockForUpdate()`, menolak stok negatif, dan menulis `saldo_setelah`. Semua endpoint stok (transaksi, batal, produksi, stok masuk, edit stok) wajib memakainya. *(BR-01, BR-02, 5.8, 5.9)*
3. **API Kasir: simpan transaksi, riwayat, detail.** Kode `TRX-XXXX` berurutan, harga dari DB (BR-08), `dibayar >= total` (BR-07), stok berkurang + movement `terjual` (BR-05), status `baru`. Tulis test UAT-01 & UAT-02 (data seeder sudah cocok). *(3.1–3.5, 8.4, 8.10)*
4. **API Dapur: antrian, status satu arah, produksi.** Validasi transisi status (BR-06), produksi multi-produk dengan sesi (BR-09), stok + perlu diproduksi (BR-03/04 lewat `$appends`), lalu test UAT-04 & UAT-05. *(4.1–4.5, 8.5)*
5. **API Owner inti + konvensi respons.** Stok masuk, riwayat/detail/batal transaksi, CRUD produk/supplier/user (nonaktifkan + reset password, BR-10), helper `ActivityLog` (BR-11), dashboard dari query nyata, dan exception handler JSON `{success, message, data}` untuk 401/403/404/422/500. Lalu test UAT-06 s.d. UAT-08 & UAT-10. Setelah itu baru laporan + ekspor PDF/Excel (UAT-09) dan backup otomatis 02:00. *(5.1–5.17, 7.4–7.6)*
