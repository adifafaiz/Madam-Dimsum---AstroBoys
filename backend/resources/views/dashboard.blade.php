<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard {{ ucfirst($role) }} - Madam Dimsum</title>
    @include('partials.head')
</head>
<body class="bg-brand-lightBg antialiased min-h-screen flex flex-col">
    {{-- Catatan: halaman cadangan. Route /dashboard/{role} mengarahkan owner/kasir/dapur ke halaman masing-masing,
         jadi view ini hanya tampil untuk peran di luar ketiganya. --}}
    <header class="bg-white border-b border-brand-border sticky top-0 z-30">
        <div class="px-6 lg:px-8 h-[72px] flex items-center justify-between">
            <div class="flex items-center gap-3">
                @include('partials.logo', ['class' => 'w-10 h-10 p-0.5 border border-brand-border'])
                <div class="leading-tight">
                    <p class="font-display text-[17px] font-semibold">Madam Dimsum</p>
                    <p class="text-xs text-brand-secondary">Panel {{ ucfirst($role) }}</p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="text-right hidden sm:block leading-tight">
                    <p class="font-semibold">{{ $user->name ?? 'Pengguna' }}</p>
                    <p class="text-[13px] text-brand-secondary">{{ $user->username ?? $role }} &middot; {{ ucfirst($role) }}</p>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-secondary"><i data-lucide="log-out" class="!w-4 !h-4"></i> Keluar</button>
                </form>
            </div>
        </div>
    </header>

    <main class="flex-1 w-full px-6 lg:px-8 py-8">
        @if(session('success'))
            <div class="mb-6 px-4 py-3 rounded border border-emerald-200 bg-emerald-50 text-emerald-800 flex items-center gap-3">
                <i data-lucide="circle-check" class="text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="kartu p-6 sm:p-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-brand-border pb-6 mb-6">
                <div>
                    <h1 class="font-display text-3xl font-semibold">Selamat Datang di Panel {{ ucfirst($role) }}!</h1>
                    <p class="text-brand-secondary mt-1">Sistem pencatatan stok &amp; penjualan Madam Dimsum berhasil terhubung.</p>
                </div>
                <span class="inline-flex items-center gap-2 h-8 px-3 rounded-full text-[13px] font-semibold bg-[#FCF1EF] text-brand-red self-start sm:self-auto">
                    <span class="w-2 h-2 rounded-full bg-brand-red"></span>
                    Aktif sebagai {{ ucfirst($role) }}
                </span>
            </div>

            @php
                $kartuPeran = match (strtolower($role)) {
                    'dapur' => [
                        ['clipboard-list', 'Antrian Pesanan', 'Pantau pesanan dimsum masuk untuk segera dikukus & disajikan.'],
                        ['chef-hat', 'Pencatatan Produksi', 'Input jumlah siomay, hakau, lumpia kulit tahu yang diproduksi hari ini.'],
                        ['package', 'Kebutuhan Bahan', 'Ajukan permintaan daging ayam, udang, dan bumbu langsung ke Owner.'],
                    ],
                    'kasir' => [
                        ['banknote', 'Kasir & POS', 'Input pesanan pelanggan, cetak struk pembayaran, dan pilih metode bayar.'],
                        ['chart-column', 'Rekap Harian', 'Total uang kas dan transaksi non-tunai hari ini tersinkronisasi otomatis.'],
                        ['receipt-text', 'Riwayat Struk', 'Cari dan cetak ulang struk transaksi sebelumnya jika dibutuhkan pembeli.'],
                    ],
                    default => [
                        ['trending-up', 'Laporan Penjualan', 'Pantau omzet harian, mingguan, dan margin profit Madam Dimsum.'],
                        ['circle-check', 'Persetujuan Bahan', 'Lihat dan setujui nota belanja bahan baku yang diajukan oleh tim Dapur.'],
                        ['users', 'Manajemen User', 'Kelola akun kasir, koki dapur, serta pengaturan hak akses sistem.'],
                    ],
                };
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                @foreach($kartuPeran as [$ikon, $judul, $teks])
                    <div class="p-5 rounded border border-brand-border bg-brand-lightBg">
                        <span class="w-10 h-10 rounded-full bg-white border border-brand-border text-brand-red flex items-center justify-center">
                            <i data-lucide="{{ $ikon }}"></i>
                        </span>
                        <h3 class="font-semibold text-lg mt-3">{{ $judul }}</h3>
                        <p class="text-[15px] text-brand-secondary mt-1">{{ $teks }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 pt-6 border-t border-brand-border flex flex-wrap items-center justify-between gap-3">
                <a href="{{ route('login') }}" class="text-[15px] font-semibold text-brand-red hover:underline underline-offset-4 inline-flex items-center gap-1.5">
                    <i data-lucide="arrow-left" class="!w-4 !h-4"></i> Kembali ke Halaman Login
                </a>
                <p class="text-[13px] text-brand-secondary">Madam Dimsum Management System &middot; Tim AstroBoys</p>
            </div>
        </div>
    </main>
</body>
</html>
