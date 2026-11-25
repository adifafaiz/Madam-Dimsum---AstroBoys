@extends('kasir.layout')

@section('title', 'Riwayat Saya - Kasir')
@section('page_title', 'Riwayat Saya')

@section('content')
<div class="max-w-7xl">

    <!-- Grid 2 Kolom (Sesuai Mockup Frame 4 Kasir) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        <!-- ==================== SISI KIRI: DAFTAR TRANSAKSI (LG: 7 COLS) ==================== -->
        <div class="lg:col-span-7 space-y-4">
            <!-- Filter Bar: Tanggal & Pencarian -->
            <form action="{{ route('kasir.riwayat') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
                <input 
                    type="date" 
                    name="tanggal" 
                    value="{{ request('tanggal') }}"
                    onchange="this.form.submit()"
                    class="w-full sm:w-auto px-3.5 py-2.5 bg-white border border-[#EADFD3] rounded-xl text-xs font-semibold text-stone-800 shadow-sm cursor-pointer focus:outline-none focus:ring-1 focus:ring-brand-red"
                >

                <div class="relative flex-1 w-full">
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}"
                        placeholder="No. transaksi / produk" 
                        class="w-full pl-9 pr-4 py-2.5 bg-white border border-[#EADFD3] rounded-xl text-xs text-stone-800 placeholder-stone-400 focus:outline-none focus:ring-1 focus:ring-brand-red shadow-sm"
                    >
                    <svg class="w-4 h-4 text-stone-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </form>

            <!-- Tabel Transaksi Kasir -->
            <div class="bg-white rounded-2xl border border-[#EADFD3] shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-[#FAF7F2] text-stone-500 font-bold border-b border-[#EADFD3]">
                            <tr>
                                <th class="py-3.5 px-6">No. Transaksi</th>
                                <th class="py-3.5 px-6">Waktu</th>
                                <th class="py-3.5 px-6">Item</th>
                                <th class="py-3.5 px-6 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#EADFD3] text-stone-800 font-medium">
                            @forelse($orders as $o)
                                <tr 
                                    onclick="window.location.href='{{ route('kasir.riwayat', array_merge(request()->query(), ['selected' => $o->kode])) }}'"
                                    class="cursor-pointer transition {{ (isset($selectedOrder) && $selectedOrder->id === $o->id) ? 'bg-red-50/40 font-semibold' : 'hover:bg-stone-50/70' }}"
                                >
                                    <td class="py-4 px-6 font-bold text-stone-900">{{ $o->kode }}</td>
                                    <td class="py-4 px-6 text-stone-500">{{ $o->waktu ? $o->waktu->format('H:i') : '-' }}</td>
                                    <td class="py-4 px-6 text-stone-600">{{ $o->items->count() }} produk</td>
                                    <td class="py-4 px-6 font-bold text-stone-900 text-right">Rp {{ number_format($o->total, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-stone-400">Belum ada riwayat transaksi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ==================== SISI KANAN: DETAIL TRANSAKSI TERPILIH (LG: 5 COLS) ==================== -->
        <div class="lg:col-span-5 bg-white rounded-2xl border border-[#EADFD3] p-7 shadow-sm space-y-6">
            @if(isset($selectedOrder) && $selectedOrder)
                <div>
                    <h3 class="text-sm font-bold text-stone-900">Detail {{ $selectedOrder->kode }}</h3>
                    <p class="text-xs text-stone-400 mt-1">{{ $selectedOrder->waktu ? $selectedOrder->waktu->format('d M Y, H:i') : '-' }}</p>
                </div>

                <!-- Rincian Produk Pesanan -->
                <div class="space-y-3 divide-y divide-stone-100 text-xs">
                    @foreach($selectedOrder->items as $it)
                        <div class="flex items-center justify-between pt-3 first:pt-0">
                            <div>
                                <span class="font-bold text-stone-900">{{ $it->product->nama ?? 'Produk' }}</span>
                                <span class="text-stone-400 font-semibold ml-1">x{{ $it->qty }}</span>
                            </div>
                            <span class="font-bold text-stone-900 font-mono">Rp {{ number_format($it->subtotal, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>

                <!-- Total -->
                <div class="border-t border-stone-200 pt-4 flex items-center justify-between">
                    <span class="text-xs font-bold text-stone-700">Total</span>
                    <span class="text-base font-extrabold text-brand-red">Rp {{ number_format($selectedOrder->total, 0, ',', '.') }}</span>
                </div>

                <!-- Tombol Cetak Ulang Struk (FR-15, BR-09) -->
                <div class="pt-2">
                    <a 
                        href="{{ route('kasir.struk', ['order' => $selectedOrder->id, 'ulang' => 1]) }}" 
                        target="_blank"
                        class="w-full py-2.5 rounded-xl border border-[#EADFD3] hover:bg-stone-50 text-stone-700 text-xs font-semibold flex items-center justify-center gap-2 transition"
                    >
                        <svg class="w-4 h-4 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                        <span>Cetak Ulang Struk</span>
                    </a>
                </div>
            @else
                <div class="text-center py-12 text-stone-400 text-xs">
                    Pilih salah satu transaksi di tabel sebelah kiri untuk melihat rinciannya.
                </div>
            @endif
        </div>

    </div>

</div>
@endsection

