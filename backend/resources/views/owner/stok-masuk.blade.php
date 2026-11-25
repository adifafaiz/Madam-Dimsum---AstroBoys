@extends('owner.layout')

@section('title', 'Catat Stok Masuk - Owner')
@section('page_title', 'Catat Stok Masuk')

@section('content')
<div class="max-w-6xl">

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- ==================== KOLOM KIRI: FORM CATAT STOK MASUK (7 Kolom) ==================== -->
        <div class="lg:col-span-7 bg-white p-6 sm:p-7 rounded-2xl border border-[#EADFD3] shadow-sm">
            <h3 class="text-base font-bold text-stone-900 mb-5">Catat Stok Masuk</h3>

            <form action="{{ route('owner.stok-masuk.store') }}" method="POST" id="stokMasukForm" class="space-y-5">
                @csrf

                <!-- Row 1: Tanggal & Pemasok -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="tanggal" class="block text-xs font-semibold text-stone-600 mb-1.5">Tanggal</label>
                        <input 
                            type="date" 
                            name="tanggal" 
                            id="tanggal" 
                            value="{{ date('Y-m-d') }}" 
                            required
                            class="w-full px-3.5 py-2.5 bg-white border border-[#EADFD3] rounded-lg text-xs font-medium text-stone-800 focus:outline-none focus:ring-1 focus:ring-brand-red focus:border-brand-red cursor-pointer"
                        >
                    </div>
                    <div>
                        <label for="supplier_id" class="block text-xs font-semibold text-stone-600 mb-1.5">Pemasok</label>
                        <select 
                            name="supplier_id" 
                            id="supplier_id" 
                            required
                            class="w-full px-3.5 py-2.5 bg-white border border-[#EADFD3] rounded-lg text-xs font-medium text-stone-800 focus:outline-none focus:ring-1 focus:ring-brand-red focus:border-brand-red cursor-pointer"
                        >
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}">{{ $sup->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Section: Item Produk Masuk -->
                <div>
                    <label class="block text-xs font-semibold text-stone-600 mb-2">Item</label>
                    <div id="itemsContainer" class="space-y-3">
                        <!-- Item Row 1 (Default Hakau Udang) -->
                        <div class="item-row flex items-center gap-3">
                            <select 
                                name="items[0][product_id]" 
                                onchange="updateLivePreview()" 
                                class="product-select flex-1 px-3.5 py-2.5 bg-white border border-[#EADFD3] rounded-lg text-xs font-medium text-stone-800 focus:outline-none focus:ring-1 focus:ring-brand-red focus:border-brand-red cursor-pointer"
                            >
                                @foreach($products as $prod)
                                    <option value="{{ $prod->id }}" data-nama="{{ $prod->nama }}" data-stok="{{ $prod->stok }}" data-min="{{ $prod->stok_minimum }}" {{ $prod->nama === 'Hakau Udang' ? 'selected' : '' }}>
                                        {{ $prod->nama }} (stok: {{ $prod->stok }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="relative w-36">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-brand-red">+</span>
                                <input 
                                    type="number" 
                                    name="items[0][qty]" 
                                    value="20" 
                                    min="1" 
                                    placeholder="20" 
                                    required 
                                    oninput="updateLivePreview()"
                                    class="qty-input w-full pl-6 pr-12 py-2.5 bg-white border border-[#EADFD3] rounded-lg text-xs font-bold text-stone-900 focus:outline-none focus:ring-1 focus:ring-brand-red focus:border-brand-red"
                                >
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[11px] text-stone-400">porsi</span>
                            </div>
                        </div>

                        <!-- Item Row 2 (Default Bakpao Coklat) -->
                        <div class="item-row flex items-center gap-3">
                            <select 
                                name="items[1][product_id]" 
                                onchange="updateLivePreview()" 
                                class="product-select flex-1 px-3.5 py-2.5 bg-white border border-[#EADFD3] rounded-lg text-xs font-medium text-stone-800 focus:outline-none focus:ring-1 focus:ring-brand-red focus:border-brand-red cursor-pointer"
                            >
                                @foreach($products as $prod)
                                    <option value="{{ $prod->id }}" data-nama="{{ $prod->nama }}" data-stok="{{ $prod->stok }}" data-min="{{ $prod->stok_minimum }}" {{ $prod->nama === 'Bakpao Coklat' ? 'selected' : '' }}>
                                        {{ $prod->nama }} (stok: {{ $prod->stok }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="relative w-36">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-brand-red">+</span>
                                <input 
                                    type="number" 
                                    name="items[1][qty]" 
                                    value="24" 
                                    min="1" 
                                    placeholder="24" 
                                    required 
                                    oninput="updateLivePreview()"
                                    class="qty-input w-full pl-6 pr-12 py-2.5 bg-white border border-[#EADFD3] rounded-lg text-xs font-bold text-stone-900 focus:outline-none focus:ring-1 focus:ring-brand-red focus:border-brand-red"
                                >
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[11px] text-stone-400">porsi</span>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol + Tambah Item -->
                    <button 
                        type="button" 
                        onclick="tambahItemRow()" 
                        class="mt-3 px-3.5 py-1.5 rounded-lg border border-dashed border-stone-300 hover:border-brand-red text-xs font-medium text-stone-600 hover:text-brand-red transition flex items-center gap-1.5"
                    >
                        <span>+ Tambah Item</span>
                    </button>
                </div>

                <!-- Catatan -->
                <div>
                    <label for="catatan" class="block text-xs font-semibold text-stone-600 mb-1.5">Catatan</label>
                    <input 
                        type="text" 
                        name="catatan" 
                        id="catatan" 
                        value="Kiriman rutin mingguan" 
                        placeholder="misal: Kiriman rutin mingguan" 
                        class="w-full px-3.5 py-2.5 bg-white border border-[#EADFD3] rounded-lg text-xs placeholder-stone-400 focus:outline-none focus:ring-1 focus:ring-brand-red focus:border-brand-red"
                    >
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-between pt-4 border-t border-stone-100">
                    <a 
                        href="{{ route('owner.produk') }}" 
                        class="px-4 py-2.5 rounded-lg border border-[#EADFD3] text-xs font-semibold text-stone-600 hover:bg-stone-50 transition"
                    >
                        Batal
                    </a>
                    <button 
                        type="submit" 
                        class="px-5 py-2.5 rounded-lg bg-brand-red hover:bg-brand-redHover text-white text-xs font-semibold shadow-sm transition"
                    >
                        Simpan Stok Masuk
                    </button>
                </div>
            </form>
        </div>

        <!-- ==================== KOLOM KANAN: PANEL PRATINJAU STOK (5 Kolom - NFR-02) ==================== -->
        <div class="lg:col-span-5 bg-white p-6 sm:p-7 rounded-2xl border border-[#EADFD3] shadow-sm">
            <h4 class="text-xs font-bold uppercase tracking-wider text-stone-500 mb-4">Stok Setelah Disimpan</h4>

            <!-- Live Calculation List -->
            <div id="previewList" class="space-y-4 mb-6">
                <!-- Diisi otomatis oleh JavaScript -->
            </div>

            <!-- Status Berubah Menjadi Aman Badge -->
            <div id="statusBadgeContainer" class="p-3.5 rounded-xl bg-[#2E8B57]/10 border border-[#2E8B57]/20 flex items-center gap-2.5 text-[#2E8B57] text-xs font-semibold mb-6">
                <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>Status berubah menjadi Aman</span>
            </div>

            <p class="text-[11px] text-stone-400 italic">
                *Tercatat otomatis di riwayat pergerakan stok.
            </p>
        </div>

    </div>

</div>

@push('scripts')
<script>
    let itemIndex = 2;
    const allProducts = @json($products);

    function tambahItemRow() {
        const container = document.getElementById('itemsContainer');
        const row = document.createElement('div');
        row.className = 'item-row flex items-center gap-3 animate-in fade-in duration-150';
        
        let options = allProducts.map(p => `
            <option value="${p.id}" data-nama="${p.nama}" data-stok="${p.stok}" data-min="${p.stok_minimum}">
                ${p.nama} (stok: ${p.stok})
            </option>
        `).join('');

        row.innerHTML = `
            <select name="items[${itemIndex}][product_id]" onchange="updateLivePreview()" class="product-select flex-1 px-3.5 py-2.5 bg-white border border-[#EADFD3] rounded-lg text-xs font-medium text-stone-800 focus:outline-none focus:ring-1 focus:ring-brand-red focus:border-brand-red cursor-pointer">
                ${options}
            </select>
            <div class="relative w-36">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-brand-red">+</span>
                <input type="number" name="items[${itemIndex}][qty]" value="10" min="1" required oninput="updateLivePreview()" class="qty-input w-full pl-6 pr-12 py-2.5 bg-white border border-[#EADFD3] rounded-lg text-xs font-bold text-stone-900 focus:outline-none focus:ring-1 focus:ring-brand-red focus:border-brand-red">
                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[11px] text-stone-400">porsi</span>
            </div>
            <button type="button" onclick="this.closest('.item-row').remove(); updateLivePreview();" class="text-stone-400 hover:text-brand-red p-1 text-sm font-bold">&times;</button>
        `;

        container.appendChild(row);
        itemIndex++;
        updateLivePreview();
    }

    function updateLivePreview() {
        const rows = document.querySelectorAll('.item-row');
        const previewContainer = document.getElementById('previewList');
        previewContainer.innerHTML = '';

        let allSafe = true;

        rows.forEach(r => {
            const select = r.querySelector('.product-select');
            const qtyInput = r.querySelector('.qty-input');
            if (!select || !qtyInput) return;

            const selectedOpt = select.options[select.selectedIndex];
            if (!selectedOpt) return;

            const nama = selectedOpt.getAttribute('data-nama') || selectedOpt.text;
            const currentStok = parseInt(selectedOpt.getAttribute('data-stok') || 0);
            const minStok = parseInt(selectedOpt.getAttribute('data-min') || 10);
            const addedQty = parseInt(qtyInput.value || 0);
            const finalStok = currentStok + addedQty;

            if (finalStok < minStok) {
                allSafe = false;
            }

            const div = document.createElement('div');
            div.className = 'flex items-center justify-between text-xs py-1.5 border-b border-stone-100 last:border-0';
            div.innerHTML = `
                <span class="font-medium text-stone-800">${nama}</span>
                <span class="font-bold text-stone-900">${currentStok} &rarr; <span class="text-brand-red">${finalStok}</span></span>
            `;
            previewContainer.appendChild(div);
        });

        const statusBadge = document.getElementById('statusBadgeContainer');
        if (allSafe && rows.length > 0) {
            statusBadge.className = 'p-3.5 rounded-xl bg-[#2E8B57]/10 border border-[#2E8B57]/20 flex items-center gap-2.5 text-[#2E8B57] text-xs font-semibold mb-6';
            statusBadge.innerHTML = `
                <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>Status berubah menjadi Aman</span>
            `;
        } else {
            statusBadge.className = 'p-3.5 rounded-xl bg-amber-50 border border-amber-200 flex items-center gap-2.5 text-amber-700 text-xs font-semibold mb-6';
            statusBadge.innerHTML = `
                <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <span>Beberapa produk masih di bawah stok minimum</span>
            `;
        }
    }

    document.addEventListener('DOMContentLoaded', updateLivePreview);
</script>
@endpush
@endsection

