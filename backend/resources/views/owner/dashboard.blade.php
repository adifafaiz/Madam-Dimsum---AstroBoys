@extends('owner.layout')

@section('title', 'Dashboard Owner')
@section('page_title', 'Dashboard')

@section('content')
<div class="space-y-6 max-w-7xl">

    <!-- ==================== 4 STAT SUMMARY CARDS ==================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Penjualan Hari Ini -->
        <div class="bg-white p-5 rounded-2xl border border-[#EADFD3] shadow-sm">
            <p class="text-xs font-semibold text-stone-500 mb-1">Penjualan Hari Ini</p>
            <h3 class="text-2xl font-extrabold text-stone-900 tracking-tight">
                Rp {{ number_format($penjualanHariIni, 0, ',', '.') }}
            </h3>
            <p class="text-[11px] font-medium text-emerald-600 mt-2 flex items-center gap-1">
                <span>&uarr;</span> +12% dari kemarin
            </p>
        </div>

        <!-- Card 2: Pendapatan Bulan Ini -->
        <div class="bg-white p-5 rounded-2xl border border-[#EADFD3] shadow-sm">
            <p class="text-xs font-semibold text-stone-500 mb-1">Pendapatan Bulan Ini</p>
            <h3 class="text-2xl font-extrabold text-stone-900 tracking-tight">
                Rp {{ number_format($pendapatanBulanIni, 0, ',', '.') }}
            </h3>
            <p class="text-[11px] font-medium text-stone-500 mt-2">
                {{ number_format($transaksiBulanIni, 0, ',', '.') }} transaksi
            </p>
        </div>

        <!-- Card 3: Stok Menipis -->
        <div class="bg-white p-5 rounded-2xl border border-[#EADFD3] shadow-sm">
            <p class="text-xs font-semibold text-stone-500 mb-1">Stok Menipis</p>
            <h3 class="text-2xl font-extrabold text-stone-900 tracking-tight">
                {{ $stokMenipisCount }} produk
            </h3>
            <p class="text-[11px] font-semibold text-brand-red mt-2">
                perlu di-restock
            </p>
        </div>

        <!-- Card 4: Produk Terlaris -->
        <div class="bg-white p-5 rounded-2xl border border-[#EADFD3] shadow-sm">
            <p class="text-xs font-semibold text-stone-500 mb-1">Produk Terlaris</p>
            <h3 class="text-xl font-extrabold text-stone-900 tracking-tight truncate">
                {{ $produkTerlarisNama }}
            </h3>
            <p class="text-[11px] font-medium text-stone-500 mt-2">
                {{ $produkTerlarisPorsi }} porsi terjual
            </p>
        </div>
    </div>

    <!-- ==================== BAGIAN TENGAH (GRAFIK & RESTOCK) ==================== -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Kiri (2 Kolom): Grafik Penjualan 7 Hari Terakhir -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-[#EADFD3] shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between mb-6">
                <h4 class="text-sm font-bold text-stone-900">Penjualan 7 Hari Terakhir</h4>
                <span class="text-xs font-medium text-stone-400">Total sepekan</span>
            </div>

            <!-- Custom Bar Chart Sesuai Desain Mockup -->
            <div class="h-48 flex items-end justify-between gap-3 sm:gap-6 pt-4 px-2 sm:px-6 border-b border-stone-100">
                @foreach($chartDays as $day)
                    <div class="flex-1 flex flex-col items-center h-full justify-end group relative">
                        <!-- Tooltip on hover -->
                        <div class="opacity-0 group-hover:opacity-100 transition absolute -top-8 px-2 py-1 bg-stone-900 text-white text-[10px] rounded shadow pointer-events-none whitespace-nowrap z-10">
                            Rp {{ number_format($day['sales'], 0, ',', '.') }}
                        </div>
                        <!-- Bar -->
                        <div 
                            style="height: {{ $day['height'] }};" 
                            class="w-full max-w-[36px] rounded-t-md transition-all duration-300 {{ !empty($day['is_highest']) ? 'bg-brand-red shadow-sm' : 'bg-brand-redPink hover:bg-brand-red/60' }}">
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Label Hari di Bawah Batang -->
            <div class="flex items-center justify-between px-2 sm:px-6 pt-3 text-xs font-medium text-stone-500">
                @foreach($chartDays as $day)
                    <span class="flex-1 text-center {{ !empty($day['is_highest']) ? 'font-bold text-brand-red' : '' }}">{{ $day['day'] }}</span>
                @endforeach
            </div>
        </div>

        <!-- Kanan (1 Kolom): Riwayat Pemesanan / Perlu Dipesan ke Pemasok -->
        <div class="bg-white p-6 rounded-2xl border border-[#EADFD3] shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-sm font-bold text-stone-900">Riwayat Pemesanan</h4>
                    <span class="text-[11px] font-semibold text-brand-red bg-red-50 px-2 py-0.5 rounded-full">Perlu Dipesan</span>
                </div>

                <!-- Daftar Produk yang Perlu Dipesan -->
                <div class="space-y-3.5 divide-y divide-stone-100">
                    @forelse($produkRestockList as $item)
                        <div class="pt-3 first:pt-0 flex items-center justify-between text-xs">
                            <span class="font-semibold text-stone-800">{{ $item->nama }}</span>
                            <span class="text-brand-red font-medium">
                                sisa {{ $item->stok }} &middot; <b class="font-bold">pesan {{ 2 * $item->stok_minimum }}</b>
                            </span>
                        </div>
                    @empty
                        <div class="py-6 text-center text-xs text-stone-400">
                            Semua stok produk saat ini dalam batas aman.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Tombol Buka Daftar Pemasok -->
            <div class="pt-6">
                <a href="{{ route('owner.produk') }}" 
                    class="block w-full text-center py-2.5 px-4 rounded-xl border border-[#EADFD3] text-xs font-semibold text-stone-700 hover:bg-stone-50 hover:border-stone-400 transition shadow-sm">
                    Buka Daftar Pemasok
                </a>
            </div>
        </div>
    </div>

    <!-- ==================== BAGIAN BAWAH: TRANSAKSI TERBARU ==================== -->
    <div class="bg-white rounded-2xl border border-[#EADFD3] shadow-sm overflow-hidden">
        <div class="p-5 border-b border-[#EADFD3] flex items-center justify-between">
            <h4 class="text-sm font-bold text-stone-900">Transaksi Terbaru</h4>
            <span class="text-xs text-stone-400">Pembaruan otomatis dari kasir</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#FAF7F2] text-stone-500 font-bold border-b border-[#EADFD3]">
                    <tr>
                        <th class="py-3 px-6">No. Transaksi</th>
                        <th class="py-3 px-6">Waktu</th>
                        <th class="py-3 px-6">Kasir</th>
                        <th class="py-3 px-6 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#EADFD3] text-stone-800 font-medium">
                    @forelse($transaksiTerbaru as $trx)
                        <tr class="hover:bg-stone-50/60 transition">
                            <td class="py-3.5 px-6 font-bold text-stone-900">{{ $trx->kode }}</td>
                            <td class="py-3.5 px-6 text-stone-500">{{ \Carbon\Carbon::parse($trx->waktu)->format('H:i') }}</td>
                            <td class="py-3.5 px-6">{{ $trx->user->name ?? 'Kasir' }}</td>
                            <td class="py-3.5 px-6 text-right font-bold text-stone-900">
                                Rp {{ number_format($trx->total, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-stone-400">Belum ada transaksi terbaru hari ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

