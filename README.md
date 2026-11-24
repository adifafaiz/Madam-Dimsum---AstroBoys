# Madam Dimsum - Sistem Pencatatan Stok & Penjualan

Aplikasi web manajemen dan pencatatan stok & penjualan Madam Dimsum dengan arsitektur terpisah antara **Backend** dan **Frontend** untuk memudahkan pengecekan, pengembangan, dan kolaborasi tim.

---

## 📁 Struktur Folder Proyek

```text
Madam-Dimsum---AstroBoys/
├── backend/                       # Backend Laravel (API, Database, Controller, Migrations)
│   ├── app/
│   │   ├── Http/Controllers/
│   │   │   └── AuthController.php # Controller Login, Validasi Peran, & Logout
│   │   └── Models/
│   │       └── User.php           # Model User dengan role & username
│   ├── database/
│   │   ├── migrations/            # Migrasi tabel users (name, username, role, password)
│   │   └── seeders/               # Seeder akun demo (Owner, Kasir, Dapur)
│   ├── routes/
│   │   ├── api.php                # Endpoint API: POST /api/login, /api/logout, /api/demo-accounts
│   │   └── web.php                # Rute Web & Dashboard
│   └── resources/views/
│       ├── auth/login.blade.php   # Tampilan Login versi Blade (Laravel monolith)
│       └── dashboard.blade.php    # Preview Dashboard tiap peran setelah login
│
├── frontend/                      # Frontend Terpisah (Standalone Web / SPA)
│   ├── index.html                 # Tampilan Login (Sesuai 100% dengan rancangan desain)
│   ├── css/
│   │   └── style.css              # Styling custom warna & transisi
│   ├── js/
│   │   └── login.js               # Logika peran interaktif & koneksi REST API
│   └── README.md
│
└── README.md                      # Dokumentasi utama proyek ini
```

---

## 🎨 Fitur Tampilan Login

1. **Pemilihan Peran (Role Selector)**:
   - Pilihan: **Owner**, **Kasir**, dan **Dapur** (default: **Dapur**).
   - Tombol submit berganti teks otomatis: `"Masuk sebagai Dapur"`, `"Masuk sebagai Kasir"`, atau `"Masuk sebagai Owner"`.
   - 3 Poin deskripsi keunggulan di panel sisi kiri berganti secara dinamis sesuai peran yang dipilih.
2. **Input Username & Password**:
   - Dilengkapi tombol toggle lihat/sembunyikan password (ikon mata).
   - Opsi *"Ingat saya"* dan tautan *"Lupa password?"*.
3. **Validasi & Keamanan Role**:
   - Jika pengguna memilih peran yang tidak sesuai dengan akun yang didaftarkan (misal: memilih peran Kasir tetapi memasukkan username akun Dapur), sistem backend akan menolak dan memberikan notifikasi edukatif.
4. **Tombol Cepat Akun Demo (Quick-Fill)**:
   - Memudahkan pengujian langsung dengan sekali klik.

---

## 👥 Akun Demo yang Tersedia

Database SQLite bawaan telah di-*seed* dengan 3 akun berikut:

| Peran (Role) | Username | Password | Nama Tampilan |
| :--- | :--- | :--- | :--- |
| **Dapur** | `agus` | `password123` | Agus Dapur |
| **Kasir** | `kasir` | `password123` | Siti Kasir |
| **Owner** | `owner` | `password123` | Madam Owner |

---

## 🚀 Cara Menjalankan

### 1. Menjalankan Backend Laravel
Buka terminal dan masuk ke folder `backend`:
```powershell
cd backend
php artisan serve
```
Backend akan berjalan di `http://127.0.0.1:8000`.

### 2. Membuka Frontend
Ada 2 opsi untuk membuka halaman login:

- **Opsi A (Folder Terpisah - `frontend/`):**
  Buka file `frontend/index.html` langsung di browser Anda (atau klik kanan -> *Open with Live Server*). Frontend ini otomatis terhubung ke REST API backend `http://127.0.0.1:8000/api/login`.

- **Opsi B (Melalui Server Laravel):**
  Akses langsung rute Laravel di browser:
  ```
  http://127.0.0.1:8000/login
  ```
  Atau `http://127.0.0.1:8000/` yang akan otomatis mengarahkan ke halaman login.

