@extends('dapur.layout')

@section('title', 'Riwayat Produksi - Dapur')
@section('page_title', 'Riwayat Produksi')

@section('content')
<div class="space-y-6 max-w-7xl">

    <!-- Bar Filter Atas Tabel (Mockup Frame 4) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <!-- Filter Tanggal -->
        <form action="{{ route('dapur.riwayat-produksi') }}" method="GET" class="flex items-center gap-2">
            <input 
                type="date" 
                name="tanggal" 
                value="{{ request('tanggal', date('Y-m-d')) }}"
                onchange="this.form.submit()"
                class="px-3.5 py-2 bg-white border border-[#EADFD3] rounded-xl text-xs font-medium text-stone-800 focus:outline-none focus:ring-1 focus:ring-brand-red shadow-sm cursor-pointer"
            >
            @if(request('tanggal'))
                <a href="{{ route('dapur.riwayat-produksi') }}" class="text-xs text-brand-red hover:underline font-semibold">
                    Reset
                </a>
            @endif
        </form>

        <!-- Ringkasan Total Produksi -->
        <div class="text-xs font-bold text-stone-700">
            Total produksi: <span class="text-brand-red">{{ $totalPorsi }} porsi</span>
        </div>
    </div>

    <!-- Tabel Riwayat Produksi (Frame 4) -->
    <div class="bg-white rounded-2xl border border-[#EADFD3] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#FAF7F2] text-stone-500 font-bold border-b border-[#EADFD3]">
                    <tr>
                        <th class="py-3.5 px-6">Tanggal</th>
                        <th class="py-3.5 px-6">Produk</th>
                        <th class="py-3.5 px-6">Jumlah</th>
                        <th class="py-3.5 px-6">Sesi</th>
                        <th class="py-3.5 px-6">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#EADFD3] text-stone-800 font-medium">
                    @forelse($riwayat as $item)
                        <tr class="hover:bg-stone-50/70 transition">
                            <!-- Tanggal & Waktu -->
                            <td class="py-4 px-6 text-stone-600 font-medium">
                                {{ $item->tanggal ? $item->tanggal->format('d M H:i') : '-' }}
                            </td>

                            <!-- Nama Produk -->
                            <td class="py-4 px-6 font-bold text-stone-900">
                                {{ $item->product->nama ?? '-' }}
                            </td>

                            <!-- Jumlah Produksi -->
                            <td class="py-4 px-6 font-bold text-brand-red">
                                +{{ $item->qty }} porsi
                            </td>

                            <!-- Sesi -->
                            <td class="py-4 px-6 capitalize">
                                {{ $item->sesi ? ucfirst($item->sesi) : 'Sore' }}
                            </td>

                            <!-- Catatan -->
                            <td class="py-4 px-6 text-stone-500">
                                {{ $item->keterangan ?: '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-stone-400">
                                Belum ada riwayat produksi pada tanggal ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

