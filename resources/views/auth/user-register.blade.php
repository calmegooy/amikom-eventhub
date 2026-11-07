<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - AmikomEventHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>body{font-family:'Plus Jakarta Sans',sans-serif;}</style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full">
        <a href="{{ route('home') }}" class="flex items-center justify-center gap-2 mb-8">
            <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white font-black text-sm">AH</div>
            <span class="font-black text-lg text-slate-900">AmikomEventHub</span>
        </a>

        <div class="bg-white rounded-[2.5rem] p-10 shadow-sm border border-slate-100">
            <h1 class="text-2xl font-black text-slate-900 mb-1 text-center">Buat Akun Baru</h1>
            <p class="text-slate-400 text-sm mb-8 text-center">Atau cukup satu klik dengan Google</p>

            <a href="{{ route('user.google.redirect') }}" class="flex items-center justify-center gap-3 w-full py-4 border-2 border-slate-200 rounded-2xl font-bold text-slate-700 hover:border-indigo-400 hover:bg-slate-50 transition mb-6">
                <svg class="w-5 h-5" viewBox="0 0 48 48"><path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3C33.9 32.6 29.4 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.1 8 2.9l6-6C34.5 5.1 29.5 3 24 3 12.4 3 3 12.4 3 24s9.4 21 21 21 21-9.4 21-21c0-1.2-.1-2.4-.4-3.5z"/><path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.8 1.1 8 2.9l6-6C34.5 5.1 29.5 3 24 3 16.3 3 9.7 7.3 6.3 14.7z"/><path fill="#4CAF50" d="M24 45c5.4 0 10.3-1.8 14-5l-6.5-5.5C29.6 36 26.9 37 24 37c-5.3 0-9.8-3.4-11.4-8.1l-6.6 5C9.6 40.6 16.3 45 24 45z"/><path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-1.1 3.1-3.5 5.7-6.4 7.3l6.5 5.5C39.9 37.1 45 31.5 45 24c0-1.2-.1-2.4-.4-3.5z"/></svg>
                Continue with Google
            </a>

            <div class="flex items-center gap-3 mb-6">
                <div class="flex-1 h-px bg-slate-200"></div>
                <span class="text-xs text-slate-400 font-bold uppercase">atau</span>
                <div class="flex-1 h-px bg-slate-200"></div>
            </div>

            <form method="POST" action="{{ route('user.register.post') }}">
                @csrf
                <div class="mb-4">
                    <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Nama Lengkap</label>
                    <input type="text" name="name" required value="{{ old('name') }}" class="w-full border-2 border-slate-100 rounded-xl px-4 py-3 focus:border-indigo-500 focus:outline-none">
                </div>
                <div class="mb-4">
                    <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Email</label>
                    <input type="email" name="email" required value="{{ old('email') }}" class="w-full border-2 border-slate-100 rounded-xl px-4 py-3 focus:border-indigo-500 focus:outline-none">
                </div>
                <div class="mb-4">
                    <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Password</label>
                    <input type="password" name="password" required class="w-full border-2 border-slate-100 rounded-xl px-4 py-3 focus:border-indigo-500 focus:outline-none">
                </div>
                <div class="mb-6">
                    <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" required class="w-full border-2 border-slate-100 rounded-xl px-4 py-3 focus:border-indigo-500 focus:outline-none">
                </div>
                <button type="submit" class="w-full py-4 bg-indigo-600 text-white rounded-2xl font-bold hover:bg-indigo-700 transition">Daftar</button>
            </form>

            <p class="text-center text-sm text-slate-400 mt-6">
                Sudah punya akun? <a href="{{ route('user.login') }}" class="text-indigo-600 font-bold hover:underline">Masuk</a>
            </p>
        </div>
    </div>
</body>
</html>
