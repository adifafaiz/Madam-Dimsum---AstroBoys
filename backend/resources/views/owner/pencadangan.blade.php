@extends('owner.layout')

@section('title', 'Pencadangan Data - Owner')
@section('page_title', 'Pencadangan Data')

@section('content')
<div class="space-y-6">

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">

        <!-- ==================== CADANGKAN MANUAL ==================== -->
        <section class="kartu p-6 flex flex-col" aria-labelledby="judulManual">
            <div class="flex items-start gap-4 flex-1">
                <span class="w-12 h-12 rounded-full bg-[#E6F3EB] text-[#2E7D4F] flex items-center justify-center shrink-0">
                    <i data-lucide="shield-check" class="!w-6 !h-6"></i>
                </span>
                <div>
                    <h3 id="judulManual" class="kartu-judul">Pencadangan Data</h3>
                    <p class="text-brand-secondary mt-1">
                        Terakhir dicadangkan
                        <b class="text-brand-ink font-semibold">{{ $terakhir && $terakhir->waktu ? $terakhir->waktu->format('d M Y, H:i') : '24 Nov 2026, 02:00' }}</b>
                    </p>
                    <span class="mt-2 inline-flex items-center gap-1.5 h-7 px-2.5 rounded-full bg-[#E6F3EB] text-[#2E7D4F] text-[13px] font-semibold">
                        <i data-lucide="check" class="!w-3.5 !h-3.5"></i> Berhasil
                    </span>
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
                    <p class="text-brand-secondary mt-1">Cadangan dibuat otomatis tanpa perlu ditekan.</p>
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
                        <option value="00:00" {{ $jam === '00:00' ? 'selected' : '' }}>00:00</option>
                        <option value="01:00" {{ $jam === '01:00' ? 'selected' : '' }}>01:00</option>
                        <option value="02:00" {{ $jam === '02:00' ? 'selected' : '' }}>02:00</option>
                        <option value="03:00" {{ $jam === '03:00' ? 'selected' : '' }}>03:00</option>
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
                            <td class="font-semibold whitespace-nowrap">{{ $b->waktu ? $b->waktu->format('d M Y H:i') : '-' }}</td>
                            <td class="text-right text-brand-secondary whitespace-nowrap">{{ number_format($b->ukuran_kb / 1000, 1) }} MB</td>
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
                                <div class="inline-flex items-center gap-1">
                                    <button type="button" onclick="alert('Mengunduh arsip cadangan {{ $b->file_path }}...')" class="btn btn-ghost btn-sm">
                                        <i data-lucide="download" class="!w-4 !h-4"></i> Unduh
                                    </button>
                                    <button type="button" onclick="alert('Fitur Pulihkan (FR-30): Memulihkan database ke kondisi tanggal {{ $b->waktu ? $b->waktu->format('d M Y H:i') : '' }}.')" class="btn btn-ghost btn-sm !text-brand-red">
                                        <i data-lucide="rotate-ccw" class="!w-4 !h-4"></i> Pulihkan
                                    </button>
                                </div>
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
@endsection
