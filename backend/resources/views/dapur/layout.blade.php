<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dapur') - Madam Dimsum</title>
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

    <!-- ==================== SIDEBAR DAPUR (220px) ==================== -->
    <aside class="w-[220px] bg-brand-dark flex flex-col justify-between shrink-0 min-h-screen text-white select-none fixed left-0 top-0 bottom-0 z-20">
        <div>
            <!-- Header Brand / Logo -->
            <div class="h-16 flex items-center gap-3 px-5 border-b border-white/5">
                <img src="{{ asset('images/logo-madam-dimsum.jpg') }}" alt="Madam Dimsum" class="w-8 h-8 rounded-lg shadow-sm object-cover">
                <span class="font-bold text-white tracking-tight text-base">Madam Dimsum</span>
            </div>

            <!-- Menu Navigasi Modul Dapur (Mockup Frame 1-4) -->
            <nav class="p-3 space-y-1 text-[13px] font-medium">
                <!-- 1. Antrian Pesanan -->
                <a href="{{ route('dapur.antrian') }}" 
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition {{ request()->routeIs('dapur.antrian*') ? 'bg-brand-red text-white font-semibold shadow-sm' : 'text-brand-menuText hover:text-white hover:bg-white/5' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <span>Antrian Pesanan</span>
                </a>

                <!-- 2. Catat Produksi -->
                <a href="{{ route('dapur.catat-produksi') }}" 
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition {{ request()->routeIs('dapur.catat-produksi*') ? 'bg-brand-red text-white font-semibold shadow-sm' : 'text-brand-menuText hover:text-white hover:bg-white/5' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Catat Produksi</span>
                </a>

                <!-- 3. Stok -->
                <a href="{{ route('dapur.stok') }}" 
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition {{ request()->routeIs('dapur.stok*') ? 'bg-brand-red text-white font-semibold shadow-sm' : 'text-brand-menuText hover:text-white hover:bg-white/5' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <span>Stok</span>
                </a>

                <!-- 4. Riwayat Produksi -->
                <a href="{{ route('dapur.riwayat-produksi') }}" 
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition {{ request()->routeIs('dapur.riwayat-produksi*') ? 'bg-brand-red text-white font-semibold shadow-sm' : 'text-brand-menuText hover:text-white hover:bg-white/5' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Riwayat Produksi</span>
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
                @yield('page_title', 'Antrian Pesanan')
            </h1>

            <!-- Akun Dropdown Dapur -->
            <div class="relative">
                <button type="button" onclick="toggleUserDropdown()" 
                    class="flex items-center gap-2 px-3.5 py-1.5 rounded-full border border-[#EADFD3] bg-white text-xs font-semibold text-stone-800 hover:bg-stone-50 shadow-sm transition">
                    <span>{{ Auth::user()->name ?? 'Dapur Agus' }}</span>
                    <svg class="w-3.5 h-3.5 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Dropdown Box -->
                <div id="userDropdown" class="hidden absolute right-0 mt-2 w-44 bg-white rounded-xl shadow-lg border border-[#EADFD3] py-1 z-30">
                    <div class="px-4 py-2 border-b border-stone-100">
                        <p class="text-xs font-bold text-stone-900">{{ Auth::user()->name ?? 'Agus' }}</p>
                        <p class="text-[11px] text-stone-500">Peran: Dapur</p>
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

        <!-- Konten Halaman Dapur -->
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

