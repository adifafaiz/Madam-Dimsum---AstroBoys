@extends('kasir.layout')

@section('title', 'Pencatatan Transaksi - Kasir')
@section('page_title', 'Pencatatan Transaksi')

@push('styles')
<style>
    .chip { height: 40px; padding: 0 16px; border-radius: 9999px; border: 1px solid #EADFD3; background: #fff;
            font-size: 15px; font-weight: 500; color: #2A211C; white-space: nowrap; transition: background-color .15s, border-color .15s, color .15s; }
    .chip:hover { border-color: #D9C8B8; }
    .chip[aria-pressed="true"] { background: #2A211C; border-color: #2A211C; color: #fff; }

    .product-card { transition: border-color .15s, transform .1s; }
    .product-card:not(:disabled):hover { border-color: #C8372D; }
    .product-card:not(:disabled):active { transform: scale(.98); }
    .product-card:focus-visible { outline: 3px solid rgba(200,55,45,.3); outline-offset: 2px; }

    .stepper button { width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; color: #2A211C; }
    .stepper button:hover { background: #FBF7F2; }
    input[type=number]::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    input[type=number] { -moz-appearance: textfield; }
</style>
@endpush

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_400px] 2xl:grid-cols-[minmax(0,1fr)_440px] gap-6 items-start">

    <!-- ==================== KATALOG PRODUK ==================== -->
    <section class="min-w-0 space-y-4" aria-label="Katalog produk">
        <div class="flex flex-col lg:flex-row lg:items-center gap-3">
            <div class="relative lg:w-80 shrink-0">
                <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 text-brand-secondary pointer-events-none"></i>
                <input
                    type="text"
                    id="searchKatalog"
                    placeholder="Cari produk…"
                    oninput="filterKatalog()"
                    class="w-full h-12 pl-11 pr-4 bg-white border border-brand-border rounded text-base text-brand-ink placeholder-[#B9ABA0] focus:outline-none focus:border-brand-red focus:ring-2 focus:ring-brand-red/15"
                >
            </div>

            <div class="flex gap-2 overflow-x-auto pb-1 lg:pb-0" role="group" aria-label="Kategori">
                @foreach($categories as $cat)
                    <button type="button" class="chip" data-kategori="{{ $cat }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}" onclick="pilihKategori(this)">
                        {{ $cat }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-[repeat(auto-fill,minmax(180px,1fr))] gap-4" id="katalogGrid">
            @foreach($products as $p)
                <button
                    type="button"
                    class="product-card text-left bg-white rounded border border-brand-border overflow-hidden flex flex-col disabled:opacity-55 disabled:cursor-not-allowed"
                    data-id="{{ $p->id }}"
                    data-nama="{{ $p->nama }}"
                    data-harga="{{ $p->harga }}"
                    data-stok="{{ $p->stok }}"
                    data-kategori="{{ $p->kategori }}"
                    onclick="addToCart({{ $p->id }}, '{{ addslashes($p->nama) }}', {{ $p->harga }}, {{ $p->stok }})"
                    @disabled($p->stok <= 0)
                >
                    <div class="relative w-full">
                        @include('partials.foto-produk', ['produk' => $p, 'class' => 'w-full aspect-[4/3]'])

                        @if($p->stok <= 0)
                            <span class="absolute left-2 top-2 h-6 px-2 inline-flex items-center rounded text-[13px] font-semibold bg-white text-brand-red">Habis</span>
                        @elseif($p->stok < $p->stok_minimum)
                            <span class="absolute left-2 top-2 h-6 px-2 inline-flex items-center rounded text-[13px] font-semibold bg-white text-[#B4641A]">Sisa {{ $p->stok }}</span>
                        @else
                            <span class="absolute left-2 top-2 h-6 px-2 inline-flex items-center rounded text-[13px] font-medium bg-white/90 text-[#2E7D4F]">Sisa {{ $p->stok }}</span>
                        @endif

                        {{-- Jumlah di keranjang, diisi lewat JS --}}
                        <span class="badge-qty hidden absolute right-2 top-2 min-w-[28px] h-7 px-1.5 rounded-full bg-brand-red text-white text-[15px] font-bold items-center justify-center"></span>
                    </div>

                    <div class="p-3 flex-1 flex flex-col justify-between gap-1.5">
                        <p class="text-base font-semibold text-brand-ink leading-snug line-clamp-2">{{ $p->nama }}</p>
                        <p class="text-base font-bold text-brand-ink">Rp {{ number_format($p->harga, 0, ',', '.') }}</p>
                    </div>
                </button>
            @endforeach
        </div>

        <div id="katalogKosong" class="hidden flex-col items-center justify-center text-center py-16 rounded border-2 border-dashed border-brand-border">
            <i data-lucide="search-x" class="!w-10 !h-10 text-[#C9BBAE]"></i>
            <p class="mt-3 text-lg font-semibold">Produk tidak ditemukan</p>
            <p class="text-brand-secondary">Coba kata kunci lain atau pilih kategori "Semua".</p>
        </div>
    </section>

    <!-- ==================== KERANJANG (STRUK) ==================== -->
    <aside class="xl:sticky xl:top-[96px]" aria-label="Keranjang">
        <form action="{{ route('kasir.transaksi.store') }}" method="POST" id="transaksiForm" class="struk">
            @csrf

            <div class="px-5 pt-5 pb-4 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <h2 class="font-display text-2xl font-semibold">Pesanan Aktif</h2>
                    <span id="cartCountBadge" class="min-w-[28px] h-7 px-2 rounded-full bg-[#F3ECE4] text-brand-ink text-[15px] font-semibold flex items-center justify-center">0</span>
                </div>
                <button type="button" onclick="clearCart()" class="h-9 px-2.5 rounded text-[15px] font-medium text-brand-red hover:bg-[#FCF1EF] flex items-center gap-1.5">
                    <i data-lucide="trash-2" class="!w-4 !h-4"></i> Kosongkan
                </button>
            </div>

            <div class="px-5 border-t-2 border-dashed border-brand-border">
                <div id="cartItemsContainer" class="divide-y divide-dashed divide-brand-border max-h-[38vh] overflow-y-auto -mx-1 px-1">
                    <!-- Item dirender lewat JavaScript -->
                </div>

                <div id="cartKosong" class="hidden flex-col items-center text-center py-10">
                    @include('partials.ikon-kukusan', ['class' => 'w-14 h-14 text-[#D2C2B3]'])
                    <p class="mt-3 text-lg font-semibold">Keranjang masih kosong</p>
                    <p class="text-brand-secondary">Ketuk produk di sebelah kiri untuk menambahkan.</p>
                </div>

                <div id="stokWarningBanner" class="hidden my-3 px-3 py-2.5 rounded bg-[#FBEBDC] text-[#8F4A14] text-[15px] items-center gap-2 font-medium">
                    <i data-lucide="triangle-alert" class="!w-4 !h-4"></i>
                    <span id="stokWarningText">1 produk mendekati batas stok minimum</span>
                </div>
            </div>

            <div class="sobek mt-2 px-5 pt-5 pb-5 space-y-4">
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between text-[15px] text-brand-secondary">
                        <span>Subtotal</span>
                        <span id="subtotalDisplay" class="font-medium text-brand-ink">Rp 0</span>
                    </div>
                    <div class="flex items-baseline justify-between">
                        <span class="text-lg font-semibold">Total</span>
                        <span id="totalDisplay" class="font-display text-3xl font-semibold text-brand-red">Rp 0</span>
                    </div>
                </div>

                <div>
                    <label for="dibayarInput" class="block text-[15px] font-medium mb-2">Uang diterima</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-base font-semibold text-brand-secondary">Rp</span>
                        <input
                            type="number"
                            name="dibayar"
                            id="dibayarInput"
                            placeholder="0"
                            min="0"
                            required
                            inputmode="numeric"
                            oninput="calculateKembalian()"
                            class="w-full h-12 pl-12 pr-4 bg-brand-lightBg border border-brand-border rounded text-lg font-semibold text-brand-ink focus:outline-none focus:bg-white focus:border-brand-red focus:ring-2 focus:ring-brand-red/15"
                        >
                    </div>
                </div>

                <div class="flex items-center justify-between rounded bg-brand-lightBg px-4 h-12">
                    <span class="font-medium">Kembalian</span>
                    <span id="kembalianDisplay" class="text-lg font-bold text-[#2E7D4F]">Rp 0</span>
                </div>

                <div class="grid grid-cols-[1fr_2fr] gap-3 pt-1">
                    <button type="button" onclick="clearCart()" class="h-12 rounded border border-brand-border bg-white text-base font-semibold text-brand-ink hover:bg-brand-lightBg transition-colors">
                        Batal
                    </button>
                    <button type="submit" id="btnSimpanTransaksi" disabled data-loading-text="Menyimpan…"
                        class="h-12 rounded bg-brand-red hover:bg-brand-redHover disabled:bg-[#D9A39E] disabled:cursor-not-allowed text-white text-base font-semibold flex items-center justify-center gap-2 transition-colors">
                        <i data-lucide="check"></i>
                        <span>Simpan Transaksi</span>
                    </button>
                </div>
            </div>
        </form>
    </aside>

</div>

<!-- ==================== MODAL TRANSAKSI BERHASIL ==================== -->
@if(isset($transaksiSukses) && $transaksiSukses)
    <div id="modalSukses" class="fixed inset-0 bg-[#2A211C]/55 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="judulSukses">
        <div class="struk w-full max-w-sm">
            <div class="px-6 pt-7 pb-5 text-center">
                <div class="w-14 h-14 rounded-full bg-[#E6F3EB] text-[#2E7D4F] flex items-center justify-center mx-auto">
                    <i data-lucide="check" class="!w-7 !h-7 !stroke-[2.5]"></i>
                </div>
                <h3 id="judulSukses" class="font-display text-2xl font-semibold mt-4">Transaksi Berhasil</h3>
                <p class="text-[15px] text-brand-secondary mt-1">{{ $transaksiSukses['kode'] }} &middot; {{ $transaksiSukses['waktu'] }}</p>
            </div>

            <div class="sobek polos px-6 pt-5 pb-6 space-y-2.5">
                <div class="flex justify-between text-[15px]">
                    <span class="text-brand-secondary">Total</span>
                    <span class="font-semibold">Rp {{ number_format($transaksiSukses['total'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-[15px]">
                    <span class="text-brand-secondary">Dibayar</span>
                    <span class="font-semibold">Rp {{ number_format($transaksiSukses['dibayar'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-baseline pt-2.5 border-t border-dashed border-brand-border">
                    <span class="font-semibold">Kembalian</span>
                    <span class="font-display text-3xl font-semibold text-brand-red">Rp {{ number_format($transaksiSukses['kembalian'], 0, ',', '.') }}</span>
                </div>

                <p class="pt-2 text-[15px] text-[#2E7D4F] font-medium flex items-center justify-center gap-2">
                    <i data-lucide="chef-hat" class="!w-4 !h-4"></i>
                    Pesanan sudah masuk ke antrian dapur
                </p>

                <div class="grid grid-cols-2 gap-3 pt-3">
                    <a href="{{ route('kasir.struk', $transaksiSukses['id']) }}" target="_blank"
                       class="h-12 rounded border border-brand-border bg-white hover:bg-brand-lightBg text-base font-semibold flex items-center justify-center gap-2 transition-colors">
                        <i data-lucide="printer" class="!w-4 !h-4"></i> Cetak Struk
                    </a>
                    <button type="button" id="btnTransaksiBaru" onclick="document.getElementById('modalSukses').remove()"
                        class="h-12 rounded bg-brand-red hover:bg-brand-redHover text-white text-base font-semibold flex items-center justify-center gap-2 transition-colors">
                        <i data-lucide="plus" class="!w-4 !h-4"></i> Transaksi Baru
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

@push('scripts')
<script>
    // State Keranjang Belanja Kasir
    let cart = [];
    let kategoriAktif = 'Semua';

    document.addEventListener('DOMContentLoaded', () => {
        @env('local')
        // Isian contoh UAT-01 hanya di lingkungan lokal (Siomay Ayam x2, Hakau Udang x1, Bakpao Coklat x3, bayar 100000).
        const productsData = @json($products);
        const siomay = productsData.find(p => p.nama.includes('Siomay'));
        const hakau = productsData.find(p => p.nama.includes('Hakau'));
        const bakpao = productsData.find(p => p.nama.includes('Bakpao'));

        if (siomay) cart.push({ id: siomay.id, nama: siomay.nama, harga: siomay.harga, qty: 2, maxStok: siomay.stok });
        if (hakau) cart.push({ id: hakau.id, nama: hakau.nama, harga: hakau.harga, qty: 1, maxStok: hakau.stok });
        if (bakpao) cart.push({ id: bakpao.id, nama: bakpao.nama, harga: bakpao.harga, qty: 3, maxStok: bakpao.stok });

        document.getElementById('dibayarInput').value = 100000;
        @endenv
        renderCart();

        const btnBaru = document.getElementById('btnTransaksiBaru');
        if (btnBaru) btnBaru.focus();
    });

    // Tutup modal sukses dengan Esc.
    document.addEventListener('keydown', e => {
        const modal = document.getElementById('modalSukses');
        if (e.key === 'Escape' && modal) modal.remove();
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

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
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
            div.className = 'py-3 flex items-center gap-3';
            div.innerHTML = `
                <input type="hidden" name="items[${index}][product_id]" value="${item.id}">
                <input type="hidden" name="items[${index}][qty]" value="${item.qty}">
                <div class="flex-1 min-w-0">
                    <p class="text-base font-semibold truncate">${escapeHtml(item.nama)}</p>
                    <p class="text-[13px] text-brand-secondary">@ Rp ${formatNumber(item.harga)}</p>
                </div>
                <div class="stepper flex items-center rounded border border-brand-border bg-white overflow-hidden shrink-0">
                    <button type="button" onclick="updateQty(${item.id}, -1)" aria-label="Kurangi ${escapeHtml(item.nama)}"><i data-lucide="minus" class="!w-4 !h-4"></i></button>
                    <span class="w-9 text-center text-base font-bold">${item.qty}</span>
                    <button type="button" onclick="updateQty(${item.id}, 1)" aria-label="Tambah ${escapeHtml(item.nama)}"><i data-lucide="plus" class="!w-4 !h-4"></i></button>
                </div>
                <span class="w-[92px] text-right text-base font-semibold shrink-0">Rp ${formatNumber(itemTotal)}</span>
            `;
            container.appendChild(div);
        });

        // Keadaan kosong
        const kosong = document.getElementById('cartKosong');
        kosong.classList.toggle('hidden', cart.length > 0);
        kosong.classList.toggle('flex', cart.length === 0);

        // Jumlah produk di badge & di kartu katalog
        document.getElementById('cartCountBadge').textContent = cart.length;
        document.querySelectorAll('.product-card').forEach(card => {
            const inCart = cart.find(i => i.id === Number(card.dataset.id));
            const badge = card.querySelector('.badge-qty');
            badge.textContent = inCart ? inCart.qty : '';
            badge.classList.toggle('hidden', !inCart);
            badge.classList.toggle('flex', !!inCart);
        });

        // Peringatan stok
        const banner = document.getElementById('stokWarningBanner');
        if (warningCount > 0) {
            banner.classList.remove('hidden');
            banner.classList.add('flex');
            document.getElementById('stokWarningText').textContent = `${warningCount} produk mendekati batas stok minimum`;
        } else {
            banner.classList.add('hidden');
            banner.classList.remove('flex');
        }

        document.getElementById('subtotalDisplay').textContent = `Rp ${formatNumber(subtotal)}`;
        document.getElementById('totalDisplay').textContent = `Rp ${formatNumber(subtotal)}`;

        calculateKembalian();
        window.renderIcons();
    }

    function calculateKembalian() {
        const total = cart.reduce((sum, item) => sum + (item.harga * item.qty), 0);
        const dibayar = parseFloat(document.getElementById('dibayarInput').value) || 0;
        const kembalian = dibayar - total;
        const kembalianDisplay = document.getElementById('kembalianDisplay');
        const btnSimpan = document.getElementById('btnSimpanTransaksi');

        if (cart.length > 0 && dibayar >= total) {
            kembalianDisplay.textContent = `Rp ${formatNumber(kembalian)}`;
            kembalianDisplay.className = 'text-lg font-bold text-[#2E7D4F]';
            btnSimpan.disabled = false;
        } else {
            kembalianDisplay.textContent = dibayar < total ? `Kurang Rp ${formatNumber(total - dibayar)}` : 'Rp 0';
            kembalianDisplay.className = 'text-lg font-bold text-brand-red';
            btnSimpan.disabled = true;
        }
    }

    function pilihKategori(btn) {
        kategoriAktif = btn.dataset.kategori;
        document.querySelectorAll('.chip[data-kategori]').forEach(c => c.setAttribute('aria-pressed', c === btn ? 'true' : 'false'));
        filterKatalog();
    }

    function filterKatalog() {
        const search = document.getElementById('searchKatalog').value.toLowerCase();
        let tampil = 0;

        document.querySelectorAll('.product-card').forEach(card => {
            const nama = card.getAttribute('data-nama').toLowerCase();
            const cat = card.getAttribute('data-kategori');
            const cocok = nama.includes(search) && (kategoriAktif === 'Semua' || cat === kategoriAktif);
            card.style.display = cocok ? '' : 'none';
            if (cocok) tampil++;
        });

        const kosong = document.getElementById('katalogKosong');
        kosong.classList.toggle('hidden', tampil > 0);
        kosong.classList.toggle('flex', tampil === 0);
    }

    function formatNumber(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    }
</script>
@endpush
@endsection
