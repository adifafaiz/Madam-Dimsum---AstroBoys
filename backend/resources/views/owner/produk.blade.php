@extends('owner.layout')

@section('title', 'Produk & Stok - Owner')
@section('page_title', 'Produk & Stok')

@section('content')
<div class="space-y-5">

    <!-- ==================== FILTER & AKSI ==================== -->
    <div class="flex flex-col xl:flex-row xl:items-center gap-3">
        <form action="{{ route('owner.produk') }}" method="GET" class="flex flex-col sm:flex-row gap-3 flex-1">
            <div class="field-ikon sm:w-80">
                <i data-lucide="search"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari produk, tekan Enter…" class="field">
            </div>
            <select name="kategori" onchange="this.form.submit()" class="field sm:w-56" aria-label="Kategori">
                @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ request('kategori', 'Semua') === $cat ? 'selected' : '' }}>
                        {{ $cat === 'Semua' ? 'Semua Kategori' : $cat }}
                    </option>
                @endforeach
            </select>
        </form>

        <div class="flex gap-3">
            <a href="{{ route('owner.stok-masuk') }}" class="btn btn-secondary">
                <i data-lucide="package-plus"></i> Catat Stok Masuk
            </a>
            <button type="button" onclick="openModalTambahProduk()" class="btn btn-primary">
                <i data-lucide="plus"></i> Tambah Produk
            </button>
        </div>
    </div>

    <!-- ==================== TABEL PRODUK ==================== -->
    <div class="kartu overflow-hidden">
        <div class="overflow-x-auto">
            <table class="tabel">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Satuan</th>
                        <th class="text-right">Harga</th>
                        <th>Stok vs minimum</th>
                        <th>Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $prod)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    @include('partials.foto-produk', ['produk' => $prod, 'class' => 'w-11 h-11 rounded shrink-0', 'ikon' => 'w-7 h-7'])
                                    <div>
                                        <p class="font-semibold text-brand-ink">{{ $prod->nama }}</p>
                                        <p class="text-[13px] text-brand-secondary">{{ $prod->kategori }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="text-brand-secondary">{{ $prod->satuan }}</td>
                            <td class="text-right font-medium whitespace-nowrap">Rp {{ number_format($prod->harga, 0, ',', '.') }}</td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <span class="w-10 text-right text-lg font-bold {{ $prod->stok <= 0 ? 'text-brand-red' : ($prod->stok < $prod->stok_minimum ? 'text-[#B4641A]' : 'text-brand-ink') }}">{{ $prod->stok }}</span>
                                    @include('partials.bar-stok', ['produk' => $prod])
                                    <span class="text-[13px] text-brand-secondary whitespace-nowrap">min {{ $prod->stok_minimum }}</span>
                                </div>
                            </td>
                            <td>@include('partials.status-stok', ['status' => $prod->status_stok])</td>
                            <td class="text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1">
                                    <button type="button" class="btn btn-ghost btn-sm"
                                        onclick="editProduk('{{ $prod->id }}', '{{ addslashes($prod->nama) }}', '{{ $prod->satuan }}', '{{ $prod->harga }}', '{{ $prod->stok }}', '{{ $prod->stok_minimum }}', '{{ $prod->kategori }}')">
                                        <i data-lucide="pencil" class="!w-4 !h-4"></i> Edit
                                    </button>
                                    <button type="button" class="btn btn-ghost btn-sm" onclick="lihatRiwayat('{{ $prod->id }}', '{{ addslashes($prod->nama) }}')">
                                        <i data-lucide="history" class="!w-4 !h-4"></i> Riwayat
                                    </button>
                                    <form action="{{ route('owner.produk.delete', $prod->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk \'{{ addslashes($prod->nama) }}\'?\nProduk akan dinonaktifkan dari katalog dan penjualan (BR-11).')">
                                        @csrf
                                        <button type="submit" class="btn btn-ghost btn-sm !text-brand-red" aria-label="Hapus {{ $prod->nama }}">
                                            <i data-lucide="trash-2" class="!w-4 !h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="kosong">
                                    <i data-lucide="package-search"></i>
                                    @if(request('search') || (request('kategori') && request('kategori') !== 'Semua'))
                                        <p class="kosong-judul">Tidak ada produk yang cocok</p>
                                        <p class="kosong-sub">Coba kata kunci atau kategori lain.</p>
                                    @else
                                        <p class="kosong-judul">Belum ada produk</p>
                                        <p class="kosong-sub">Tambahkan produk pertama beserta stok awalnya.</p>
                                        <button type="button" onclick="openModalTambahProduk()" class="btn btn-primary mt-4"><i data-lucide="plus"></i> Tambah Produk</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 border-t border-brand-border bg-brand-lightBg text-[13px] text-brand-secondary flex flex-wrap items-center gap-x-4 gap-y-1">
            <span>Menampilkan {{ $products->count() }} dari {{ $products->count() }} produk &middot; Dihitung otomatis dari riwayat stok produk</span>
            <span class="flex items-center gap-2 sm:ml-auto">
                <span class="relative inline-block w-6 h-2 rounded-full bg-[#F0E7DD]"><span class="absolute -top-1 -bottom-1 left-1/2 w-0.5 -ml-px bg-[#8A7B70]/60 rounded-full"></span></span>
                garis = batas minimum, bar penuh = 2&times; minimum
            </span>
        </div>
    </div>

</div>

<!-- ==================== MODAL TAMBAH PRODUK ==================== -->
<div id="modalTambahProduk" class="modal hidden" role="dialog" aria-modal="true" aria-labelledby="judulTambahProduk">
    <div class="modal-panel max-w-lg">
        <div class="modal-kepala">
            <h3 id="judulTambahProduk" class="kartu-judul">Tambah Produk</h3>
            <button type="button" onclick="closeModalTambahProduk()" class="btn btn-ghost btn-sm" aria-label="Tutup"><i data-lucide="x"></i></button>
        </div>

        <form action="{{ route('owner.produk.store') }}" method="POST">
            @csrf
            <div class="modal-isi space-y-4">
                <div>
                    <label for="nama" class="label">Nama produk</label>
                    <input type="text" name="nama" id="nama" placeholder="misal: Siomay Ayam" required class="field">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="satuan" class="label">Satuan</label>
                        <input type="text" name="satuan" id="satuan" value="Porsi" placeholder="Porsi / pcs" required class="field">
                    </div>
                    <div>
                        <label for="harga" class="label">Harga jual (Rp)</label>
                        <input type="number" name="harga" id="harga" placeholder="15000" min="0" step="500" required class="field">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="stok" class="label">Stok awal</label>
                        <input type="number" name="stok" id="stok" value="48" min="0" required class="field">
                    </div>
                    <div>
                        <label for="stok_minimum" class="label">Batas minimum</label>
                        <input type="number" name="stok_minimum" id="stok_minimum" value="10" min="0" required class="field">
                    </div>
                </div>

                <div>
                    <label for="kategori" class="label">Kategori</label>
                    <select name="kategori" id="kategori" required class="field">
                        <option value="Dimsum Kukus">Dimsum Kukus</option>
                        <option value="Dimsum Goreng">Dimsum Goreng</option>
                        <option value="Bakpao">Bakpao</option>
                        <option value="Minuman">Minuman</option>
                    </select>
                </div>

                <p class="bantuan flex items-start gap-2">
                    <i data-lucide="info" class="!w-4 !h-4 mt-0.5"></i>
                    Stok awal akan dicatat otomatis di riwayat stok produk sebagai "stok awal".
                </p>
            </div>

            <div class="modal-kaki">
                <button type="button" onclick="closeModalTambahProduk()" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary" data-loading-text="Menyimpan…"><i data-lucide="check"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== MODAL EDIT PRODUK & KOREKSI STOK ==================== -->
<div id="modalEditProduk" class="modal hidden" role="dialog" aria-modal="true" aria-labelledby="judulEditProduk">
    <div class="modal-panel max-w-lg">
        <div class="modal-kepala">
            <h3 id="judulEditProduk" class="kartu-judul">Edit Produk</h3>
            <button type="button" onclick="closeModalEditProduk()" class="btn btn-ghost btn-sm" aria-label="Tutup"><i data-lucide="x"></i></button>
        </div>

        <form id="formEditProduk" action="" method="POST">
            @csrf
            <div class="modal-isi space-y-4">
                <div>
                    <label for="edit_nama" class="label">Nama produk</label>
                    <input type="text" name="nama" id="edit_nama" required class="field">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="edit_satuan" class="label">Satuan</label>
                        <input type="text" name="satuan" id="edit_satuan" required class="field">
                    </div>
                    <div>
                        <label for="edit_harga" class="label">Harga jual (Rp)</label>
                        <input type="number" name="harga" id="edit_harga" min="0" required class="field">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="edit_stok" class="label">Stok saat ini</label>
                        <input type="number" name="stok" id="edit_stok" min="0" required class="field">
                    </div>
                    <div>
                        <label for="edit_stok_minimum" class="label">Batas minimum</label>
                        <input type="number" name="stok_minimum" id="edit_stok_minimum" min="0" required class="field">
                    </div>
                </div>

                <div>
                    <label for="edit_kategori" class="label">Kategori</label>
                    <select name="kategori" id="edit_kategori" required class="field">
                        <option value="Dimsum Kukus">Dimsum Kukus</option>
                        <option value="Dimsum Goreng">Dimsum Goreng</option>
                        <option value="Bakpao">Bakpao</option>
                        <option value="Minuman">Minuman</option>
                    </select>
                </div>

                <div>
                    <label for="edit_alasan_koreksi" class="label">Keterangan / alasan koreksi</label>
                    <input type="text" name="alasan_koreksi" id="edit_alasan_koreksi" placeholder="Contoh: Penyesuaian stok fisik mingguan" class="field">
                    <p class="bantuan">Jika stok diubah, sistem mencatat penyesuaian stok di riwayat.</p>
                </div>
            </div>

            <div class="modal-kaki !justify-between">
                <button type="button" id="btnHapusModal" onclick="hapusProdukDariModal()" class="btn btn-danger">
                    <i data-lucide="trash-2" class="!w-4 !h-4"></i> Hapus Produk
                </button>
                <div class="flex gap-2.5">
                    <button type="button" onclick="closeModalEditProduk()" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary" data-loading-text="Menyimpan…"><i data-lucide="check"></i> Simpan Perubahan</button>
                </div>
            </div>
        </form>

        <form id="formDeleteModal" action="" method="POST" class="hidden">
            @csrf
        </form>
    </div>
</div>

<!-- ==================== MODAL RIWAYAT STOK ==================== -->
<div id="modalRiwayatStok" class="modal hidden" role="dialog" aria-modal="true" aria-labelledby="judulRiwayatStok">
    <div class="modal-panel max-w-xl">
        <div class="modal-kepala">
            <div>
                <h3 id="judulRiwayatStok" class="kartu-judul">Riwayat Pergerakan Stok</h3>
                <p id="riwayatProdukNama" class="text-[15px] text-brand-red font-medium"></p>
            </div>
            <button type="button" onclick="closeModalRiwayat()" class="btn btn-ghost btn-sm" aria-label="Tutup"><i data-lucide="x"></i></button>
        </div>

        <div id="riwayatContent" class="modal-isi max-h-[60vh] overflow-y-auto divide-y divide-brand-border">
            <p class="text-center py-6 text-brand-secondary">Memuat riwayat stok…</p>
        </div>

        <div class="modal-kaki">
            <button type="button" onclick="closeModalRiwayat()" class="btn btn-secondary">Tutup</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openModalTambahProduk() {
        document.getElementById('modalTambahProduk').classList.remove('hidden');
        document.getElementById('nama').focus();
    }

    function closeModalTambahProduk() {
        document.getElementById('modalTambahProduk').classList.add('hidden');
    }

    function closeModalEditProduk() {
        document.getElementById('modalEditProduk').classList.add('hidden');
    }

    function closeModalRiwayat() {
        document.getElementById('modalRiwayatStok').classList.add('hidden');
    }

    // Esc atau klik latar menutup modal.
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') document.querySelectorAll('.modal').forEach(m => m.classList.add('hidden'));
    });
    document.querySelectorAll('.modal').forEach(m => m.addEventListener('click', e => { if (e.target === m) m.classList.add('hidden'); }));

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

    const labelJenis = { stok_awal: 'Stok awal', masuk: 'Stok masuk', produksi: 'Produksi', terjual: 'Terjual', penyesuaian: 'Penyesuaian' };
    const ikonJenis  = { stok_awal: 'flag', masuk: 'truck', produksi: 'chef-hat', terjual: 'shopping-basket', penyesuaian: 'sliders-horizontal' };

    function escapeHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }

    async function lihatRiwayat(productId, nama) {
        document.getElementById('riwayatProdukNama').textContent = nama;
        document.getElementById('modalRiwayatStok').classList.remove('hidden');
        const container = document.getElementById('riwayatContent');
        container.innerHTML = '<p class="text-center py-6 text-brand-secondary">Memuat riwayat stok…</p>';

        try {
            const res = await fetch(`/owner/produk/${productId}/riwayat`);
            const data = await res.json();

            if (data.success && data.movements.length > 0) {
                container.innerHTML = data.movements.map(m => `
                    <div class="py-3 flex items-center gap-3">
                        <span class="w-9 h-9 rounded-full bg-brand-lightBg text-brand-secondary flex items-center justify-center shrink-0">
                            <i data-lucide="${ikonJenis[m.jenis] || 'circle'}" class="!w-4 !h-4"></i>
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold">${labelJenis[m.jenis] || escapeHtml(m.jenis)}
                                <span class="text-[13px] font-normal text-brand-secondary ml-1.5">${new Date(m.tanggal).toLocaleDateString('id-ID', {day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit'})}</span>
                            </p>
                            <p class="text-[13px] text-brand-secondary truncate">${escapeHtml(m.keterangan || '-')}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-lg font-bold ${m.qty >= 0 ? 'text-[#2E7D4F]' : 'text-brand-red'}">${m.qty >= 0 ? '+' : ''}${m.qty}</p>
                            <p class="text-[13px] text-brand-secondary">saldo ${m.saldo_setelah}</p>
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = `<div class="kosong"><i data-lucide="history"></i><p class="kosong-judul">Belum ada pergerakan stok</p><p class="kosong-sub">Stok masuk, produksi, dan penjualan produk ini akan tercatat di sini.</p></div>`;
            }
        } catch (e) {
            container.innerHTML = '<p class="text-center py-6 text-brand-red">Gagal memuat data riwayat.</p>';
        }
        window.renderIcons();
    }
</script>
@endpush
@endsection
