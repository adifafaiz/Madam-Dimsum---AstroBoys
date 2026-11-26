@extends('dapur.layout')

@section('title', 'Antrian Pesanan - Dapur')
@section('page_title', 'Antrian Pesanan')

@php
    // Konfigurasi tampilan per kolom. Nilai 'status' = status tujuan yang dikirim ke server (tidak berubah).
    $kolom = [
        [
            'judul' => 'Baru', 'pesanan' => $pesananBaru, 'warna' => '#C8372D', 'hover' => '#B22F26',
            'status' => 'diproses', 'tombol' => 'Mulai masak', 'ikon' => 'flame',
            'kosong' => 'Belum ada pesanan baru', 'kosongSub' => 'Pesanan dari kasir akan muncul di sini.',
        ],
        [
            'judul' => 'Diproses', 'pesanan' => $pesananDiproses, 'warna' => '#D9822B', 'hover' => '#C27324',
            'status' => 'siap', 'tombol' => 'Tandai siap', 'ikon' => 'bell-ring',
            'kosong' => 'Tidak ada yang sedang dimasak', 'kosongSub' => 'Tekan "Mulai masak" pada pesanan baru.',
        ],
        [
            'judul' => 'Siap diambil', 'pesanan' => $pesananSiap, 'warna' => '#2E8B57', 'hover' => '#257346',
            'status' => 'selesai', 'tombol' => 'Sudah diambil', 'ikon' => 'hand-platter',
            'kosong' => 'Belum ada yang siap diambil', 'kosongSub' => 'Pesanan yang selesai dimasak tampil di sini.',
        ],
    ];
    $totalAktif = $pesananBaru->count() + $pesananDiproses->count() + $pesananSiap->count();
@endphp

@section('header_extra')
    <div class="hidden md:flex items-center gap-5">
        <span class="inline-flex items-center gap-2 h-10 px-3.5 rounded bg-white border border-brand-border text-[15px]">
            <span class="w-2 h-2 rounded-full {{ $totalAktif ? 'bg-[#D9822B]' : 'bg-[#C9BBAE]' }}"></span>
            <b class="font-semibold">{{ $totalAktif }}</b> pesanan aktif
        </span>
        <div class="text-right leading-none">
            <p id="jamDapur" class="font-display text-[34px] font-semibold text-brand-ink tabular-nums">--:--</p>
            <p id="tanggalDapur" class="text-[13px] text-brand-secondary mt-1">&nbsp;</p>
        </div>
    </div>
@endsection

@push('styles')
<style>
    /* Tiket pesanan: tepi atas bergerigi + strip status di kiri. */
    .tiket {
        position: relative; background: #fff; margin-top: 9px;
        border-radius: 0 0 6px 6px;
        filter: drop-shadow(0 0 .6px #CDBBAA) drop-shadow(0 1px 1px rgba(42,33,28,.05));
    }
    .tiket::before {
        content: ''; position: absolute; left: 0; right: 0; top: -9px; height: 9px;
        background: radial-gradient(circle at 7px 0, transparent 5.5px, #fff 6px) 0 0 / 14px 9px repeat-x;
    }
    .tiket .strip { position: absolute; left: 0; top: 0; bottom: 0; width: 6px; border-radius: 0 0 0 6px; }
    .tunggu { transition: background-color .2s, color .2s; }
    .tunggu[data-level="lama"]   { background: #FBEBDC; color: #A8571A; }
    .tunggu[data-level="kritis"] { background: #FCE6E3; color: #B0261D; }
    .tunggu .kedip { display: none; }
    .tunggu[data-level="kritis"] .kedip { display: inline-block; animation: kedip 1s steps(1, end) infinite; }
    @keyframes kedip { 50% { opacity: 0; } }
    @media (prefers-reduced-motion: reduce) { .tunggu[data-level="kritis"] .kedip { animation: none; } }
</style>
@endpush

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

    @foreach($kolom as $k)
        <section aria-label="Pesanan {{ $k['judul'] }}">
            {{-- Header kolom: garis status 3px + nama + jumlah dalam lingkaran --}}
            <div class="border-t-[3px] pt-3 mb-4 flex items-center justify-between" style="border-color: {{ $k['warna'] }}">
                <h2 class="font-display text-2xl font-semibold text-brand-ink">{{ $k['judul'] }}</h2>
                <span class="w-9 h-9 rounded-full border-2 flex items-center justify-center font-bold text-lg tabular-nums"
                      style="border-color: {{ $k['warna'] }}; color: {{ $k['warna'] }}">
                    {{ $k['pesanan']->count() }}
                </span>
            </div>

            <div class="space-y-5">
                @forelse($k['pesanan'] as $order)
                    <article class="tiket">
                        <span class="strip" style="background: {{ $k['warna'] }}"></span>

                        <div class="pl-6 pr-5 pt-4 pb-5">
                            {{-- Kode + jam masuk + lama menunggu --}}
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-display text-[22px] font-semibold leading-none text-brand-ink">{{ $order->kode }}</p>
                                    <p class="text-[13px] text-brand-secondary mt-1.5">
                                        Masuk <span class="jam-pesan" data-waktu="{{ $order->waktu?->toIso8601String() }}">{{ $order->waktu ? $order->waktu->format('H:i') : '-' }}</span>
                                    </p>
                                </div>
                                @if($order->waktu)
                                    <span class="tunggu inline-flex items-center gap-1.5 h-8 px-2.5 rounded bg-[#F3ECE4] text-brand-secondary text-[15px] font-semibold whitespace-nowrap"
                                          data-waktu="{{ $order->waktu->toIso8601String() }}" title="Lama menunggu sejak pesanan masuk">
                                        <span class="kedip w-2 h-2 rounded-full bg-[#C8372D]"></span>
                                        <i data-lucide="timer" class="!w-4 !h-4"></i>
                                        <span class="tunggu-teks">{{ max(0, (int) $order->waktu->diffInMinutes(now())) }} mnt</span>
                                    </span>
                                @endif
                            </div>

                            {{-- Item: jumlah dulu, tebal dan besar --}}
                            <ul class="mt-4 pt-4 border-t-2 border-dashed border-brand-border space-y-2.5">
                                @foreach($order->items as $item)
                                    <li class="flex items-baseline gap-3">
                                        <span class="min-w-[2.75rem] text-2xl font-bold text-brand-ink tabular-nums">{{ $item->qty }}&times;</span>
                                        <span class="text-lg font-medium text-brand-ink leading-snug">{{ $item->product->nama ?? 'Produk' }}</span>
                                    </li>
                                @endforeach
                            </ul>

                            <form action="{{ route('dapur.antrian.update', $order->id) }}" method="POST" class="mt-5">
                                @csrf
                                <input type="hidden" name="status" value="{{ $k['status'] }}">
                                <button type="submit" data-loading-text="Menyimpan…"
                                    class="w-full h-12 rounded text-white text-base font-semibold flex items-center justify-center gap-2 transition-colors disabled:opacity-70 disabled:cursor-wait"
                                    style="background: {{ $k['warna'] }}"
                                    onmouseover="this.style.background='{{ $k['hover'] }}'" onmouseout="this.style.background='{{ $k['warna'] }}'">
                                    <i data-lucide="{{ $k['ikon'] }}"></i>
                                    <span>{{ $k['tombol'] }}</span>
                                </button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="flex flex-col items-center justify-center text-center min-h-[220px] px-6 rounded border-2 border-dashed border-brand-border">
                        @include('partials.ikon-kukusan', ['class' => 'w-14 h-14 text-[#C9BBAE]'])
                        <p class="mt-3 text-lg font-semibold text-brand-ink">{{ $k['kosong'] }}</p>
                        <p class="mt-1 text-[15px] text-brand-secondary">{{ $k['kosongSub'] }}</p>
                    </div>
                @endforelse
            </div>
        </section>
    @endforeach

</div>
@endsection

@push('scripts')
<script>
    (function () {
        const pad = n => String(n).padStart(2, '0');

        // Jam besar di bar atas.
        function updateJam() {
            const now = new Date();
            document.getElementById('jamDapur').textContent = pad(now.getHours()) + ':' + pad(now.getMinutes());
            document.getElementById('tanggalDapur').textContent =
                now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long' });
        }

        // Lama menunggu: oranye > 10 menit, merah + titik berkedip > 15 menit.
        function updateTunggu() {
            const now = Date.now();
            document.querySelectorAll('.tunggu[data-waktu]').forEach(el => {
                const mnt = Math.max(0, Math.floor((now - new Date(el.dataset.waktu).getTime()) / 60000));
                el.querySelector('.tunggu-teks').textContent = mnt + ' mnt';
                el.dataset.level = mnt > 15 ? 'kritis' : mnt > 10 ? 'lama' : 'normal';
            });
        }

        // Jam pesanan ditampilkan dalam zona waktu perangkat.
        document.querySelectorAll('.jam-pesan[data-waktu]').forEach(el => {
            if (!el.dataset.waktu) return;
            const d = new Date(el.dataset.waktu);
            el.textContent = pad(d.getHours()) + ':' + pad(d.getMinutes());
        });

        updateJam();
        updateTunggu();
        setInterval(updateJam, 1000 * 10);
        setInterval(updateTunggu, 1000 * 30);
    })();
</script>
@endpush
