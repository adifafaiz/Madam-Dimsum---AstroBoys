@extends('owner.layout')

@section('title', 'Dashboard Owner')
@section('page_title', 'Dashboard')

@php
    $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
    $adaTerlaris = $produkTerlarisNama && $produkTerlarisNama !== '-' && $produkTerlarisPorsi > 0;
    $totalSepekan = collect($chartDays)->sum('sales');
    $hariTertinggi = collect($chartDays)->sortByDesc('sales')->first();
@endphp

@push('styles')
<style>
    .ringkasan b { font-weight: 700; color: #2A211C; }
    .ringkasan .sorot { color: #C8372D; }
    .batang { transition: opacity .15s; }
    .kolom-batang:hover .batang, .kolom-batang:focus-visible .batang { opacity: .82; }
    .kolom-batang .tip { opacity: 0; transform: translate(-50%, 4px); transition: opacity .12s, transform .12s; }
    .kolom-batang:hover .tip, .kolom-batang:focus-visible .tip { opacity: 1; transform: translate(-50%, 0); }
</style>
@endpush

@section('content')
<div class="space-y-6">

    <!-- ==================== KALIMAT RINGKASAN ==================== -->
    <section class="pb-6 border-b border-brand-border" aria-label="Ringkasan hari ini">
        <p class="text-[15px] text-brand-secondary flex items-center gap-2">
            <i data-lucide="calendar" class="!w-4 !h-4"></i>
            <span id="tanggalHariIni">{{ now()->locale('id')->translatedFormat('l, j F Y') }}</span>
        </p>
        <h2 class="font-display text-3xl font-semibold mt-2"><span id="sapaanOwner">Halo</span>, {{ Auth::user()->name ?? 'Owner' }}.</h2>
        <p class="ringkasan mt-3 text-xl leading-relaxed text-brand-secondary max-w-5xl">
            Penjualan hari ini <b class="sorot">{{ $rp($penjualanHariIni) }}</b>.
            Bulan ini sudah <b>{{ $rp($pendapatanBulanIni) }}</b> dari <b>{{ number_format($transaksiBulanIni, 0, ',', '.') }} transaksi</b>.
            @if($adaTerlaris)
                <b>{{ $produkTerlarisNama }}</b> paling laku hari ini ({{ $produkTerlarisPorsi }} porsi).
            @endif
            @if($stokMenipisCount > 0)
                <a href="#restock" class="underline decoration-[#EADFD3] decoration-2 underline-offset-4 hover:decoration-brand-red"><b class="sorot">{{ $stokMenipisCount }} produk</b> stoknya menipis</a> dan perlu di-restock.
            @else
                Semua stok produk masih aman.
            @endif
        </p>
    </section>

    <!-- ==================== GRAFIK & RESTOCK ==================== -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        <section class="xl:col-span-2 kartu p-6" aria-labelledby="judulGrafik">
            <div class="flex flex-wrap items-baseline justify-between gap-2 mb-6">
                <h3 id="judulGrafik" class="kartu-judul">Penjualan 7 Hari Terakhir</h3>
                <p class="text-[15px] text-brand-secondary">Total sepekan <b class="text-brand-ink font-semibold">{{ $rp($totalSepekan) }}</b></p>
            </div>

            <div class="relative h-60" aria-hidden="true">
                {{-- Garis bantu samar --}}
                <div class="absolute inset-x-0 top-0 border-t border-dashed border-[#F0E7DD]"></div>
                <div class="absolute inset-x-0 top-1/2 border-t border-dashed border-[#F0E7DD]"></div>

                <div class="absolute inset-0 flex items-end gap-3 sm:gap-6 border-b border-brand-border">
                    @foreach($chartDays as $day)
                        @php $tertinggi = $hariTertinggi && $day['sales'] === $hariTertinggi['sales']; @endphp
                        <div class="kolom-batang relative flex-1 h-full flex flex-col items-center justify-end" tabindex="0">
                            <span class="tip pointer-events-none absolute left-1/2 z-10 -mt-1 px-2.5 py-1.5 rounded bg-brand-dark text-white text-[13px] whitespace-nowrap"
                                  style="bottom: calc({{ $day['height'] }} + 8px)">
                                {{ $day['day'] }} &middot; {{ $rp($day['sales']) }}
                            </span>
                            @if($tertinggi)
                                <span class="text-[13px] font-semibold text-brand-ink mb-1.5 whitespace-nowrap">{{ $rp($day['sales']) }}</span>
                            @endif
                            <div class="batang w-full max-w-[44px] rounded-t-[4px] bg-brand-red" style="height: {{ $day['height'] }}"></div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex gap-3 sm:gap-6 pt-2.5 text-[15px] text-brand-secondary" aria-hidden="true">
                @foreach($chartDays as $day)
                    <span class="flex-1 text-center">{{ $day['day'] }}</span>
                @endforeach
            </div>

            {{-- Versi tabel untuk pembaca layar --}}
            <table class="sr-only">
                <caption>Penjualan 7 hari terakhir</caption>
                <thead><tr><th>Hari</th><th>Penjualan</th></tr></thead>
                <tbody>
                    @foreach($chartDays as $day)
                        <tr><td>{{ $day['day'] }}</td><td>{{ $rp($day['sales']) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        <section id="restock" class="kartu flex flex-col scroll-mt-24" aria-labelledby="judulRestock">
            <div class="px-6 pt-6 pb-4 flex items-center justify-between gap-3">
                <h3 id="judulRestock" class="kartu-judul">Riwayat Pemesanan</h3>
                @if($produkRestockList->count())
                    <span class="inline-flex items-center gap-1.5 h-7 px-2.5 rounded-full bg-[#FCE6E3] text-[#B0261D] text-[13px] font-semibold">
                        <i data-lucide="triangle-alert" class="!w-3.5 !h-3.5"></i> Perlu Dipesan
                    </span>
                @endif
            </div>

            <div class="flex-1 px-6">
                @forelse($produkRestockList as $item)
                    <div class="py-3 border-t border-brand-border flex items-center gap-3">
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold truncate">{{ $item->nama }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                @include('partials.bar-stok', ['produk' => $item])
                                <span class="text-[13px] text-brand-secondary">sisa {{ $item->stok }}</span>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-[13px] text-brand-secondary">pesan</p>
                            <p class="text-lg font-bold text-brand-red leading-none">{{ 2 * $item->stok_minimum }}</p>
                        </div>
                    </div>
                @empty
                    <div class="kosong border-t border-brand-border">
                        <i data-lucide="circle-check"></i>
                        <p class="kosong-judul">Stok aman semua</p>
                        <p class="kosong-sub">Tidak ada produk di bawah batas minimum saat ini.</p>
                    </div>
                @endforelse
            </div>

            <div class="p-6 pt-4">
                <a href="{{ route('owner.produk') }}" class="btn btn-secondary w-full">
                    <i data-lucide="truck" class="!w-4 !h-4"></i> Buka Daftar Pemasok
                </a>
            </div>
        </section>
    </div>

    <!-- ==================== TRANSAKSI TERBARU ==================== -->
    <section class="kartu overflow-hidden" aria-labelledby="judulTerbaru">
        <div class="px-6 py-5 flex flex-wrap items-center justify-between gap-2 border-b border-brand-border">
            <h3 id="judulTerbaru" class="kartu-judul">Transaksi Terbaru</h3>
            <a href="{{ route('owner.riwayat-transaksi') }}" class="text-[15px] font-medium text-brand-red hover:underline underline-offset-4 inline-flex items-center gap-1">
                Lihat semua <i data-lucide="arrow-right" class="!w-4 !h-4"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="tabel">
                <thead>
                    <tr>
                        <th>No. Transaksi</th>
                        <th>Waktu</th>
                        <th>Kasir</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksiTerbaru as $trx)
                        <tr>
                            <td class="font-semibold">{{ $trx->kode }}</td>
                            <td class="text-brand-secondary">{{ \Carbon\Carbon::parse($trx->waktu)->format('H:i') }}</td>
                            <td>{{ $trx->user->name ?? 'Kasir' }}</td>
                            <td class="text-right font-semibold">{{ $rp($trx->total) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="kosong">
                                    <i data-lucide="receipt-text"></i>
                                    <p class="kosong-judul">Belum ada transaksi hari ini</p>
                                    <p class="kosong-sub">Transaksi dari kasir akan muncul di sini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

</div>
@endsection

@push('scripts')
<script>
    (function () {
        const h = new Date().getHours();
        const waktu = h < 11 ? 'pagi' : h < 15 ? 'siang' : h < 18 ? 'sore' : 'malam';
        document.getElementById('sapaanOwner').textContent = 'Selamat ' + waktu;
        document.getElementById('tanggalHariIni').textContent =
            new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    })();
</script>
@endpush
