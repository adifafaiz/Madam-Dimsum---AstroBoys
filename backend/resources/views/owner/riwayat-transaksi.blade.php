@extends('owner.layout')

@section('title', 'Riwayat Transaksi - Owner')
@section('page_title', 'Riwayat Transaksi')

@php
    $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
    $labelStatus = [
        'baru'     => ['Baru', 'bg-[#FCE6E3] text-[#B0261D]'],
        'diproses' => ['Diproses', 'bg-[#FBEBDC] text-[#A8571A]'],
        'siap'     => ['Siap diambil', 'bg-[#E6F3EB] text-[#2E7D4F]'],
        'selesai'  => ['Selesai', 'bg-[#F3ECE4] text-brand-ink'],
        'batal'    => ['Dibatalkan', 'bg-[#2A211C] text-white'],
    ];
@endphp

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_420px] gap-6 items-start">

    <!-- ==================== DAFTAR TRANSAKSI ==================== -->
    <section class="min-w-0 space-y-4" aria-label="Daftar transaksi">
        <form action="{{ route('owner.riwayat-transaksi') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="field-ikon sm:w-56 shrink-0">
                <i data-lucide="calendar"></i>
                <input type="date" name="tanggal" value="{{ request('tanggal') }}" onchange="this.form.submit()" class="field" aria-label="Tanggal">
            </div>
            <div class="field-ikon flex-1">
                <i data-lucide="search"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari no. transaksi / produk…" class="field">
            </div>
            <button type="submit" class="btn btn-dark btn-lg"><i data-lucide="search" class="!w-4 !h-4"></i> Cari</button>
            @if(request('tanggal') || request('search'))
                <a href="{{ route('owner.riwayat-transaksi') }}" class="btn btn-ghost btn-lg text-brand-red"><i data-lucide="x" class="!w-4 !h-4"></i> Reset</a>
            @endif
        </form>

        <div class="kartu overflow-hidden">
            <div class="overflow-x-auto">
                <table class="tabel">
                    <thead>
                        <tr>
                            <th>No. Transaksi</th>
                            <th>Waktu</th>
                            <th>Kasir</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $o)
                            @php
                                $isSelected = ($selectedOrder && $selectedOrder->id === $o->id);
                                $urlDetail = route('owner.riwayat-transaksi', ['selected' => $o->kode, 'tanggal' => request('tanggal'), 'search' => request('search')]);
                            @endphp
                            <tr
                                onclick="window.location.href='{{ $urlDetail }}'"
                                onkeydown="if (event.key === 'Enter') window.location.href='{{ $urlDetail }}'"
                                tabindex="0"
                                @if($isSelected) aria-current="true" @endif
                                class="cursor-pointer focus:outline-none focus-visible:bg-brand-lightBg {{ $isSelected ? '!bg-[#FCF1EF] shadow-[inset_4px_0_0_#C8372D]' : '' }} {{ $o->status === 'batal' ? 'text-brand-secondary' : '' }}"
                            >
                                <td class="font-semibold whitespace-nowrap {{ $isSelected ? 'text-brand-red' : '' }}">
                                    <span class="{{ $o->status === 'batal' ? 'line-through decoration-1' : '' }}">{{ $o->kode }}</span>
                                    @if($o->status === 'batal')
                                        <span class="ml-1.5 inline-flex items-center h-6 px-2 rounded-full text-[13px] font-semibold bg-[#2A211C] text-white no-underline">Batal</span>
                                    @endif
                                </td>
                                <td class="text-brand-secondary whitespace-nowrap">{{ $o->waktu ? $o->waktu->format('d M H:i') : '-' }}</td>
                                <td>{{ $o->user->name ?? 'Kasir' }}</td>
                                <td class="text-right font-semibold whitespace-nowrap">{{ $rp($o->total) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <div class="kosong">
                                        <i data-lucide="receipt-text"></i>
                                        @if(request('tanggal') || request('search'))
                                            <p class="kosong-judul">Tidak ada transaksi yang cocok</p>
                                            <p class="kosong-sub">Coba tanggal atau kata kunci lain.</p>
                                        @else
                                            <p class="kosong-judul">Belum ada transaksi</p>
                                            <p class="kosong-sub">Transaksi dari kasir akan tercatat di sini.</p>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- ==================== DETAIL TRANSAKSI ==================== -->
    <aside class="xl:sticky xl:top-[96px]" aria-label="Detail transaksi">
        @if($selectedOrder)
            @php [$teksStatus, $gayaStatus] = $labelStatus[$selectedOrder->status] ?? [ucfirst($selectedOrder->status), 'bg-[#F3ECE4] text-brand-ink']; @endphp
            <div class="struk">
                <div class="px-6 pt-6 pb-5">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-[13px] uppercase tracking-wide text-brand-secondary">Detail transaksi</p>
                        <span class="inline-flex items-center h-7 px-2.5 rounded-full text-[13px] font-semibold {{ $gayaStatus }}">{{ $teksStatus }}</span>
                    </div>
                    <h2 class="font-display text-2xl font-semibold mt-1">{{ $selectedOrder->kode }}</h2>
                    <p class="text-[15px] text-brand-secondary mt-0.5">
                        {{ $selectedOrder->waktu ? $selectedOrder->waktu->format('d M Y, H:i') : '-' }} &middot; Kasir <b class="text-brand-ink font-medium">{{ $selectedOrder->user->name ?? 'Kasir' }}</b>
                    </p>
                </div>

                <ul class="px-6 py-4 border-t-2 border-dashed border-brand-border space-y-3">
                    @foreach($selectedOrder->items as $item)
                        <li class="flex items-baseline gap-3">
                            <span class="w-9 font-bold">{{ $item->qty }}&times;</span>
                            <span class="flex-1 min-w-0">{{ $item->product->nama ?? 'Dimsum' }}</span>
                            <span class="font-semibold">{{ $rp($item->subtotal) }}</span>
                        </li>
                    @endforeach
                </ul>

                <div class="sobek px-6 pt-5 pb-6 space-y-2">
                    <div class="flex items-baseline justify-between">
                        <span class="text-lg font-semibold">Total</span>
                        <span class="font-display text-3xl font-semibold {{ $selectedOrder->status === 'batal' ? 'text-brand-secondary line-through decoration-2' : 'text-brand-red' }}">{{ $rp($selectedOrder->total) }}</span>
                    </div>
                    <div class="flex justify-between text-[15px] text-brand-secondary">
                        <span>Dibayar</span><span class="text-brand-ink">{{ $rp($selectedOrder->dibayar) }}</span>
                    </div>
                    <div class="flex justify-between text-[15px] text-brand-secondary">
                        <span>Kembalian</span><span class="text-brand-ink">{{ $rp($selectedOrder->kembalian) }}</span>
                    </div>

                    @if($selectedOrder->status === 'batal')
                        <div class="mt-3 px-4 py-3 rounded bg-brand-lightBg border border-brand-border">
                            <p class="text-[13px] font-semibold text-brand-secondary uppercase tracking-wide">Alasan batal</p>
                            <p class="mt-0.5">{{ $selectedOrder->alasan_batal }}</p>
                        </div>
                    @else
                        <button type="button" onclick="openModalBatalTransaksi('{{ $selectedOrder->id }}', '{{ $selectedOrder->kode }}')" class="btn btn-danger btn-lg w-full !mt-4">
                            <i data-lucide="ban" class="!w-4 !h-4"></i> Batalkan Transaksi
                        </button>
                    @endif
                </div>
            </div>
        @else
            <div class="kosong min-h-[320px] rounded border-2 border-dashed border-brand-border">
                <i data-lucide="mouse-pointer-click"></i>
                <p class="kosong-judul">Pilih satu transaksi</p>
                <p class="kosong-sub">Rincian item, uang dibayar, kembalian, dan opsi pembatalan akan tampil di sini.</p>
            </div>
        @endif
    </aside>

</div>

<!-- ==================== MODAL BATALKAN TRANSAKSI ==================== -->
<div id="modalBatal" class="modal hidden" role="dialog" aria-modal="true" aria-labelledby="judulBatal">
    <div class="modal-panel max-w-md">
        <div class="modal-kepala">
            <h3 id="judulBatal" class="kartu-judul">Batalkan Transaksi</h3>
            <button type="button" onclick="closeModalBatal()" class="btn btn-ghost btn-sm" aria-label="Tutup"><i data-lucide="x"></i></button>
        </div>

        <form id="formBatal" method="POST">
            @csrf
            <div class="modal-isi space-y-4">
                <div class="flex items-start gap-3 px-4 py-3 rounded bg-[#FBEBDC] text-[#8F4A14]">
                    <i data-lucide="undo-2" class="mt-0.5"></i>
                    <p>Membatalkan nota <b id="batalKodeText"></b> akan mengembalikan stok semua produk di dalamnya.</p>
                </div>
                <div>
                    <label for="alasan_batal" class="label">Alasan pembatalan <span class="text-brand-red">*</span></label>
                    <textarea name="alasan_batal" id="alasan_batal" rows="3" required placeholder="misal: Salah input pesanan / pelanggan membatalkan pembelian" class="field"></textarea>
                </div>
            </div>
            <div class="modal-kaki">
                <button type="button" onclick="closeModalBatal()" class="btn btn-secondary">Kembali</button>
                <button type="submit" class="btn btn-primary" data-loading-text="Membatalkan…"><i data-lucide="ban" class="!w-4 !h-4"></i> Konfirmasi Pembatalan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openModalBatalTransaksi(orderId, kode) {
        document.getElementById('batalKodeText').textContent = kode;
        document.getElementById('formBatal').action = `/owner/transaksi/${orderId}/batal`;
        document.getElementById('modalBatal').classList.remove('hidden');
        document.getElementById('alasan_batal').focus();
    }
    function closeModalBatal() {
        document.getElementById('modalBatal').classList.add('hidden');
    }
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModalBatal(); });
    document.getElementById('modalBatal').addEventListener('click', e => { if (e.target.id === 'modalBatal') closeModalBatal(); });
</script>
@endpush
@endsection
