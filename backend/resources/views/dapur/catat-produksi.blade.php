@extends('dapur.layout')

@section('title', 'Catat Produksi - Dapur')
@section('page_title', 'Catat Produksi')

@push('styles')
<style>
    .sesi-opsi { display: flex; align-items: center; justify-content: center; gap: .5rem; height: 48px; border: 2px solid #EADFD3;
        border-radius: 6px; background: #fff; font-size: 16px; font-weight: 600; color: #8A7B70; cursor: pointer; transition: border-color .15s, background-color .15s, color .15s; }
    .sesi-opsi:hover { border-color: #D9C8B8; }
    input:checked + .sesi-opsi { border-color: #C8372D; background: #FCF1EF; color: #C8372D; }
    input:focus-visible + .sesi-opsi { outline: 3px solid rgba(200,55,45,.3); outline-offset: 2px; }
    .stepper-btn { width: 44px; height: 48px; display: flex; align-items: center; justify-content: center; color: #2A211C; }
    .stepper-btn:hover { background: #FBF7F2; }
    .qty-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    .qty-input { -moz-appearance: textfield; }
</style>
@endpush

@php
    // Template satu baris item; dipakai untuk baris awal (Blade) dan baris tambahan (JS).
    $opsiProduk = fn ($pilihIndex = null) => $products->map(fn ($p, $i) =>
        '<option value="' . e($p->id) . '" data-nama="' . e($p->nama) . '" data-stok="' . e($p->stok) . '" data-min="' . e($p->stok_minimum) . '"'
        . ($i === $pilihIndex ? ' selected' : '') . '>' . e($p->nama) . '</option>'
    )->implode('');
@endphp

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_420px] gap-6 items-start">

    <!-- ==================== FORM PRODUKSI ==================== -->
    <section class="kartu" aria-labelledby="judulForm">
        <form action="{{ route('dapur.catat-produksi.store') }}" method="POST" id="formProduksi">
            @csrf
            <div class="p-6 space-y-6">
                <h3 id="judulForm" class="kartu-judul">Catat Produksi</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="tanggal" class="label">Tanggal</label>
                        <input type="date" name="tanggal" id="tanggal" value="{{ date('Y-m-d') }}" required class="field">
                    </div>
                    <fieldset>
                        <legend class="label">Sesi</legend>
                        <div class="grid grid-cols-2 gap-2">
                            <label>
                                <input type="radio" name="sesi" value="pagi" class="sr-only" required>
                                <span class="sesi-opsi"><i data-lucide="sunrise"></i> Pagi</span>
                            </label>
                            <label>
                                <input type="radio" name="sesi" value="sore" class="sr-only" checked>
                                <span class="sesi-opsi"><i data-lucide="sunset"></i> Sore</span>
                            </label>
                        </div>
                    </fieldset>
                </div>

                <div>
                    <div class="flex items-baseline justify-between mb-2">
                        <span class="label !mb-0">Hasil produksi</span>
                        <span class="text-[13px] text-brand-secondary">jumlah porsi yang selesai</span>
                    </div>

                    <div id="itemsContainer" class="space-y-3">
                        @foreach([[1, 20], [2, 15], [3, 20]] as $i => [$pilih, $qtyAwal])
                            <div class="item-row flex items-center gap-3">
                                <select name="items[{{ $i }}][product_id]" class="product-select field flex-1 min-w-0 font-medium" onchange="updatePreview()" aria-label="Produk">
                                    {!! $opsiProduk($pilih) !!}
                                </select>
                                <div class="flex items-center rounded border border-brand-border bg-white overflow-hidden shrink-0">
                                    <button type="button" class="stepper-btn" onclick="ubahQty(this, -1)" aria-label="Kurangi"><i data-lucide="minus"></i></button>
                                    <input type="number" name="items[{{ $i }}][qty]" value="{{ $qtyAwal }}" min="1" class="qty-input w-16 h-12 text-center text-lg font-bold text-brand-ink border-x border-brand-border focus:outline-none focus:bg-[#FCF1EF]" oninput="updatePreview()" aria-label="Jumlah porsi">
                                    <button type="button" class="stepper-btn" onclick="ubahQty(this, 1)" aria-label="Tambah"><i data-lucide="plus"></i></button>
                                </div>
                                <button type="button" onclick="removeItem(this)" class="btn btn-ghost w-11 !px-0 shrink-0 text-brand-secondary hover:!text-brand-red" aria-label="Hapus baris"><i data-lucide="x"></i></button>
                            </div>
                        @endforeach
                    </div>

                    <button type="button" onclick="addItem()" class="btn w-full mt-3 border-2 border-dashed !border-brand-border text-brand-secondary hover:!border-brand-red hover:text-brand-red">
                        <i data-lucide="plus" class="!w-4 !h-4"></i> Tambah Produk
                    </button>
                </div>

                <div>
                    <label for="catatan" class="label">Catatan <span class="font-normal text-brand-secondary">(opsional)</span></label>
                    <textarea name="catatan" id="catatan" rows="2" placeholder="Contoh: Produksi sesi 2, persiapan permintaan akhir pekan" class="field">Produksi sesi 2, persiapan permintaan akhir pekan</textarea>
                </div>
            </div>

            <div class="modal-kaki !justify-between rounded-b">
                <a href="{{ route('dapur.stok') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary btn-lg" data-loading-text="Menyimpan…">
                    <i data-lucide="check"></i> Simpan Produksi
                </button>
            </div>
        </form>
    </section>

    <!-- ==================== PRATINJAU ==================== -->
    <aside class="struk xl:sticky xl:top-[96px]" aria-labelledby="judulPratinjau">
        <div class="px-6 pt-6 pb-4">
            <p class="text-[13px] uppercase tracking-wide text-brand-secondary">Pratinjau</p>
            <h3 id="judulPratinjau" class="kartu-judul mt-0.5">Stok Setelah Disimpan</h3>
        </div>

        <div id="previewList" class="px-6 border-t-2 border-dashed border-brand-border divide-y divide-dashed divide-brand-border">
            <!-- Diisi otomatis oleh JS -->
        </div>

        <div class="sobek px-6 pt-5 pb-6">
            <div id="statusPratinjau" class="px-3.5 py-3 rounded bg-[#E6F3EB] text-[#2E7D4F] flex items-start gap-2.5 font-medium"></div>
        </div>
    </aside>

</div>

@push('scripts')
<script>
    let itemIndex = 3;
    const opsiProduk = @json($opsiProduk());

    function escapeHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }

    function addItem() {
        const container = document.getElementById('itemsContainer');
        const div = document.createElement('div');
        div.className = 'item-row flex items-center gap-3';
        div.innerHTML = `
            <select name="items[${itemIndex}][product_id]" class="product-select field flex-1 min-w-0 font-medium" onchange="updatePreview()" aria-label="Produk">
                ${opsiProduk}
            </select>
            <div class="flex items-center rounded border border-brand-border bg-white overflow-hidden shrink-0">
                <button type="button" class="stepper-btn" onclick="ubahQty(this, -1)" aria-label="Kurangi"><i data-lucide="minus"></i></button>
                <input type="number" name="items[${itemIndex}][qty]" value="10" min="1" class="qty-input w-16 h-12 text-center text-lg font-bold text-brand-ink border-x border-brand-border focus:outline-none focus:bg-[#FCF1EF]" oninput="updatePreview()" aria-label="Jumlah porsi">
                <button type="button" class="stepper-btn" onclick="ubahQty(this, 1)" aria-label="Tambah"><i data-lucide="plus"></i></button>
            </div>
            <button type="button" onclick="removeItem(this)" class="btn btn-ghost w-11 !px-0 shrink-0 text-brand-secondary hover:!text-brand-red" aria-label="Hapus baris"><i data-lucide="x"></i></button>
        `;
        container.appendChild(div);
        itemIndex++;
        updatePreview();
        div.querySelector('select').focus();
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

    function ubahQty(btn, delta) {
        const input = btn.parentElement.querySelector('.qty-input');
        input.value = Math.max(1, (parseInt(input.value) || 0) + delta);
        updatePreview();
    }

    function statusStok(stok, min) {
        if (stok <= 0) return ['Habis', 'bg-[#FCE6E3] text-[#B0261D]'];
        if (stok < min) return ['Menipis', 'bg-[#FBEBDC] text-[#A8571A]'];
        return ['Aman', 'bg-[#E6F3EB] text-[#2E7D4F]'];
    }

    function updatePreview() {
        const previewList = document.getElementById('previewList');
        previewList.innerHTML = '';
        let masihKurang = 0;

        document.querySelectorAll('.item-row').forEach(row => {
            const select = row.querySelector('.product-select');
            const qtyInput = row.querySelector('.qty-input');
            const selectedOption = select.options[select.selectedIndex];
            if (!selectedOption) return;

            const nama = selectedOption.getAttribute('data-nama') || selectedOption.text.trim();
            const stokAwal = parseInt(selectedOption.getAttribute('data-stok') || 0);
            const minStok = parseInt(selectedOption.getAttribute('data-min') || 0);
            const stokAkhir = stokAwal + parseInt(qtyInput.value || 0);
            if (stokAkhir < minStok) masihKurang++;

            const [label, gaya] = statusStok(stokAkhir, minStok);
            const itemDiv = document.createElement('div');
            itemDiv.className = 'py-3 flex items-center gap-3';
            itemDiv.innerHTML = `
                <span class="flex-1 min-w-0 font-medium truncate">${escapeHtml(nama)}</span>
                <span class="text-brand-secondary">${stokAwal}</span>
                <i data-lucide="arrow-right" class="!w-4 !h-4 text-brand-secondary"></i>
                <span class="w-10 text-lg font-bold">${stokAkhir}</span>
                <span class="inline-flex items-center h-6 px-2 rounded-full text-[13px] font-semibold ${gaya}">${label}</span>
            `;
            previewList.appendChild(itemDiv);
        });

        const status = document.getElementById('statusPratinjau');
        if (masihKurang === 0) {
            status.className = 'px-3.5 py-3 rounded bg-[#E6F3EB] text-[#2E7D4F] flex items-start gap-2.5 font-medium';
            status.innerHTML = '<i data-lucide="circle-check" class="mt-0.5"></i><span>Stok kembali menjadi Aman<br><span class="font-normal text-[15px]">Semua produk di atas siap jual.</span></span>';
        } else {
            status.className = 'px-3.5 py-3 rounded bg-[#FBEBDC] text-[#8F4A14] flex items-start gap-2.5 font-medium';
            status.innerHTML = `<i data-lucide="triangle-alert" class="mt-0.5"></i><span>${masihKurang} produk masih di bawah minimum<br><span class="font-normal text-[15px]">Pertimbangkan menambah jumlah produksi.</span></span>`;
        }
        window.renderIcons();
    }

    document.addEventListener('DOMContentLoaded', updatePreview);
</script>
@endpush
@endsection
