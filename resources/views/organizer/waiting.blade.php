<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menunggu Persetujuan - AmikomEventHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full bg-white rounded-3xl border border-slate-100 shadow-xl shadow-slate-100/50 p-8 text-center relative overflow-hidden">
        <!-- Accent Top Bar -->
        <div class="absolute top-0 left-0 right-0 h-2 bg-amber-500"></div>

        <!-- Illustration / Icon -->
        <div class="w-20 h-20 bg-amber-50 rounded-2xl flex items-center justify-center mx-auto mb-6 text-amber-500">
            <svg class="w-10 h-10 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>

        <!-- Text Content -->
        <h1 class="text-2xl font-black text-slate-800 mb-3">Akun Menunggu Persetujuan</h1>
        <p class="text-slate-500 text-sm leading-relaxed mb-8">
            Terima kasih telah mendaftar di <strong>AmikomEventHub</strong>. Tim administrator kami sedang memverifikasi detail kepanitiaan/organisasi Anda. Proses peninjauan biasanya memakan waktu maksimal 24 jam.
        </p>

        <!-- Status Card -->
        <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4 mb-8 text-left">
            <div class="flex items-start gap-3">
                <div class="w-5 h-5 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center flex-shrink-0 text-xs mt-0.5">!</div>
                <div>
                    <p class="text-xs font-bold text-slate-700">Status Saat Ini</p>
                    <p class="text-xs text-slate-400 mt-0.5">Menunggu konfirmasi admin utama.</p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="space-y-3">
            <a href="{{ route('home') }}" class="block w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-bold transition text-sm shadow-lg shadow-emerald-600/20">
                Kembali ke Halaman Utama
            </a>
            
            <form action="{{ route('user.logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full py-3.5 bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-600 rounded-2xl font-bold transition text-sm border border-slate-200/60">
                    Keluar dari Akun
                </button>
            </form>
        </div>
    </div>
</body>
</html>
