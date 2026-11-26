@extends('owner.layout')

@section('title', 'Pemasok - Owner')
@section('page_title', 'Pemasok')

@php
    // Nomor lokal -> format wa.me (0812... / 812... / +62812... -> 62812...). Null bila tidak layak.
    $nomorWa = function (?string $kontak) {
        $angka = preg_replace('/\D+/', '', (string) $kontak);
        if (str_starts_with($angka, '0')) $angka = '62' . substr($angka, 1);
        elseif (str_starts_with($angka, '8')) $angka = '62' . $angka;
        return strlen($angka) >= 10 ? $angka : null;
    };

    $teksPesanan = function ($pemasok, $produk) {
        $baris = $produk->map(fn ($p) => '- ' . $p->nama . ': ' . (2 * $p->stok_minimum) . ' ' . mb_strtolower($p->satuan ?: 'porsi'))->implode("\n");
        $sapaan = $pemasok ? 'Halo ' . $pemasok->nama . ', kami' : 'Halo, kami';
        return $sapaan . " dari Madam Dimsum ingin memesan restock produk berikut:\n" . $baris
            . "\n\nMohon konfirmasi ketersediaan dan estimasi pengirimannya. Terima kasih!";
    };

    $formLama = old('_form');
@endphp

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_400px] gap-6 items-start">

    <!-- ==================== DAFTAR PEMASOK ==================== -->
    <section class="kartu overflow-hidden" aria-labelledby="judulPemasok">
        <div class="px-6 py-5 border-b border-brand-border flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 id="judulPemasok" class="kartu-judul">Daftar Pemasok</h3>
                <p class="text-[15px] text-brand-secondary">Kelola kontak pemasok resmi Madam Dimsum</p>
            </div>
            <button type="button" onclick="openModalPemasok()" class="btn btn-primary">
                <i data-lucide="plus"></i> Tambah Pemasok
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="tabel">
                <thead>
                    <tr>
                        <th>Pemasok</th>
                        <th>Kontak</th>
                        <th>Produk</th>
                        <th>Kiriman terakhir</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $sup)
                        @php
                            $kirimanTerakhir = $sup->stockMovements->first();
                            $wa = $nomorWa($sup->kontak);
                            $dipakai = $sup->products->count() > 0 || $sup->stockMovements->count() > 0;
                        @endphp
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <span class="w-9 h-9 rounded-full bg-brand-lightBg border border-brand-border text-brand-secondary flex items-center justify-center shrink-0">
                                        <i data-lucide="truck" class="!w-4 !h-4"></i>
                                    </span>
                                    <span class="font-semibold">{{ $sup->nama }}</span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap">
                                @if($sup->kontak)
                                    <span class="inline-flex items-center gap-1.5 text-brand-secondary"><i data-lucide="phone" class="!w-4 !h-4"></i>{{ $sup->kontak }}</span>
                                    @if($wa)
                                        <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="ml-1.5 inline-flex items-center align-middle text-[#2E7D4F] hover:underline" title="Chat WhatsApp {{ $sup->nama }}" aria-label="Chat WhatsApp {{ $sup->nama }}">
                                            <i data-lucide="message-circle" class="!w-4 !h-4"></i>
                                        </a>
                                    @endif
                                @else
                                    <span class="text-brand-secondary">Belum ada</span>
                                @endif
                            </td>
                            <td>
                                @if($sup->products->count())
                                    {{ $sup->products->pluck('nama')->implode(', ') }}
                                @else
                                    <span class="text-brand-secondary">Belum ada produk</span>
                                @endif
                            </td>
                            <td class="text-brand-secondary whitespace-nowrap">
                                @if($kirimanTerakhir)
                                    {{ $kirimanTerakhir->tanggal->locale('id')->translatedFormat('j M Y') }}
                                @else
                                    Belum pernah
                                @endif
                            </td>
                            <td class="text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1">
                                    <button type="button" class="btn btn-ghost btn-sm"
                                            onclick="openModalEditPemasok(this)"
                                            data-url="{{ route('owner.pemasok.update', $sup) }}"
                                            data-id="{{ $sup->id }}"
                                            data-nama="{{ $sup->nama }}"
                                            data-kontak="{{ $sup->kontak }}">
                                        <i data-lucide="pencil" class="!w-4 !h-4"></i> Edit
                                    </button>
                                    @if(!$dipakai)
                                        <form action="{{ route('owner.pemasok.destroy', $sup) }}" method="POST" class="inline"
                                              onsubmit="return confirm(this.dataset.pesan)" data-pesan="Hapus pemasok {{ $sup->nama }}?">
                                            @csrf
                                            <button type="submit" class="btn btn-ghost btn-sm !text-brand-red">
                                                <i data-lucide="trash-2" class="!w-4 !h-4"></i> Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="kosong">
                                    <i data-lucide="truck"></i>
                                    <p class="kosong-judul">Belum ada pemasok</p>
                                    <p class="kosong-sub">Tambahkan pemasok supaya stok masuk bisa dicatat.</p>
                                    <button type="button" onclick="openModalPemasok()" class="btn btn-primary mt-4"><i data-lucide="plus"></i> Tambah Pemasok</button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <!-- ==================== KEBUTUHAN STOK ==================== -->
    <aside class="struk xl:sticky xl:top-[96px]" aria-labelledby="judulKebutuhan">
        <div class="px-6 pt-6 pb-4">
            <div class="flex items-center justify-between gap-2">
                <h3 id="judulKebutuhan" class="kartu-judul">Kebutuhan Stok</h3>
                @if($kebutuhanStok->count())
                    <span class="inline-flex items-center h-7 px-2.5 rounded-full bg-[#FCE6E3] text-[#B0261D] text-[13px] font-semibold">{{ $kebutuhanStok->count() }} perlu restock</span>
                @endif
            </div>
            <p class="text-[15px] text-brand-secondary">Produk menipis &amp; habis &middot; jumlah pesan = 2&times; minimum</p>
        </div>

        @forelse($kebutuhanPerPemasok as $grup)
            @php
                $pemasokGrup = $grup['pemasok'];
                $waGrup = $pemasokGrup ? $nomorWa($pemasokGrup->kontak) : null;
                $teks = $teksPesanan($pemasokGrup, $grup['produk']);
            @endphp
            <div class="border-t-2 border-dashed border-brand-border">
                <div class="px-6 pt-4 flex items-center gap-2 text-[15px] font-semibold">
                    <i data-lucide="{{ $pemasokGrup ? 'truck' : 'circle-help' }}" class="!w-4 !h-4 text-brand-secondary"></i>
                    {{ $pemasokGrup ? $pemasokGrup->nama : 'Belum ada pemasok' }}
                </div>
                <div class="px-6 divide-y divide-dashed divide-brand-border">
                    @foreach($grup['produk'] as $prod)
                        <div class="py-3 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-medium truncate">{{ $prod->nama }}</p>
                                <p class="text-[13px] {{ $prod->stok == 0 ? 'text-[#B0261D] font-semibold' : 'text-brand-secondary' }}">
                                    {{ $prod->stok == 0 ? 'Habis' : 'sisa ' . $prod->stok }} &middot; min {{ $prod->stok_minimum }}
                                </p>
                            </div>
                            <span class="text-lg font-bold shrink-0">{{ 2 * $prod->stok_minimum }} <span class="text-[13px] font-normal text-brand-secondary">{{ mb_strtolower($prod->satuan ?: 'porsi') }}</span></span>
                        </div>
                    @endforeach
                </div>
                <div class="px-6 pt-2 pb-5 flex flex-col gap-2">
                    <button type="button" onclick="salinPesanan(this)" data-teks="{{ $teks }}" class="btn btn-primary w-full">
                        <i data-lucide="clipboard-copy"></i>
                        <span class="label-tombol">Salin Daftar Pesanan</span>
                    </button>
                    @if($waGrup)
                        <a href="https://wa.me/{{ $waGrup }}?text={{ rawurlencode($teks) }}" target="_blank" rel="noopener" class="btn btn-secondary w-full">
                            <i data-lucide="message-circle" class="text-[#2E7D4F]"></i>
                            <span>Kirim via WhatsApp</span>
                        </a>
                    @elseif($pemasokGrup)
                        <p class="bantuan text-center">Tambahkan nomor kontak {{ $pemasokGrup->nama }} untuk kirim via WhatsApp.</p>
                    @else
                        <p class="bantuan text-center">Atur pemasok produk ini di menu Produk &amp; Stok.</p>
                    @endif
                </div>
            </div>
        @empty
            <div class="kosong border-t-2 border-dashed border-brand-border">
                <i data-lucide="circle-check"></i>
                <p class="kosong-judul">Stok aman semua</p>
                <p class="kosong-sub">Belum ada produk yang perlu dipesan ulang.</p>
            </div>
        @endforelse
    </aside>

</div>

<!-- ==================== MODAL TAMBAH PEMASOK ==================== -->
<div id="modalTambahPemasok" class="modal hidden" role="dialog" aria-modal="true" aria-labelledby="judulTambahPemasok">
    <div class="modal-panel max-w-md">
        <div class="modal-kepala">
            <h3 id="judulTambahPemasok" class="kartu-judul">Tambah Pemasok</h3>
            <button type="button" onclick="tutupModal('modalTambahPemasok')" class="btn btn-ghost btn-sm" aria-label="Tutup"><i data-lucide="x"></i></button>
        </div>

        <form action="{{ route('owner.pemasok.store') }}" method="POST">
            @csrf
            <input type="hidden" name="_form" value="tambah">
            <div class="modal-isi space-y-4">
                <div>
                    <label for="nama" class="label">Nama pemasok</label>
                    <input type="text" name="nama" id="nama" value="{{ $formLama === 'tambah' ? old('nama') : '' }}" placeholder="misal: Pemasok D" required maxlength="100" class="field">
                </div>
                <div>
                    <label for="kontak" class="label">Nomor kontak / WhatsApp <span class="font-normal text-brand-secondary">(opsional)</span></label>
                    <div class="field-ikon">
                        <i data-lucide="phone"></i>
                        <input type="text" name="kontak" id="kontak" value="{{ $formLama === 'tambah' ? old('kontak') : '' }}" placeholder="0812-xxxx-xxxx" maxlength="20" class="field" inputmode="tel">
                    </div>
                </div>
            </div>
            <div class="modal-kaki">
                <button type="button" onclick="tutupModal('modalTambahPemasok')" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary" data-loading-text="Menyimpan…"><i data-lucide="check"></i> Simpan Pemasok</button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== MODAL EDIT PEMASOK ==================== -->
<div id="modalEditPemasok" class="modal hidden" role="dialog" aria-modal="true" aria-labelledby="judulEditPemasok">
    <div class="modal-panel max-w-md">
        <div class="modal-kepala">
            <h3 id="judulEditPemasok" class="kartu-judul">Edit Pemasok</h3>
            <button type="button" onclick="tutupModal('modalEditPemasok')" class="btn btn-ghost btn-sm" aria-label="Tutup"><i data-lucide="x"></i></button>
        </div>

        <form id="formEditPemasok" method="POST" action="{{ $formLama === 'edit' && old('_id') ? route('owner.pemasok.update', old('_id')) : '' }}">
            @csrf
            <input type="hidden" name="_form" value="edit">
            <input type="hidden" name="_id" id="editPemasokId" value="{{ $formLama === 'edit' ? old('_id') : '' }}">
            <div class="modal-isi space-y-4">
                <div>
                    <label for="edit_nama" class="label">Nama pemasok</label>
                    <input type="text" name="nama" id="edit_nama" value="{{ $formLama === 'edit' ? old('nama') : '' }}" required maxlength="100" class="field">
                </div>
                <div>
                    <label for="edit_kontak" class="label">Nomor kontak / WhatsApp <span class="font-normal text-brand-secondary">(opsional)</span></label>
                    <div class="field-ikon">
                        <i data-lucide="phone"></i>
                        <input type="text" name="kontak" id="edit_kontak" value="{{ $formLama === 'edit' ? old('kontak') : '' }}" maxlength="20" class="field" inputmode="tel">
                    </div>
                </div>
            </div>
            <div class="modal-kaki">
                <button type="button" onclick="tutupModal('modalEditPemasok')" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary" data-loading-text="Menyimpan…"><i data-lucide="check"></i> Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function bukaModal(id, fokus) {
        document.getElementById(id).classList.remove('hidden');
        if (fokus) document.getElementById(fokus).focus();
    }
    function tutupModal(id) { document.getElementById(id).classList.add('hidden'); }

    function openModalPemasok() { bukaModal('modalTambahPemasok', 'nama'); }
    function openModalEditPemasok(tombol) {
        const d = tombol.dataset;
        document.getElementById('formEditPemasok').action = d.url;
        document.getElementById('editPemasokId').value = d.id;
        document.getElementById('edit_nama').value = d.nama;
        document.getElementById('edit_kontak').value = d.kontak || '';
        bukaModal('modalEditPemasok', 'edit_nama');
    }

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') document.querySelectorAll('.modal').forEach(m => m.classList.add('hidden'));
    });
    document.querySelectorAll('.modal').forEach(m => m.addEventListener('click', e => { if (e.target === m) m.classList.add('hidden'); }));

    // Salin teks pesanan; tombol berubah jadi "Tersalin" sebentar sebagai tanda berhasil.
    async function salinPesanan(tombol) {
        const teks = tombol.dataset.teks;
        try {
            await navigator.clipboard.writeText(teks);
        } catch (e) {
            const area = Object.assign(document.createElement('textarea'), { value: teks });
            area.style.position = 'fixed'; area.style.opacity = '0';
            document.body.appendChild(area); area.select();
            document.execCommand('copy'); area.remove();
        }
        const label = tombol.querySelector('.label-tombol');
        const asli = label.textContent;
        label.textContent = 'Tersalin ✓';
        tombol.disabled = true;
        setTimeout(() => { label.textContent = asli; tombol.disabled = false; }, 1800);
    }

    @if($errors->any())
        @if($formLama === 'tambah') openModalPemasok(); @endif
        @if($formLama === 'edit') bukaModal('modalEditPemasok'); @endif
    @endif
</script>
@endpush
@endsection
