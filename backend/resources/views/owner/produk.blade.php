@extends('owner.layout')

@section('title', 'Produk & Stok - Owner')
@section('page_title', 'Produk & Stok')

@section('content')
<div class="space-y-6 max-w-7xl">

    <!-- ==================== FILTER & ACTION BAR ==================== -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        
        <!-- Search & Filter Form -->
        <form action="{{ route('owner.produk') }}" method="GET" class="flex flex-wrap items-center gap-3 flex-1">
            <!-- Search Input -->
            <div class="relative w-full sm:w-64">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}"
                    placeholder="Cari produk..." 
                    class="w-full pl-9 pr-4 py-2 bg-white border border-[#EADFD3] rounded-lg text-xs placeholder-stone-400 focus:outline-none focus:ring-1 focus:ring-brand-red focus:border-brand-red shadow-sm"
                >
                <svg class="w-4 h-4 text-stone-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            <!-- Kategori Dropdown -->
            <div class="relative">
                <select 
                    name="kategori" 
                    onchange="this.form.submit()"
                    class="appearance-none bg-white border border-[#EADFD3] rounded-lg pl-3.5 pr-8 py-2 text-xs font-medium text-stone-700 focus:outline-none focus:ring-1 focus:ring-brand-red focus:border-brand-red shadow-sm cursor-pointer"
                >
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('kategori', 'Semua') === $cat ? 'selected' : '' }}>
                            {{ $cat === 'Semua' ? 'Semua Kategori' : $cat }}
                        </option>
                    @endforeach
                </select>
                <svg class="w-3.5 h-3.5 text-stone-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </form>

        <!-- Action Buttons Sesuai Mockup -->
        <div class="flex items-center gap-3">
            <!-- Button Catat Stok Masuk -->
            <a 
                href="{{ route('owner.stok-masuk') }}" 
                class="px-3.5 py-2 rounded-lg border border-[#EADFD3] bg-white text-xs font-semibold text-stone-700 hover:bg-stone-50 shadow-sm transition flex items-center gap-1.5"
            >
                <span>+ Catat Stok Masuk</span>
            </a>

            <!-- Button Tambah Produk (Membuka Frame 3) -->
            <button 
                type="button" 
                onclick="openModalTambahProduk()"
                class="px-4 py-2 rounded-lg bg-brand-red hover:bg-brand-redHover text-white text-xs font-semibold shadow-sm transition flex items-center gap-1.5"
            >
                <span>+ Tambah Produk</span>
            </button>
        </div>
    </div>

    <!-- ==================== TABEL PRODUK & STOK (FRAME 2) ==================== -->
    <div class="bg-white rounded-2xl border border-[#EADFD3] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#FAF7F2] text-stone-500 font-bold border-b border-[#EADFD3]">
                    <tr>
                        <th class="py-3.5 px-6">Produk</th>
                        <th class="py-3.5 px-6">Satuan</th>
                        <th class="py-3.5 px-6">Harga</th>
                        <th class="py-3.5 px-6">Stok</th>
                        <th class="py-3.5 px-6">Status</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#EADFD3] text-stone-800 font-medium">
                    @forelse($products as $prod)
                        <tr class="hover:bg-stone-50/70 transition">
                            <!-- Nama & Kategori -->
                            <td class="py-4 px-6">
                                <p class="font-bold text-stone-900">{{ $prod->nama }}</p>
                                <p class="text-[11px] text-stone-400 mt-0.5">{{ $prod->kategori }}</p>
                            </td>

                            <!-- Satuan -->
                            <td class="py-4 px-6 text-stone-600">
                                {{ $prod->satuan }}
                            </td>

                            <!-- Harga -->
                            <td class="py-4 px-6 font-semibold text-stone-900">
                                Rp {{ number_format($prod->harga, 0, ',', '.') }}
                            </td>

                            <!-- Stok -->
                            <td class="py-4 px-6 font-bold {{ $prod->stok < $prod->stok_minimum ? 'text-brand-red' : 'text-stone-900' }}">
                                {{ $prod->stok }}
                            </td>

                            <!-- Status Badge (Aman / Menipis / Habis - BR-03) -->
                            <td class="py-4 px-6">
                                @if($prod->status_stok === 'Aman')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold text-[#2E8B57] bg-[#2E8B57]/10 border border-[#2E8B57]/20">
                                        Aman
                                    </span>
                                @elseif($prod->status_stok === 'Menipis')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold text-brand-red bg-brand-red/10 border border-brand-red/20">
                                        Menipis
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold text-stone-600 bg-stone-200">
                                        Habis
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi (Edit · Riwayat · Hapus) -->
                            <td class="py-4 px-6 text-right whitespace-nowrap">
                                <a href="javascript:void(0)" onclick="editProduk('{{ $prod->id }}', '{{ addslashes($prod->nama) }}', '{{ $prod->satuan }}', '{{ $prod->harga }}', '{{ $prod->stok }}', '{{ $prod->stok_minimum }}', '{{ $prod->kategori }}')" 
                                    class="text-xs font-semibold text-stone-600 hover:text-stone-900 hover:underline">
                                    Edit
                                </a>
                                <span class="text-stone-300 mx-1.5">&middot;</span>
                                <a href="javascript:void(0)" onclick="lihatRiwayat('{{ $prod->id }}', '{{ addslashes($prod->nama) }}')" 
                                    class="text-xs font-semibold text-stone-600 hover:text-stone-900 hover:underline">
                                    Riwayat
                                </a>
                                <span class="text-stone-300 mx-1.5">&middot;</span>
                                <form action="{{ route('owner.produk.delete', $prod->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk \'{{ addslashes($prod->nama) }}\'?\nProduk akan dinonaktifkan dari katalog dan penjualan (BR-11).')">
                                    @csrf
                                    <button type="submit" class="text-xs font-semibold text-brand-red hover:underline">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-stone-400">
                                Tidak ada produk yang sesuai dengan pencarian atau filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Footer Keterangan Tabel -->
        <div class="p-4 border-t border-[#EADFD3] bg-[#FAF7F2]/50 text-[11px] text-stone-400 text-center">
            Menampilkan {{ $products->count() }} dari {{ $products->count() }} produk &middot; Dihitung otomatis dari riwayat stok produk
        </div>
    </div>

</div>

<!-- ==================== MODAL TAMBAH PRODUK (FRAME 3) ==================== -->
<div id="modalTambahProduk" class="fixed inset-0 bg-stone-900/50 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden transition-all">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 sm:p-7 border border-[#EADFD3] shadow-2xl relative animate-in fade-in zoom-in-95 duration-200">
        
        <!-- Header Modal -->
        <div class="flex items-center justify-between pb-4 border-b border-stone-100 mb-5">
            <h3 class="text-base font-bold text-stone-900">Tambah Produk</h3>
            <button type="button" onclick="closeModalTambahProduk()" class="text-stone-400 hover:text-stone-700 p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Form Tambah Produk -->
        <form action="{{ route('owner.produk.store') }}" method="POST" class="space-y-4">
            @csrf

            <!-- 1. Nama Produk -->
            <div>
                <label for="nama" class="block text-xs font-semibold text-stone-600 mb-1.5">Nama produk</label>
                <input 
                    type="text" 
                    name="nama" 
                    id="nama" 
                    placeholder="misal: Siomay Ayam" 
                    required
                    class="w-full px-3.5 py-2.5 bg-white border border-[#EADFD3] rounded-lg text-xs placeholder-stone-400 focus:outline-none focus:ring-1 focus:ring-brand-red focus:border-brand-red"
                >
            </div>

            <!-- 2. Row: Satuan & Harga Jual -->
            <div class="grid grid-cols-2 gap-3.5">
                <div>
                    <label for="satuan" class="block text-xs font-semibold text-stone-600 mb-1.5">Satuan</label>
                    <input 
                        type="text" 
                        name="satuan" 
                        id="satuan" 
                        value="Porsi" 
                        placeholder="Porsi / pcs" 
                        required
                        class="w-full px-3.5 py-2.5 bg-white border border-[#EADFD3] rounded-lg text-xs placeholder-stone-400 focus:outline-none focus:ring-1 focus:ring-brand-red focus:border-brand-red"
                    >
                </div>
                <div>
                    <label for="harga" class="block text-xs font-semibold text-stone-600 mb-1.5">Harga jual (Rp)</label>
                    <input 
                        type="number" 
                        name="harga" 
                        id="harga" 
                        placeholder="15000" 
                        min="0"
                        step="500"
                        required
                        class="w-full px-3.5 py-2.5 bg-white border border-[#EADFD3] rounded-lg text-xs placeholder-stone-400 focus:outline-none focus:ring-1 focus:ring-brand-red focus:border-brand-red"
                    >
                </div>
            </div>

            <!-- 3. Row: Stok Awal & Batas Minimum -->
            <div class="grid grid-cols-2 gap-3.5">
                <div>
                    <label for="stok" class="block text-xs font-semibold text-stone-600 mb-1.5">Stok awal</label>
                    <input 
                        type="number" 
                        name="stok" 
                        id="stok" 
                        value="48" 
                        min="0"
                        required
                        class="w-full px-3.5 py-2.5 bg-white border border-[#EADFD3] rounded-lg text-xs placeholder-stone-400 focus:outline-none focus:ring-1 focus:ring-brand-red focus:border-brand-red"
                    >
                </div>
                <div>
                    <label for="stok_minimum" class="block text-xs font-semibold text-stone-600 mb-1.5">Batas minimum</label>
                    <input 
                        type="number" 
                        name="stok_minimum" 
                        id="stok_minimum" 
                        value="10" 
                        min="0"
                        required
                        class="w-full px-3.5 py-2.5 bg-white border border-[#EADFD3] rounded-lg text-xs placeholder-stone-400 focus:outline-none focus:ring-1 focus:ring-brand-red focus:border-brand-red"
                    >
                </div>
            </div>

            <!-- 4. Kategori Dropdown -->
            <div>
                <label for="kategori" class="block text-xs font-semibold text-stone-600 mb-1.5">Kategori</label>
                <select 
                    name="kategori" 
                    id="kategori" 
                    required
                    class="w-full px-3.5 py-2.5 bg-white border border-[#EADFD3] rounded-lg text-xs font-medium text-stone-800 focus:outline-none focus:ring-1 focus:ring-brand-red focus:border-brand-red cursor-pointer"
                >
                    <option value="Dimsum Kukus">Dimsum Kukus</option>
                    <option value="Dimsum Goreng">Dimsum Goreng</option>
                    <option value="Bakpao">Bakpao</option>
                    <option value="Minuman">Minuman</option>
                </select>
            </div>

            <!-- Helper Note Sesuai Mockup -->
            <p class="text-[11px] text-stone-400 italic pt-1">
                *Stok awal akan dicatat otomatis di riwayat stok produk (BR-02).
            </p>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-stone-100">
                <button 
                    type="button" 
                    onclick="closeModalTambahProduk()" 
                    class="px-4 py-2.5 rounded-lg border border-[#EADFD3] text-xs font-semibold text-stone-600 hover:bg-stone-50 transition"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="px-5 py-2.5 rounded-lg bg-brand-red hover:bg-brand-redHover text-white text-xs font-semibold shadow-sm transition"
                >
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== MODAL EDIT PRODUK & KOREKSI STOK (FR-22) ==================== -->
<div id="modalEditProduk" class="fixed inset-0 bg-stone-900/50 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden transition-all">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 sm:p-7 border border-[#EADFD3] shadow-2xl relative animate-in fade-in zoom-in-95 duration-200">
        
        <!-- Header Modal -->
        <div class="flex items-center justify-between pb-4 border-b border-stone-100 mb-5">
            <h3 class="text-base font-bold text-stone-900">Edit Produk</h3>
            <button type="button" onclick="closeModalEditProduk()" class="text-stone-400 hover:text-stone-700 transition p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Form Edit Produk -->
        <form id="formEditProduk" action="" method="POST" class="space-y-4">
            @csrf

            <!-- 1. Nama Produk -->
            <div>
                <label for="edit_nama" class="block text-xs font-semibold text-stone-600 mb-1.5">Nama produk</label>
                <input 
                    type="text" 
                    name="nama" 
                    id="edit_nama" 
                    required 
                    class="w-full px-3.5 py-2.5 bg-white border border-[#EADFD3] rounded-lg text-xs placeholder-stone-400 focus:outline-none focus:ring-1 focus:ring-brand-red focus:border-brand-red"
                >
            </div>

            <!-- 2. Row: Satuan & Harga -->
            <div class="grid grid-cols-2 gap-3.5">
                <div>
                    <label for="edit_satuan" class="block text-xs font-semibold text-stone-600 mb-1.5">Satuan</label>
                    <input 
                        type="text" 
                        name="satuan" 
                        id="edit_satuan" 
                        required 
                        class="w-full px-3.5 py-2.5 bg-white border border-[#EADFD3] rounded-lg text-xs placeholder-stone-400 focus:outline-none focus:ring-1 focus:ring-brand-red focus:border-brand-red"
                    >
                </div>
                <div>
                    <label for="edit_harga" class="block text-xs font-semibold text-stone-600 mb-1.5">Harga jual</label>
                    <input 
                        type="number" 
                        name="harga" 
                        id="edit_harga" 
                        min="0"
                        required 
                        class="w-full px-3.5 py-2.5 bg-white border border-[#EADFD3] rounded-lg text-xs placeholder-stone-400 focus:outline-none focus:ring-1 focus:ring-brand-red focus:border-brand-red"
                    >
                </div>
            </div>

            <!-- 3. Row: Stok Saat Ini & Batas Minimum -->
            <div class="grid grid-cols-2 gap-3.5">
                <div>
                    <label for="edit_stok" class="block text-xs font-semibold text-stone-600 mb-1.5">Stok saat ini</label>
                    <input 
                        type="number" 
                        name="stok" 
                        id="edit_stok" 
                        min="0"
                        required 
                        class="w-full px-3.5 py-2.5 bg-white border border-[#EADFD3] rounded-lg text-xs placeholder-stone-400 focus:outline-none focus:ring-1 focus:ring-brand-red focus:border-brand-red"
                    >
                </div>
                <div>
                    <label for="edit_stok_minimum" class="block text-xs font-semibold text-stone-600 mb-1.5">Batas minimum</label>
                    <input 
                        type="number" 
                        name="stok_minimum" 
                        id="edit_stok_minimum" 
                        min="0"
                        required 
                        class="w-full px-3.5 py-2.5 bg-white border border-[#EADFD3] rounded-lg text-xs placeholder-stone-400 focus:outline-none focus:ring-1 focus:ring-brand-red focus:border-brand-red"
                    >
                </div>
            </div>

            <!-- 4. Kategori Dropdown -->
            <div>
                <label for="edit_kategori" class="block text-xs font-semibold text-stone-600 mb-1.5">Kategori</label>
                <select 
                    name="kategori" 
                    id="edit_kategori" 
                    required 
                    class="w-full px-3.5 py-2.5 bg-white border border-[#EADFD3] rounded-lg text-xs font-medium text-stone-800 focus:outline-none focus:ring-1 focus:ring-brand-red focus:border-brand-red cursor-pointer"
                >
                    <option value="Dimsum Kukus">Dimsum Kukus</option>
                    <option value="Dimsum Goreng">Dimsum Goreng</option>
                    <option value="Bakpao">Bakpao</option>
                    <option value="Minuman">Minuman</option>
                </select>
            </div>

            <!-- 5. Alasan Koreksi (BR-02 & FR-22) -->
            <div>
                <label for="edit_alasan_koreksi" class="block text-xs font-semibold text-stone-600 mb-1.5">Keterangan / Alasan Koreksi</label>
                <input 
                    type="text" 
                    name="alasan_koreksi" 
                    id="edit_alasan_koreksi" 
                    placeholder="Contoh: Penyesuaian stok fisik mingguan"
                    class="w-full px-3.5 py-2.5 bg-white border border-[#EADFD3] rounded-lg text-xs placeholder-stone-400 focus:outline-none focus:ring-1 focus:ring-brand-red focus:border-brand-red"
                >
            </div>

            <p class="text-[11px] text-stone-400 italic pt-1">
                *Jika stok diubah, sistem akan otomatis mencatat penyesuaian stok di riwayat (BR-02).
            </p>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between pt-4 border-t border-stone-100">
                <button 
                    type="button" 
                    id="btnHapusModal"
                    onclick="hapusProdukDariModal()" 
                    class="px-3.5 py-2.5 rounded-lg bg-red-50 hover:bg-red-100 text-xs font-semibold text-brand-red transition"
                >
                    Hapus Produk
                </button>
                <div class="flex items-center gap-2.5">
                    <button 
                        type="button" 
                        onclick="closeModalEditProduk()" 
                        class="px-4 py-2.5 rounded-lg border border-[#EADFD3] text-xs font-semibold text-stone-600 hover:bg-stone-50 transition"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        class="px-5 py-2.5 rounded-lg bg-brand-red hover:bg-brand-redHover text-white text-xs font-semibold shadow-sm transition"
                    >
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>

        <!-- Hidden Form Delete dari Modal -->
        <form id="formDeleteModal" action="" method="POST" class="hidden">
            @csrf
        </form>
    </div>
</div>

<!-- ==================== MODAL RIWAYAT STOK (FR-23) ==================== -->
<div id="modalRiwayatStok" class="fixed inset-0 bg-stone-900/50 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden transition-all">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 border border-[#EADFD3] shadow-2xl relative">
        <div class="flex items-center justify-between pb-3 border-b border-stone-100 mb-4">
            <div>
                <h3 class="text-sm font-bold text-stone-900">Riwayat Pergerakan Stok</h3>
                <p id="riwayatProdukNama" class="text-xs text-brand-red font-semibold mt-0.5"></p>
            </div>
            <button type="button" onclick="closeModalRiwayat()" class="text-stone-400 hover:text-stone-700 p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div id="riwayatContent" class="max-h-72 overflow-y-auto space-y-2 text-xs divide-y divide-stone-100">
            <!-- Loading placeholder -->
            <p class="text-center py-6 text-stone-400">Memuat riwayat stok...</p>
        </div>

        <div class="pt-4 border-t border-stone-100 text-right">
            <button type="button" onclick="closeModalRiwayat()" class="px-4 py-2 rounded-lg bg-stone-100 text-xs font-semibold text-stone-700 hover:bg-stone-200">
                Tutup
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openModalTambahProduk() {
        document.getElementById('modalTambahProduk').classList.remove('hidden');
    }

    function closeModalTambahProduk() {
        document.getElementById('modalTambahProduk').classList.add('hidden');
    }

    function closeModalEditProduk() {
        document.getElementById('modalEditProduk').classList.add('hidden');
    }

    let currentEditId = null;
    let currentEditNama = '';

    function editProduk(id, nama, satuan, harga, stok, stokMin, kategori) {
        currentEditId = id;
        currentEditNama = nama;
        const form = document.getElementById('formEditProduk');
        form.action = `/owner/produk/${id}`;

        document.getElementById('edit_nama').value = nama;
        document.getElementById('edit_satuan').value = satuan;
        document.getElementById('edit_harga').value = parseInt(harga);
        document.getElementById('edit_stok').value = parseInt(stok);
        document.getElementById('edit_stok_minimum').value = parseInt(stokMin);
        document.getElementById('edit_kategori').value = kategori;
        document.getElementById('edit_alasan_koreksi').value = '';

        document.getElementById('modalEditProduk').classList.remove('hidden');
    }

    function hapusProdukDariModal() {
        if (!currentEditId) return;
        if (confirm(`Apakah Anda yakin ingin menghapus produk '${currentEditNama}'?\nProduk akan dinonaktifkan dari katalog dan penjualan (BR-11).`)) {
            const form = document.getElementById('formDeleteModal');
            form.action = `/owner/produk/${currentEditId}/delete`;
            form.submit();
        }
    }

    async function lihatRiwayat(productId, nama) {
        document.getElementById('riwayatProdukNama').textContent = nama;
        document.getElementById('modalRiwayatStok').classList.remove('hidden');
        const container = document.getElementById('riwayatContent');
        container.innerHTML = '<p class="text-center py-6 text-stone-400">Memuat riwayat stok...</p>';

        try {
            const res = await fetch(`/owner/produk/${productId}/riwayat`);
            const data = await res.json();

            if (data.success && data.movements.length > 0) {
                container.innerHTML = data.movements.map(m => `
                    <div class="pt-2.5 flex items-center justify-between text-xs">
                        <div>
                            <span class="font-bold text-stone-800 capitalize">${m.jenis.replace('_', ' ')}</span>
                            <span class="text-[11px] text-stone-400 ml-1.5">${new Date(m.tanggal).toLocaleDateString('id-ID', {day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit'})}</span>
                            <p class="text-[11px] text-stone-500 mt-0.5">${m.keterangan || '-'}</p>
                        </div>
                        <div class="text-right">
                            <span class="font-bold ${m.qty >= 0 ? 'text-emerald-600' : 'text-brand-red'}">${m.qty >= 0 ? '+' : ''}${m.qty}</span>
                            <p class="text-[10px] text-stone-400">Saldo: ${m.saldo_setelah}</p>
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<p class="text-center py-6 text-stone-400">Belum ada riwayat pergerakan stok.</p>';
            }
        } catch (e) {
            container.innerHTML = '<p class="text-center py-6 text-red-500">Gagal memuat data riwayat.</p>';
        }
    }
</script>
@endpush
@endsection

