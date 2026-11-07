<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#4f46e5">
    <title>@yield('title', 'AmikomEventHub - Temukan Event Seru!')</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" href="/logo.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/logo.svg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .glass {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
        }
    </style>
    @stack('styles')
</head>

<body class="bg-slate-50 text-slate-900 flex flex-col min-h-screen">

    <!-- Navigation Header (Diperbaiki Lebarnya) -->
    <header class="sticky top-0 z-50 glass border-b border-slate-200/60 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">

            <!-- Logo Brand -->
            <a href="{{ route('home') }}" class="flex items-center gap-2 sm:gap-3 min-w-0 group">
                <div class="w-10 h-10 shrink-0 bg-indigo-600 rounded-xl flex items-center justify-center text-white font-black text-lg shadow-md shadow-indigo-600/30 group-hover:bg-indigo-700 transition">
                    AH
                </div>
                <span class="hidden sm:inline text-xl font-bold tracking-tight text-slate-900 group-hover:text-indigo-600 transition">AmikomEventHub</span>
            </a>

            <!-- Navigation Menu -->
            <nav class="hidden md:flex items-center gap-8 font-semibold text-sm text-slate-600">
                <a href="{{ route('katalog') }}" class="{{ request()->routeIs('katalog') ? 'text-indigo-600 font-bold' : 'hover:text-indigo-600 transition' }}">Jelajahi</a>
                <a href="{{ route('home') }}#categories" class="hover:text-indigo-600 transition">Kategori</a>
                <a href="{{ route('profil') }}" class="{{ request()->routeIs('profil') ? 'text-indigo-600 font-bold' : 'hover:text-indigo-600 transition' }}">Tentang Kami</a>
            </nav>

            <!-- Right Side: Action Buttons -->
            <div class="flex items-center gap-3 shrink-0">
                @auth
                    <div class="flex items-center gap-2 min-w-0" title="Masuk sebagai {{ Auth::user()->name }}">
                        @if(filled(Auth::user()->avatar))
                            <img src="{{ Auth::user()->avatar }}"
                                 alt="Foto profil {{ Auth::user()->name }}"
                                 referrerpolicy="no-referrer"
                                 class="w-8 h-8 shrink-0 rounded-xl object-cover border border-indigo-100">
                        @else
                            <div class="w-8 h-8 shrink-0 bg-indigo-100 text-indigo-700 rounded-xl flex items-center justify-center text-xs font-black" aria-hidden="true">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                        @endif
                        <span class="hidden md:inline max-w-[9rem] truncate text-xs font-bold text-slate-700">
                            Hai, {{ Auth::user()->name }}
                        </span>
                    </div>

                    <!-- 1. USER / PEMBELI TIKET -->
                    @if(Auth::user()->role === 'user')
                        <a href="{{ route('user.tickets') }}"
                           class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                            </svg>
                            <span>Tiket Saya</span>
                        </a>
                    @endif

                    <!-- 2. ORGANIZER / PENYELENGGARA -->
                    @if(Auth::user()->role === 'organizer')
                        <a href="{{ route('organizer.dashboard') }}"
                           class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-50 border border-indigo-100 text-indigo-600 hover:bg-indigo-100 text-xs font-bold rounded-xl transition-all">
                            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                            <span>Panel Organizer</span>
                        </a>
                    @endif

                    <!-- 3. ADMIN -->
                    @if(Auth::user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}"
                           class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-900 text-white hover:bg-slate-800 text-xs font-bold rounded-xl shadow-sm transition-all">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>Panel Admin</span>
                        </a>
                    @endif

                    <div class="h-4 w-px bg-slate-200 mx-1"></div>

                    <form action="{{ route('user.logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                                title="Keluar Akun"
                                class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </button>
                    </form>

                @else
                    <a href="{{ route('user.login') }}" class="text-slate-700 hover:text-indigo-600 text-sm font-bold px-4 py-2.5 rounded-xl transition">
                        Masuk
                    </a>
                    <a href="{{ route('user.register') }}" class="hidden sm:inline-block px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-md shadow-indigo-600/20 transition-all">
                        Daftar
                    </a>
                @endauth
            </div>

        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-indigo-900 text-indigo-100 py-16 px-6 mt-20">
        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-4 gap-12">
            <div class="space-y-4 lg:col-span-2">
                <div class="flex items-center gap-2">
                    <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-indigo-900 font-black text-xl shadow-md">
                        AH
                    </div>
                    <span class="text-2xl font-bold text-white">AmikomEventHub</span>
                </div>
                <p class="max-w-xs text-indigo-300 text-sm leading-relaxed">
                    Platform reservasi tiket event online terpadu untuk mahasiswa dan penyelenggara acara profesional.
                </p>
            </div>
            <div>
                <h4 class="text-white font-bold mb-6 text-sm uppercase tracking-wider">Navigasi</h4>
                <ul class="space-y-3 text-sm text-indigo-200">
                    <li><a href="{{ route('home') }}" class="hover:text-white transition">Beranda</a></li>
                    <li><a href="{{ route('katalog') }}" class="hover:text-white transition">Katalog</a></li>
                    <li><a href="{{ route('bantuan') }}" class="hover:text-white transition">Bantuan</a></li>
                    <li><a href="{{ route('kontak') }}" class="hover:text-white transition">Kontak</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-bold mb-6 text-sm uppercase tracking-wider">Hubungi Kami</h4>
                <ul class="space-y-3 text-sm text-indigo-200">
                    <li>support@amikomeventhub.com</li>
                    <li>+62 812 3456 7890</li>
                    <li>Sleman, DI Yogyakarta</li>
                </ul>
            </div>
        </div>
        <div class="max-w-7xl mx-auto pt-8 mt-12 border-t border-indigo-800 text-center text-indigo-400 text-xs font-medium">
            &copy; 2026 AmikomEventHub. Built with Laravel & Tailwind CSS.
        </div>
    </footer>

    @yield('extra-scripts')
    @stack('scripts')

    {{-- PWA: Service Worker Registration --}}
    <script>
        if ('serviceWorker' in navigator && (location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1')) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('/sw.js', { scope: '/' }).catch(function () {
                    // SW registration failed — non-critical, app tetap berjalan normal
                });
            });
        }
    </script>

</body>

</html>
