<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#065f46">
    <title>@yield('title', 'Organizer') - AmikomEventHub</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" href="/logo.svg" type="image/svg+xml">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
    @yield('extra-styles')
</head>

<body class="bg-slate-50 text-slate-900 min-h-screen md:flex">
    <!-- Sidebar -->
    <aside class="w-full md:w-64 bg-emerald-900 text-emerald-100 flex flex-col p-4 md:p-6 space-y-4 md:space-y-8 md:sticky md:top-0 md:h-screen">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-emerald-900 font-black text-sm">AH</div>
            <div>
                <span class="text-lg font-bold text-white tracking-tight block leading-none">AmikomEventHub</span>
                <span class="text-[10px] font-bold text-emerald-300 uppercase tracking-widest">Organizer Panel</span>
            </div>
        </div>

        @if(!Auth::user()->is_approved)
        <div class="bg-amber-500/20 border border-amber-400/40 text-amber-200 text-xs font-bold rounded-xl p-3">
            ⚠️ Akun Anda menunggu persetujuan Superadmin sebelum event bisa dipublikasikan.
        </div>
        @endif

        <nav class="flex md:block gap-2 md:flex-1 md:space-y-1 overflow-x-auto pb-2 md:pb-0">
            <p class="hidden md:block text-[10px] font-bold uppercase tracking-widest text-emerald-400 mb-4 px-2">Main Menu</p>

            <a href="{{ route('organizer.dashboard') }}"
                class="min-w-max flex items-center gap-3 px-4 py-3 {{ request()->routeIs('organizer.dashboard') ? 'bg-white/10 text-white' : 'hover:bg-white/10' }} rounded-xl font-bold transition text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>

            <a href="{{ route('organizer.events.index') }}"
                class="min-w-max flex items-center gap-3 px-4 py-3 {{ request()->routeIs('organizer.events.*') ? 'bg-white/10 text-white' : 'hover:bg-white/10' }} rounded-xl font-bold transition text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Event Saya
            </a>

            <a href="{{ route('organizer.coupons.index') }}"
                class="min-w-max flex items-center gap-3 px-4 py-3 {{ request()->routeIs('organizer.coupons.*') ? 'bg-white/10 text-white' : 'hover:bg-white/10' }} rounded-xl font-bold transition text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                Kupon / Voucher
            </a>

            <a href="{{ route('organizer.reviews.index') }}"
                class="min-w-max flex items-center gap-3 px-4 py-3 {{ request()->routeIs('organizer.reviews.*') ? 'bg-white/10 text-white' : 'hover:bg-white/10' }} rounded-xl font-bold transition text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                Ulasan Event
            </a>

            <a href="{{ route('organizer.events.index') }}"
                class="min-w-max flex items-center gap-3 px-4 py-3 hover:bg-white/10 rounded-xl font-bold transition text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M5 3h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V4a1 1 0 011-1zm10 0h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V4a1 1 0 011-1zM5 15h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4a1 1 0 011-1z"/></svg>
                Pilih Event untuk Scan
            </a>

            <a href="{{ route('organizer.organization.edit') }}"
                class="min-w-max flex items-center gap-3 px-4 py-3 {{ request()->routeIs('organizer.organization.*') ? 'bg-white/10 text-white' : 'hover:bg-white/10' }} rounded-xl font-bold transition text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4M9 9h.01M9 13h.01M9 17h.01M15 13h.01M15 17h.01"/></svg>
                Profil Organisasi
            </a>
        </nav>

        <div class="flex md:block gap-2 pt-4 border-t border-emerald-800 md:space-y-1">
            <a href="{{ route('home') }}" class="flex items-center gap-3 px-4 py-3 hover:bg-white/10 rounded-xl font-bold transition text-sm text-emerald-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                Lihat Halaman Publik
            </a>
            <form action="{{ route('user.logout') }}" method="POST" class="flex-1 md:block">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 text-emerald-300 hover:bg-rose-600/20 hover:text-rose-300 rounded-xl font-bold transition text-sm text-left">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                    Keluar
                </button>
            </form>
        </div>
    </aside>

    <main class="flex-1 min-w-0 p-4 sm:p-6 lg:p-10 overflow-x-hidden md:overflow-y-auto">
        <div class="flex justify-end items-center mb-6">
            <div class="flex items-center gap-3 bg-white rounded-2xl px-4 py-2 shadow-sm border border-slate-100">
                <div class="w-8 h-8 bg-emerald-600 rounded-xl flex items-center justify-center text-white text-xs font-bold">
                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                </div>
                <div>
                    <p class="text-sm font-bold text-slate-800">{{ Auth::user()->activeOrganization()?->name ?? Auth::user()->organization_name ?? Auth::user()->name }}</p>
                    <p class="text-xs text-emerald-500 font-semibold capitalize">Organizer</p>
                </div>
            </div>
        </div>

        @if(session('info'))
        <div class="mb-6 bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 rounded-xl font-semibold text-sm">{{ session('info') }}</div>
        @endif
        @if(session('success'))
        <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl font-semibold text-sm">{{ session('success') }}</div>
        @endif

        @yield('content')
    </main>

    @yield('extra-scripts')

    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
        }
    </script>

</body>
</html>
