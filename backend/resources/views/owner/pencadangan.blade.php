@extends('owner.layout')

@section('title', 'Pencadangan Data - Owner')
@section('page_title', 'Pencadangan Data')

@php
    $ukuran = fn ($kb) => $kb >= 1024 ? number_format($kb / 1024, 1, ',', '.') . ' MB' : number_format($kb, 0, ',', '.') . ' KB';
    $gagalTerakhir = $backups->first() && $backups->first()->status === 'gagal';
@endphp

@section('content')
<div class="space-y-6">

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">

        <!-- ==================== CADANGKAN MANUAL ==================== -->
        <section class="kartu p-6 flex flex-col" aria-labelledby="judulManual">
            <div class="flex items-start gap-4 flex-1">
                <span class="w-12 h-12 rounded-full flex items-center justify-center shrink-0 {{ $terakhir ? 'bg-[#E6F3EB] text-[#2E7D4F]' : 'bg-[#FCF1E3] text-[#A15C07]' }}">
                    <i data-lucide="{{ $terakhir ? 'shield-check' : 'shield-alert' }}" class="!w-6 !h-6"></i>
                </span>
                <div>
                    <h3 id="judulManual" class="kartu-judul">Pencadangan Data</h3>
                    @if($terakhir)
                        <p class="text-brand-secondary mt-1">
                            Cadangan terakhir
                            <b class="text-brand-ink font-semibold">{{ $terakhir->waktu->locale('id')->translatedFormat('j M Y, H:i') }}</b>
                            &middot; {{ $ukuran($terakhir->ukuran_kb) }}
                        </p>
                    @else
                        <p class="text-brand-secondary mt-1">Belum ada cadangan yang berhasil. Buat cadangan pertama sekarang.</p>
                    @endif
                    @if($gagalTerakhir)
                        <span class="mt-2 inline-flex items-center gap-1.5 h-7 px-2.5 rounded-full bg-[#FCE6E3] text-[#B0261D] text-[13px] font-semibold">
                            <i data-lucide="triangle-alert" class="!w-3.5 !h-3.5"></i> Percobaan terakhir gagal
                        </span>
                    @endif
                    <p class="text-[15px] text-brand-secondary mt-2 flex flex-wrap items-center gap-1.5">
                        <i data-lucide="calendar-clock" class="!w-4 !h-4"></i>
                        Otomatis berikutnya: <b class="text-brand-ink font-semibold">{{ $jadwalBerikutnya->locale('id')->translatedFormat('l, j M Y H:i') }}</b>
                    </p>
                </div>
            </div>

            <form action="{{ route('owner.pencadangan.store') }}" method="POST" class="pt-6">
                @csrf
                <button type="submit" class="btn btn-primary btn-lg" data-loading-text="Mencadangkan…">
                    <i data-lucide="database-backup"></i> Cadangkan Sekarang
                </button>
            </form>
        </section>

        <!-- ==================== JADWAL OTOMATIS ==================== -->
        <section class="kartu p-6 flex flex-col" aria-labelledby="judulJadwal">
            <div class="flex items-start gap-4">
                <span class="w-12 h-12 rounded-full bg-brand-lightBg border border-brand-border text-brand-secondary flex items-center justify-center shrink-0">
                    <i data-lucide="clock" class="!w-6 !h-6"></i>
                </span>
                <div>
                    <h3 id="judulJadwal" class="kartu-judul">Jadwal Otomatis</h3>
                    <p class="text-brand-secondary mt-1">Harian: tiap hari &middot; Mingguan: tiap Senin &middot; Bulanan: tiap tanggal 1.</p>
                </div>
            </div>

            <form action="{{ route('owner.pencadangan.schedule') }}" method="POST" class="mt-5 flex flex-col sm:flex-row sm:items-end gap-3">
                @csrf
                <div class="flex-1">
                    <label for="frekuensi" class="label">Frekuensi</label>
                    <select name="frekuensi" id="frekuensi" class="field">
                        <option value="Harian" {{ $frekuensi === 'Harian' ? 'selected' : '' }}>Harian</option>
                        <option value="Mingguan" {{ $frekuensi === 'Mingguan' ? 'selected' : '' }}>Mingguan</option>
                        <option value="Bulanan" {{ $frekuensi === 'Bulanan' ? 'selected' : '' }}>Bulanan</option>
                    </select>
                </div>
                <div class="flex-1">
                    <label for="jam" class="label">Jam</label>
                    <select name="jam" id="jam" class="field">
                        @for($h = 0; $h < 24; $h++)
                            @php $opsiJam = sprintf('%02d:00', $h); @endphp
                            <option value="{{ $opsiJam }}" {{ $jam === $opsiJam ? 'selected' : '' }}>{{ $opsiJam }}</option>
                        @endfor
                        @if(!str_ends_with($jam, ':00'))
                            <option value="{{ $jam }}" selected>{{ $jam }}</option>
                        @endif
                    </select>
                </div>
                <button type="submit" class="btn btn-secondary btn-lg" data-loading-text="Menyimpan…">
                    <i data-lucide="check" class="!w-4 !h-4"></i> Simpan Jadwal
                </button>
            </form>
        </section>
    </div>

    <!-- ==================== RIWAYAT CADANGAN ==================== -->
    <section class="kartu overflow-hidden" aria-labelledby="judulRiwayatCadangan">
        <div class="px-6 py-5 border-b border-brand-border">
            <h3 id="judulRiwayatCadangan" class="kartu-judul">Riwayat Cadangan</h3>
            <p class="text-[15px] text-brand-secondary">Daftar arsip cadangan basis data yang dapat diunduh dan dipulihkan</p>
        </div>

        <div class="overflow-x-auto">
            <table class="tabel">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th class="text-right">Ukuran</th>
                        <th>Jenis</th>
                        <th>Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($backups as $b)
                        <tr>
                            <td class="font-semibold whitespace-nowrap">{{ $b->waktu ? $b->waktu->locale('id')->translatedFormat('j M Y H:i') : '-' }}</td>
                            <td class="text-right text-brand-secondary whitespace-nowrap">{{ $b->status === 'gagal' ? '-' : $ukuran($b->ukuran_kb) }}</td>
                            <td>
                                <span class="inline-flex items-center gap-1.5 capitalize">
                                    <i data-lucide="{{ $b->jenis === 'otomatis' ? 'clock' : 'hand' }}" class="!w-4 !h-4 text-brand-secondary"></i>
                                    {{ $b->jenis }}
                                </span>
                            </td>
                            <td>
                                @if($b->status === 'gagal')
                                    <span class="inline-flex items-center gap-1.5 h-7 px-2.5 rounded-full text-[13px] font-semibold bg-[#FCE6E3] text-[#B0261D]">
                                        <i data-lucide="x" class="!w-3.5 !h-3.5"></i> Gagal
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 h-7 px-2.5 rounded-full text-[13px] font-semibold bg-[#E6F3EB] text-[#2E7D4F]">
                                        <i data-lucide="check" class="!w-3.5 !h-3.5"></i> Berhasil
                                    </span>
                                @endif
                            </td>
                            <td class="text-right whitespace-nowrap">
                                @if($fileTersedia[$b->id] ?? false)
                                    <div class="inline-flex items-center gap-1">
                                        <a href="{{ route('owner.pencadangan.unduh', $b) }}" class="btn btn-ghost btn-sm">
                                            <i data-lucide="download" class="!w-4 !h-4"></i> Unduh
                                        </a>
                                        <button type="button" class="btn btn-ghost btn-sm !text-brand-red"
                                                onclick="bukaPulihkan(this)"
                                                data-url="{{ route('owner.pencadangan.pulihkan', $b) }}"
                                                data-waktu="{{ $b->waktu ? $b->waktu->locale('id')->translatedFormat('l, j F Y') . ' pukul ' . $b->waktu->format('H:i') : '' }}">
                                            <i data-lucide="rotate-ccw" class="!w-4 !h-4"></i> Pulihkan
                                        </button>
                                    </div>
                                @else
                                    <span class="text-[13px] text-brand-secondary">File tidak tersedia</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="kosong">
                                    <i data-lucide="database"></i>
                                    <p class="kosong-judul">Belum ada cadangan</p>
                                    <p class="kosong-sub">Tekan "Cadangkan Sekarang" untuk membuat cadangan pertama.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

</div>

<!-- ==================== MODAL PULIHKAN ==================== -->
<div id="modalPulihkan" class="modal hidden" role="dialog" aria-modal="true" aria-labelledby="judulPulihkan">
    <div class="modal-panel max-w-lg">
        <div class="modal-kepala">
            <h3 id="judulPulihkan" class="kartu-judul flex items-center gap-2"><i data-lucide="rotate-ccw" class="text-brand-red"></i> Pulihkan Data</h3>
            <button type="button" onclick="tutupPulihkan()" class="btn btn-ghost btn-sm" aria-label="Tutup"><i data-lucide="x"></i></button>
        </div>
        <form id="formPulihkan" method="POST" action="">
            @csrf
            <div class="modal-isi space-y-4">
                <p>Semua data (produk, stok, transaksi, pemasok, pengguna, log aktivitas) akan dikembalikan ke kondisi
                    <b id="waktuPulihkan" class="text-brand-ink"></b>.</p>
                <div class="px-4 py-3 rounded border border-[#F2D3CE] bg-[#FCF1EF] text-[15px] text-[#8E2A22] flex gap-3">
                    <i data-lucide="triangle-alert" class="shrink-0 mt-0.5"></i>
                    <span>Data yang dicatat setelah waktu tersebut akan hilang. Sebelum memulihkan, kondisi saat ini otomatis dicadangkan dulu, jadi masih bisa dikembalikan.</span>
                </div>
                <div>
                    <label for="konfirmasi" class="label">Ketik <b>PULIHKAN</b> untuk melanjutkan</label>
                    <input type="text" name="konfirmasi" id="konfirmasi" class="field" autocomplete="off" required oninput="cekKonfirmasi()">
                </div>
            </div>
            <div class="modal-kaki">
                <button type="button" onclick="tutupPulihkan()" class="btn btn-secondary">Batal</button>
                <button type="submit" id="tombolPulihkan" class="btn btn-danger" data-loading-text="Memulihkan…" disabled>
                    <i data-lucide="rotate-ccw" class="!w-4 !h-4"></i> Pulihkan Data
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const modalPulihkan = document.getElementById('modalPulihkan');
    function bukaPulihkan(tombol) {
        document.getElementById('formPulihkan').action = tombol.dataset.url;
        document.getElementById('waktuPulihkan').textContent = tombol.dataset.waktu;
        document.getElementById('konfirmasi').value = '';
        cekKonfirmasi();
        modalPulihkan.classList.remove('hidden');
        setTimeout(() => document.getElementById('konfirmasi').focus(), 30);
    }
    function tutupPulihkan() { modalPulihkan.classList.add('hidden'); }
    function cekKonfirmasi() {
        document.getElementById('tombolPulihkan').disabled = document.getElementById('konfirmasi').value.trim() !== 'PULIHKAN';
    }
    modalPulihkan.addEventListener('click', e => { if (e.target === modalPulihkan) tutupPulihkan(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') tutupPulihkan(); });
</script>
@endpush
