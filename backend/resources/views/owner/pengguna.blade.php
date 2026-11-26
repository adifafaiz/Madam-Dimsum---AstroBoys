@extends('owner.layout')

@section('title', 'Pengguna & Hak Akses - Owner')
@section('page_title', 'Pengguna & Hak Akses')

@php
    $ikonPeran = ['owner' => 'store', 'kasir' => 'receipt-text', 'dapur' => 'chef-hat'];
@endphp

@section('content')
<div class="grid grid-cols-1 2xl:grid-cols-[minmax(0,1fr)_320px] gap-6 items-start">

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
                                    <span class="font-semibold whitespace-nowrap {{ $u->is_active ? 'text-brand-ink' : '' }}">
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
                                    <button type="button" class="btn btn-ghost btn-sm !px-2.5" title="Edit akun"
                                            onclick="openModalEdit(this)"
                                            data-url="{{ route('owner.pengguna.update', $u) }}"
                                            data-id="{{ $u->id }}"
                                            data-name="{{ $u->name }}"
                                            data-username="{{ $u->username }}"
                                            data-role="{{ $u->role }}"
                                            data-self="{{ $u->id === Auth::id() ? '1' : '0' }}">
                                        <i data-lucide="pencil" class="!w-4 !h-4"></i><span class="sr-only">Edit {{ $u->name }}</span>
                                    </button>
                                    <button type="button" class="btn btn-ghost btn-sm !px-2.5" title="Reset password"
                                            onclick="openModalReset(this)"
                                            data-url="{{ route('owner.pengguna.reset', $u) }}"
                                            data-id="{{ $u->id }}"
                                            data-name="{{ $u->name }}">
                                        <i data-lucide="key-round" class="!w-4 !h-4"></i><span class="sr-only">Reset password {{ $u->name }}</span>
                                    </button>
                                    @if($u->id !== Auth::id())
                                        <form action="{{ route('owner.pengguna.toggle', $u->id) }}" method="POST" class="inline"
                                              onsubmit="return confirm(this.dataset.pesan)"
                                              data-pesan="{{ $u->is_active ? 'Nonaktifkan akun ' . $u->name . '? Ia tidak bisa masuk lagi, tetapi riwayat transaksinya tetap tersimpan.' : 'Aktifkan kembali akun ' . $u->name . '?' }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm {{ $u->is_active ? 'btn-danger' : 'btn-secondary !text-[#2E7D4F]' }}">
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
    <aside class="kartu p-6 2xl:sticky 2xl:top-[96px]" aria-labelledby="judulHakAkses">
        <h3 id="judulHakAkses" class="kartu-judul mb-4">Hak Akses Peran</h3>
        <dl class="grid grid-cols-1 md:grid-cols-3 2xl:grid-cols-1 gap-4">
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
            <input type="hidden" name="_form" value="tambah">
            <div class="modal-isi space-y-4">
                <div>
                    <label for="name" class="label">Nama lengkap</label>
                    <input type="text" name="name" id="name" value="{{ old('_form') === 'tambah' ? old('name') : '' }}" placeholder="misal: Siti Kasir" required maxlength="100" class="field">
                </div>
                <div>
                    <label for="username" class="label">Username</label>
                    <input type="text" name="username" id="username" value="{{ old('_form') === 'tambah' ? old('username') : '' }}" placeholder="misal: siti" required minlength="3" maxlength="50" pattern="[A-Za-z0-9._\-]+" class="field" autocomplete="off">
                    <p class="bantuan">Harus unik, tanpa spasi. Dipakai untuk masuk.</p>
                </div>
                <div>
                    <label for="password" class="label">Kata sandi awal</label>
                    <div class="flex gap-2">
                        <input type="text" name="password" id="password" required minlength="6" maxlength="72" class="field font-semibold" autocomplete="new-password" placeholder="minimal 6 karakter">
                        <button type="button" onclick="isiPasswordAcak('password')" class="btn btn-secondary shrink-0" title="Buat password acak"><i data-lucide="dices" class="!w-4 !h-4"></i> Acak</button>
                    </div>
                    <p class="bantuan">Catat dan sampaikan langsung ke pemilik akun.</p>
                </div>
                <div>
                    <label for="role" class="label">Peran</label>
                    <select name="role" id="role" required class="field">
                        @foreach(['kasir' => 'Kasir', 'dapur' => 'Dapur', 'owner' => 'Owner'] as $kode => $label)
                            <option value="{{ $kode }}" {{ old('_form') === 'tambah' && old('role') === $kode ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
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

        <form id="formResetPass" method="POST" action="{{ old('_form') === 'reset' && old('_user_id') ? route('owner.pengguna.reset', old('_user_id')) : '' }}">
            @csrf
            <input type="hidden" name="_form" value="reset">
            <input type="hidden" name="_user_id" id="resetUserId" value="{{ old('_form') === 'reset' ? old('_user_id') : '' }}">
            <div class="modal-isi space-y-4">
                <p>Atur kata sandi baru untuk <b id="resetUserName"></b>.</p>
                <div>
                    <label for="new_password" class="label">Password baru</label>
                    <div class="flex gap-2">
                        <input type="text" name="password" id="new_password" required minlength="6" maxlength="72" class="field font-semibold" autocomplete="off" placeholder="minimal 6 karakter">
                        <button type="button" onclick="isiPasswordAcak('new_password')" class="btn btn-secondary shrink-0" title="Buat password acak"><i data-lucide="dices" class="!w-4 !h-4"></i> Acak</button>
                    </div>
                    <p class="bantuan">Login API lama akun ini akan dikeluarkan.</p>
                </div>
            </div>
            <div class="modal-kaki">
                <button type="button" onclick="closeModalReset()" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary" data-loading-text="Menyimpan…"><i data-lucide="key-round" class="!w-4 !h-4"></i> Simpan Password Baru</button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== MODAL EDIT PENGGUNA ==================== -->
<div id="modalEditPengguna" class="modal hidden" role="dialog" aria-modal="true" aria-labelledby="judulEditPengguna">
    <div class="modal-panel max-w-md">
        <div class="modal-kepala">
            <h3 id="judulEditPengguna" class="kartu-judul">Edit Pengguna</h3>
            <button type="button" onclick="closeModalEdit()" class="btn btn-ghost btn-sm" aria-label="Tutup"><i data-lucide="x"></i></button>
        </div>

        <form id="formEditPengguna" method="POST" action="{{ old('_form') === 'edit' && old('_user_id') ? route('owner.pengguna.update', old('_user_id')) : '' }}">
            @csrf
            <input type="hidden" name="_form" value="edit">
            <input type="hidden" name="_user_id" id="editUserId" value="{{ old('_form') === 'edit' ? old('_user_id') : '' }}">
            <div class="modal-isi space-y-4">
                <div>
                    <label for="edit_name" class="label">Nama lengkap</label>
                    <input type="text" name="name" id="edit_name" value="{{ old('_form') === 'edit' ? old('name') : '' }}" required maxlength="100" class="field">
                </div>
                <div>
                    <label for="edit_username" class="label">Username</label>
                    <input type="text" name="username" id="edit_username" value="{{ old('_form') === 'edit' ? old('username') : '' }}" required minlength="3" maxlength="50" pattern="[A-Za-z0-9._\-]+" class="field" autocomplete="off">
                    <p class="bantuan">Jika diganti, pemilik akun masuk dengan username baru.</p>
                </div>
                <div>
                    <label for="edit_role" class="label">Peran</label>
                    <select name="role" id="edit_role" required class="field">
                        @foreach(['kasir' => 'Kasir', 'dapur' => 'Dapur', 'owner' => 'Owner'] as $kode => $label)
                            <option value="{{ $kode }}" {{ old('_form') === 'edit' && old('role') === $kode ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="role" id="edit_role_tetap" value="owner" disabled>
                    <p id="catatanPeranSendiri" class="bantuan hidden">Peran akun Anda sendiri tidak bisa diubah.</p>
                </div>
            </div>
            <div class="modal-kaki">
                <button type="button" onclick="closeModalEdit()" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary" data-loading-text="Menyimpan…"><i data-lucide="check"></i> Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const idSaya = '{{ Auth::id() }}';

    function openModalTambahPengguna() {
        document.getElementById('modalTambahPengguna').classList.remove('hidden');
        document.getElementById('name').focus();
    }
    function closeModalTambahPengguna() {
        document.getElementById('modalTambahPengguna').classList.add('hidden');
    }

    function aturPeranSendiri(sendiri) {
        document.getElementById('edit_role').disabled = sendiri;
        document.getElementById('edit_role_tetap').disabled = !sendiri;
        document.getElementById('catatanPeranSendiri').classList.toggle('hidden', !sendiri);
    }
    function openModalEdit(tombol) {
        const d = tombol.dataset;
        document.getElementById('formEditPengguna').action = d.url;
        document.getElementById('editUserId').value = d.id;
        document.getElementById('edit_name').value = d.name;
        document.getElementById('edit_username').value = d.username;
        document.getElementById('edit_role').value = d.role;
        aturPeranSendiri(d.self === '1');
        document.getElementById('modalEditPengguna').classList.remove('hidden');
        document.getElementById('edit_name').focus();
    }
    function closeModalEdit() {
        document.getElementById('modalEditPengguna').classList.add('hidden');
    }

    function openModalReset(tombol) {
        document.getElementById('resetUserName').textContent = tombol.dataset.name;
        document.getElementById('resetUserId').value = tombol.dataset.id;
        document.getElementById('formResetPass').action = tombol.dataset.url;
        document.getElementById('new_password').value = '';
        document.getElementById('modalResetPass').classList.remove('hidden');
        document.getElementById('new_password').focus();
    }
    function closeModalReset() {
        document.getElementById('modalResetPass').classList.add('hidden');
    }

    // Password acak 10 karakter tanpa huruf yang mirip (0/O, 1/l/I)
    function isiPasswordAcak(idInput) {
        const huruf = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        const acak = crypto.getRandomValues(new Uint32Array(10));
        const input = document.getElementById(idInput);
        input.value = Array.from(acak, n => huruf[n % huruf.length]).join('');
        input.select();
    }

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') { closeModalTambahPengguna(); closeModalEdit(); closeModalReset(); }
    });
    document.querySelectorAll('.modal').forEach(m => m.addEventListener('click', e => { if (e.target === m) m.classList.add('hidden'); }));

    // Validasi server gagal: buka lagi modal yang tadi dipakai
    @if($errors->any())
        @switch(old('_form'))
            @case('tambah') openModalTambahPengguna(); @break
            @case('edit')
                aturPeranSendiri(document.getElementById('editUserId').value === idSaya);
                document.getElementById('modalEditPengguna').classList.remove('hidden');
                @break
            @case('reset')
                document.getElementById('modalResetPass').classList.remove('hidden');
                @break
        @endswitch
    @endif
</script>
@endpush
@endsection
