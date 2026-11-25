@extends('dapur.layout')

@section('title', 'Catat Produksi - Dapur')
@section('page_title', 'Catat Produksi')

@section('content')
<div class="max-w-7xl">

    <!-- Grid 2 Kolom (Sesuai Mockup Frame 2) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        <!-- ==================== KOLOM KIRI: FORM CATAT PRODUKSI ==================== -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-[#EADFD3] p-7 shadow-sm">
            <h3 class="text-base font-bold text-stone-900 mb-6">Catat Produksi</h3>

            <form action="{{ route('dapur.catat-produksi.store') }}" method="POST" id="formProduksi" class="space-y-6">
                @csrf

                <!-- Row 1: Tanggal & Sesi -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="tanggal" class="block text-xs font-semibold text-stone-600 mb-1.5">Tanggal</label>
                        <input 
                            type="date" 
                            name="tanggal" 
                            id="tanggal" 
                            value="{{ date('Y-m-d') }}"
                            required
                            class="w-full px-3.5 py-2.5 bg-stone-50 border border-[#EADFD3] rounded-xl text-xs font-medium text-stone-800 focus:outline-none focus:ring-1 focus:ring-brand-red"
                        >
                    </div>
                    <div>
                        <label for="sesi" class="block text-xs font-semibold text-stone-600 mb-1.5">Sesi</label>
                        <select 
                            name="sesi" 
                            id="sesi" 
                            required
                            class="w-full px-3.5 py-2.5 bg-white border border-[#EADFD3] rounded-xl text-xs font-medium text-stone-800 focus:outline-none focus:ring-1 focus:ring-brand-red cursor-pointer"
                        >
                            <option value="sore" selected>Sore</option>
                            <option value="pagi">Pagi</option>
                        </select>
                    </div>
                </div>

                <!-- Bagian Hasil Produksi (Repeater) -->
                <div>
                    <label class="block text-xs font-semibold text-stone-600 mb-2.5">Hasil produksi</label>
                    <div id="itemsContainer" class="space-y-3">
                        <!-- Default Row 1: Hakau Udang -->
                        <div class="item-row flex items-center justify-between p-3.5 bg-stone-50 rounded-xl border border-[#EADFD3]">
                            <div class="flex-1 mr-3">
                                <select name="items[0][product_id]" class="product-select w-full bg-transparent text-xs font-semibold text-stone-900 focus:outline-none" onchange="updatePreview()">
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}" data-nama="{{ $p->nama }}" data-stok="{{ $p->stok }}" {{ $loop->index == 1 ? 'selected' : '' }}>
                                            {{ $p->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="flex items-center text-brand-red font-bold text-xs bg-red-50 border border-red-100 rounded-lg px-2.5 py-1.5">
                                    <span>+</span>
                                    <input type="number" name="items[0][qty]" value="20" min="1" class="qty-input w-12 bg-transparent text-center text-xs font-bold text-brand-red focus:outline-none" oninput="updatePreview()">
                                    <span>porsi</span>
                                </div>
                                <button type="button" onclick="removeItem(this)" class="text-stone-400 hover:text-brand-red text-xs p-1">&times;</button>
                            </div>
                        </div>

                        <!-- Default Row 2: Lumpia Udang -->
                        <div class="item-row flex items-center justify-between p-3.5 bg-stone-50 rounded-xl border border-[#EADFD3]">
                            <div class="flex-1 mr-3">
                                <select name="items[1][product_id]" class="product-select w-full bg-transparent text-xs font-semibold text-stone-900 focus:outline-none" onchange="updatePreview()">
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}" data-nama="{{ $p->nama }}" data-stok="{{ $p->stok }}" {{ $loop->index == 2 ? 'selected' : '' }}>
                                            {{ $p->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="flex items-center text-brand-red font-bold text-xs bg-red-50 border border-red-100 rounded-lg px-2.5 py-1.5">
                                    <span>+</span>
                                    <input type="number" name="items[1][qty]" value="15" min="1" class="qty-input w-12 bg-transparent text-center text-xs font-bold text-brand-red focus:outline-none" oninput="updatePreview()">
                                    <span>porsi</span>
                                </div>
                                <button type="button" onclick="removeItem(this)" class="text-stone-400 hover:text-brand-red text-xs p-1">&times;</button>
                            </div>
                        </div>

                        <!-- Default Row 3: Bakpao Coklat -->
                        <div class="item-row flex items-center justify-between p-3.5 bg-stone-50 rounded-xl border border-[#EADFD3]">
                            <div class="flex-1 mr-3">
                                <select name="items[2][product_id]" class="product-select w-full bg-transparent text-xs font-semibold text-stone-900 focus:outline-none" onchange="updatePreview()">
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}" data-nama="{{ $p->nama }}" data-stok="{{ $p->stok }}" {{ $loop->index == 3 ? 'selected' : '' }}>
                                            {{ $p->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="flex items-center text-brand-red font-bold text-xs bg-red-50 border border-red-100 rounded-lg px-2.5 py-1.5">
                                    <span>+</span>
                                    <input type="number" name="items[2][qty]" value="20" min="1" class="qty-input w-12 bg-transparent text-center text-xs font-bold text-brand-red focus:outline-none" oninput="updatePreview()">
                                    <span>porsi</span>
                                </div>
                                <button type="button" onclick="removeItem(this)" class="text-stone-400 hover:text-brand-red text-xs p-1">&times;</button>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol + Tambah Produk -->
                    <button type="button" onclick="addItem()" class="mt-3.5 w-full py-2.5 rounded-xl border border-dashed border-[#EADFD3] hover:border-brand-red text-xs font-semibold text-stone-600 hover:text-brand-red transition flex items-center justify-center gap-1.5">
                        <span>+ Tambah Produk</span>
                    </button>
                </div>

                <!-- Catatan -->
                <div>
                    <label for="catatan" class="block text-xs font-semibold text-stone-600 mb-1.5">Catatan</label>
                    <textarea 
                        name="catatan" 
                        id="catatan" 
                        rows="2"
                        placeholder="Contoh: Produksi sesi 2, persiapan permintaan akhir pekan"
                        class="w-full px-3.5 py-2.5 bg-stone-50 border border-[#EADFD3] rounded-xl text-xs text-stone-800 placeholder-stone-400 focus:outline-none focus:ring-1 focus:ring-brand-red"
                    >Produksi sesi 2, persiapan permintaan akhir pekan</textarea>
                </div>

                <!-- Footer Form: Batal & Simpan Produksi -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-stone-100">
                    <a href="{{ route('dapur.stok') }}" class="px-5 py-2.5 rounded-xl border border-[#EADFD3] text-stone-600 hover:bg-stone-50 text-xs font-semibold transition">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-red hover:bg-brand-redHover text-white text-xs font-semibold shadow-sm transition">
                        Simpan Produksi
                    </button>
                </div>
            </form>
        </div>

        <!-- ==================== KOLOM KANAN: PREVIEW STOK SETELAH DISIMPAN ==================== -->
        <div class="lg:col-span-5 bg-white rounded-2xl border border-[#EADFD3] p-7 shadow-sm">
            <h4 class="text-xs font-bold uppercase tracking-wider text-stone-500 mb-5">Stok Setelah Disimpan</h4>

            <div id="previewList" class="space-y-4 text-xs font-medium text-stone-800 divide-y divide-stone-100">
                <!-- Preview items di-render secara dinamis oleh JS -->
            </div>

            <!-- Pesan Status Aman -->
            <div class="mt-6 pt-5 border-t border-stone-100">
                <p class="text-xs font-bold text-[#2E8B57] flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    <span>Stok kembali menjadi Aman</span>
                </p>
                <p class="text-[11px] text-stone-500 mt-1">
                    Semua stok produk di atas sekarang aman dan siap jual.
                </p>
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script>
    let itemIndex = 3;

    function addItem() {
        const container = document.getElementById('itemsContainer');
        const div = document.createElement('div');
        div.className = 'item-row flex items-center justify-between p-3.5 bg-stone-50 rounded-xl border border-[#EADFD3]';
        
        let optionsHtml = '';
        @foreach($products as $p)
            optionsHtml += `<option value="{{ $p->id }}" data-nama="{{ $p->nama }}" data-stok="{{ $p->stok }}">{{ $p->nama }}</option>`;
        @endforeach

        div.innerHTML = `
            <div class="flex-1 mr-3">
                <select name="items[${itemIndex}][product_id]" class="product-select w-full bg-transparent text-xs font-semibold text-stone-900 focus:outline-none" onchange="updatePreview()">
                    ${optionsHtml}
                </select>
            </div>
            <div class="flex items-center gap-2">
                <div class="flex items-center text-brand-red font-bold text-xs bg-red-50 border border-red-100 rounded-lg px-2.5 py-1.5">
                    <span>+</span>
                    <input type="number" name="items[${itemIndex}][qty]" value="10" min="1" class="qty-input w-12 bg-transparent text-center text-xs font-bold text-brand-red focus:outline-none" oninput="updatePreview()">
                    <span>porsi</span>
                </div>
                <button type="button" onclick="removeItem(this)" class="text-stone-400 hover:text-brand-red text-xs p-1">&times;</button>
            </div>
        `;
        container.appendChild(div);
        itemIndex++;
        updatePreview();
    }

    function removeItem(button) {
        const rows = document.querySelectorAll('.item-row');
        if (rows.length > 1) {
            button.closest('.item-row').remove();
            updatePreview();
        } else {
            alert('Minimal 1 item hasil produksi harus dicatat.');
        }
    }

    function updatePreview() {
        const previewList = document.getElementById('previewList');
        previewList.innerHTML = '';

        const rows = document.querySelectorAll('.item-row');
        rows.forEach(row => {
            const select = row.querySelector('.product-select');
            const qtyInput = row.querySelector('.qty-input');
            const selectedOption = select.options[select.selectedIndex];

            if (selectedOption) {
                const nama = selectedOption.getAttribute('data-nama') || selectedOption.text.trim();
                const stokAwal = parseInt(selectedOption.getAttribute('data-stok') || 0);
                const tambahQty = parseInt(qtyInput.value || 0);
                const stokAkhir = stokAwal + tambahQty;

                const itemDiv = document.createElement('div');
                itemDiv.className = 'flex items-center justify-between pt-3 first:pt-0';
                itemDiv.innerHTML = `
                    <span class="text-stone-700">${nama}</span>
                    <span class="font-bold text-stone-900 font-mono">${stokAwal} &rarr; <span class="text-brand-red">${stokAkhir}</span></span>
                `;
                previewList.appendChild(itemDiv);
            }
        });
    }

    document.addEventListener('DOMContentLoaded', updatePreview);
</script>
@endpush
@endsection

