<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Kasir') - Madam Dimsum</title>
    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            red: '#C8372D',
                            redHover: '#B22F26',
                            redPink: '#E9A9A1',
                            dark: '#2A211C',
                            darkSidebar: '#2A211C',
                            menuText: '#CDBFB3',
                            lightBg: '#FBF7F2',
                            border: '#EADFD3',
                            secondary: '#8A7B70',
                            safe: '#2E8B57',
                            process: '#D9822B',
                        }
                    },
                    fontFamily: {
                        sans: ['"Inter"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #FBF7F2;
            color: #2A211C;
        }
    </style>
</head>
<body class="min-h-screen flex antialiased">

    <!-- ==================== SIDEBAR KASIR (220px) ==================== -->
    <aside class="w-[220px] bg-brand-dark flex flex-col justify-between shrink-0 min-h-screen text-white select-none fixed left-0 top-0 bottom-0 z-20">
        <div>
            <!-- Header Brand / Logo -->
            <div class="h-16 flex items-center gap-3 px-5 border-b border-white/5">
                <img src="{{ asset('images/logo-madam-dimsum.jpg') }}" alt="Madam Dimsum" class="w-8 h-8 rounded-lg shadow-sm object-cover">
                <span class="font-bold text-white tracking-tight text-base">Madam Dimsum</span>
            </div>

            <!-- Menu Navigasi Modul Kasir -->
            <nav class="p-3 space-y-1 text-[13px] font-medium">
                <!-- 1. Transaksi -->
                <a href="{{ route('kasir.transaksi') }}" 
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition {{ request()->routeIs('kasir.transaksi*') ? 'bg-brand-red text-white font-semibold shadow-sm' : 'text-brand-menuText hover:text-white hover:bg-white/5' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    <span>Transaksi</span>
                </a>

                <!-- 2. Cek Stok -->
                <a href="{{ route('kasir.cek-stok') }}" 
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition {{ request()->routeIs('kasir.cek-stok*') ? 'bg-brand-red text-white font-semibold shadow-sm' : 'text-brand-menuText hover:text-white hover:bg-white/5' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    <span>Cek Stok</span>
                </a>

                <!-- 3. Riwayat Saya -->
                <a href="{{ route('kasir.riwayat') }}" 
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition {{ request()->routeIs('kasir.riwayat*') ? 'bg-brand-red text-white font-semibold shadow-sm' : 'text-brand-menuText hover:text-white hover:bg-white/5' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Riwayat Saya</span>
                </a>
            </nav>
        </div>

        <!-- Footer Sidebar -->
        <div class="p-4 border-t border-white/5 text-[11px] text-stone-500">
            DimsumKu v2 &middot; Tim Astroboys
        </div>
    </aside>

    <!-- ==================== AREA UTAMA KANAN ==================== -->
    <div class="ml-[220px] flex-1 flex flex-col min-w-0">

        <!-- Top Header Bar -->
        <header class="h-16 bg-brand-lightBg border-b border-[#EADFD3] px-8 flex items-center justify-between sticky top-0 z-10">
            <h1 class="text-lg font-bold text-stone-900 tracking-tight">
                @yield('page_title', 'Pencatatan Transaksi')
            </h1>

            <!-- Akun Dropdown Kasir -->
            <div class="relative">
                <button type="button" onclick="toggleUserDropdown()" 
                    class="flex items-center gap-2 px-3.5 py-1.5 rounded-full border border-[#EADFD3] bg-white text-xs font-semibold text-stone-800 hover:bg-stone-50 shadow-sm transition">
                    <span>{{ Auth::user()->name ?? 'Kasir Rina' }}</span>
                    <svg class="w-3.5 h-3.5 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div id="userDropdown" class="hidden absolute right-0 mt-2 w-44 bg-white rounded-xl shadow-lg border border-[#EADFD3] py-1 z-30">
                    <div class="px-4 py-2 border-b border-stone-100">
                        <p class="text-xs font-bold text-stone-900">{{ Auth::user()->name ?? 'Rina' }}</p>
                        <p class="text-[11px] text-stone-500">Peran: Kasir</p>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-xs font-semibold text-brand-red hover:bg-red-50 flex items-center gap-2 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Konten Halaman Kasir -->
        <main class="flex-1 p-8">
            <!-- Flash Notification Success -->
            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-medium">{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 text-xs font-bold">&times;</button>
                </div>
            @endif

            <!-- Errors Notification -->
            @if($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-brand-red text-sm">
                    <ul class="list-disc list-inside space-y-1 font-medium">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Script Dropdown -->
    <script>
        function toggleUserDropdown() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('hidden');
        }

        window.addEventListener('click', function(e) {
            const dropdown = document.getElementById('userDropdown');
            if (dropdown && !e.target.closest('.relative')) {
                dropdown.classList.add('hidden');
            }
        });
    </script>
    @stack('scripts')
</body>
</html>

