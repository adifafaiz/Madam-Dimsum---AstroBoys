@extends('owner.layout')

@section('title', 'Pengguna & Hak Akses - Owner')
@section('page_title', 'Pengguna & Hak Akses')

@section('content')
<div class="space-y-6 max-w-7xl">

    <!-- ==================== TABEL PENGGUNA (FRAME 8) ==================== -->
    <div class="bg-white rounded-2xl border border-[#EADFD3] shadow-sm overflow-hidden">
        <div class="p-5 border-b border-[#EADFD3] flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-stone-900">Daftar Akun Pengguna</h3>
                <p class="text-xs text-stone-400 mt-0.5">Kelola kredensial dan hak akses karyawan Madam Dimsum</p>
            </div>
            <!-- Tombol Tambah Pengguna -->
            <button 
                type="button" 
                onclick="openModalTambahPengguna()" 
                class="px-4 py-2 rounded-lg bg-brand-red hover:bg-brand-redHover text-white text-xs font-semibold shadow-sm transition flex items-center gap-1.5"
            >
                <span>+ Tambah Pengguna</span>
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#FAF7F2] text-stone-500 font-bold border-b border-[#EADFD3]">
                    <tr>
                        <th class="py-3.5 px-6">Nama</th>
                        <th class="py-3.5 px-6">Username</th>
                        <th class="py-3.5 px-6">Peran</th>
                        <th class="py-3.5 px-6">Status</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#EADFD3] text-stone-800 font-medium">
                    @forelse($users as $u)
                        <tr class="hover:bg-stone-50/70 transition {{ !$u->is_active ? 'opacity-60 bg-stone-50/40' : '' }}">
                            <!-- Nama -->
                            <td class="py-4 px-6 font-bold text-stone-900">
                                {{ $u->name }}
                                @if($u->id === Auth::id())
                                    <span class="text-[10px] text-brand-red font-semibold ml-1.5">(Anda)</span>
                                @endif
                            </td>

                            <!-- Username -->
                            <td class="py-4 px-6 font-mono text-[11px] text-stone-600">
                                {{ $u->username }}
                            </td>

                            <!-- Peran -->
                            <td class="py-4 px-6">
                                <span class="capitalize font-semibold text-stone-800">
                                    {{ $u->role }}
                                </span>
                            </td>

                            <!-- Status (Aktif / Nonaktif - BR-10, UAT-10) -->
                            <td class="py-4 px-6">
                                @if($u->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold text-[#2E8B57] bg-[#2E8B57]/10 border border-[#2E8B57]/20">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold text-red-600 bg-red-100 border border-red-200">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="py-4 px-6 text-right whitespace-nowrap">
                                @if($u->role === 'owner')
                                    <button 
                                        type="button" 
                                        onclick="openModalReset('{{ $u->id }}', '{{ $u->name }}')" 
                                        class="text-xs font-semibold text-stone-600 hover:text-stone-900 hover:underline"
                                    >
                                        Reset Password
                                    </button>
                                @else
                                    <button 
                                        type="button" 
                                        onclick="openModalReset('{{ $u->id }}', '{{ $u->name }}')" 
                                        class="text-xs font-semibold text-stone-600 hover:text-stone-900 hover:underline"
                                    >
                                        Reset
                                    </button>
                                    <span class="text-stone-300 mx-1.5">&middot;</span>
                                    <form action="{{ route('owner.pengguna.toggle', $u->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button 
                                            type="submit" 
                                            onclick="return confirm('Apakah Anda yakin ingin {{ $u->is_active ? 'menonaktifkan (UAT-10)' : 'mengaktifkan kembali' }} akun {{ $u->name }}?')" 
                                            class="text-xs font-semibold {{ $u->is_active ? 'text-brand-red hover:underline' : 'text-emerald-600 hover:underline' }}"
                                        >
                                            {{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-stone-400">Belum ada akun pengguna.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ==================== RINGKASAN HAK AKSES PERAN (FRAME 8 BAWAH) ==================== -->
    <div class="bg-white p-6 rounded-2xl border border-[#EADFD3] shadow-sm">
        <h4 class="text-xs font-bold uppercase tracking-wider text-stone-900 mb-3">Hak Akses Peran</h4>
        
        <div class="space-y-2 text-xs text-stone-600">
            <p><b class="text-stone-900 font-semibold">Owner:</b> Dashboard, Produk & Stok, Pemasok, Laporan, Riwayat Transaksi, Pengguna, Pencadangan</p>
            <p><b class="text-stone-900 font-semibold">Kasir:</b> Transaksi, Cek Stok, Riwayat Saya</p>
            <p><b class="text-stone-900 font-semibold">Dapur:</b> Antrian Pesanan, Catat Produksi, Stok Dapur, Riwayat Produksi</p>
        </div>

        <p class="text-[11px] text-stone-400 italic mt-4 pt-3 border-t border-stone-100">
            *Pengguna tidak dihapus melainkan dinonaktifkan agar riwayat transaksi tetap terjaga (BR-10).
        </p>
    </div>

</div>

<!-- Modal Tambah Pengguna (FR-29) -->
<div id="modalTambahPengguna" class="fixed inset-0 bg-stone-900/50 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden transition-all">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 sm:p-7 border border-[#EADFD3] shadow-2xl relative">
        <div class="flex items-center justify-between pb-3 border-b border-stone-100 mb-4">
            <h3 class="text-base font-bold text-stone-900">Tambah Pengguna Baru</h3>
            <button type="button" onclick="closeModalTambahPengguna()" class="text-stone-400 hover:text-stone-700 p-1">✕</button>
        </div>

        <form action="{{ route('owner.pengguna.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="name" class="block text-xs font-semibold text-stone-600 mb-1.5">Nama Lengkap</label>
                <input type="text" name="name" id="name" placeholder="misal: Siti Kasir" required class="w-full px-3.5 py-2.5 bg-white border border-[#EADFD3] rounded-lg text-xs placeholder-stone-400 focus:outline-none focus:ring-1 focus:ring-brand-red">
            </div>

            <div>
                <label for="username" class="block text-xs font-semibold text-stone-600 mb-1.5">Username (Unik)</label>
                <input type="text" name="username" id="username" placeholder="misal: siti" required class="w-full px-3.5 py-2.5 bg-white border border-[#EADFD3] rounded-lg text-xs placeholder-stone-400 focus:outline-none focus:ring-1 focus:ring-brand-red">
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-stone-600 mb-1.5">Kata Sandi Awal</label>
                <input type="password" name="password" id="password" value="password123" required class="w-full px-3.5 py-2.5 bg-white border border-[#EADFD3] rounded-lg text-xs placeholder-stone-400 focus:outline-none focus:ring-1 focus:ring-brand-red">
            </div>

            <div>
                <label for="role" class="block text-xs font-semibold text-stone-600 mb-1.5">Peran (Role)</label>
                <select name="role" id="role" required class="w-full px-3.5 py-2.5 bg-white border border-[#EADFD3] rounded-lg text-xs font-medium text-stone-800 focus:outline-none focus:ring-1 focus:ring-brand-red cursor-pointer">
                    <option value="kasir">Kasir</option>
                    <option value="dapur">Dapur</option>
                    <option value="owner">Owner</option>
                </select>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-stone-100">
                <button type="button" onclick="closeModalTambahPengguna()" class="px-4 py-2.5 rounded-lg border border-[#EADFD3] text-xs font-semibold text-stone-600 hover:bg-stone-50">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-brand-red hover:bg-brand-redHover text-white text-xs font-semibold shadow-sm">Simpan Pengguna</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Reset Password (FR-04, FR-29) -->
<div id="modalResetPass" class="fixed inset-0 bg-stone-900/50 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden transition-all">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 border border-[#EADFD3] shadow-2xl relative">
        <div class="flex items-center justify-between pb-3 border-b border-stone-100 mb-4">
            <h3 class="text-base font-bold text-stone-900">Reset Password Pengguna</h3>
            <button type="button" onclick="closeModalReset()" class="text-stone-400 hover:text-stone-700 p-1">✕</button>
        </div>

        <form id="formResetPass" method="POST" class="space-y-4">
            @csrf
            <p class="text-xs text-stone-600">
                Reset kata sandi untuk pengguna <b id="resetUserName" class="text-stone-900"></b> (FR-04).
            </p>

            <div>
                <label for="new_password" class="block text-xs font-semibold text-stone-600 mb-1.5">Password Baru</label>
                <input type="text" name="password" id="new_password" value="password123" required class="w-full px-3.5 py-2.5 bg-white border border-[#EADFD3] rounded-lg text-xs font-bold text-stone-900 focus:outline-none focus:ring-1 focus:ring-brand-red">
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-stone-100">
                <button type="button" onclick="closeModalReset()" class="px-4 py-2.5 rounded-lg border border-[#EADFD3] text-xs font-semibold text-stone-600 hover:bg-stone-50">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-brand-red hover:bg-brand-redHover text-white text-xs font-semibold shadow-sm">Simpan Password Baru</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openModalTambahPengguna() {
        document.getElementById('modalTambahPengguna').classList.remove('hidden');
    }
    function closeModalTambahPengguna() {
        document.getElementById('modalTambahPengguna').classList.add('hidden');
    }
    function openModalReset(userId, name) {
        document.getElementById('resetUserName').textContent = name;
        document.getElementById('formResetPass').action = `/owner/pengguna/${userId}/reset`;
        document.getElementById('modalResetPass').classList.remove('hidden');
    }
    function closeModalReset() {
        document.getElementById('modalResetPass').classList.add('hidden');
    }
</script>
@endpush
@endsection

