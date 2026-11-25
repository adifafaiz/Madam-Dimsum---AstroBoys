@extends('kasir.layout')

@section('title', 'Pencatatan Transaksi - Kasir')
@section('page_title', 'Pencatatan Transaksi')

@section('content')
<div class="max-w-7xl">

    <!-- Grid 2 Kolom: Katalog Produk (Kiri) & Keranjang Pesanan (Kanan) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        <!-- ==================== SISI KIRI: KATALOG PRODUK (LG: 7 COLS) ==================== -->
        <div class="lg:col-span-7 space-y-5">
            <!-- Search & Filter Kategori -->
            <div class="flex flex-col sm:flex-row items-center gap-3">
                <div class="relative flex-1 w-full">
                    <input 
                        type="text" 
                        id="searchKatalog"
                        placeholder="Cari produk..." 
                        oninput="filterKatalog()"
                        class="w-full pl-9 pr-4 py-2.5 bg-white border border-[#EADFD3] rounded-xl text-xs text-stone-800 placeholder-stone-400 focus:outline-none focus:ring-1 focus:ring-brand-red shadow-sm"
                    >
                    <svg class="w-4 h-4 text-stone-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                <select id="kategoriKatalog" onchange="filterKatalog()" class="w-full sm:w-auto px-4 py-2.5 bg-white border border-[#EADFD3] rounded-xl text-xs font-semibold text-stone-800 shadow-sm cursor-pointer focus:outline-none focus:ring-1 focus:ring-brand-red">
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Grid Kartu Produk (Frame 1 Mockup) -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4" id="katalogGrid">
                @foreach($products as $p)
                    <div 
                        class="product-card bg-white rounded-2xl border border-[#EADFD3] p-3.5 shadow-sm hover:border-brand-red cursor-pointer transition flex flex-col justify-between group {{ $p->stok <= 0 ? 'opacity-60 pointer-events-none' : '' }}"
                        data-id="{{ $p->id }}"
                        data-nama="{{ $p->nama }}"
                        data-harga="{{ $p->harga }}"
                        data-stok="{{ $p->stok }}"
                        data-kategori="{{ $p->kategori }}"
                        onclick="addToCart({{ $p->id }}, '{{ addslashes($p->nama) }}', {{ $p->harga }}, {{ $p->stok }})"
                    >
                        <!-- Kotak Foto Placeholder Produk -->
                        <div class="w-full h-24 bg-[#FAF7F2] border border-[#EADFD3]/60 rounded-xl flex items-center justify-center mb-3 text-stone-300 group-hover:bg-red-50/50 transition">
                            <span class="text-xs font-bold text-stone-400 uppercase tracking-wider font-mono">Foto</span>
                        </div>

                        <!-- Info Produk -->
                        <div>
                            <h4 class="text-xs font-bold text-stone-900 group-hover:text-brand-red transition truncate">{{ $p->nama }}</h4>
                            <div class="flex items-center justify-between mt-1.5 pt-1 border-t border-stone-100">
                                <span class="text-xs font-bold text-stone-800">Rp {{ number_format($p->harga, 0, ',', '.') }}</span>
                                @if($p->stok <= 0)
                                    <span class="text-[10px] font-semibold text-brand-red bg-red-50 px-1.5 py-0.5 rounded">Habis</span>
                                @elseif($p->stok < $p->stok_minimum)
                                    <span class="text-[10px] font-semibold text-[#D9822B] bg-orange-50 px-1.5 py-0.5 rounded">Sisa {{ $p->stok }}</span>
                                @else
                                    <span class="text-[10px] font-semibold text-[#2E8B57] bg-green-50 px-1.5 py-0.5 rounded">Sisa {{ $p->stok }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- ==================== SISI KANAN: KERANJANG PESANAN (LG: 5 COLS) ==================== -->
        <div class="lg:col-span-5 bg-white rounded-2xl border border-[#EADFD3] p-6 shadow-sm flex flex-col justify-between min-h-[580px]">
            <div>
                <!-- Header Keranjang -->
                <div class="flex items-center justify-between border-b border-stone-100 pb-4 mb-4">
                    <h3 class="text-sm font-bold text-stone-900 flex items-center gap-2">
                        <span>Pesanan Aktif</span>
                        <span id="cartCountBadge" class="text-xs font-semibold px-2 py-0.5 rounded-full bg-stone-100 text-stone-600">(0)</span>
                    </h3>
                    <button type="button" onclick="clearCart()" class="text-xs font-semibold text-brand-red hover:underline">
                        Kosongkan
                    </button>
                </div>

                <!-- Form Transaksi -->
                <form action="{{ route('kasir.transaksi.store') }}" method="POST" id="transaksiForm">
                    @csrf
                    <div id="cartItemsContainer" class="space-y-3 max-h-64 overflow-y-auto pr-1">
                        <!-- Items rendered via JavaScript -->
                    </div>

                    <!-- Warning Stok Menipis Banner (Frame 1) -->
                    <div id="stokWarningBanner" class="hidden mt-4 p-3 rounded-xl bg-red-50 border border-red-100 text-brand-red text-xs flex items-center gap-2 font-medium">
                        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        <span id="stokWarningText">1 produk mendekati batas stok minimum</span>
                    </div>

                    <!-- Total & Pembayaran -->
                    <div class="border-t border-stone-100 mt-6 pt-4 space-y-3">
                        <div class="flex items-center justify-between text-xs text-stone-600">
                            <span>Subtotal</span>
                            <span id="subtotalDisplay" class="font-bold text-stone-900">Rp 0</span>
                        </div>

                        <div class="flex items-center justify-between text-sm">
                            <span class="font-bold text-stone-900">Total</span>
                            <span id="totalDisplay" class="text-lg font-extrabold text-brand-red">Rp 0</span>
                        </div>

                        <!-- Input Uang Diterima -->
                        <div class="pt-2">
                            <label for="dibayarInput" class="block text-xs font-semibold text-stone-600 mb-1.5">Uang Diterima</label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-stone-400">Rp</span>
                                <input 
                                    type="number" 
                                    name="dibayar" 
                                    id="dibayarInput" 
                                    placeholder="0"
                                    min="0"
                                    required
                                    oninput="calculateKembalian()"
                                    class="w-full pl-10 pr-4 py-2.5 bg-stone-50 border border-[#EADFD3] rounded-xl text-xs font-bold text-stone-900 focus:outline-none focus:ring-1 focus:ring-brand-red"
                                >
                            </div>
                        </div>

                        <!-- Baris Kembalian Real-time (FR-13) -->
                        <div class="flex items-center justify-between text-xs pt-1">
                            <span class="font-medium text-stone-600">Kembalian:</span>
                            <span id="kembalianDisplay" class="font-bold text-[#2E8B57]">Rp 0</span>
                        </div>
                    </div>

                    <!-- Tombol Aksi Bawah -->
                    <div class="flex items-center gap-3 pt-6">
                        <button type="button" onclick="clearCart()" class="w-1/3 py-2.5 rounded-xl border border-[#EADFD3] text-stone-600 hover:bg-stone-50 text-xs font-semibold transition">
                            Batal
                        </button>
                        <button type="submit" id="btnSimpanTransaksi" disabled class="w-2/3 py-2.5 rounded-xl bg-brand-red hover:bg-brand-redHover disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-semibold shadow-sm transition">
                            Simpan Transaksi
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

</div>

<!-- ==================== MODAL TRANSAKSI BERHASIL (FRAME 2 MOCKUP) ==================== -->
@if(isset($transaksiSukses) && $transaksiSukses)
    <div id="modalSukses" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-sm w-full p-8 text-center shadow-2xl border border-[#EADFD3] space-y-5">
            <!-- Ikon Centang Hijau -->
            <div class="w-16 h-16 rounded-full bg-emerald-50 border border-emerald-200 text-[#2E8B57] flex items-center justify-center mx-auto">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <!-- Header Transaksi Berhasil -->
            <div>
                <h3 class="text-lg font-bold text-stone-900">Transaksi Berhasil</h3>
                <p class="text-xs text-stone-400 mt-1 font-mono">{{ $transaksiSukses['kode'] }} &middot; {{ $transaksiSukses['waktu'] }}</p>
            </div>

            <!-- Rincian Pembayaran -->
            <div class="bg-stone-50 rounded-2xl p-4 text-xs space-y-2 border border-stone-100">
                <div class="flex justify-between text-stone-600">
                    <span>Total</span>
                    <span class="font-bold text-stone-900">Rp {{ number_format($transaksiSukses['total'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-stone-600">
                    <span>Dibayar</span>
                    <span class="font-bold text-stone-900">Rp {{ number_format($transaksiSukses['dibayar'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-stone-800 border-t border-stone-200 pt-2 font-bold">
                    <span>Kembalian</span>
                    <span class="text-brand-red text-sm">Rp {{ number_format($transaksiSukses['kembalian'], 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Info Dapur -->
            <div class="text-[11px] text-[#2E8B57] font-semibold flex items-center justify-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                <span>Pesanan telah dikirimkan ke Antrian Dapur</span>
            </div>

            <!-- 2 Tombol Modal: Cetak Struk & Transaksi Baru -->
            <div class="flex items-center gap-3 pt-2">
                <a href="{{ route('kasir.struk', $transaksiSukses['id']) }}" target="_blank" class="w-1/2 py-2.5 rounded-xl border border-[#EADFD3] text-stone-700 hover:bg-stone-50 text-xs font-semibold transition">
                    Cetak Struk
                </a>
                <button type="button" onclick="document.getElementById('modalSukses').remove()" class="w-1/2 py-2.5 rounded-xl bg-brand-red hover:bg-brand-redHover text-white text-xs font-semibold shadow-sm transition">
                    Transaksi Baru
                </button>
            </div>
        </div>
    </div>
@endif

@push('scripts')
<script>
    // State Keranjang Belanja Kasir
    let cart = [];

    // Data awal produk dari seeder jika keranjang default di-load (Frame 1 mockup default: Siomay Ayam x2, Hakau Udang x1, Bakpao Coklat x3)
    document.addEventListener('DOMContentLoaded', () => {
        // Cek jika keranjang kosong saat baru buka, isi contoh sesuai mockup
        const productsData = @json($products);
        const siomay = productsData.find(p => p.nama.includes('Siomay'));
        const hakau = productsData.find(p => p.nama.includes('Hakau'));
        const bakpao = productsData.find(p => p.nama.includes('Bakpao'));

        if (siomay) cart.push({ id: siomay.id, nama: siomay.nama, harga: siomay.harga, qty: 2, maxStok: siomay.stok });
        if (hakau) cart.push({ id: hakau.id, nama: hakau.nama, harga: hakau.harga, qty: 1, maxStok: hakau.stok });
        if (bakpao) cart.push({ id: bakpao.id, nama: bakpao.nama, harga: bakpao.harga, qty: 3, maxStok: bakpao.stok });

        document.getElementById('dibayarInput').value = 100000;
        renderCart();
    });

    function addToCart(id, nama, harga, maxStok) {
        const existing = cart.find(item => item.id === id);
        if (existing) {
            if (existing.qty < maxStok) {
                existing.qty++;
            } else {
                alert(`Stok produk '${nama}' hanya tersisa ${maxStok}.`);
                return;
            }
        } else {
            cart.push({ id, nama, harga, qty: 1, maxStok });
        }
        renderCart();
    }

    function updateQty(id, delta) {
        const item = cart.find(i => i.id === id);
        if (!item) return;

        item.qty += delta;
        if (item.qty <= 0) {
            cart = cart.filter(i => i.id !== id);
        } else if (item.qty > item.maxStok) {
            item.qty = item.maxStok;
            alert(`Maksimal stok tercapai (${item.maxStok}).`);
        }
        renderCart();
    }

    function clearCart() {
        cart = [];
        document.getElementById('dibayarInput').value = '';
        renderCart();
    }

    function renderCart() {
        const container = document.getElementById('cartItemsContainer');
        container.innerHTML = '';

        let subtotal = 0;
        let warningCount = 0;

        cart.forEach((item, index) => {
            const itemTotal = item.harga * item.qty;
            subtotal += itemTotal;

            if (item.maxStok - item.qty <= 5) {
                warningCount++;
            }

            const div = document.createElement('div');
            div.className = 'flex items-center justify-between p-3 bg-stone-50 rounded-xl border border-[#EADFD3]';
            div.innerHTML = `
                <input type="hidden" name="items[${index}][product_id]" value="${item.id}">
                <input type="hidden" name="items[${index}][qty]" value="${item.qty}">
                <div class="flex-1 mr-2 truncate">
                    <p class="text-xs font-bold text-stone-900 truncate">${item.nama}</p>
                    <p class="text-[10px] text-stone-400">@ Rp ${formatNumber(item.harga)}</p>
                </div>
                <div class="flex items-center gap-2">
                    <div class="flex items-center border border-stone-200 rounded-lg bg-white overflow-hidden">
                        <button type="button" onclick="updateQty(${item.id}, -1)" class="px-2 py-0.5 text-xs font-bold text-stone-600 hover:bg-stone-100">&minus;</button>
                        <span class="px-2 text-xs font-bold text-stone-900">${item.qty}</span>
                        <button type="button" onclick="updateQty(${item.id}, 1)" class="px-2 py-0.5 text-xs font-bold text-stone-600 hover:bg-stone-100">&plus;</button>
                    </div>
                    <span class="text-xs font-bold text-stone-900 min-w-[70px] text-right font-mono">Rp ${formatNumber(itemTotal)}</span>
                </div>
            `;
            container.appendChild(div);
        });

        // Update badge count
        document.getElementById('cartCountBadge').textContent = `(${cart.length})`;

        // Warning banner
        const banner = document.getElementById('stokWarningBanner');
        if (warningCount > 0) {
            banner.classList.remove('hidden');
            document.getElementById('stokWarningText').textContent = `${warningCount} produk mendekati batas stok minimum`;
        } else {
            banner.classList.add('hidden');
        }

        // Update Displays
        document.getElementById('subtotalDisplay').textContent = `Rp ${formatNumber(subtotal)}`;
        document.getElementById('totalDisplay').textContent = `Rp ${formatNumber(subtotal)}`;

        calculateKembalian();
    }

    function calculateKembalian() {
        const total = cart.reduce((sum, item) => sum + (item.harga * item.qty), 0);
        const dibayar = parseFloat(document.getElementById('dibayarInput').value) || 0;
        const kembalian = dibayar - total;
        const kembalianDisplay = document.getElementById('kembalianDisplay');
        const btnSimpan = document.getElementById('btnSimpanTransaksi');

        if (cart.length > 0 && dibayar >= total) {
            kembalianDisplay.textContent = `Rp ${formatNumber(kembalian)}`;
            kembalianDisplay.className = 'font-bold text-[#2E8B57]';
            btnSimpan.disabled = false;
        } else {
            kembalianDisplay.textContent = dibayar < total ? `Kurang Rp ${formatNumber(total - dibayar)}` : 'Rp 0';
            kembalianDisplay.className = 'font-bold text-brand-red';
            btnSimpan.disabled = true;
        }
    }

    function filterKatalog() {
        const search = document.getElementById('searchKatalog').value.toLowerCase();
        const kategori = document.getElementById('kategoriKatalog').value;
        const cards = document.querySelectorAll('.product-card');

        cards.forEach(card => {
            const nama = card.getAttribute('data-nama').toLowerCase();
            const cat = card.getAttribute('data-kategori');
            const matchesSearch = nama.includes(search);
            const matchesCat = kategori === 'Semua' || cat === kategori;

            if (matchesSearch && matchesCat) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function formatNumber(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    }
</script>
@endpush
@endsection

