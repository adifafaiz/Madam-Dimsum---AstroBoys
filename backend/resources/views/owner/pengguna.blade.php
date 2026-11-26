@extends('owner.layout')

@section('title', 'Pengguna & Hak Akses - Owner')
@section('page_title', 'Pengguna & Hak Akses')

@php
    $ikonPeran = ['owner' => 'store', 'kasir' => 'receipt-text', 'dapur' => 'chef-hat'];
@endphp

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_360px] gap-6 items-start">

    <!-- ==================== DAFTAR PENGGUNA ==================== -->
    <section class="kartu overflow-hidden" aria-labelledby="judulPengguna">
        <div class="px-6 py-5 border-b border-brand-border flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 id="judulPengguna" class="kartu-judul">Daftar Akun Pengguna</h3>
                <p class="text-[15px] text-brand-secondary">Kelola kredensial dan hak akses karyawan Madam Dimsum</p>
            </div>
            <button type="button" onclick="openModalTambahPengguna()" class="btn btn-primary">
                <i data-lucide="user-plus"></i> Tambah Pengguna
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="tabel">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Peran</th>
                        <th>Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                        <tr class="{{ !$u->is_active ? 'bg-brand-lightBg/60 text-brand-secondary' : '' }}">
                            <td>
                                <div class="flex items-center gap-3">
                                    <span class="w-9 h-9 rounded-full flex items-center justify-center font-semibold shrink-0 {{ $u->is_active ? 'bg-[#FCF1EF] text-brand-red' : 'bg-[#F3ECE4] text-brand-secondary' }}">
                                        {{ mb_strtoupper(mb_substr($u->name, 0, 1)) }}
                                    </span>
                                    <span class="font-semibold {{ $u->is_active ? 'text-brand-ink' : '' }}">
                                        {{ $u->name }}
                                        @if($u->id === Auth::id())
                                            <span class="text-[13px] text-brand-red font-medium ml-1">(Anda)</span>
                                        @endif
                                    </span>
                                </div>
                            </td>
                            <td class="text-brand-secondary">{{ $u->username }}</td>
                            <td>
                                <span class="inline-flex items-center gap-1.5 capitalize">
                                    <i data-lucide="{{ $ikonPeran[$u->role] ?? 'user' }}" class="!w-4 !h-4 text-brand-secondary"></i>
                                    {{ $u->role }}
                                </span>
                            </td>
                            <td>
                                @if($u->is_active)
                                    <span class="inline-flex items-center gap-1.5 h-7 px-2.5 rounded-full text-[13px] font-semibold bg-[#E6F3EB] text-[#2E7D4F]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#2E8B57]"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 h-7 px-2.5 rounded-full text-[13px] font-semibold bg-[#F3ECE4] text-brand-secondary">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#B9ABA0]"></span> Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1">
                                    <button type="button" onclick="openModalReset('{{ $u->id }}', '{{ $u->name }}')" class="btn btn-ghost btn-sm">
                                        <i data-lucide="key-round" class="!w-4 !h-4"></i> {{ $u->role === 'owner' ? 'Reset Password' : 'Reset' }}
                                    </button>
                                    @if($u->role !== 'owner')
                                        <form action="{{ route('owner.pengguna.toggle', $u->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button
                                                type="submit"
                                                onclick="return confirm('Apakah Anda yakin ingin {{ $u->is_active ? 'menonaktifkan (UAT-10)' : 'mengaktifkan kembali' }} akun {{ $u->name }}?')"
                                                class="btn btn-sm {{ $u->is_active ? 'btn-danger' : 'btn-secondary !text-[#2E7D4F]' }}"
                                            >
                                                <i data-lucide="{{ $u->is_active ? 'user-x' : 'user-check' }}" class="!w-4 !h-4"></i>
                                                {{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
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
                                    <i data-lucide="users"></i>
                                    <p class="kosong-judul">Belum ada akun pengguna</p>
                                    <p class="kosong-sub">Tambahkan akun untuk kasir dan dapur.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <!-- ==================== HAK AKSES PERAN ==================== -->
    <aside class="kartu p-6 xl:sticky xl:top-[96px]" aria-labelledby="judulHakAkses">
        <h3 id="judulHakAkses" class="kartu-judul mb-4">Hak Akses Peran</h3>
        <dl class="space-y-4">
            @foreach([
                ['owner', 'Owner', 'Dashboard, Produk & Stok, Pemasok, Laporan, Riwayat Transaksi, Pengguna, Pencadangan'],
                ['kasir', 'Kasir', 'Transaksi, Cek Stok, Riwayat Saya'],
                ['dapur', 'Dapur', 'Antrian Pesanan, Catat Produksi, Stok Dapur, Riwayat Produksi'],
            ] as [$kode, $nama, $akses])
                <div class="flex gap-3">
                    <span class="w-9 h-9 rounded-full bg-brand-lightBg border border-brand-border text-brand-secondary flex items-center justify-center shrink-0">
                        <i data-lucide="{{ $ikonPeran[$kode] }}" class="!w-4 !h-4"></i>
                    </span>
                    <div>
                        <dt class="font-semibold">{{ $nama }}</dt>
                        <dd class="text-[15px] text-brand-secondary">{{ $akses }}</dd>
                    </div>
                </div>
            @endforeach
        </dl>
        <p class="bantuan flex items-start gap-2 mt-5 pt-4 border-t border-brand-border">
            <i data-lucide="info" class="!w-4 !h-4 mt-0.5"></i>
            Pengguna tidak dihapus, hanya dinonaktifkan, supaya riwayat transaksinya tetap tersimpan.
        </p>
    </aside>

</div>

<!-- ==================== MODAL TAMBAH PENGGUNA ==================== -->
<div id="modalTambahPengguna" class="modal hidden" role="dialog" aria-modal="true" aria-labelledby="judulTambahPengguna">
    <div class="modal-panel max-w-md">
        <div class="modal-kepala">
            <h3 id="judulTambahPengguna" class="kartu-judul">Tambah Pengguna Baru</h3>
            <button type="button" onclick="closeModalTambahPengguna()" class="btn btn-ghost btn-sm" aria-label="Tutup"><i data-lucide="x"></i></button>
        </div>

        <form action="{{ route('owner.pengguna.store') }}" method="POST">
            @csrf
            <div class="modal-isi space-y-4">
                <div>
                    <label for="name" class="label">Nama lengkap</label>
                    <input type="text" name="name" id="name" placeholder="misal: Siti Kasir" required class="field">
                </div>
                <div>
                    <label for="username" class="label">Username</label>
                    <input type="text" name="username" id="username" placeholder="misal: siti" required class="field" autocomplete="off">
                    <p class="bantuan">Harus unik, dipakai untuk masuk.</p>
                </div>
                <div>
                    <label for="password" class="label">Kata sandi awal</label>
                    <input type="password" name="password" id="password" value="password123" required class="field" autocomplete="new-password">
                </div>
                <div>
                    <label for="role" class="label">Peran</label>
                    <select name="role" id="role" required class="field">
                        <option value="kasir">Kasir</option>
                        <option value="dapur">Dapur</option>
                        <option value="owner">Owner</option>
                    </select>
                </div>
            </div>
            <div class="modal-kaki">
                <button type="button" onclick="closeModalTambahPengguna()" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary" data-loading-text="Menyimpan…"><i data-lucide="check"></i> Simpan Pengguna</button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== MODAL RESET PASSWORD ==================== -->
<div id="modalResetPass" class="modal hidden" role="dialog" aria-modal="true" aria-labelledby="judulReset">
    <div class="modal-panel max-w-md">
        <div class="modal-kepala">
            <h3 id="judulReset" class="kartu-judul">Reset Password</h3>
            <button type="button" onclick="closeModalReset()" class="btn btn-ghost btn-sm" aria-label="Tutup"><i data-lucide="x"></i></button>
        </div>

        <form id="formResetPass" method="POST">
            @csrf
            <div class="modal-isi space-y-4">
                <p>Atur kata sandi baru untuk <b id="resetUserName"></b>.</p>
                <div>
                    <label for="new_password" class="label">Password baru</label>
                    <input type="text" name="password" id="new_password" value="password123" required class="field font-semibold" autocomplete="off">
                </div>
            </div>
            <div class="modal-kaki">
                <button type="button" onclick="closeModalReset()" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary" data-loading-text="Menyimpan…"><i data-lucide="key-round" class="!w-4 !h-4"></i> Simpan Password Baru</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openModalTambahPengguna() {
        document.getElementById('modalTambahPengguna').classList.remove('hidden');
        document.getElementById('name').focus();
    }
    function closeModalTambahPengguna() {
        document.getElementById('modalTambahPengguna').classList.add('hidden');
    }
    function openModalReset(userId, name) {
        document.getElementById('resetUserName').textContent = name;
        document.getElementById('formResetPass').action = `/owner/pengguna/${userId}/reset`;
        document.getElementById('modalResetPass').classList.remove('hidden');
        document.getElementById('new_password').select();
    }
    function closeModalReset() {
        document.getElementById('modalResetPass').classList.add('hidden');
    }
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') { closeModalTambahPengguna(); closeModalReset(); }
    });
    document.querySelectorAll('.modal').forEach(m => m.addEventListener('click', e => { if (e.target === m) m.classList.add('hidden'); }));
</script>
@endpush
@endsection
