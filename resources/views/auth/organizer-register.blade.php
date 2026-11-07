<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Sebagai Organizer - AmikomEventHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>body{font-family:'Plus Jakarta Sans',sans-serif;}</style>
</head>
<body class="bg-emerald-50 min-h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full">
        <a href="{{ route('home') }}" class="flex items-center justify-center gap-2 mb-8">
            <div class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center text-white font-black text-sm">AH</div>
            <span class="font-black text-lg text-slate-900">AmikomEventHub</span>
        </a>

        <div class="bg-white rounded-[2.5rem] p-10 shadow-sm border border-slate-100">
            <p class="text-emerald-600 font-bold uppercase tracking-widest text-xs text-center mb-2">Platform Multi-Tenant</p>
            <h1 class="text-2xl font-black text-slate-900 mb-1 text-center">Daftarkan Kepanitiaan Anda</h1>
            <p class="text-slate-400 text-sm mb-8 text-center">Kelola acara & pendapatan Anda sendiri di dashboard khusus</p>

            @if($errors->any())
            <div class="mb-6 bg-rose-50 border border-rose-200 text-rose-600 px-4 py-3 rounded-xl font-semibold text-sm">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('user.register.organizer.post') }}">
                @csrf
                <div class="mb-4">
                    <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Nama Penanggung Jawab</label>
                    <input type="text" name="name" required value="{{ old('name') }}" class="w-full border-2 border-slate-100 rounded-xl px-4 py-3 focus:border-emerald-500 focus:outline-none">
                </div>
                <div class="mb-4">
                    <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Nama Organisasi / HIMA / Kepanitiaan</label>
                    <input type="text" name="organization_name" required value="{{ old('organization_name') }}" class="w-full border-2 border-slate-100 rounded-xl px-4 py-3 focus:border-emerald-500 focus:outline-none">
                </div>
                <div class="mb-4">
                    <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Deskripsi Singkat (Opsional)</label>
                    <textarea name="organization_bio" rows="2" class="w-full border-2 border-slate-100 rounded-xl px-4 py-3 focus:border-emerald-500 focus:outline-none">{{ old('organization_bio') }}</textarea>
                </div>
                <div class="mb-4">
                    <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Email</label>
                    <input type="email" name="email" required value="{{ old('email') }}" class="w-full border-2 border-slate-100 rounded-xl px-4 py-3 focus:border-emerald-500 focus:outline-none">
                </div>
                <div class="mb-4">
                    <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Password</label>
                    <input type="password" name="password" required class="w-full border-2 border-slate-100 rounded-xl px-4 py-3 focus:border-emerald-500 focus:outline-none">
                </div>
                <div class="mb-6">
                    <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" required class="w-full border-2 border-slate-100 rounded-xl px-4 py-3 focus:border-emerald-500 focus:outline-none">
                </div>
                <button type="submit" class="w-full py-4 bg-emerald-600 text-white rounded-2xl font-bold hover:bg-emerald-700 transition">Daftar sebagai Organizer</button>
            </form>

            <p class="text-center text-xs text-slate-400 mt-6">
                Akun akan ditinjau Superadmin sebelum bisa mempublikasikan event.
            </p>
        </div>
    </div>
</body>
</html>
