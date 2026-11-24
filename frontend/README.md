# Frontend - Madam Dimsum Login

Folder ini berisi antarmuka pengguna (Frontend) untuk halaman Login **Madam Dimsum**, yang dibuat terpisah dari backend agar mudah diperiksa, dimodifikasi, dan diintegrasikan.

## Struktur File Frontend

```
frontend/
├── index.html        # Halaman login utama (Sesuai desain 1:1)
├── css/
│   └── style.css     # Kustomisasi CSS, warna tema, dan animasi
├── js/
│   └── login.js      # Logika interaktif: ganti peran, password toggle, & koneksi API
└── README.md         # Dokumentasi frontend ini
```

## Fitur Utama

1. **Pilihan Peran (Role Switcher)**:
   - **Owner**, **Kasir**, dan **Dapur** (default aktif: Dapur sesuai gambar desain).
   - Mengubah teks tombol secara dinamis: `"Masuk sebagai Dapur"`, `"Masuk sebagai Kasir"`, atau `"Masuk sebagai Owner"`.
   - Mengubah daftar 3 poin keunggulan di sisi kiri secara dinamis sesuai peran yang dipilih.
2. **Username & Password Input**:
   - Dilengkapi toggle lihat/sembunyikan kata sandi (ikon mata).
3. **Koneksi REST API**:
   - Mengirim request ke endpoint backend: `http://127.0.0.1:8000/api/login`.
   - Menampilkan alert validasi kesalahan peran (misal: jika akun Dapur mencoba masuk sebagai Kasir).
4. **Akun Uji Coba (Demo)**:
   - Dapur: username `agus`, password `password123`
   - Kasir: username `kasir`, password `password123`
   - Owner: username `owner`, password `password123`

## Cara Menjalankan

Anda dapat langsung membuka file `frontend/index.html` di browser Anda:
- Cukup double-click file `frontend/index.html` atau gunakan ekstensi VS Code **Live Server**.
- Pastikan backend Laravel sedang berjalan dengan perintah `php artisan serve` di dalam folder `backend/` agar API login dapat merespon request.

