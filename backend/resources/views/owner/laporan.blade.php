@extends('owner.layout')

@section('title', 'Laporan Penjualan - Owner')
@section('page_title', 'Laporan Penjualan')

@section('content')
<div class="space-y-6 max-w-7xl">

    <!-- ==================== FILTER & EKSPOR BAR (FRAME 6) ==================== -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <!-- Filter Periode & Rentang Tanggal -->
        <div class="flex flex-wrap items-center gap-3">
            <!-- Rentang Tanggal Dropdown -->
            <div class="relative">
                <button type="button" class="flex items-center gap-2 bg-white border border-[#EADFD3] rounded-lg px-3.5 py-2 text-xs font-semibold text-stone-800 shadow-sm hover:bg-stone-50 transition">
                    <span>{{ $range }}</span>
                    <svg class="w-3.5 h-3.5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            </div>

            <!-- Periode Dropdown -->
            <div class="relative">
                <select 
                    onchange="this.form ? this.form.submit() : null"
                    class="appearance-none bg-white border border-[#EADFD3] rounded-lg pl-3.5 pr-8 py-2 text-xs font-semibold text-stone-800 shadow-sm focus:outline-none focus:ring-1 focus:ring-brand-red cursor-pointer"
                >
                    <option value="Harian" {{ $periode === 'Harian' ? 'selected' : '' }}>Harian</option>
                    <option value="Mingguan" {{ $periode === 'Mingguan' ? 'selected' : '' }}>Mingguan</option>
                    <option value="Bulanan" {{ $periode === 'Bulanan' ? 'selected' : '' }}>Bulanan</option>
                </select>
                <svg class="w-3.5 h-3.5 text-stone-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </div>

        <!-- Tombol Ekspor Sesuai Mockup -->
        <div class="flex items-center gap-2.5">
            <!-- Ekspor PDF -->
            <button 
                type="button" 
                onclick="window.print()" 
                class="px-4 py-2 rounded-lg border border-[#EADFD3] bg-white hover:bg-stone-50 text-xs font-semibold text-stone-700 shadow-sm transition flex items-center gap-1.5"
            >
                <svg class="w-3.5 h-3.5 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                <span>Ekspor PDF</span>
            </button>

            <!-- Ekspor Excel -->
            <a 
                href="{{ route('owner.laporan.excel') }}" 
                class="px-4 py-2 rounded-lg border border-[#EADFD3] bg-white hover:bg-stone-50 text-xs font-semibold text-stone-700 shadow-sm transition flex items-center gap-1.5"
            >
                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Ekspor Excel</span>
            </a>
        </div>
    </div>

    <!-- ==================== 3 KARTU RINGKASAN LAPORAN ==================== -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Total Penjualan -->
        <div class="bg-white p-5 rounded-2xl border border-[#EADFD3] shadow-sm">
            <p class="text-xs font-semibold text-stone-500 mb-1">Total Penjualan</p>
            <h3 class="text-2xl font-extrabold text-stone-900 tracking-tight">
                Rp {{ number_format($totalPenjualan, 0, ',', '.') }}
            </h3>
        </div>

        <!-- Jumlah Transaksi -->
        <div class="bg-white p-5 rounded-2xl border border-[#EADFD3] shadow-sm">
            <p class="text-xs font-semibold text-stone-500 mb-1">Jumlah Transaksi</p>
            <h3 class="text-2xl font-extrabold text-stone-900 tracking-tight">
                {{ number_format($jumlahTransaksi, 0, ',', '.') }}
            </h3>
        </div>

        <!-- Rata-rata/Transaksi -->
        <div class="bg-white p-5 rounded-2xl border border-[#EADFD3] shadow-sm">
            <p class="text-xs font-semibold text-stone-500 mb-1">Rata-rata/Transaksi</p>
            <h3 class="text-2xl font-extrabold text-stone-900 tracking-tight">
                Rp {{ number_format($rataRataTransaksi, 0, ',', '.') }}
            </h3>
        </div>
    </div>

    <!-- ==================== BAGIAN TENGAH (TREN & PRODUK TERLARIS) ==================== -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Kiri: Tren Penjualan (Line Chart SVG) -->
        <div class="bg-white p-6 rounded-2xl border border-[#EADFD3] shadow-sm flex flex-col justify-between">
            <div class="mb-4">
                <h4 class="text-sm font-bold text-stone-900">Tren Penjualan</h4>
                <p class="text-xs text-stone-400">Pergerakan transaksi harian</p>
            </div>

            <!-- SVG Line Chart Sesuai Mockup Frame 6 -->
            <div class="h-56 relative w-full pt-4">
                <svg class="w-full h-full overflow-visible" viewBox="0 0 500 180" preserveAspectRatio="none">
                    <!-- Area Fill Subtle Gradient -->
                    <defs>
                        <linearGradient id="trendGradient" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#C8372D" stop-opacity="0.15"/>
                            <stop offset="100%" stop-color="#C8372D" stop-opacity="0.0"/>
                        </linearGradient>
                    </defs>

                    <!-- Grid Horizontal Lines -->
                    <line x1="0" y1="40" x2="500" y2="40" stroke="#FAF7F2" stroke-width="1.5" />
                    <line x1="0" y1="90" x2="500" y2="90" stroke="#FAF7F2" stroke-width="1.5" />
                    <line x1="0" y1="140" x2="500" y2="140" stroke="#FAF7F2" stroke-width="1.5" />

                    <!-- Filled Area -->
                    <polygon points="0,120 70,100 140,115 220,70 300,85 390,45 480,25 480,180 0,180" fill="url(#trendGradient)" />

                    <!-- Red Line -->
                    <polyline 
                        fill="none" 
                        stroke="#C8372D" 
                        stroke-width="2.5" 
                        stroke-linecap="round" 
                        stroke-linejoin="round"
                        points="0,120 70,100 140,115 220,70 300,85 390,45 480,25" 
                    />

                    <!-- Points -->
                    <circle cx="0" cy="120" r="3.5" fill="#C8372D" />
                    <circle cx="70" cy="100" r="3.5" fill="#C8372D" />
                    <circle cx="140" cy="115" r="3.5" fill="#C8372D" />
                    <circle cx="220" cy="70" r="3.5" fill="#C8372D" />
                    <circle cx="300" cy="85" r="3.5" fill="#C8372D" />
                    <circle cx="390" cy="45" r="3.5" fill="#C8372D" />
                    <circle cx="480" cy="25" r="4.5" fill="#C8372D" stroke="#fff" stroke-width="2" />
                </svg>
            </div>

            <!-- X-axis Labels -->
            <div class="flex items-center justify-between pt-3 text-[11px] font-medium text-stone-400">
                <span>01 Nov</span>
                <span>08 Nov</span>
                <span>15 Nov</span>
                <span>24 Nov</span>
            </div>
        </div>

        <!-- Kanan: Produk Terlaris (Horizontal Bars) -->
        <div class="bg-white p-6 rounded-2xl border border-[#EADFD3] shadow-sm flex flex-col justify-between">
            <div class="mb-4">
                <h4 class="text-sm font-bold text-stone-900">Produk Terlaris</h4>
                <p class="text-xs text-stone-400">Volume penjualan tertinggi</p>
            </div>

            <!-- Horizontal Bars Sesuai Mockup Frame 6 -->
            <div class="space-y-4 pt-1">
                @foreach($produkTerlaris as $item)
                    <div>
                        <div class="flex items-center justify-between text-xs font-semibold mb-1.5">
                            <span class="text-stone-800">{{ $item['nama'] }}</span>
                            <span class="text-stone-500 font-normal">{{ $item['porsi'] }} porsi</span>
                        </div>
                        <div class="w-full h-2.5 bg-stone-100 rounded-full overflow-hidden">
                            <div 
                                style="width: {{ $item['pct'] }}%;" 
                                class="h-full bg-brand-red rounded-full transition-all duration-500"
                            ></div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="pt-4 border-t border-stone-100 text-[11px] text-stone-400 flex items-center justify-between">
                <span>Total 5 varian terfavorit</span>
                <span class="font-semibold text-brand-red">1.240 porsi total</span>
            </div>
        </div>

    </div>

    <!-- ==================== BAGIAN BAWAH: PERBANDINGAN PERIODE ==================== -->
    <div class="bg-white p-5 rounded-2xl border border-[#EADFD3] shadow-sm">
        <h4 class="text-xs font-bold uppercase tracking-wider text-stone-500 mb-1.5">Perbandingan Periode</h4>
        <p class="text-sm font-bold text-stone-800">
            Bulan ini Rp {{ number_format($perbandingan['bulan_ini'], 0, ',', '.') }} vs Bulan Lalu Rp {{ number_format($perbandingan['bulan_lalu'], 0, ',', '.') }}
        </p>
        <p class="text-xs font-semibold text-[#2E8B57] mt-1.5 flex items-center gap-1.5">
            <span>&uarr;</span> {{ $perbandingan['persentase'] }} &middot; <span class="font-normal">{{ $perbandingan['keterangan'] }}</span>
        </p>
    </div>

</div>
@endsection

