<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard {{ ucfirst($role) }} - Madam Dimsum</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            red: '#B83227',
                            redHover: '#A1281E',
                            dark: '#1E1B18',
                            lightBg: '#FAF8F5',
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-brand-lightBg text-stone-800 min-h-screen flex flex-col font-sans">
    <!-- Navbar -->
    <header class="bg-white border-b border-stone-200 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-brand-red rounded-lg flex items-center justify-center font-bold text-white text-lg shadow-sm">
                    M
                </div>
                <div>
                    <span class="font-bold text-stone-900 text-lg">Madam Dimsum</span>
                    <span class="text-xs text-stone-500 ml-2 px-2 py-0.5 bg-stone-100 rounded-full font-medium">Panel {{ ucfirst($role) }}</span>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-semibold text-stone-900">{{ $user->name ?? 'Pengguna' }}</p>
                    <p class="text-xs text-stone-500">@ {{ $user->username ?? $role }} &middot; {{ ucfirst($role) }}</p>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-3.5 py-1.8 text-xs font-semibold rounded-lg bg-stone-100 hover:bg-stone-200 text-stone-700 transition">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="bg-white rounded-2xl p-6 sm:p-8 border border-stone-200 shadow-sm mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-stone-100 pb-6 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-stone-900">Selamat Datang di Panel {{ ucfirst($role) }}!</h1>
                    <p class="text-stone-500 text-sm mt-1">Sistem pencatatan stok & penjualan Madam Dimsum berhasil terhubung.</p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-red-50 text-brand-red border border-red-200/60 self-start sm:self-auto">
                    <span class="w-2 h-2 rounded-full bg-brand-red animate-pulse"></span>
                    Aktif sebagai {{ ucfirst($role) }}
                </span>
            </div>

            <!-- Role specific cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                @if(strtolower($role) === 'dapur')
                    <div class="p-5 rounded-xl bg-stone-50 border border-stone-200">
                        <span class="text-2xl">📋</span>
                        <h3 class="font-bold text-stone-900 mt-2">Antrian Pesanan</h3>
                        <p class="text-xs text-stone-500 mt-1">Pantau pesanan dimsum masuk untuk segera dikukus & disajikan.</p>
                    </div>
                    <div class="p-5 rounded-xl bg-stone-50 border border-stone-200">
                        <span class="text-2xl">🥟</span>
                        <h3 class="font-bold text-stone-900 mt-2">Pencatatan Produksi</h3>
                        <p class="text-xs text-stone-500 mt-1">Input jumlah siomay, hakau, lumpia kulit tahu yang diproduksi hari ini.</p>
                    </div>
                    <div class="p-5 rounded-xl bg-stone-50 border border-stone-200">
                        <span class="text-2xl">📦</span>
                        <h3 class="font-bold text-stone-900 mt-2">Kebutuhan Bahan</h3>
                        <p class="text-xs text-stone-500 mt-1">Ajukan permintaan daging ayam, udang, dan bumbu langsung ke Owner.</p>
                    </div>
                @elseif(strtolower($role) === 'kasir')
                    <div class="p-5 rounded-xl bg-stone-50 border border-stone-200">
                        <span class="text-2xl">💵</span>
                        <h3 class="font-bold text-stone-900 mt-2">Kasir & POS</h3>
                        <p class="text-xs text-stone-500 mt-1">Input pesanan pelanggan, cetak struk pembayaran, dan pilih metode bayar.</p>
                    </div>
                    <div class="p-5 rounded-xl bg-stone-50 border border-stone-200">
                        <span class="text-2xl">📊</span>
                        <h3 class="font-bold text-stone-900 mt-2">Rekap Harian</h3>
                        <p class="text-xs text-stone-500 mt-1">Total uang kas dan transaksi non-tunai hari ini tersinkronisasi otomatis.</p>
                    </div>
                    <div class="p-5 rounded-xl bg-stone-50 border border-stone-200">
                        <span class="text-2xl">🧾</span>
                        <h3 class="font-bold text-stone-900 mt-2">Riwayat Struk</h3>
                        <p class="text-xs text-stone-500 mt-1">Cari dan cetak ulang struk transaksi sebelumnya jika dibutuhkan pembeli.</p>
                    </div>
                @else
                    <div class="p-5 rounded-xl bg-stone-50 border border-stone-200">
                        <span class="text-2xl">📈</span>
                        <h3 class="font-bold text-stone-900 mt-2">Laporan Penjualan</h3>
                        <p class="text-xs text-stone-500 mt-1">Pantau omzet harian, mingguan, dan margin profit Madam Dimsum.</p>
                    </div>
                    <div class="p-5 rounded-xl bg-stone-50 border border-stone-200">
                        <span class="text-2xl">✅</span>
                        <h3 class="font-bold text-stone-900 mt-2">Persetujuan Bahan</h3>
                        <p class="text-xs text-stone-500 mt-1">Lihat dan setujui nota belanja bahan baku yang diajukan oleh tim Dapur.</p>
                    </div>
                    <div class="p-5 rounded-xl bg-stone-50 border border-stone-200">
                        <span class="text-2xl">👥</span>
                        <h3 class="font-bold text-stone-900 mt-2">Manajemen User</h3>
                        <p class="text-xs text-stone-500 mt-1">Kelola akun kasir, koki dapur, serta pengaturan hak akses sistem.</p>
                    </div>
                @endif
            </div>

            <div class="mt-8 pt-6 border-t border-stone-100 flex items-center justify-between">
                <a href="{{ route('login') }}" class="text-sm font-semibold text-brand-red hover:underline flex items-center gap-1.5">
                    &larr; Kembali ke Halaman Login
                </a>
                <p class="text-xs text-stone-400">Madam Dimsum Management System &middot; Tim Astroboys</p>
            </div>
        </div>
    </main>
</body>
</html>

