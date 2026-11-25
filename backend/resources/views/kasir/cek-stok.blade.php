@extends('kasir.layout')

@section('title', 'Cek Stok - Kasir')
@section('page_title', 'Cek Stok')

@section('content')
<div class="space-y-6 max-w-7xl">

    <!-- Filter & Pencarian (Mockup Frame 3 Kasir) -->
    <div class="bg-white rounded-2xl border border-[#EADFD3] p-5 shadow-sm space-y-3.5">
        <form action="{{ route('kasir.cek-stok') }}" method="GET" id="filterForm" class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <!-- Search Bar -->
            <div class="relative w-full sm:w-80">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}"
                    placeholder="Cari produk..." 
                    class="w-full pl-9 pr-4 py-2 bg-stone-50 border border-[#EADFD3] rounded-xl text-xs text-stone-800 placeholder-stone-400 focus:outline-none focus:ring-1 focus:ring-brand-red"
                >
                <svg class="w-4 h-4 text-stone-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            <!-- Checkbox Filter: Hanya tampilkan yang perlu diproduksi atau sudah habis -->
            <div class="flex items-center gap-2">
                <label class="flex items-center gap-2 cursor-pointer select-none text-xs font-semibold text-stone-700">
                    <input 
                        type="checkbox" 
                        name="hanya_perlu_produksi" 
                        value="1" 
                        {{ request('hanya_perlu_produksi') ? 'checked' : '' }}
                        onchange="document.getElementById('filterForm').submit()"
                        class="w-4 h-4 rounded border-stone-300 text-brand-red focus:ring-brand-red accent-brand-red"
                    >
                    <span>Hanya tampilkan yang perlu diproduksi atau sudah habis</span>
                </label>
            </div>
        </form>
    </div>

    <!-- Tabel Cek Stok Kasir (Frame 3) -->
    <div class="bg-white rounded-2xl border border-[#EADFD3] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#FAF7F2] text-stone-500 font-bold border-b border-[#EADFD3]">
                    <tr>
                        <th class="py-3.5 px-6">Produk</th>
                        <th class="py-3.5 px-6">Satuan</th>
                        <th class="py-3.5 px-6">Stok Tersedia</th>
                        <th class="py-3.5 px-6">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#EADFD3] text-stone-800 font-medium">
                    @forelse($products as $product)
                        <tr class="hover:bg-stone-50/70 transition">
                            <!-- Nama Produk -->
                            <td class="py-4 px-6 font-bold text-stone-900">
                                {{ $product->nama }}
                            </td>

                            <!-- Satuan -->
                            <td class="py-4 px-6 text-stone-500">
                                {{ $product->satuan }}
                            </td>

                            <!-- Stok Tersedia -->
                            <td class="py-4 px-6 font-bold {{ $product->stok <= 0 ? 'text-brand-red' : ($product->stok < $product->stok_minimum ? 'text-[#D9822B]' : 'text-stone-900') }}">
                                {{ $product->stok }}
                            </td>

                            <!-- Status Stok (BR-03) -->
                            <td class="py-4 px-6">
                                @if($product->status_stok === 'Aman')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold text-[#2E8B57] bg-[#2E8B57]/10 border border-[#2E8B57]/20">
                                        Aman
                                    </span>
                                @elseif($product->status_stok === 'Menipis')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold text-[#D9822B] bg-[#D9822B]/10 border border-[#D9822B]/20">
                                        Menipis
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold text-brand-red bg-brand-red/10 border border-brand-red/20">
                                        Habis
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-stone-400">
                                Tidak ada produk yang sesuai kriteria pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

