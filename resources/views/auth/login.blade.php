<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - AmikomEventHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>

<body class="bg-indigo-900 text-white min-h-screen flex items-center justify-center p-6">

    <div class="max-w-md w-full bg-white text-slate-900 rounded-[2rem] p-8 shadow-2xl">

        {{-- Logo & Header --}}
        <div class="text-center mb-8">
            <div
                class="w-16 h-16 bg-indigo-600 rounded-2xl flex items-center justify-center text-white font-bold text-2xl mx-auto mb-4">
                AH
            </div>
            <h1 class="text-2xl font-black">Admin Login</h1>
            <p class="text-slate-500 text-sm mt-1">AmikomEventHub Dashboard</p>
        </div>

        {{-- Error Message dari session --}}
        @if (session('error'))
            <div class="bg-red-100 text-red-600 p-4 rounded-xl mb-6 font-bold text-sm text-center">
                {{ session('error') }}
            </div>
        @endif

        {{-- Error dari validasi --}}
        @if ($errors->any())
            <div class="bg-red-100 text-red-600 p-4 rounded-xl mb-6 text-sm text-center font-semibold">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Form Login --}}
        <form action="{{ route('admin.login.post') }}" method="POST" class="space-y-6">
            @csrf

            {{-- Email --}}
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide">
                    Email
                </label>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="admin@amikom.ac.id"
                    class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl
                           focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600
                           outline-none transition font-medium"
                    required>
            </div>

            {{-- Password --}}
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide">
                    Password
                </label>
                <input
                    type="password"
                    name="password"
                    placeholder="••••••••"
                    class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl
                           focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600
                           outline-none transition font-medium"
                    required>
            </div>

            {{-- Submit Button --}}
            <button
                type="submit"
                class="w-full py-4 bg-indigo-600 text-white rounded-2xl font-black text-xl
                       shadow-lg shadow-indigo-200 hover:bg-indigo-700 active:scale-95 transition">
                Masuk
            </button>
        </form>

        {{-- Back to Home --}}
        <p class="text-center text-sm text-slate-400 mt-6">
            <a href="{{ route('home') }}" class="text-indigo-500 hover:underline font-semibold">
                ← Kembali ke Halaman Publik
            </a>
        </p>

    </div>

</body>

</html>
