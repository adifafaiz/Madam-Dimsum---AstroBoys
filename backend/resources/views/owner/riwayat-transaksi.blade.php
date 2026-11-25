@extends('owner.layout')

@section('title', 'Riwayat Transaksi - Owner')
@section('page_title', 'Riwayat Transaksi')

@section('content')
<div class="max-w-7xl">

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- ==================== KOLOM KIRI: DAFTAR TRANSAKSI (8 Kolom) ==================== -->
        <div class="lg:col-span-8 bg-white rounded-2xl border border-[#EADFD3] shadow-sm overflow-hidden">
            <!-- Filter & Search Bar -->
            <div class="p-5 border-b border-[#EADFD3] flex flex-wrap items-center justify-between gap-3 bg-white">
                <form action="{{ route('owner.riwayat-transaksi') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full">
                    <!-- Filter Tanggal -->
                    <div class="relative">
                        <input 
                            type="date" 
                            name="tanggal" 
                            value="{{ request('tanggal') }}" 
                            onchange="this.form.submit()"
                            class="px-3 py-2 bg-white border border-[#EADFD3] rounded-lg text-xs font-semibold text-stone-800 focus:outline-none focus:ring-1 focus:ring-brand-red cursor-pointer shadow-sm"
                        >
                    </div>

                    <!-- Search No. Transaksi / Produk -->
                    <div class="relative flex-1 min-w-[200px]">
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ request('search') }}"
                            placeholder="Cari No. transaksi / produk..." 
                            class="w-full pl-9 pr-4 py-2 bg-white border border-[#EADFD3] rounded-lg text-xs placeholder-stone-400 focus:outline-none focus:ring-1 focus:ring-brand-red shadow-sm"
                        >
                        <svg class="w-4 h-4 text-stone-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    @if(request('tanggal') || request('search'))
                        <a href="{{ route('owner.riwayat-transaksi') }}" class="text-xs font-semibold text-brand-red hover:underline py-2">
                            Reset
                        </a>
                    @endif
                </form>
            </div>

            <!-- Tabel Transaksi -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#FAF7F2] text-stone-500 font-bold border-b border-[#EADFD3]">
                        <tr>
                            <th class="py-3.5 px-6">No. Transaksi</th>
                            <th class="py-3.5 px-6">Waktu</th>
                            <th class="py-3.5 px-6">Kasir</th>
                            <th class="py-3.5 px-6 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EADFD3] text-stone-800 font-medium">
                        @forelse($orders as $o)
                            @php
                                $isSelected = ($selectedOrder && $selectedOrder->id === $o->id);
                            @endphp
                            <tr 
                                onclick="window.location.href='{{ route('owner.riwayat-transaksi', ['selected' => $o->kode, 'tanggal' => request('tanggal'), 'search' => request('search')]) }}'"
                                class="cursor-pointer transition {{ $isSelected ? 'bg-red-50/70 border-l-4 border-l-brand-red' : 'hover:bg-stone-50/70' }}"
                            >
                                <td class="py-4 px-6 font-bold {{ $isSelected ? 'text-brand-red' : 'text-stone-900' }}">
                                    {{ $o->kode }}
                                    @if($o->status === 'batal')
                                        <span class="ml-1 text-[10px] text-red-600 bg-red-100 px-1.5 py-0.5 rounded">Batal</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-stone-500 whitespace-nowrap">
                                    {{ $o->waktu ? $o->waktu->format('d M H:i') : '-' }}
                                </td>
                                <td class="py-4 px-6">{{ $o->user->name ?? 'Kasir' }}</td>
                                <td class="py-4 px-6 text-right font-bold text-stone-900">
                                    Rp {{ number_format($o->total, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-stone-400">Tidak ada transaksi ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ==================== KOLOM KANAN: PANEL DETAIL TRANSAKSI (4 Kolom - FR-28) ==================== -->
        <div class="lg:col-span-4 bg-white p-6 rounded-2xl border border-[#EADFD3] shadow-sm flex flex-col justify-between">
            @if($selectedOrder)
                <div>
                    <!-- Header Detail -->
                    <div class="pb-3 border-b border-stone-100 mb-4">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-bold text-stone-900">Detail {{ $selectedOrder->kode }}</h4>
                            @if($selectedOrder->status === 'batal')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-brand-red uppercase">Dibatalkan</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase">Selesai</span>
                            @endif
                        </div>
                        <p class="text-[11px] text-stone-400 mt-1">
                            {{ $selectedOrder->waktu ? $selectedOrder->waktu->format('d M Y, H:i') : '-' }} &middot; Kasir: <b class="text-stone-700">{{ $selectedOrder->user->name ?? 'Kasir' }}</b>
                        </p>
                    </div>

                    <!-- Breakdown Items Sesuai Mockup Frame 7 -->
                    <div class="space-y-3.5 text-xs mb-6 divide-y divide-stone-100">
                        @foreach($selectedOrder->items as $item)
                            <div class="pt-2.5 first:pt-0 flex items-center justify-between">
                                <span class="text-stone-700 font-medium">
                                    {{ $item->product->nama ?? 'Dimsum' }} <span class="text-stone-400 font-normal">x{{ $item->qty }}</span>
                                </span>
                                <span class="font-semibold text-stone-900">
                                    {{ number_format($item->subtotal, 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <!-- Ringkasan Pembayaran Sesuai Mockup Frame 7 -->
                    <div class="pt-4 border-t border-[#EADFD3] space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-stone-800">Total</span>
                            <span class="text-lg font-extrabold text-brand-red">
                                Rp {{ number_format($selectedOrder->total, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-stone-500 pt-1">
                            <span>Dibayar</span>
                            <span class="font-medium text-stone-800">
                                Rp {{ number_format($selectedOrder->dibayar, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-stone-500">
                            <span>Kembalian</span>
                            <span class="font-medium text-stone-800">
                                Rp {{ number_format($selectedOrder->kembalian, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    @if($selectedOrder->status === 'batal')
                        <div class="mt-4 p-3 rounded-lg bg-red-50 border border-red-200 text-brand-red text-xs">
                            <p class="font-bold">Alasan Batal:</p>
                            <p class="text-stone-700 mt-0.5">{{ $selectedOrder->alasan_batal }}</p>
                        </div>
                    @endif
                </div>

                <!-- Tombol Batalkan Transaksi (BR-06) -->
                @if($selectedOrder->status !== 'batal')
                    <div class="pt-6 border-t border-stone-100 mt-6">
                        <button 
                            type="button" 
                            onclick="openModalBatalTransaksi('{{ $selectedOrder->id }}', '{{ $selectedOrder->kode }}')" 
                            class="w-full py-2.5 px-4 rounded-xl border border-red-200 text-brand-red hover:bg-red-50 text-xs font-semibold shadow-sm transition flex items-center justify-center gap-1.5"
                        >
                            <span>Batalkan Transaksi (BR-06)</span>
                        </button>
                    </div>
                @endif
            @else
                <div class="py-12 text-center text-xs text-stone-400">
                    Pilih salah satu transaksi untuk melihat rincian nota.
                </div>
            @endif
        </div>

    </div>

</div>

<!-- Modal Batalkan Transaksi (BR-06) -->
<div id="modalBatal" class="fixed inset-0 bg-stone-900/50 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden transition-all">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 border border-[#EADFD3] shadow-2xl relative">
        <div class="flex items-center justify-between pb-3 border-b border-stone-100 mb-4">
            <h3 class="text-base font-bold text-stone-900">Batalkan Transaksi</h3>
            <button type="button" onclick="closeModalBatal()" class="text-stone-400 hover:text-stone-700 p-1">✕</button>
        </div>

        <form id="formBatal" method="POST" class="space-y-4">
            @csrf
            <p class="text-xs text-stone-600">
                Membatalkan nota <b id="batalKodeText" class="text-stone-900"></b> akan otomatis mengembalikan stok seluruh produk terkait ke status sebelum transaksi (BR-06).
            </p>

            <div>
                <label for="alasan_batal" class="block text-xs font-semibold text-stone-600 mb-1.5">Alasan Pembatalan (Wajib)</label>
                <textarea 
                    name="alasan_batal" 
                    id="alasan_batal" 
                    rows="2" 
                    required 
                    placeholder="misal: Salah input pesanan / pelanggan membatalkan pembelian"
                    class="w-full px-3.5 py-2.5 bg-white border border-[#EADFD3] rounded-lg text-xs placeholder-stone-400 focus:outline-none focus:ring-1 focus:ring-brand-red"
                ></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-stone-100">
                <button type="button" onclick="closeModalBatal()" class="px-4 py-2.5 rounded-lg border border-[#EADFD3] text-xs font-semibold text-stone-600 hover:bg-stone-50">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-brand-red hover:bg-brand-redHover text-white text-xs font-semibold shadow-sm">
                    Konfirmasi Pembatalan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openModalBatalTransaksi(orderId, kode) {
        document.getElementById('batalKodeText').textContent = kode;
        document.getElementById('formBatal').action = `/owner/transaksi/${orderId}/batal`;
        document.getElementById('modalBatal').classList.remove('hidden');
    }
    function closeModalBatal() {
        document.getElementById('modalBatal').classList.add('hidden');
    }
</script>
@endpush
@endsection

