@extends('kasir.layout')

@section('title', 'Cek Stok - Kasir')
@section('page_title', 'Cek Stok')

@section('content')
<div class="space-y-5">

    <form action="{{ route('kasir.cek-stok') }}" method="GET" id="filterForm" class="flex flex-col lg:flex-row lg:items-center gap-3">
        <div class="relative lg:w-96">
            <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 text-brand-secondary pointer-events-none"></i>
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Cari produk, tekan Enter…"
                class="w-full h-12 pl-11 pr-4 bg-white border border-brand-border rounded text-base text-brand-ink placeholder-[#B9ABA0] focus:outline-none focus:border-brand-red focus:ring-2 focus:ring-brand-red/15"
            >
        </div>

        <label class="inline-flex items-center gap-3 h-12 px-4 rounded border border-brand-border bg-white cursor-pointer select-none text-[15px] font-medium hover:border-[#D9C8B8] has-[:checked]:border-brand-red has-[:checked]:bg-[#FCF1EF]">
            <input
                type="checkbox"
                name="hanya_perlu_produksi"
                value="1"
                {{ request('hanya_perlu_produksi') ? 'checked' : '' }}
                onchange="document.getElementById('filterForm').submit()"
                class="w-[18px] h-[18px] accent-brand-red"
            >
            <span>Hanya tampilkan yang perlu diproduksi atau sudah habis</span>
        </label>

        <p class="lg:ml-auto text-[15px] text-brand-secondary flex items-center gap-2">
            <span class="relative inline-block w-6 h-2 rounded-full bg-[#F0E7DD]">
                <span class="absolute -top-1 -bottom-1 left-1/2 w-0.5 -ml-px bg-[#8A7B70]/60 rounded-full"></span>
            </span>
            garis = batas minimum
        </p>
    </form>

    <div class="bg-white rounded border border-brand-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[15px]">
                <thead class="bg-brand-lightBg text-[13px] uppercase tracking-wide text-brand-secondary border-b border-brand-border">
                    <tr>
                        <th class="py-3 px-5 font-semibold">Produk</th>
                        <th class="py-3 px-5 font-semibold">Satuan</th>
                        <th class="py-3 px-5 font-semibold">Stok Tersedia</th>
                        <th class="py-3 px-5 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border">
                    @forelse($products as $product)
                        <tr class="hover:bg-brand-lightBg/60 transition-colors">
                            <td class="py-3 px-5">
                                <div class="flex items-center gap-3">
                                    @include('partials.foto-produk', ['produk' => $product, 'class' => 'w-11 h-11 rounded shrink-0', 'ikon' => 'w-7 h-7'])
                                    <span class="font-semibold text-brand-ink">{{ $product->nama }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-5 text-brand-secondary">{{ $product->satuan }}</td>
                            <td class="py-3 px-5">
                                <div class="flex items-center gap-3">
                                    <span class="w-10 text-right text-lg font-bold {{ $product->stok <= 0 ? 'text-brand-red' : ($product->stok < $product->stok_minimum ? 'text-[#B4641A]' : 'text-brand-ink') }}">{{ $product->stok }}</span>
                                    @include('partials.bar-stok', ['produk' => $product])
                                    <span class="text-[13px] text-brand-secondary whitespace-nowrap">min {{ $product->stok_minimum }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-5">
                                @include('partials.status-stok', ['status' => $product->status_stok])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-14">
                                <div class="flex flex-col items-center text-center">
                                    <i data-lucide="package-search" class="!w-10 !h-10 text-[#C9BBAE]"></i>
                                    <p class="mt-3 text-lg font-semibold">Tidak ada produk yang cocok</p>
                                    <p class="text-brand-secondary">
                                        @if(request('hanya_perlu_produksi'))
                                            Kabar baik &mdash; semua stok masih aman.
                                        @else
                                            Coba kata kunci lain.
                                        @endif
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
