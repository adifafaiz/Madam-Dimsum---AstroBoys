<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Madam Dimsum</title>
    @include('partials.head')
    <style>
        .role-card {
            display: flex; flex-direction: column; align-items: flex-start; gap: .5rem;
            min-height: 128px; padding: 1rem .875rem; text-align: left;
            background: #fff; border: 2px solid #EADFD3; border-radius: 6px;
            color: #2A211C; transition: border-color .15s, background-color .15s;
        }
        .role-card:hover { border-color: #D9C8B8; }
        .role-card:focus-visible { outline: 3px solid rgba(200,55,45,.25); outline-offset: 2px; }
        .role-card .role-icon { color: #8A7B70; }
        .role-card[aria-checked="true"] { border-color: #C8372D; background: #FCF1EF; }
        .role-card[aria-checked="true"] .role-icon { color: #C8372D; }
        .field {
            width: 100%; height: 48px; background: #fff; border: 1px solid #EADFD3; border-radius: 6px;
            font-size: 16px; color: #2A211C; transition: border-color .15s, box-shadow .15s;
        }
        .field::placeholder { color: #B9ABA0; }
        .field:focus { outline: none; border-color: #C8372D; box-shadow: 0 0 0 3px rgba(200,55,45,.15); }
    </style>
</head>
<body class="bg-brand-lightBg antialiased min-h-screen relative overflow-x-hidden">

    {{-- Motif tutup kukusan di dua sudut berlawanan --}}
    <div class="pointer-events-none fixed -top-[180px] -left-[180px] w-[420px] h-[420px] sm:-top-[220px] sm:-left-[220px] sm:w-[600px] sm:h-[600px]">
        @include('partials.kukusan-motif', ['id' => 'kukusan-a', 'class' => 'w-full h-full'])
    </div>
    <div class="pointer-events-none fixed -bottom-[200px] -right-[200px] w-[460px] h-[460px] sm:-bottom-[260px] sm:-right-[260px] sm:w-[680px] sm:h-[680px]">
        @include('partials.kukusan-motif', ['id' => 'kukusan-b', 'class' => 'w-full h-full rotate-12'])
    </div>

    <main class="relative min-h-screen flex items-center justify-center px-5 py-12">
        <div class="w-full max-w-[460px]">

            <header class="text-center mb-8">
                @include('partials.logo', ['class' => 'w-24 h-24 mx-auto p-1 border border-brand-border'])
                <p id="sapaan" class="mt-5 text-base font-medium text-brand-red">Selamat datang</p>
                <h1 class="font-display text-3xl font-semibold text-brand-ink mt-1">Masuk ke Madam Dimsum</h1>
                <p class="text-brand-secondary mt-1.5">Produksi tertata, stok terjaga.</p>
            </header>

            @if(session('success'))
                <div class="mb-5 px-4 py-3 rounded border border-emerald-200 bg-emerald-50 text-emerald-800 flex items-start gap-2.5">
                    <i data-lucide="circle-check" class="mt-0.5 text-emerald-600"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-5 px-4 py-3 rounded border border-[#EFC5C0] bg-[#FCF1EF] text-brand-red flex items-start gap-2.5" role="alert">
                    <i data-lucide="circle-alert" class="mt-0.5"></i>
                    <div class="space-y-0.5">
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" id="loginForm" class="space-y-5">
                @csrf

                {{-- Peran --}}
                <fieldset>
                    <legend class="text-[15px] font-medium text-brand-ink mb-2">Masuk sebagai</legend>
                    <input type="hidden" name="role" id="roleInput" value="{{ old('role', 'dapur') }}">

                    <div class="grid grid-cols-3 gap-2.5" role="radiogroup" aria-label="Peran">
                        <button type="button" id="role-owner" onclick="selectRole('owner')" role="radio" class="role-card">
                            <i data-lucide="store" class="role-icon !w-7 !h-7"></i>
                            <span class="font-display text-lg font-semibold leading-none mt-1">Owner</span>
                            <span class="text-[13px] leading-snug text-brand-secondary">Lihat semua laporan</span>
                        </button>
                        <button type="button" id="role-kasir" onclick="selectRole('kasir')" role="radio" class="role-card">
                            <i data-lucide="receipt-text" class="role-icon !w-7 !h-7"></i>
                            <span class="font-display text-lg font-semibold leading-none mt-1">Kasir</span>
                            <span class="text-[13px] leading-snug text-brand-secondary">Catat pesanan &amp; bayar</span>
                        </button>
                        <button type="button" id="role-dapur" onclick="selectRole('dapur')" role="radio" class="role-card">
                            <i data-lucide="chef-hat" class="role-icon !w-7 !h-7"></i>
                            <span class="font-display text-lg font-semibold leading-none mt-1">Dapur</span>
                            <span class="text-[13px] leading-snug text-brand-secondary">Antrian &amp; produksi</span>
                        </button>
                    </div>
                </fieldset>

                {{-- Username --}}
                <div>
                    <label for="username" class="block text-[15px] font-medium text-brand-ink mb-2">Username</label>
                    <div class="relative">
                        <i data-lucide="user-round" class="absolute left-4 top-1/2 -translate-y-1/2 text-brand-secondary pointer-events-none"></i>
                        <input
                            type="text"
                            name="username"
                            id="username"
                            value="{{ old('username') }}"
                            placeholder="Masukkan username"
                            required
                            autocomplete="username"
                            class="field pl-11 pr-4"
                        >
                    </div>
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-[15px] font-medium text-brand-ink mb-2">Password</label>
                    <div class="relative">
                        <i data-lucide="lock-keyhole" class="absolute left-4 top-1/2 -translate-y-1/2 text-brand-secondary pointer-events-none"></i>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            placeholder="Masukkan password"
                            required
                            autocomplete="current-password"
                            class="field pl-11 pr-12"
                        >
                        <button
                            type="button"
                            onclick="togglePasswordVisibility()"
                            class="absolute right-1.5 top-1/2 -translate-y-1/2 w-10 h-10 flex items-center justify-center text-brand-secondary hover:text-brand-ink rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-red/30"
                            title="Lihat/Sembunyikan Password"
                            aria-label="Lihat atau sembunyikan password"
                        >
                            <span id="eyeIconOpen"><i data-lucide="eye"></i></span>
                            <span id="eyeIconClosed" class="hidden"><i data-lucide="eye-off"></i></span>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2.5 cursor-pointer select-none text-brand-ink text-[15px]">
                        <input type="checkbox" name="remember" id="remember" class="w-[18px] h-[18px] accent-brand-red">
                        Ingat saya
                    </label>
                    <a href="javascript:void(0)" onclick="alert('Silakan hubungi Owner untuk mereset kata sandi Anda.')" class="text-brand-red hover:underline underline-offset-2 text-[15px] font-medium">
                        Lupa password?
                    </a>
                </div>

                <button
                    type="submit"
                    id="submitBtn"
                    class="w-full h-12 px-4 bg-brand-red hover:bg-brand-redHover disabled:opacity-70 disabled:cursor-wait text-white font-semibold rounded transition-colors text-base flex items-center justify-center gap-2"
                >
                    <span id="submitBtnIcon"><i data-lucide="log-in"></i></span>
                    <span id="submitBtnText">Masuk sebagai Dapur</span>
                </button>

                <p class="text-center text-brand-secondary text-[15px]">
                    Belum punya akun? <span class="text-brand-ink font-medium">Hubungi Owner.</span>
                </p>
            </form>

            @env('local')
                <p class="mt-8 pt-4 border-t border-dashed border-brand-border text-center text-[13px] text-brand-secondary leading-relaxed">
                    Akun demo (password <code class="font-mono">password123</code>):
                    <button type="button" onclick="quickFill('owner', 'owner', 'password123')" class="underline underline-offset-2 hover:text-brand-red">owner</button> &middot;
                    <button type="button" onclick="quickFill('kasir', 'kasir', 'password123')" class="underline underline-offset-2 hover:text-brand-red">kasir</button> &middot;
                    <button type="button" onclick="quickFill('dapur', 'agus', 'password123')" class="underline underline-offset-2 hover:text-brand-red">agus (dapur)</button>
                </p>
            @endenv

            <p class="mt-8 text-center text-[13px] text-brand-secondary/70">&copy; 2026 Madam Dimsum &middot; Tim AstroBoys</p>
        </div>
    </main>

    <script>
        const roleTitles = { owner: 'Owner', kasir: 'Kasir', dapur: 'Dapur' };

        function selectRole(role) {
            document.getElementById('roleInput').value = role;
            Object.keys(roleTitles).forEach(r => {
                document.getElementById('role-' + r).setAttribute('aria-checked', r === role ? 'true' : 'false');
            });
            document.getElementById('submitBtnText').textContent = 'Masuk sebagai ' + roleTitles[role];
        }

        function togglePasswordVisibility() {
            const passInput = document.getElementById('password');
            const show = passInput.type === 'password';
            passInput.type = show ? 'text' : 'password';
            document.getElementById('eyeIconOpen').classList.toggle('hidden', show);
            document.getElementById('eyeIconClosed').classList.toggle('hidden', !show);
        }

        function quickFill(role, username, password) {
            selectRole(role);
            document.getElementById('username').value = username;
            document.getElementById('password').value = password;
        }

        // Sapaan sesuai jam di perangkat pengguna.
        function setSapaan() {
            const h = new Date().getHours();
            const waktu = h < 11 ? 'pagi' : h < 15 ? 'siang' : h < 18 ? 'sore' : 'malam';
            document.getElementById('sapaan').textContent = 'Selamat ' + waktu;
        }

        // Status tombol saat dikirim, supaya tidak diklik dua kali.
        document.getElementById('loginForm').addEventListener('submit', () => {
            document.getElementById('submitBtn').disabled = true;
            document.getElementById('submitBtnIcon').innerHTML = '<i data-lucide="loader-circle" class="animate-spin"></i>';
            document.getElementById('submitBtnText').textContent = 'Memeriksa…';
            window.renderIcons();
        });

        // Kembali lewat tombol Back: pulihkan tombol.
        window.addEventListener('pageshow', (e) => {
            if (!e.persisted) return;
            document.getElementById('submitBtn').disabled = false;
            document.getElementById('submitBtnIcon').innerHTML = '<i data-lucide="log-in"></i>';
            selectRole(document.getElementById('roleInput').value || 'dapur');
            window.renderIcons();
        });

        window.addEventListener('DOMContentLoaded', () => {
            setSapaan();
            selectRole(document.getElementById('roleInput').value || 'dapur');
        });
    </script>
</body>
</html>
