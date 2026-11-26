@extends('kasir.layout')

@section('title', 'Riwayat Saya - Kasir')
@section('page_title', 'Riwayat Saya')

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_420px] gap-6 items-start">

    <!-- ==================== DAFTAR TRANSAKSI ==================== -->
    <section class="min-w-0 space-y-4" aria-label="Daftar transaksi">
        <form action="{{ route('kasir.riwayat') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="relative sm:w-56 shrink-0">
                <i data-lucide="calendar" class="absolute left-4 top-1/2 -translate-y-1/2 text-brand-secondary pointer-events-none"></i>
                <input
                    type="date"
                    name="tanggal"
                    value="{{ request('tanggal') }}"
                    onchange="this.form.submit()"
                    aria-label="Tanggal"
                    class="w-full h-12 pl-11 pr-3 bg-white border border-brand-border rounded text-base text-brand-ink cursor-pointer focus:outline-none focus:border-brand-red focus:ring-2 focus:ring-brand-red/15"
                >
            </div>

            <div class="relative flex-1">
                <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 text-brand-secondary pointer-events-none"></i>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="No. transaksi / produk"
                    class="w-full h-12 pl-11 pr-4 bg-white border border-brand-border rounded text-base text-brand-ink placeholder-[#B9ABA0] focus:outline-none focus:border-brand-red focus:ring-2 focus:ring-brand-red/15"
                >
            </div>

            <button type="submit" class="h-12 px-5 rounded bg-brand-dark hover:bg-black text-white text-base font-semibold flex items-center justify-center gap-2 transition-colors">
                <i data-lucide="search" class="!w-4 !h-4"></i> Cari
            </button>
        </form>

        <div class="bg-white rounded border border-brand-border overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[15px]">
                    <thead class="bg-brand-lightBg text-[13px] uppercase tracking-wide text-brand-secondary border-b border-brand-border">
                        <tr>
                            <th class="py-3 px-5 font-semibold">No. Transaksi</th>
                            <th class="py-3 px-5 font-semibold">Waktu</th>
                            <th class="py-3 px-5 font-semibold">Item</th>
                            <th class="py-3 px-5 font-semibold text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-border">
                        @forelse($orders as $o)
                            @php
                                $dipilih = isset($selectedOrder) && $selectedOrder && $selectedOrder->id === $o->id;
                                $urlDetail = route('kasir.riwayat', array_merge(request()->query(), ['selected' => $o->kode]));
                            @endphp
                            <tr
                                onclick="window.location.href='{{ $urlDetail }}'"
                                onkeydown="if (event.key === 'Enter') window.location.href='{{ $urlDetail }}'"
                                tabindex="0"
                                @if($dipilih) aria-current="true" @endif
                                class="cursor-pointer transition-colors focus:outline-none focus-visible:bg-brand-lightBg {{ $dipilih ? 'bg-[#FCF1EF] shadow-[inset_4px_0_0_#C8372D]' : 'hover:bg-brand-lightBg/70' }}"
                            >
                                <td class="py-3.5 px-5 font-semibold text-brand-ink">{{ $o->kode }}</td>
                                <td class="py-3.5 px-5 text-brand-secondary">{{ $o->waktu ? $o->waktu->format('H:i') : '-' }}</td>
                                <td class="py-3.5 px-5 text-brand-secondary">{{ $o->items->count() }} produk</td>
                                <td class="py-3.5 px-5 font-semibold text-brand-ink text-right">Rp {{ number_format($o->total, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-14">
                                    <div class="flex flex-col items-center text-center px-6">
                                        <i data-lucide="receipt-text" class="!w-10 !h-10 text-[#C9BBAE]"></i>
                                        @if(request('tanggal') || request('search'))
                                            <p class="mt-3 text-lg font-semibold">Tidak ada transaksi yang cocok</p>
                                            <p class="text-brand-secondary">Coba tanggal atau kata kunci lain.</p>
                                        @else
                                            <p class="mt-3 text-lg font-semibold">Belum ada transaksi</p>
                                            <p class="text-brand-secondary">Transaksi yang Anda simpan akan tercatat di sini.</p>
                                            <a href="{{ route('kasir.transaksi') }}" class="mt-4 h-11 px-4 rounded bg-brand-red hover:bg-brand-redHover text-white font-semibold inline-flex items-center gap-2 transition-colors">
                                                <i data-lucide="plus" class="!w-4 !h-4"></i> Buat transaksi
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- ==================== DETAIL TRANSAKSI ==================== -->
    <aside class="xl:sticky xl:top-[96px]" aria-label="Detail transaksi">
        @if(isset($selectedOrder) && $selectedOrder)
            <div class="struk">
                <div class="px-6 pt-6 pb-5">
                    <p class="text-[13px] uppercase tracking-wide text-brand-secondary">Detail transaksi</p>
                    <h2 class="font-display text-2xl font-semibold mt-1">{{ $selectedOrder->kode }}</h2>
                    <p class="text-[15px] text-brand-secondary mt-0.5">{{ $selectedOrder->waktu ? $selectedOrder->waktu->format('d M Y, H:i') : '-' }}</p>
                </div>

                <ul class="px-6 py-4 border-t-2 border-dashed border-brand-border space-y-3">
                    @foreach($selectedOrder->items as $it)
                        <li class="flex items-baseline gap-3">
                            <span class="w-9 font-bold">{{ $it->qty }}&times;</span>
                            <span class="flex-1 min-w-0">{{ $it->product->nama ?? 'Produk' }}</span>
                            <span class="font-semibold">Rp {{ number_format($it->subtotal, 0, ',', '.') }}</span>
                        </li>
                    @endforeach
                </ul>

                <div class="sobek px-6 pt-5 pb-6 space-y-2">
                    <div class="flex items-baseline justify-between">
                        <span class="text-lg font-semibold">Total</span>
                        <span class="font-display text-3xl font-semibold text-brand-red">Rp {{ number_format($selectedOrder->total, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-[15px] text-brand-secondary">
                        <span>Dibayar</span>
                        <span class="text-brand-ink">Rp {{ number_format($selectedOrder->dibayar, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-[15px] text-brand-secondary">
                        <span>Kembalian</span>
                        <span class="text-brand-ink">Rp {{ number_format($selectedOrder->kembalian, 0, ',', '.') }}</span>
                    </div>

                    <a
                        href="{{ route('kasir.struk', ['order' => $selectedOrder->id, 'ulang' => 1]) }}"
                        target="_blank"
                        class="mt-4 h-12 rounded border border-brand-border bg-white hover:bg-brand-lightBg text-base font-semibold flex items-center justify-center gap-2 transition-colors"
                    >
                        <i data-lucide="printer" class="!w-4 !h-4"></i>
                        <span>Cetak Ulang Struk</span>
                    </a>
                </div>
            </div>
        @else
            <div class="flex flex-col items-center justify-center text-center min-h-[320px] px-8 rounded border-2 border-dashed border-brand-border">
                <i data-lucide="mouse-pointer-click" class="!w-10 !h-10 text-[#C9BBAE]"></i>
                <p class="mt-3 text-lg font-semibold">Pilih satu transaksi</p>
                <p class="text-brand-secondary">Rincian item, uang dibayar, dan kembalian akan tampil di sini.</p>
            </div>
        @endif
    </aside>

</div>
@endsection
