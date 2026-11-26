@extends('owner.layout')

@section('title', 'Laporan Penjualan - Owner')
@section('page_title', 'Laporan Penjualan')

@php
    $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
    // Angka ringkas untuk sumbu: 1,2 jt / 850 rb
    $singkat = fn ($n) => $n >= 1000000 ? rtrim(rtrim(number_format($n / 1000000, 1, ',', '.'), '0'), ',') . ' jt'
        : ($n >= 1000 ? number_format($n / 1000, 0, ',', '.') . ' rb' : (string) (int) $n);

    // Titik grafik tren dari $trendPoints (koordinat dalam persen).
    $tren = collect($trendPoints ?? []);
    $maxTren = max(1, (float) $tren->max('val'));
    $langkah = 10 ** max(0, floor(log10($maxTren)) - 1);
    $skalaAtas = ceil($maxTren * 1.15 / $langkah) * $langkah;
    $selangLabel = max(1, (int) ceil($tren->count() / 7));
    $adaPenjualan = $tren->sum('val') > 0;
    $jumlahTitik = max(1, $tren->count() - 1);
    $titik = $tren->values()->map(fn ($p, $i) => [
        'x' => $p['x'], 'val' => $p['val'],
        'left' => round($i / $jumlahTitik * 100, 2),
        'top' => round(100 - $p['val'] / $skalaAtas * 100, 2),
    ]);
    $garis = $titik->map(fn ($t) => $t['left'] . ',' . $t['top'])->implode(' ');
    $area = $titik->count() ? '0,100 ' . $garis . ' 100,100' : '';

    $totalPorsiTerlaris = collect($produkTerlaris)->sum('porsi');
    $persenTeks = trim((string) ($perbandingan['persentase'] ?? ''));
    $netral = $persenTeks === '' || $persenTeks === '-';
    $naik = !$netral && !str_starts_with($persenTeks, '-');
    $parameterPeriode = ['periode' => $periode, 'tanggal' => $tanggalAcuan ?? null];
@endphp

@push('styles')
<style>
    .titik .tip { opacity: 0; transform: translate(-50%, 4px); transition: opacity .12s, transform .12s; }
    .titik:hover .tip, .titik:focus-visible .tip { opacity: 1; transform: translate(-50%, 0); }
    .titik:hover .bulat, .titik:focus-visible .bulat { transform: scale(1.25); }
    @media print {
        aside, header, .no-print { display: none !important; }
        .ml-\[240px\] { margin-left: 0 !important; }
        body { background: #fff; }
    }
</style>
@endpush

@section('content')
<div class="space-y-6">

    <!-- ==================== FILTER & EKSPOR ==================== -->
    <div class="flex flex-col lg:flex-row lg:items-center gap-3 no-print">
        <form action="{{ route('owner.laporan') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <select name="periode" onchange="this.form.submit()" class="field !w-40" aria-label="Periode">
                <option value="Harian" {{ $periode === 'Harian' ? 'selected' : '' }}>Harian</option>
                <option value="Mingguan" {{ $periode === 'Mingguan' ? 'selected' : '' }}>Mingguan</option>
                <option value="Bulanan" {{ $periode === 'Bulanan' ? 'selected' : '' }}>Bulanan</option>
            </select>
            <div class="field-ikon w-52">
                <i data-lucide="calendar"></i>
                <input type="date" name="tanggal" value="{{ $tanggalAcuan ?? '' }}" onchange="this.form.submit()" class="field" aria-label="Tanggal acuan periode" title="Pilih tanggal mana saja di dalam periode yang diinginkan">
            </div>
            <span class="inline-flex items-center gap-2 h-12 px-1 text-base font-semibold">
                <i data-lucide="calendar-range" class="text-brand-secondary"></i>
                {{ $range }}
            </span>
        </form>

        <div class="flex gap-2.5 lg:ml-auto">
            <button type="button" onclick="window.print()" class="btn btn-secondary">
                <i data-lucide="file-text" class="text-brand-red"></i> Ekspor PDF
            </button>
            <a href="{{ route('owner.laporan.excel', $parameterPeriode) }}" class="btn btn-secondary">
                <i data-lucide="file-spreadsheet" class="text-[#2E7D4F]"></i> Ekspor Excel
            </a>
        </div>
    </div>

    <!-- ==================== RINGKASAN ==================== -->
    <section class="kartu grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-brand-border" aria-label="Ringkasan periode">
        <div class="p-6">
            <p class="text-[15px] text-brand-secondary">Total Penjualan</p>
            <p class="font-display text-4xl font-semibold mt-1 text-brand-ink">{{ $rp($totalPenjualan) }}</p>
        </div>
        <div class="p-6">
            <p class="text-[15px] text-brand-secondary">Jumlah Transaksi</p>
            <p class="font-display text-4xl font-semibold mt-1">{{ number_format($jumlahTransaksi, 0, ',', '.') }}</p>
        </div>
        <div class="p-6">
            <p class="text-[15px] text-brand-secondary">Rata-rata/Transaksi</p>
            <p class="font-display text-4xl font-semibold mt-1">{{ $rp($rataRataTransaksi) }}</p>
        </div>
    </section>

    <!-- ==================== TREN & TERLARIS ==================== -->
    <div class="grid grid-cols-1 xl:grid-cols-5 gap-6">

        <section class="xl:col-span-3 kartu p-6" aria-labelledby="judulTren">
            <div class="mb-5">
                <h3 id="judulTren" class="kartu-judul">Tren Penjualan</h3>
                <p class="text-[15px] text-brand-secondary">Pergerakan transaksi harian</p>
            </div>

            @if($titik->count() && $adaPenjualan)
                <div class="flex gap-3">
                    {{-- Sumbu Y --}}
                    <div class="relative w-14 shrink-0 h-60 text-[13px] text-brand-secondary text-right whitespace-nowrap" aria-hidden="true">
                        <span class="absolute right-0 -top-2">{{ $singkat($skalaAtas) }}</span>
                        <span class="absolute right-0 top-1/2 -translate-y-1/2">{{ $singkat($skalaAtas / 2) }}</span>
                        <span class="absolute right-0 -bottom-2">0</span>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="relative h-60" aria-hidden="true">
                            <div class="absolute inset-x-0 top-0 border-t border-dashed border-[#F0E7DD]"></div>
                            <div class="absolute inset-x-0 top-1/2 border-t border-dashed border-[#F0E7DD]"></div>
                            <div class="absolute inset-x-0 bottom-0 border-t border-brand-border"></div>

                            <svg class="absolute inset-0 w-full h-full overflow-visible" viewBox="0 0 100 100" preserveAspectRatio="none">
                                <polygon points="{{ $area }}" fill="#C8372D" fill-opacity="0.07"/>
                                <polyline points="{{ $garis }}" fill="none" stroke="#C8372D" stroke-width="2" stroke-linejoin="round" stroke-linecap="round" vector-effect="non-scaling-stroke"/>
                            </svg>

                            @foreach($titik as $t)
                                <div class="titik absolute w-8 h-8 -ml-4 -mt-4 flex items-center justify-center" style="left: {{ $t['left'] }}%; top: {{ $t['top'] }}%" tabindex="0">
                                    <span class="bulat w-2.5 h-2.5 rounded-full bg-brand-red ring-2 ring-white transition-transform"></span>
                                    <span class="tip pointer-events-none absolute left-1/2 bottom-full mb-1 px-2.5 py-1.5 rounded bg-brand-dark text-white text-[13px] whitespace-nowrap z-10">
                                        {{ $t['x'] }} &middot; {{ $rp($t['val']) }}
                                    </span>
                                </div>
                            @endforeach

                            {{-- Label langsung hanya pada titik tertinggi --}}
                            @php $puncak = $titik->sortByDesc('val')->first(); @endphp
                            <span class="absolute -translate-y-full -mt-3 text-[13px] font-semibold text-brand-ink whitespace-nowrap {{ $puncak['left'] > 80 ? '-translate-x-full' : ($puncak['left'] < 20 ? '' : '-translate-x-1/2') }}" style="left: {{ $puncak['left'] }}%; top: {{ $puncak['top'] }}%">{{ $rp($puncak['val']) }}</span>
                        </div>

                        <div class="relative h-6 mt-2 text-[13px] text-brand-secondary" aria-hidden="true">
                            @foreach($titik as $t)
                                @continue(!$loop->last && $loop->index % $selangLabel !== 0)
                                @continue(!$loop->last && $loop->remaining < $selangLabel / 2)
                                <span class="absolute -translate-x-1/2 whitespace-nowrap {{ $loop->first ? '!translate-x-0' : '' }} {{ $loop->last ? '!-translate-x-full' : '' }}" style="left: {{ $t['left'] }}%">{{ $t['x'] }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <table class="sr-only">
                    <caption>Tren penjualan</caption>
                    <thead><tr><th>Tanggal</th><th>Nilai</th></tr></thead>
                    <tbody>
                        @foreach($titik as $t)<tr><td>{{ $t['x'] }}</td><td>{{ $rp($t['val']) }}</td></tr>@endforeach
                    </tbody>
                </table>
            @else
                <div class="kosong"><i data-lucide="chart-line"></i><p class="kosong-judul">Belum ada data tren</p><p class="kosong-sub">Grafik muncul setelah ada transaksi pada periode ini.</p></div>
            @endif
        </section>

        <section class="xl:col-span-2 kartu p-6 flex flex-col" aria-labelledby="judulTerlaris">
            <div class="mb-5">
                <h3 id="judulTerlaris" class="kartu-judul">Produk Terlaris</h3>
                <p class="text-[15px] text-brand-secondary">Volume penjualan tertinggi</p>
            </div>

            <ol class="space-y-4 flex-1">
                @forelse($produkTerlaris as $item)
                    <li>
                        <div class="flex items-baseline justify-between gap-3 mb-1.5">
                            <span class="font-medium"><span class="text-brand-secondary mr-1.5">{{ $loop->iteration }}.</span>{{ $item['nama'] }}</span>
                            <span class="text-[15px] text-brand-secondary whitespace-nowrap"><b class="text-brand-ink font-semibold">{{ $item['porsi'] }}</b> porsi</span>
                        </div>
                        <div class="h-2 rounded-full bg-[#F0E7DD]">
                            <div class="h-full rounded-full bg-brand-red" style="width: {{ $item['pct'] }}%"></div>
                        </div>
                    </li>
                @empty
                    <li class="kosong"><i data-lucide="trophy"></i><p class="kosong-judul">Belum ada penjualan</p></li>
                @endforelse
            </ol>

            @if(count($produkTerlaris))
                <div class="mt-5 pt-4 border-t border-brand-border text-[15px] text-brand-secondary flex items-center justify-between">
                    <span>Total {{ count($produkTerlaris) }} varian terfavorit</span>
                    <span class="font-semibold text-brand-ink">{{ number_format($totalPorsiTerlaris, 0, ',', '.') }} porsi total</span>
                </div>
            @endif
        </section>
    </div>

    <!-- ==================== PERBANDINGAN PERIODE ==================== -->
    <section class="kartu p-6 flex flex-col md:flex-row md:items-center gap-4" aria-labelledby="judulBanding">
        @php
            $gayaBanding = $netral ? 'bg-[#F3ECE4] text-brand-secondary' : ($naik ? 'bg-[#E6F3EB] text-[#2E7D4F]' : 'bg-[#FCE6E3] text-[#B0261D]');
            $warnaBanding = $netral ? 'text-brand-secondary' : ($naik ? 'text-[#2E7D4F]' : 'text-[#B0261D]');
        @endphp
        <span class="w-12 h-12 rounded-full flex items-center justify-center shrink-0 {{ $gayaBanding }}">
            <i data-lucide="{{ $netral ? 'minus' : ($naik ? 'trending-up' : 'trending-down') }}" class="!w-6 !h-6"></i>
        </span>
        <div class="flex-1">
            <h3 id="judulBanding" class="text-[13px] uppercase tracking-wide text-brand-secondary font-semibold">Perbandingan Periode</h3>
            <p class="text-lg mt-0.5">
                {{ $perbandingan['label_ini'] ?? 'Periode ini' }} <b>{{ $rp($perbandingan['bulan_ini']) }}</b>
                vs {{ $perbandingan['label_lalu'] ?? 'periode lalu' }} <b>{{ $rp($perbandingan['bulan_lalu']) }}</b>
            </p>
        </div>
        <div class="md:text-right">
            <p class="font-display text-3xl font-semibold {{ $warnaBanding }}">{{ $perbandingan['persentase'] }}</p>
            <p class="text-[15px] text-brand-secondary">{{ $perbandingan['keterangan'] }}</p>
        </div>
    </section>

</div>
@endsection
