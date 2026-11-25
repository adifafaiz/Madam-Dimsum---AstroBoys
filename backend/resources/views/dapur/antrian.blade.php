@extends('dapur.layout')

@section('title', 'Antrian Pesanan - Dapur')
@section('page_title', 'Antrian Pesanan')

@section('content')
<div class="max-w-7xl">

    <!-- 3 Kolom Status Pesanan (Kanban View: Frame 1) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">

        <!-- ==================== KOLOM 1: BARU (MERAH) ==================== -->
        <div class="bg-transparent flex flex-col space-y-4">
            <!-- Header Kolom -->
            <div class="px-5 py-3.5 rounded-xl bg-brand-red text-white flex items-center justify-between shadow-sm">
                <span class="font-bold text-sm">Baru ({{ $pesananBaru->count() }})</span>
            </div>

            <!-- Kartu Pesanan Baru -->
            <div class="space-y-4">
                @forelse($pesananBaru as $order)
                    <div class="bg-white rounded-2xl border border-[#EADFD3] p-5 shadow-sm space-y-4">
                        <div class="flex items-center justify-between border-b border-stone-100 pb-3">
                            <span class="font-bold text-sm text-stone-900">{{ $order->kode }}</span>
                            <span class="text-xs text-stone-500 font-medium">
                                {{ $order->waktu ? $order->waktu->format('H:i') : '-' }}
                            </span>
                        </div>

                        <!-- Daftar Item Pesanan -->
                        <div class="space-y-1.5 text-xs text-stone-700">
                            @foreach($order->items as $item)
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-stone-800">{{ $item->product->nama ?? 'Produk' }}</span>
                                    <span class="font-bold text-stone-900">x{{ $item->qty }}</span>
                                </div>
                            @endforeach
                        </div>

                        <!-- Tombol Mulai Proses -->
                        <form action="{{ route('dapur.antrian.update', $order->id) }}" method="POST" class="pt-2">
                            @csrf
                            <input type="hidden" name="status" value="diproses">
                            <button type="submit" 
                                class="w-full py-2.5 rounded-xl bg-brand-red hover:bg-brand-redHover text-white text-xs font-semibold shadow-sm transition">
                                Mulai Proses
                            </button>
                        </form>
                    </div>
                @empty
                    <div class="p-6 text-center text-xs text-stone-400 bg-white/60 rounded-2xl border border-dashed border-[#EADFD3]">
                        Tidak ada pesanan baru.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- ==================== KOLOM 2: DIPROSES (ORANYE) ==================== -->
        <div class="bg-transparent flex flex-col space-y-4">
            <!-- Header Kolom -->
            <div class="px-5 py-3.5 rounded-xl bg-[#D9822B] text-white flex items-center justify-between shadow-sm">
                <span class="font-bold text-sm">Diproses ({{ $pesananDiproses->count() }})</span>
            </div>

            <!-- Kartu Pesanan Diproses -->
            <div class="space-y-4">
                @forelse($pesananDiproses as $order)
                    <div class="bg-white rounded-2xl border border-[#EADFD3] p-5 shadow-sm space-y-4">
                        <div class="flex items-center justify-between border-b border-stone-100 pb-3">
                            <span class="font-bold text-sm text-stone-900">{{ $order->kode }}</span>
                            <span class="text-xs text-stone-500 font-medium">
                                {{ $order->waktu ? $order->waktu->format('H:i') : '-' }}
                            </span>
                        </div>

                        <!-- Daftar Item Pesanan -->
                        <div class="space-y-1.5 text-xs text-stone-700">
                            @foreach($order->items as $item)
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-stone-800">{{ $item->product->nama ?? 'Produk' }}</span>
                                    <span class="font-bold text-stone-900">x{{ $item->qty }}</span>
                                </div>
                            @endforeach
                        </div>

                        <!-- Tombol Selesai Siap -->
                        <form action="{{ route('dapur.antrian.update', $order->id) }}" method="POST" class="pt-2">
                            @csrf
                            <input type="hidden" name="status" value="siap">
                            <button type="submit" 
                                class="w-full py-2.5 rounded-xl bg-[#D9822B] hover:bg-[#c27324] text-white text-xs font-semibold shadow-sm transition">
                                Selesai Siap
                            </button>
                        </form>
                    </div>
                @empty
                    <div class="p-6 text-center text-xs text-stone-400 bg-white/60 rounded-2xl border border-dashed border-[#EADFD3]">
                        Tidak ada pesanan sedang diproses.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- ==================== KOLOM 3: SIAP DIAMBIL (HIJAU) ==================== -->
        <div class="bg-transparent flex flex-col space-y-4">
            <!-- Header Kolom -->
            <div class="px-5 py-3.5 rounded-xl bg-[#2E8B57] text-white flex items-center justify-between shadow-sm">
                <span class="font-bold text-sm">Siap Diambil ({{ $pesananSiap->count() }})</span>
            </div>

            <!-- Kartu Pesanan Siap Diambil -->
            <div class="space-y-4">
                @forelse($pesananSiap as $order)
                    <div class="bg-white rounded-2xl border border-[#EADFD3] p-5 shadow-sm space-y-4">
                        <div class="flex items-center justify-between border-b border-stone-100 pb-3">
                            <span class="font-bold text-sm text-stone-900">{{ $order->kode }}</span>
                            <span class="text-xs text-stone-500 font-medium">
                                {{ $order->waktu ? $order->waktu->format('H:i') : '-' }}
                            </span>
                        </div>

                        <!-- Daftar Item Pesanan -->
                        <div class="space-y-1.5 text-xs text-stone-700">
                            @foreach($order->items as $item)
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-stone-800">{{ $item->product->nama ?? 'Produk' }}</span>
                                    <span class="font-bold text-stone-900">x{{ $item->qty }}</span>
                                </div>
                            @endforeach
                        </div>

                        <!-- Tombol Diambil (Selesai) -->
                        <form action="{{ route('dapur.antrian.update', $order->id) }}" method="POST" class="pt-2">
                            @csrf
                            <input type="hidden" name="status" value="selesai">
                            <button type="submit" 
                                class="w-full py-2.5 rounded-xl bg-[#2E8B57] hover:bg-[#257346] text-white text-xs font-semibold shadow-sm transition">
                                Diambil
                            </button>
                        </form>
                    </div>
                @empty
                    <div class="p-6 text-center text-xs text-stone-400 bg-white/60 rounded-2xl border border-dashed border-[#EADFD3]">
                        Tidak ada pesanan siap diambil.
                    </div>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection

