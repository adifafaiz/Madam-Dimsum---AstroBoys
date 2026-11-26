<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak - Madam Dimsum</title>
    @include('partials.head')
</head>
<body class="bg-brand-lightBg antialiased min-h-screen relative overflow-x-hidden">

    <div class="pointer-events-none fixed -bottom-[200px] -right-[200px] w-[460px] h-[460px] sm:-bottom-[260px] sm:-right-[260px] sm:w-[640px] sm:h-[640px]">
        @include('partials.kukusan-motif', ['id' => 'kukusan-akses', 'class' => 'w-full h-full rotate-12'])
    </div>

    <main class="relative min-h-screen flex items-center justify-center px-5 py-12">
        <div class="w-full max-w-[460px] text-center">
            @include('partials.logo', ['class' => 'w-16 h-16 mx-auto p-1 border border-brand-border'])

            <div class="mt-8 w-16 h-16 rounded-full bg-[#FCF1EF] text-brand-red flex items-center justify-center mx-auto">
                <i data-lucide="lock-keyhole" class="!w-8 !h-8"></i>
            </div>

            <h1 class="font-display text-3xl font-semibold mt-5">Akses Ditolak</h1>
            <p class="text-brand-secondary mt-2 text-base leading-relaxed">
                Anda masuk sebagai <b class="text-brand-ink capitalize">{{ $userRole ?? 'pengguna' }}</b>,
                jadi halaman <b class="text-brand-ink capitalize">{{ $attemptedRole ?? 'peran lain' }}</b> tidak bisa dibuka.
            </p>

            <p class="mt-5 px-4 py-3 rounded border border-dashed border-brand-border bg-white text-[15px] text-brand-secondary flex items-start gap-2.5 text-left">
                <i data-lucide="info" class="!w-4 !h-4 mt-1"></i>
                Setiap akun hanya bisa membuka menu yang sesuai tugas perannya.
            </p>

            <div class="mt-7 space-y-3">
                <a href="{{ route('dashboard', ['role' => $userRole ?? 'dapur']) }}" class="btn btn-primary btn-lg w-full">
                    <i data-lucide="arrow-left"></i>
                    Kembali ke Halaman {{ ucfirst($userRole ?? 'Peran Anda') }}
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-lg w-full">
                        <i data-lucide="log-out" class="!w-4 !h-4"></i> Keluar Akun
                    </button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
