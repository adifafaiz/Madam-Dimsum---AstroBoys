@extends('owner.layout')

@section('title', 'Catat Stok Masuk - Owner')
@section('page_title', 'Catat Stok Masuk')

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_420px] gap-6 items-start">

    <!-- ==================== FORM STOK MASUK ==================== -->
    <section class="kartu" aria-labelledby="judulForm">
        <div class="px-6 pt-6 pb-2 flex items-center gap-3">
            <a href="{{ route('owner.produk') }}" class="btn btn-ghost btn-sm !px-2" aria-label="Kembali ke Produk & Stok"><i data-lucide="arrow-left"></i></a>
            <h3 id="judulForm" class="kartu-judul">Catat Stok Masuk</h3>
        </div>

        <form action="{{ route('owner.stok-masuk.store') }}" method="POST" id="stokMasukForm">
            @csrf
            <div class="px-6 py-4 space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="tanggal" class="label">Tanggal</label>
                        <input type="date" name="tanggal" id="tanggal" value="{{ date('Y-m-d') }}" required class="field">
                    </div>
                    <div>
                        <label for="supplier_id" class="label">Pemasok</label>
                        <select name="supplier_id" id="supplier_id" required class="field">
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}">{{ $sup->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <div class="flex items-baseline justify-between mb-2">
                        <span class="label !mb-0">Item</span>
                        <span class="text-[13px] text-brand-secondary">Jumlah yang diterima</span>
                    </div>
                    <div id="itemsContainer" class="space-y-3">
                        @foreach([['Hakau Udang', 20], ['Bakpao Coklat', 24]] as $i => [$default, $qtyDefault])
                            <div class="item-row flex items-center gap-3">
                                <select name="items[{{ $i }}][product_id]" onchange="updateLivePreview()" class="product-select field flex-1 min-w-0" aria-label="Produk">
                                    @foreach($products as $prod)
                                        <option value="{{ $prod->id }}" data-nama="{{ $prod->nama }}" data-stok="{{ $prod->stok }}" data-min="{{ $prod->stok_minimum }}" {{ $prod->nama === $default ? 'selected' : '' }}>
                                            {{ $prod->nama }} (stok: {{ $prod->stok }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="relative w-40 shrink-0">
                                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 font-bold text-[#2E7D4F]">+</span>
                                    <input type="number" name="items[{{ $i }}][qty]" value="{{ $qtyDefault }}" min="1" placeholder="{{ $qtyDefault }}" required oninput="updateLivePreview()" aria-label="Jumlah"
                                        class="qty-input field !pl-8 !pr-14 font-semibold">
                                    <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-[13px] text-brand-secondary">porsi</span>
                                </div>
                                <span class="w-11 shrink-0"></span>
                            </div>
                        @endforeach
                    </div>

                    <button type="button" onclick="tambahItemRow()" class="btn w-full mt-3 border-2 border-dashed !border-brand-border text-brand-secondary hover:!border-brand-red hover:text-brand-red">
                        <i data-lucide="plus" class="!w-4 !h-4"></i> Tambah Item
                    </button>
                </div>

                <div>
                    <label for="catatan" class="label">Catatan</label>
                    <input type="text" name="catatan" id="catatan" value="Kiriman rutin mingguan" placeholder="misal: Kiriman rutin mingguan" class="field">
                </div>
            </div>

            <div class="modal-kaki !justify-between rounded-b">
                <a href="{{ route('owner.produk') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary btn-lg" data-loading-text="Menyimpan…">
                    <i data-lucide="package-check"></i> Simpan Stok Masuk
                </button>
            </div>
        </form>
    </section>

    <!-- ==================== PRATINJAU ==================== -->
    <aside class="xl:sticky xl:top-[96px] struk" aria-labelledby="judulPratinjau">
        <div class="px-6 pt-6 pb-4">
            <p class="text-[13px] uppercase tracking-wide text-brand-secondary">Pratinjau</p>
            <h3 id="judulPratinjau" class="kartu-judul mt-0.5">Stok Setelah Disimpan</h3>
        </div>

        <div id="previewList" class="px-6 border-t-2 border-dashed border-brand-border divide-y divide-dashed divide-brand-border">
            <!-- Diisi otomatis oleh JavaScript -->
        </div>

        <div class="sobek px-6 pt-5 pb-6 space-y-3">
            <div id="statusBadgeContainer" class="px-3.5 py-3 rounded bg-[#E6F3EB] text-[#2E7D4F] flex items-center gap-2.5 font-medium">
                <i data-lucide="circle-check" class="!w-4 !h-4"></i>
                <span>Status berubah menjadi Aman</span>
            </div>
            <p class="bantuan flex items-center gap-2 !mt-0">
                <i data-lucide="info" class="!w-4 !h-4"></i> Tercatat otomatis di riwayat pergerakan stok.
            </p>
        </div>
    </aside>

</div>

@push('scripts')
<script>
    let itemIndex = 2;
    const allProducts = @json($products);

    function escapeHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }

    function tambahItemRow() {
        const container = document.getElementById('itemsContainer');
        const row = document.createElement('div');
        row.className = 'item-row flex items-center gap-3';

        const options = allProducts.map(p => `
            <option value="${p.id}" data-nama="${escapeHtml(p.nama)}" data-stok="${p.stok}" data-min="${p.stok_minimum}">
                ${escapeHtml(p.nama)} (stok: ${p.stok})
            </option>
        `).join('');

        row.innerHTML = `
            <select name="items[${itemIndex}][product_id]" onchange="updateLivePreview()" class="product-select field flex-1 min-w-0" aria-label="Produk">
                ${options}
            </select>
            <div class="relative w-40 shrink-0">
                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 font-bold text-[#2E7D4F]">+</span>
                <input type="number" name="items[${itemIndex}][qty]" value="10" min="1" required oninput="updateLivePreview()" aria-label="Jumlah" class="qty-input field !pl-8 !pr-14 font-semibold">
                <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-[13px] text-brand-secondary">porsi</span>
            </div>
            <button type="button" onclick="this.closest('.item-row').remove(); updateLivePreview();" class="btn btn-ghost w-11 !px-0 shrink-0 text-brand-secondary hover:!text-brand-red" aria-label="Hapus item">
                <i data-lucide="x"></i>
            </button>
        `;

        container.appendChild(row);
        itemIndex++;
        updateLivePreview();
        row.querySelector('select').focus();
    }

    function statusStok(stok, min) {
        if (stok <= 0) return ['Habis', 'bg-[#FCE6E3] text-[#B0261D]'];
        if (stok < min) return ['Menipis', 'bg-[#FBEBDC] text-[#A8571A]'];
        return ['Aman', 'bg-[#E6F3EB] text-[#2E7D4F]'];
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

            const [label, gaya] = statusStok(finalStok, minStok);
            const div = document.createElement('div');
            div.className = 'py-3 flex items-center gap-3';
            div.innerHTML = `
                <span class="flex-1 min-w-0 font-medium truncate">${escapeHtml(nama)}</span>
                <span class="text-brand-secondary">${currentStok}</span>
                <i data-lucide="arrow-right" class="!w-4 !h-4 text-brand-secondary"></i>
                <span class="w-10 text-lg font-bold">${finalStok}</span>
                <span class="inline-flex items-center h-6 px-2 rounded-full text-[13px] font-semibold ${gaya}">${label}</span>
            `;
            previewContainer.appendChild(div);
        });

        if (rows.length === 0) {
            previewContainer.innerHTML = '<div class="kosong !py-8"><i data-lucide="package-plus"></i><p class="kosong-judul">Belum ada item</p><p class="kosong-sub">Tambahkan produk yang diterima dari pemasok.</p></div>';
        }

        const statusBadge = document.getElementById('statusBadgeContainer');
        if (allSafe && rows.length > 0) {
            statusBadge.className = 'px-3.5 py-3 rounded bg-[#E6F3EB] text-[#2E7D4F] flex items-center gap-2.5 font-medium';
            statusBadge.innerHTML = '<i data-lucide="circle-check" class="!w-4 !h-4"></i><span>Status berubah menjadi Aman</span>';
        } else {
            statusBadge.className = 'px-3.5 py-3 rounded bg-[#FBEBDC] text-[#8F4A14] flex items-center gap-2.5 font-medium';
            statusBadge.innerHTML = '<i data-lucide="triangle-alert" class="!w-4 !h-4"></i><span>Beberapa produk masih di bawah stok minimum</span>';
        }
        window.renderIcons();
    }

    document.addEventListener('DOMContentLoaded', updateLivePreview);
</script>
@endpush
@endsection
