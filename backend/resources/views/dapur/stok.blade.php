@extends('dapur.layout')

@section('title', 'Stok Produk - Dapur')
@section('page_title', 'Stok Produk')

@php
    $perluProduksi = $products->filter(fn ($p) => $p->perlu_diproduksi > 0);
@endphp

@section('content')
<div class="space-y-5">

    <div class="flex flex-col xl:flex-row xl:items-center gap-3">
        <form action="{{ route('dapur.stok') }}" method="GET" id="filterForm" class="flex flex-col lg:flex-row lg:items-center gap-3 flex-1">
            <div class="field-ikon lg:w-96">
                <i data-lucide="search"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari produk, tekan Enter…" class="field">
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
        </form>

        @if($perluProduksi->count())
            <a href="{{ route('dapur.catat-produksi') }}" class="btn btn-primary btn-lg">
                <i data-lucide="circle-plus"></i> Catat Produksi
            </a>
        @endif
    </div>

    <div class="kartu overflow-hidden">
        <div class="overflow-x-auto">
            <table class="tabel">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Stok vs minimum</th>
                        <th>Status</th>
                        <th class="text-right">Perlu Diproduksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    @include('partials.foto-produk', ['produk' => $product, 'class' => 'w-11 h-11 rounded shrink-0', 'ikon' => 'w-7 h-7'])
                                    <span class="text-base font-semibold">{{ $product->nama }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <span class="w-10 text-right text-xl font-bold {{ $product->stok <= 0 ? 'text-brand-red' : ($product->stok < $product->stok_minimum ? 'text-[#B4641A]' : 'text-brand-ink') }}">{{ $product->stok }}</span>
                                    @include('partials.bar-stok', ['produk' => $product])
                                    <span class="text-[13px] text-brand-secondary whitespace-nowrap">min {{ $product->stok_minimum }}</span>
                                </div>
                            </td>
                            <td>@include('partials.status-stok', ['status' => $product->status_stok])</td>
                            <td class="text-right">
                                @if($product->perlu_diproduksi > 0)
                                    <span class="inline-flex items-baseline gap-1 h-9 px-3 rounded bg-[#FCF1EF] text-brand-red items-center">
                                        <i data-lucide="flame" class="!w-4 !h-4 self-center"></i>
                                        <b class="text-xl">{{ $product->perlu_diproduksi }}</b> porsi
                                    </span>
                                @else
                                    <span class="text-brand-secondary/60">&mdash;</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="kosong">
                                    @if(request('hanya_perlu_produksi'))
                                        <i data-lucide="circle-check"></i>
                                        <p class="kosong-judul">Tidak ada yang perlu diproduksi</p>
                                        <p class="kosong-sub">Semua stok masih di atas batas minimum.</p>
                                    @else
                                        <i data-lucide="package-search"></i>
                                        <p class="kosong-judul">Produk tidak ditemukan</p>
                                        <p class="kosong-sub">Coba kata kunci lain.</p>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 border-t border-brand-border bg-brand-lightBg text-[13px] text-brand-secondary flex flex-wrap items-center gap-x-4 gap-y-1">
            <span>Perlu diproduksi = 2&times; batas minimum, hanya untuk stok Menipis atau Habis.</span>
            <span class="flex items-center gap-2 sm:ml-auto">
                <span class="relative inline-block w-6 h-2 rounded-full bg-[#F0E7DD]"><span class="absolute -top-1 -bottom-1 left-1/2 w-0.5 -ml-px bg-[#8A7B70]/60 rounded-full"></span></span>
                garis = batas minimum
            </span>
        </div>
    </div>

</div>
@endsection
