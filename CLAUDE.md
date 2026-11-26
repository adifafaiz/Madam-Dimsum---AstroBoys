# DimsumKu — Konteks Backend
Aplikasi web pencatatan penjualan, produksi, dan stok UMKM Madam Dimsum (capstone Tim AstroBoys). Backend Laravel di folder backend/. Jangan ubah folder frontend/.

## Peran & login
Peran (users.role): owner, kasir, dapur. Login: user memilih peran + username + password. Ditolak jika peran tidak sama dengan users.role atau is_active=false. Pesan gagal login dibuat umum ("Username atau password salah") supaya tidak membocorkan username. Setiap API dibatasi per peran; akses peran lain dibalas 403.

## Database (ERD v2, 9 tabel, semua tabel punya created_at/updated_at)
- users: id, name, username unik, password (hashed), role enum(owner,kasir,dapur), is_active
- activity_logs: id, user_id FK, aksi, tabel, data_lama json null, data_baru json null, waktu
- backups: id, waktu, ukuran_kb, jenis enum(otomatis,manual), status enum(berhasil,gagal), file_path
- settings: id, kunci unik, nilai (backup_frekuensi, backup_jam)
- suppliers: id, nama, kontak null
- products: id, nama, kategori, satuan, harga decimal(12,2), stok int, stok_minimum int, supplier_id FK null, is_active
- orders: id, kode unik (TRX-0001), waktu, user_id FK kasir, total, dibayar, kembalian decimal(12,2), status enum(baru,diproses,siap,selesai,batal), alasan_batal null
- order_items: id, order_id FK, product_id FK, qty, harga_satuan, subtotal
- stock_movements: id, product_id FK, user_id FK, supplier_id FK null, order_id FK null, tanggal, jenis enum(stok_awal,masuk,produksi,terjual,penyesuaian), qty (+ tambah / − kurang), saldo_setelah, sesi enum(pagi,sore) null, keterangan null

## Aturan bisnis
- BR-01: setiap perubahan products.stok selalu disertai 1 baris stock_movements dalam SATU transaksi database. Stok tidak boleh negatif.
- BR-02: jenis pergerakan: stok_awal (tambah produk), masuk (dari pemasok, oleh owner), produksi (dapur), terjual (transaksi kasir), penyesuaian (edit stok oleh owner atau batal transaksi).
- BR-03: status stok dihitung, tidak disimpan: Habis (stok=0), Menipis (stok<stok_minimum), Aman (stok>=stok_minimum).
- BR-04: jumlah restock / perlu diproduksi = 2 x stok_minimum, hanya untuk Menipis/Habis.
- BR-05: stok berkurang saat kasir menyimpan transaksi (status awal baru).
- BR-06: status hanya maju baru -> diproses -> siap -> selesai. Batal wajib alasan_batal dan stok dikembalikan lewat penyesuaian.
- BR-07: kembalian = dibayar - total, tidak boleh negatif; kode TRX berurutan.
- BR-08: harga_satuan disalin dari harga produk saat transaksi.
- BR-09: produksi dapur = stock_movements jenis produksi + sesi; tidak ada tabel produksi.
- BR-10: user tidak dihapus, hanya dinonaktifkan.
- BR-11: catat ke activity_logs untuk tambah/edit produk, penyesuaian stok, batal transaksi, ubah user, pulihkan backup.

## Fitur per peran
- KASIR: daftar produk + stok + status; simpan transaksi (keranjang, total, uang diterima, kembalian; tolak jika stok kurang atau uang kurang); riwayat transaksi milik sendiri + detail.
- DAPUR: antrian pesanan per status + ubah status; catat produksi (tanggal, sesi, daftar produk+jumlah, catatan); stok + perlu diproduksi; riwayat produksi milik sendiri.
- OWNER: dashboard (penjualan hari ini + % vs kemarin, pendapatan bulan ini + jumlah transaksi, jumlah stok menipis, produk terlaris hari ini, penjualan 7 hari, 5 transaksi terbaru, daftar restock) — semua dari query, bukan angka tetap; CRUD produk (stok awal = stok_awal; ubah stok lewat edit = penyesuaian, keterangan wajib); riwayat stok per produk; stok masuk dari pemasok; CRUD pemasok; laporan penjualan per periode (total, jumlah transaksi, rata-rata, tren harian, produk terlaris, perbandingan periode lalu) + ekspor PDF/Excel; riwayat semua transaksi + batalkan; kelola user (tambah, edit, nonaktifkan, reset password); backup manual, jadwal otomatis, riwayat, unduh, pulihkan.

## Non-fungsional
- Password di-hash bcrypt; autentikasi token (Laravel Sanctum); middleware peran di server.
- Validasi semua input di server; pesan error Bahasa Indonesia; jangan kirim pesan error asli/stack trace ke klien.
- Format respons JSON seragam: { "success": bool, "message": string, "data": ... }.
- Tidak ada endpoint publik yang menampilkan password atau data akun.
- Laporan 1 bulan tampil < 5 detik; backup otomatis harian default 02:00 dan bisa dipulihkan.

## Skenario UAT (wajib lulus)
- UAT-01 Kasir: Siomay Ayam x2, Hakau Udang x1, Bakpao Coklat x3, bayar 100000 -> total 84000, kembalian 16000, stok berkurang, status baru.
- UAT-02 Kasir: pesan Hakau Udang 5 saat stok 4 -> ditolak.
- UAT-03 Kasir memanggil API owner -> 403.
- UAT-04 Dapur: status baru -> diproses -> siap -> selesai; status mundur ditolak.
- UAT-05 Dapur: produksi sore Hakau Udang +20 -> stok 4 jadi 24, jenis produksi.
- UAT-06 Owner: tambah produk stok awal 48, minimum 10 -> Aman, tercatat stok_awal 48.
- UAT-07 Owner: stok masuk Pemasok A Bakpao Coklat +24 -> stok 6 jadi 30, jenis masuk.
- UAT-08 Owner: cari transaksi per tanggal -> detail item, dibayar, kembalian tampil.
- UAT-09 Owner: ekspor laporan November ke Excel -> berisi total, jumlah transaksi, produk terlaris.
- UAT-10 Owner nonaktifkan akun Dani -> Dani tidak bisa login, transaksinya tetap ada.
