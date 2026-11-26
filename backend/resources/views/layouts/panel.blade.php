{{-- Layout bersama halaman Owner, Kasir & Dapur. Data dari @extends: $peran, $ikonPeran,
     $menu (route, icon, label, opsional aktif = daftar pola route yang menandai menu aktif). --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', $peran) - Madam Dimsum</title>
    @include('partials.head')
    @stack('styles')
</head>
<body class="min-h-screen bg-brand-lightBg antialiased">

    <!-- ==================== SIDEBAR ==================== -->
    <aside class="w-[240px] bg-brand-dark flex flex-col justify-between text-white select-none fixed inset-y-0 left-0 z-20">
        <div>
            <div class="h-[72px] flex items-center gap-3 px-5 border-b border-white/[0.06]">
                @include('partials.logo', ['class' => 'w-10 h-10 p-0.5'])
                <div class="leading-tight">
                    <p class="font-display text-[17px] font-semibold">Madam Dimsum</p>
                    <p class="text-xs text-white/45">{{ $peran }}</p>
                </div>
            </div>

            <nav class="p-3 space-y-1">
                @foreach($menu as $m)
                    @php $aktif = request()->routeIs(...($m['aktif'] ?? [$m['route'].'*'])); @endphp
                    <a href="{{ route($m['route']) }}"
                       @if($aktif) aria-current="page" @endif
                       class="flex items-center gap-3 h-11 px-3.5 rounded text-[15px] transition-colors {{ $aktif ? 'bg-brand-red text-white font-semibold' : 'text-[#CDBFB3] hover:text-white hover:bg-white/[0.06]' }}">
                        <i data-lucide="{{ $m['icon'] }}"></i>
                        <span>{{ $m['label'] }}</span>
                    </a>
                @endforeach
            </nav>
        </div>

        <div class="px-5 py-4 border-t border-white/[0.06] text-xs text-white/35">
            DimsumKu v2 &middot; Tim AstroBoys
        </div>
    </aside>

    <!-- ==================== AREA UTAMA ==================== -->
    <div class="ml-[240px] min-w-0 flex flex-col min-h-screen">

        <header class="min-h-[72px] bg-brand-lightBg/95 backdrop-blur border-b border-brand-border px-6 lg:px-8 py-3 flex items-center gap-6 sticky top-0 z-10">
            <h1 class="font-display text-3xl font-semibold text-brand-ink mr-auto">
                @yield('page_title', $peran)
            </h1>

            @yield('header_extra')

            <div class="relative" id="userMenu">
                <button type="button" onclick="toggleUserDropdown()"
                    class="flex items-center gap-2 h-11 pl-2 pr-3 rounded border border-brand-border bg-white text-[15px] font-medium text-brand-ink hover:border-[#D9C8B8] transition-colors">
                    <span class="w-7 h-7 rounded-full bg-brand-redSoft text-brand-red flex items-center justify-center">
                        <i data-lucide="{{ $ikonPeran }}" class="!w-4 !h-4"></i>
                    </span>
                    <span>{{ Auth::user()->name ?? $peran }}</span>
                    <i data-lucide="chevron-down" class="!w-4 !h-4 text-brand-secondary"></i>
                </button>

                <div id="userDropdown" class="hidden absolute right-0 mt-2 w-52 bg-white rounded border border-brand-border shadow-lg py-1 z-30">
                    <div class="px-4 py-2.5 border-b border-brand-border">
                        <p class="font-semibold text-brand-ink">{{ Auth::user()->name ?? $peran }}</p>
                        <p class="text-[13px] text-brand-secondary">Peran: {{ $peran }}</p>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full h-11 text-left px-4 text-[15px] font-medium text-brand-red hover:bg-[#FCF1EF] flex items-center gap-2.5 transition-colors">
                            <i data-lucide="log-out" class="!w-4 !h-4"></i>
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="flex-1 px-6 lg:px-8 py-6">
            @if(session('success'))
                <div class="mb-6 px-4 py-3 rounded border border-emerald-200 bg-emerald-50 text-emerald-800 flex items-center gap-3" role="status">
                    <i data-lucide="circle-check" class="text-emerald-600"></i>
                    <span class="font-medium flex-1">{{ session('success') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" class="w-8 h-8 flex items-center justify-center rounded text-emerald-700 hover:bg-emerald-100" aria-label="Tutup">
                        <i data-lucide="x" class="!w-4 !h-4"></i>
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 px-4 py-3 rounded border border-[#EFC5C0] bg-[#FCF1EF] text-brand-red flex items-start gap-3" role="alert">
                    <i data-lucide="circle-alert" class="mt-0.5"></i>
                    <div class="space-y-0.5 font-medium">
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        function toggleUserDropdown() {
            document.getElementById('userDropdown').classList.toggle('hidden');
        }

        window.addEventListener('click', function (e) {
            if (!e.target.closest('#userMenu')) {
                document.getElementById('userDropdown').classList.add('hidden');
            }
        });

        // Tombol kirim form: tampilkan status memuat & cegah klik ganda.
        // Pakai: <button type="submit" data-loading-text="Menyimpan…">
        document.addEventListener('submit', function (e) {
            const btn = e.target.querySelector('button[type="submit"][data-loading-text]');
            if (!btn) return;
            btn.disabled = true;
            btn.innerHTML = '<i data-lucide="loader-circle" class="animate-spin"></i><span>' + btn.dataset.loadingText + '</span>';
            window.renderIcons();
        });
    </script>
    @stack('scripts')
</body>
</html>
