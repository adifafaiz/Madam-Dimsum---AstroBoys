<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak - DimsumKu</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            red: '#C8372D',
                            redHover: '#B22F26',
                            dark: '#2A211C',
                            lightBg: '#FBF7F2',
                            warning: '#D9822B',
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-brand-lightBg text-stone-800 min-h-screen flex items-center justify-center p-6 font-sans">
    <div class="max-w-md w-full bg-white rounded-2xl p-8 border border-[#EADFD3] shadow-sm text-center">
        <!-- Warning Icon -->
        <div class="w-16 h-16 rounded-full bg-red-100/60 border border-red-200/50 flex items-center justify-center mx-auto mb-5 text-brand-red">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-stone-900 tracking-tight mb-2">Akses Ditolak</h1>
        <p class="text-stone-600 text-sm mb-6 leading-relaxed">
            Anda terdaftar sebagai peran <span class="font-semibold text-brand-red uppercase">{{ $userRole ?? 'Pengguna' }}</span> dan tidak memiliki izin untuk membuka halaman peran <span class="font-semibold uppercase text-stone-800">{{ $attemptedRole ?? 'Lain' }}</span>.
        </p>

        <div class="p-3.5 rounded-xl bg-[#FFF6E5] border border-[#F0DDB0] text-[#8A6A1F] text-xs mb-6 text-left flex items-start gap-2.5">
            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <span>Setiap akun hanya dapat mengakses menu dan fitur yang sesuai dengan tugas perannya (FR-02).</span>
        </div>

        <div class="flex flex-col gap-3">
            <a href="{{ route('dashboard', ['role' => $userRole ?? 'dapur']) }}" class="w-full py-3 px-4 bg-brand-red hover:bg-brand-redHover text-white font-semibold rounded-lg shadow-sm text-sm transition">
                Kembali ke Halaman {{ ucfirst($userRole ?? 'Peran Anda') }}
            </a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full py-2.5 px-4 bg-white border border-[#EADFD3] hover:bg-stone-50 text-stone-700 font-medium rounded-lg text-sm transition">
                    Keluar Akun
                </button>
            </form>
        </div>
    </div>
</body>
</html>

