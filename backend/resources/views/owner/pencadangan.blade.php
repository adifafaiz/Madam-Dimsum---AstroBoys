@extends('owner.layout')

@section('title', 'Pencadangan Data - Owner')
@section('page_title', 'Pencadangan Data')

@section('content')
<div class="space-y-6 max-w-7xl">

    <!-- ==================== BAGIAN ATAS: 2 KARTU STATUS & JADWAL (FRAME 9) ==================== -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">
        
        <!-- Kartu Kiri: Pencadangan Data Manual -->
        <div class="bg-white p-6 rounded-2xl border border-[#EADFD3] shadow-sm flex flex-col justify-between">
            <div>
                <h4 class="text-sm font-bold text-stone-900 mb-2">Pencadangan Data</h4>
                <p class="text-xs text-stone-500">
                    Terakhir dicadangkan: <b class="text-stone-800">{{ $terakhir && $terakhir->waktu ? $terakhir->waktu->format('d M Y, H:i') : '24 Nov 2026, 02:00' }}</b>
                </p>
                <div class="mt-2.5">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-[#2E8B57]/10 text-[#2E8B57] text-xs font-semibold border border-[#2E8B57]/20">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        <span>Berhasil</span>
                    </span>
                </div>
            </div>

            <!-- Tombol Cadangkan Sekarang -->
            <form action="{{ route('owner.pencadangan.store') }}" method="POST" class="pt-6">
                @csrf
                <button 
                    type="submit" 
                    class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-brand-red hover:bg-brand-redHover text-white text-xs font-semibold shadow-sm transition flex items-center justify-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                    <span>Cadangkan Sekarang</span>
                </button>
            </form>
        </div>

        <!-- Kartu Kanan: Jadwal Otomatis -->
        <div class="bg-white p-6 rounded-2xl border border-[#EADFD3] shadow-sm flex flex-col justify-between">
            <h4 class="text-sm font-bold text-stone-900 mb-2">Jadwal Otomatis</h4>

            <form action="{{ route('owner.pencadangan.schedule') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3.5">
                    <div>
                        <label for="frekuensi" class="block text-xs font-semibold text-stone-600 mb-1.5">Frekuensi</label>
                        <select 
                            name="frekuensi" 
                            id="frekuensi" 
                            class="w-full px-3 py-2 bg-white border border-[#EADFD3] rounded-lg text-xs font-medium text-stone-800 focus:outline-none focus:ring-1 focus:ring-brand-red cursor-pointer"
                        >
                            <option value="Harian" {{ $frekuensi === 'Harian' ? 'selected' : '' }}>Harian</option>
                            <option value="Mingguan" {{ $frekuensi === 'Mingguan' ? 'selected' : '' }}>Mingguan</option>
                            <option value="Bulanan" {{ $frekuensi === 'Bulanan' ? 'selected' : '' }}>Bulanan</option>
                        </select>
                    </div>

                    <div>
                        <label for="jam" class="block text-xs font-semibold text-stone-600 mb-1.5">Jam</label>
                        <select 
                            name="jam" 
                            id="jam" 
                            class="w-full px-3 py-2 bg-white border border-[#EADFD3] rounded-lg text-xs font-medium text-stone-800 focus:outline-none focus:ring-1 focus:ring-brand-red cursor-pointer"
                        >
                            <option value="00:00" {{ $jam === '00:00' ? 'selected' : '' }}>00:00</option>
                            <option value="01:00" {{ $jam === '01:00' ? 'selected' : '' }}>01:00</option>
                            <option value="02:00" {{ $jam === '02:00' ? 'selected' : '' }}>02:00</option>
                            <option value="03:00" {{ $jam === '03:00' ? 'selected' : '' }}>03:00</option>
                        </select>
                    </div>
                </div>

                <div class="pt-2 text-right">
                    <button 
                        type="submit" 
                        class="px-4 py-2 rounded-lg border border-[#EADFD3] bg-white hover:bg-stone-50 text-xs font-semibold text-stone-700 shadow-sm transition"
                    >
                        Simpan Jadwal
                    </button>
                </div>
            </form>
        </div>

    </div>

    <!-- ==================== TABEL RIWAYAT CADANGAN (FRAME 9 BAWAH) ==================== -->
    <div class="bg-white rounded-2xl border border-[#EADFD3] shadow-sm overflow-hidden">
        <div class="p-5 border-b border-[#EADFD3]">
            <h4 class="text-sm font-bold text-stone-900">Riwayat Cadangan</h4>
            <p class="text-xs text-stone-400 mt-0.5">Daftar arsip cadangan basis data yang dapat diunduh dan dipulihkan</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#FAF7F2] text-stone-500 font-bold border-b border-[#EADFD3]">
                    <tr>
                        <th class="py-3.5 px-6">Tanggal</th>
                        <th class="py-3.5 px-6">Ukuran</th>
                        <th class="py-3.5 px-6">Jenis</th>
                        <th class="py-3.5 px-6">Status</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#EADFD3] text-stone-800 font-medium">
                    @forelse($backups as $b)
                        <tr class="hover:bg-stone-50/70 transition">
                            <!-- Tanggal -->
                            <td class="py-4 px-6 font-semibold text-stone-900">
                                {{ $b->waktu ? $b->waktu->format('d M Y H:i') : '-' }}
                            </td>

                            <!-- Ukuran -->
                            <td class="py-4 px-6 text-stone-600">
                                {{ number_format($b->ukuran_kb / 1000, 1) }} MB
                            </td>

                            <!-- Jenis (Otomatis / Manual) -->
                            <td class="py-4 px-6 capitalize">
                                {{ $b->jenis }}
                            </td>

                            <!-- Status -->
                            <td class="py-4 px-6">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold text-[#2E8B57] bg-[#2E8B57]/10 border border-[#2E8B57]/20">
                                    Berhasil
                                </span>
                            </td>

                            <!-- Aksi (Unduh · Pulihkan) -->
                            <td class="py-4 px-6 text-right whitespace-nowrap">
                                <button 
                                    type="button" 
                                    onclick="alert('Mengunduh arsip cadangan {{ $b->file_path }}...')" 
                                    class="text-xs font-semibold text-stone-600 hover:text-stone-900 hover:underline"
                                >
                                    Unduh
                                </button>
                                <span class="text-stone-300 mx-1.5">&middot;</span>
                                <button 
                                    type="button" 
                                    onclick="alert('Fitur Pulihkan (FR-30): Memulihkan database ke kondisi tanggal {{ $b->waktu ? $b->waktu->format('d M Y H:i') : '' }}.')" 
                                    class="text-xs font-semibold text-brand-red hover:underline"
                                >
                                    Pulihkan
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-stone-400">Belum ada riwayat cadangan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

