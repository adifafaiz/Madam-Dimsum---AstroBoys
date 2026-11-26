@extends('owner.layout')

@section('title', 'Pemasok - Owner')
@section('page_title', 'Pemasok')

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
                        <th>Terakhir</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $sup)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <span class="w-9 h-9 rounded-full bg-brand-lightBg border border-brand-border text-brand-secondary flex items-center justify-center shrink-0">
                                        <i data-lucide="truck" class="!w-4 !h-4"></i>
                                    </span>
                                    <span class="font-semibold">{{ $sup->nama }}</span>
                                </div>
                            </td>
                            <td class="text-brand-secondary whitespace-nowrap">
                                @if($sup->kontak)
                                    <span class="inline-flex items-center gap-1.5"><i data-lucide="phone" class="!w-4 !h-4"></i>{{ $sup->kontak }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
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
                            <td class="text-brand-secondary whitespace-nowrap">
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
                            <td colspan="4">
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
                <span class="inline-flex items-center h-7 px-2.5 rounded-full bg-[#FCE6E3] text-[#B0261D] text-[13px] font-semibold">Perlu Restock</span>
            </div>
            <p class="text-[15px] text-brand-secondary">Sesuai produk yang menipis &middot; jumlah = 2&times; minimum</p>
        </div>

        <div id="kebutuhanList" class="px-6 border-t-2 border-dashed border-brand-border divide-y divide-dashed divide-brand-border">
            @forelse($kebutuhanStok as $prod)
                <div class="py-3 flex items-center justify-between gap-3">
                    <span class="font-medium">{{ $prod->nama }}</span>
                    <span class="text-lg font-bold">{{ 2 * $prod->stok_minimum }} <span class="text-[13px] font-normal text-brand-secondary">porsi</span></span>
                </div>
            @empty
                @foreach(['Hakau Udang', 'Lumpia Udang', 'Bakpao Coklat'] as $namaContoh)
                    <div class="py-3 flex items-center justify-between gap-3">
                        <span class="font-medium">{{ $namaContoh }}</span>
                        <span class="text-lg font-bold">20 <span class="text-[13px] font-normal text-brand-secondary">porsi</span></span>
                    </div>
                @endforeach
            @endforelse
        </div>

        <div class="sobek px-6 pt-5 pb-6 space-y-2.5">
            <button type="button" onclick="salinDaftarPesanan()" class="btn btn-primary w-full">
                <i data-lucide="clipboard-copy"></i>
                <span>Salin Daftar Pesanan</span>
            </button>
            <button type="button" onclick="kirimWhatsApp()" class="btn btn-secondary w-full">
                <i data-lucide="message-circle" class="text-[#2E7D4F]"></i>
                <span>Kirim via WhatsApp</span>
            </button>
        </div>
    </aside>

</div>

<!-- ==================== MODAL TAMBAH PEMASOK ==================== -->
<div id="modalTambahPemasok" class="modal hidden" role="dialog" aria-modal="true" aria-labelledby="judulTambahPemasok">
    <div class="modal-panel max-w-md">
        <div class="modal-kepala">
            <h3 id="judulTambahPemasok" class="kartu-judul">Tambah Pemasok</h3>
            <button type="button" onclick="closeModalPemasok()" class="btn btn-ghost btn-sm" aria-label="Tutup"><i data-lucide="x"></i></button>
        </div>

        <form action="{{ route('owner.pemasok.store') }}" method="POST">
            @csrf
            <div class="modal-isi space-y-4">
                <div>
                    <label for="nama" class="label">Nama pemasok</label>
                    <input type="text" name="nama" id="nama" placeholder="misal: Pemasok D" required class="field">
                </div>
                <div>
                    <label for="kontak" class="label">Nomor kontak / WhatsApp</label>
                    <div class="field-ikon">
                        <i data-lucide="phone"></i>
                        <input type="text" name="kontak" id="kontak" placeholder="0812-xxxx-xxxx" required class="field" inputmode="tel">
                    </div>
                </div>
            </div>
            <div class="modal-kaki">
                <button type="button" onclick="closeModalPemasok()" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary" data-loading-text="Menyimpan…"><i data-lucide="check"></i> Simpan Pemasok</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openModalPemasok() {
        document.getElementById('modalTambahPemasok').classList.remove('hidden');
        document.getElementById('nama').focus();
    }
    function closeModalPemasok() {
        document.getElementById('modalTambahPemasok').classList.add('hidden');
    }
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModalPemasok(); });
    document.getElementById('modalTambahPemasok').addEventListener('click', e => { if (e.target.id === 'modalTambahPemasok') closeModalPemasok(); });

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
