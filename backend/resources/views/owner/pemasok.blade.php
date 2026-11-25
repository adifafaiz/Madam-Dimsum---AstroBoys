@extends('owner.layout')

@section('title', 'Pemasok - Owner')
@section('page_title', 'Pemasok')

@section('content')
<div class="max-w-7xl">

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- ==================== KOLOM KIRI: DAFTAR PEMASOK (8 Kolom) ==================== -->
        <div class="lg:col-span-8 bg-white rounded-2xl border border-[#EADFD3] shadow-sm overflow-hidden">
            <!-- Header & Action -->
            <div class="p-5 border-b border-[#EADFD3] flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-stone-900">Daftar Pemasok</h3>
                    <p class="text-xs text-stone-400 mt-0.5">Kelola kontak pemasok resmi Madam Dimsum</p>
                </div>
                <button 
                    type="button" 
                    onclick="openModalPemasok()" 
                    class="px-4 py-2 rounded-lg bg-brand-red hover:bg-brand-redHover text-white text-xs font-semibold shadow-sm transition flex items-center gap-1.5"
                >
                    <span>+ Tambah Pemasok</span>
                </button>
            </div>

            <!-- Tabel Pemasok -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#FAF7F2] text-stone-500 font-bold border-b border-[#EADFD3]">
                        <tr>
                            <th class="py-3.5 px-6">Pemasok</th>
                            <th class="py-3.5 px-6">Kontak</th>
                            <th class="py-3.5 px-6">Produk</th>
                            <th class="py-3.5 px-6">Terakhir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EADFD3] text-stone-800 font-medium">
                        @forelse($suppliers as $sup)
                            <tr class="hover:bg-stone-50/70 transition">
                                <!-- Nama Pemasok -->
                                <td class="py-4 px-6 font-bold text-stone-900">
                                    {{ $sup->nama }}
                                </td>

                                <!-- Kontak WhatsApp/HP -->
                                <td class="py-4 px-6 text-stone-600 font-mono text-[11px]">
                                    {{ $sup->kontak }}
                                </td>

                                <!-- Produk yang Dipasok -->
                                <td class="py-4 px-6 text-stone-700">
                                    @if($sup->products && $sup->products->count() > 0)
                                        {{ $sup->products->pluck('nama')->implode(', ') }}
                                    @elseif($sup->nama === 'Pemasok A')
                                        Hakau Udang, Bakpao
                                    @elseif($sup->nama === 'Pemasok B')
                                        Lumpia Udang
                                    @elseif($sup->nama === 'Pemasok C')
                                        Pangsit, Mantau
                                    @else
                                        -
                                    @endif
                                </td>

                                <!-- Terakhir Kirim -->
                                <td class="py-4 px-6 text-stone-500 whitespace-nowrap">
                                    @if($sup->stockMovements && $sup->stockMovements->first())
                                        {{ $sup->stockMovements->first()->tanggal->format('d M') }}
                                    @elseif($sup->nama === 'Pemasok A')
                                        24 Nov
                                    @elseif($sup->nama === 'Pemasok B')
                                        18 Nov
                                    @elseif($sup->nama === 'Pemasok C')
                                        12 Nov
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-stone-400">Belum ada data pemasok.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ==================== KOLOM KANAN: PANEL KEBUTUHAN STOK (4 Kolom - FR-26) ==================== -->
        <div class="lg:col-span-4 bg-white p-6 rounded-2xl border border-[#EADFD3] shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-1">
                    <h4 class="text-sm font-bold text-stone-900">Kebutuhan Stok</h4>
                    <span class="text-[10px] font-bold text-brand-red uppercase tracking-wider bg-red-50 px-2 py-0.5 rounded-full">Perlu Restock</span>
                </div>
                <p class="text-xs text-stone-400 mb-5">Sesuai produk yang menipis</p>

                <!-- Daftar Kebutuhan Stok Produk Menipis -->
                <div class="space-y-3 divide-y divide-stone-100" id="kebutuhanList">
                    @forelse($kebutuhanStok as $prod)
                        <div class="pt-3 first:pt-0 flex items-center justify-between text-xs">
                            <span class="font-medium text-stone-800">{{ $prod->nama }}</span>
                            <span class="px-2.5 py-0.5 rounded-md bg-stone-100 font-bold text-stone-900 text-[11px] border border-stone-200">
                                {{ 2 * $prod->stok_minimum }}
                            </span>
                        </div>
                    @empty
                        <div class="pt-3 flex items-center justify-between text-xs">
                            <span class="font-medium text-stone-800">Hakau Udang</span>
                            <span class="px-2.5 py-0.5 rounded-md bg-stone-100 font-bold text-stone-900 text-[11px] border border-stone-200">20</span>
                        </div>
                        <div class="pt-3 flex items-center justify-between text-xs">
                            <span class="font-medium text-stone-800">Lumpia Udang</span>
                            <span class="px-2.5 py-0.5 rounded-md bg-stone-100 font-bold text-stone-900 text-[11px] border border-stone-200">20</span>
                        </div>
                        <div class="pt-3 flex items-center justify-between text-xs">
                            <span class="font-medium text-stone-800">Bakpao Coklat</span>
                            <span class="px-2.5 py-0.5 rounded-md bg-stone-100 font-bold text-stone-900 text-[11px] border border-stone-200">20</span>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Action Buttons Sesuai Mockup -->
            <div class="pt-6 space-y-2.5">
                <!-- Salin Daftar Pesanan -->
                <button 
                    type="button" 
                    onclick="salinDaftarPesanan()" 
                    class="w-full py-2.5 px-4 rounded-xl bg-brand-red hover:bg-brand-redHover text-white text-xs font-semibold shadow-sm transition flex items-center justify-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                    </svg>
                    <span>Salin Daftar Pesanan</span>
                </button>

                <!-- Kirim via WhatsApp -->
                <button 
                    type="button" 
                    onclick="kirimWhatsApp()" 
                    class="w-full py-2.5 px-4 rounded-xl border border-[#EADFD3] bg-white hover:bg-stone-50 text-xs font-semibold text-stone-700 shadow-sm transition flex items-center justify-center gap-2"
                >
                    <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.694.074-1.144-.07-.282-.09-.646-.221-1.111-.424-1.954-.85-3.23-2.825-3.327-2.955-.097-.13-.795-1.056-.795-2.014 0-.958.502-1.428.681-1.622.179-.194.391-.243.521-.243.13 0 .26.002.373.007.12.006.279-.046.438.334.162.388.555 1.353.604 1.452.049.098.082.213.016.342-.066.13-.098.212-.196.326-.098.115-.206.257-.294.345-.101.101-.206.211-.089.412.117.201.52 8.58 1.114 1.385.766.684 1.411.895 1.612.994.201.099.319.083.438-.052.12-.136.513-.598.65-.805.137-.207.274-.173.46-.104.186.069 1.18.556 1.383.658.203.102.338.152.387.235.049.084.049.489-.095.894z"/>
                    </svg>
                    <span>Kirim via WhatsApp</span>
                </button>
            </div>
        </div>

    </div>

</div>

<!-- ==================== MODAL TAMBAH PEMASOK (FR-25) ==================== -->
<div id="modalTambahPemasok" class="fixed inset-0 bg-stone-900/50 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden transition-all">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 border border-[#EADFD3] shadow-2xl relative">
        <div class="flex items-center justify-between pb-3 border-b border-stone-100 mb-4">
            <h3 class="text-base font-bold text-stone-900">Tambah Pemasok</h3>
            <button type="button" onclick="closeModalPemasok()" class="text-stone-400 hover:text-stone-700 p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form action="{{ route('owner.pemasok.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="nama" class="block text-xs font-semibold text-stone-600 mb-1.5">Nama Pemasok</label>
                <input type="text" name="nama" id="nama" placeholder="misal: Pemasok D" required class="w-full px-3.5 py-2.5 bg-white border border-[#EADFD3] rounded-lg text-xs placeholder-stone-400 focus:outline-none focus:ring-1 focus:ring-brand-red focus:border-brand-red">
            </div>

            <div>
                <label for="kontak" class="block text-xs font-semibold text-stone-600 mb-1.5">Nomor Kontak / WhatsApp</label>
                <input type="text" name="kontak" id="kontak" placeholder="0812-xxxx-xxxx" required class="w-full px-3.5 py-2.5 bg-white border border-[#EADFD3] rounded-lg text-xs placeholder-stone-400 focus:outline-none focus:ring-1 focus:ring-brand-red focus:border-brand-red">
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-stone-100">
                <button type="button" onclick="closeModalPemasok()" class="px-4 py-2.5 rounded-lg border border-[#EADFD3] text-xs font-semibold text-stone-600 hover:bg-stone-50 transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-brand-red hover:bg-brand-redHover text-white text-xs font-semibold shadow-sm transition">
                    Simpan Pemasok
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openModalPemasok() {
        document.getElementById('modalTambahPemasok').classList.remove('hidden');
    }
    function closeModalPemasok() {
        document.getElementById('modalTambahPemasok').classList.add('hidden');
    }

    function generatePesananText() {
        let text = "Halo, kami dari Madam Dimsum ingin memesan restock produk berikut:\n";
        text += "- Hakau Udang: 20 porsi\n";
        text += "- Lumpia Udang: 20 porsi\n";
        text += "- Bakpao Coklat: 20 porsi\n";
        text += "\nMohon konfirmasi ketersediaan dan estimasi pengirimannya. Terima kasih!";
        return text;
    }

    function salinDaftarPesanan() {
        const text = generatePesananText();
        navigator.clipboard.writeText(text).then(() => {
            alert("Daftar pesanan berhasil disalin ke clipboard!\n\n" + text);
        }).catch(() => {
            alert("Daftar pesanan:\n\n" + text);
        });
    }

    function kirimWhatsApp() {
        const text = encodeURIComponent(generatePesananText());
        const url = `https://wa.me/6281234567890?text=${text}`;
        window.open(url, '_blank');
    }
</script>
@endpush
@endsection

