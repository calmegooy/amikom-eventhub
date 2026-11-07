@extends('layouts.app')

@section('title', \App\Models\Setting::get('site_name', 'AmikomEventHub') . ' - Temukan Event Seru!')

@section('content')

@push('styles')
<style>
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    .animate-float {
        animation: float 4s ease-in-out infinite;
    }
</style>
@endpush

<!-- Hero Section -->
<section class="max-w-7xl mx-auto px-6 py-20 flex flex-col md:flex-row items-center gap-16">
    <div class="flex-1 space-y-8">
        <span class="inline-flex items-center gap-2 px-4 py-1.5 bg-indigo-50 border border-indigo-100 text-indigo-700 rounded-full text-sm font-bold uppercase tracking-wider">
            <span class="w-2 h-2 bg-indigo-600 rounded-full animate-ping"></span>
            🎉 {{ $totalEvents }} Event Aktif Saat Ini
        </span>
        
        <!-- JUDUL HERO DINAMIS DARI ADMIN CMS -->
        <h1 class="text-5xl md:text-7xl font-black leading-tight tracking-tight">
            {{ \App\Models\Setting::get('hero_title', 'Temukan & Pesan Tiket Event Kampus.') }}
        </h1>

        <!-- SUBTITLE HERO DINAMIS DARI ADMIN CMS -->
        <p class="text-lg text-slate-500 max-w-lg leading-relaxed font-medium">
            {{ \App\Models\Setting::get('hero_subtitle', 'Dari seminar akademis hingga konser musik seru, dapatkan tiket digital dengan mudah dan aman di platform resmi Universitas AMIKOM Yogyakarta.') }}
        </p>

        <div class="flex flex-wrap gap-4">
            <a href="#events" class="px-8 py-4 bg-indigo-600 text-white rounded-2xl font-bold text-lg shadow-xl shadow-indigo-100 hover:bg-indigo-700 hover:shadow-indigo-200 transition duration-300 transform hover:-translate-y-0.5">
                Mulai Jelajah
            </a>
            <a href="{{ route('bantuan') }}" class="px-8 py-4 bg-white border-2 border-slate-100 text-slate-600 rounded-2xl font-bold text-lg hover:border-indigo-600 hover:text-indigo-600 hover:shadow-md transition duration-300 transform hover:-translate-y-0.5">
                Cara Pesan
            </a>
        </div>
        
        <!-- Live Stats -->
        <div class="grid grid-cols-3 gap-6 pt-8 border-t border-slate-100">
            <div>
                <p class="text-3xl font-black text-indigo-600">{{ $totalEvents }}</p>
                <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mt-1">Event Aktif</p>
            </div>
            <div>
                <p class="text-3xl font-black text-indigo-600">{{ $totalParticipants }}</p>
                <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mt-1">Tiket Terjual</p>
            </div>
            <div>
                <p class="text-3xl font-black text-indigo-600">{{ $totalOrganizers }}</p>
                <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mt-1">Organizer</p>
            </div>
        </div>
    </div>

    <!-- BANNER HERO DINAMIS DARI ADMIN CMS -->
    <div class="flex-1 relative animate-float z-10">
        <div class="absolute inset-0 bg-indigo-200 rounded-[3rem] blur-3xl opacity-30 transform -rotate-3 z-0"></div>
        @php
            $customBanner = \App\Models\Setting::get('hero_banner');
            $bannerSrc = $customBanner ? asset('storage/' . $customBanner) : asset('assets/concert.png');
        @endphp
        <img src="{{ $bannerSrc }}" alt="Hero Banner"
            class="rounded-[3rem] shadow-2xl relative z-10 w-full object-cover aspect-[4/5] object-center border-4 border-white transition duration-500 hover:scale-[1.01]"
            onerror="this.onerror=null; this.src='{{ asset('assets/concert.png') }}';">
    </div>
</section>

{{-- Event Terdekat — Dinamis dari Database --}}
<section id="events" class="max-w-7xl mx-auto px-6 py-20">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4 mb-12">
        <div>
            <h2 class="text-3xl font-extrabold mb-2">Event Terdekat</h2>
            <p class="text-slate-500 font-medium">Jangan sampai ketinggalan acara seru minggu ini!</p>
        </div>
        <a href="{{ route('katalog') }}" class="flex items-center gap-2 text-indigo-600 font-bold hover:text-indigo-700 transition">
            <span>Lihat Semua Event</span>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </a>
    </div>

    @if($events->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach($events as $event)
        <div class="group bg-white rounded-3xl border border-slate-100 shadow-sm hover:shadow-2xl transition-all duration-300 overflow-hidden">
            <div class="relative overflow-hidden aspect-[3/4]">
                <img src="{{ $event->poster_url }}"
                    alt="{{ $event->title }}"
                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                    onerror="this.onerror=null;this.src='{{ asset('assets/workshop.png') }}';">
                
                <!-- Category Badge -->
                <div class="absolute top-4 left-4 px-3 py-1 bg-white/90 backdrop-blur rounded-lg text-xs font-bold uppercase text-indigo-600 shadow-sm">
                    {{ $event->category->name ?? 'Event' }}
                </div>
                
                <!-- Promo / Price Type Badge -->
                @if(isset($event->active_price) && $event->active_price == 0)
                    <div class="absolute top-4 right-4 px-3 py-1 bg-emerald-500 text-white rounded-lg text-xs font-bold uppercase shadow-sm">
                        Gratis
                    </div>
                @elseif(isset($event->active_price_label) && $event->active_price_label !== 'Regular')
                    <div class="absolute top-4 right-4 px-3 py-1 bg-amber-500 text-white rounded-lg text-xs font-bold uppercase shadow-sm animate-pulse">
                        {{ $event->active_price_label }}
                    </div>
                @endif
            </div>
            <div class="p-6">
                <h3 class="text-xl font-bold mb-2 group-hover:text-indigo-600 transition">{{ $event->title }}</h3>
                <div class="flex items-center gap-2 text-slate-500 text-sm mb-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ \Carbon\Carbon::parse($event->date)->translatedFormat('d M Y, H:i') }}</span>
                </div>
                <div class="flex items-center gap-2 text-slate-500 text-sm mb-4">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>{{ $event->location }}</span>
                </div>
                <div class="flex justify-between items-center pt-4 border-t border-slate-50">
                    <div class="flex flex-col">
                        @if(isset($event->active_price))
                            @if($event->active_price == 0)
                                <span class="text-2xl font-black text-emerald-600">Gratis</span>
                            @else
                                @if($event->active_price < $event->price)
                                    <span class="text-xs text-slate-400 line-through">Rp {{ number_format($event->price, 0, ',', '.') }}</span>
                                @endif
                                <span class="text-2xl font-black text-indigo-600">
                                    Rp {{ number_format($event->active_price, 0, ',', '.') }}
                                </span>
                            @endif
                        @else
                            <span class="text-2xl font-black text-indigo-600">
                                Rp {{ number_format($event->price, 0, ',', '.') }}
                            </span>
                        @endif
                    </div>
                    <a href="{{ route('events.show', $event->id) }}"
                        class="px-5 py-2.5 bg-indigo-50 hover:bg-indigo-600 text-indigo-600 hover:text-white rounded-xl font-bold transition duration-300">
                        Lihat Detail
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="text-center py-20 bg-white rounded-[3rem] border border-slate-100 shadow-sm max-w-2xl mx-auto">
        <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-400">
            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
        </div>
        <h3 class="text-2xl font-bold text-slate-700">Belum ada event tersedia saat ini.</h3>
        <p class="text-slate-500 mt-2 font-medium max-w-sm mx-auto">Pantau terus halaman ini, kami akan segera merilis event-event seru untuk Anda.</p>
    </div>
    @endif
</section>

{{-- Kategori Platform --}}
<section id="categories" class="max-w-7xl mx-auto px-6 py-16 border-t border-slate-100">
    <div class="text-center mb-12">
        <h2 class="text-3xl font-extrabold mb-2">Kategori Event Kami</h2>
        <p class="text-slate-500 font-medium">Temukan event sesuai minat dan kebutuhanmu</p>
    </div>
    <div class="flex flex-wrap justify-center gap-4">
        @forelse($categories as $category)
            @php
                $icon = '';
                $lowerName = Str::lower($category->name);
                if (Str::contains($lowerName, 'seminar') || Str::contains($lowerName, 'it') || Str::contains($lowerName, 'tech')) {
                    $icon = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>';
                } elseif (Str::contains($lowerName, 'entertain') || Str::contains($lowerName, 'music') || Str::contains($lowerName, 'concert') || Str::contains($lowerName, 'jazz')) {
                    $icon = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg>';
                } elseif (Str::contains($lowerName, 'workshop') || Str::contains($lowerName, 'class') || Str::contains($lowerName, 'belajar')) {
                    $icon = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>';
                } else {
                    $icon = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>';
                }
            @endphp
            <a href="{{ route('katalog', ['category' => $category->id]) }}" class="flex items-center gap-3 px-6 py-3.5 bg-indigo-50 border border-indigo-100 hover:border-indigo-600 hover:bg-indigo-600 text-indigo-700 hover:text-white rounded-2xl font-bold text-sm transition duration-300 transform hover:-translate-y-0.5 shadow-sm">
                {!! $icon !!}
                <span>{{ $category->name }}</span>
            </a>
        @empty
            <p class="text-slate-400 font-medium">Belum ada kategori tersedia.</p>
        @endforelse
    </div>
</section>

{{-- Daftar Partner --}}
<section class="max-w-7xl mx-auto px-6 py-16 border-t border-slate-100">
    <div class="text-center mb-12">
        <h2 class="text-3xl font-extrabold mb-2">Partner Kami</h2>
        <p class="text-slate-500 font-medium">Didukung oleh berbagai organisasi dan perusahaan terpercaya</p>
    </div>

    @if($partners->count() > 0)
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
        @foreach($partners as $partner)
        <div class="group bg-white rounded-3xl border border-slate-100 shadow-sm hover:shadow-xl hover:border-indigo-100 transition-all duration-300 p-6 flex flex-col items-center justify-center gap-4 text-center h-44">
            @if(!empty($partner->logo_url))
                <div class="h-16 w-full flex items-center justify-center filter grayscale opacity-70 group-hover:grayscale-0 group-hover:opacity-100 transition duration-300">
                    <img src="{{ $partner->logo_url }}" alt="{{ $partner->name }}" class="max-h-full max-w-full object-contain">
                </div>
            @else
                <div class="w-14 h-14 bg-indigo-50 border border-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center font-black text-xl uppercase transition duration-300 group-hover:bg-indigo-600 group-hover:text-white group-hover:border-indigo-600">
                    {{ substr($partner->name, 0, 1) }}
                </div>
            @endif

            <p class="font-bold text-slate-800 text-sm mt-auto transition duration-300 group-hover:text-indigo-600">{{ $partner->name }}</p>
        </div>
        @endforeach
    </div>
    @else
    <div class="text-center py-16 bg-white rounded-[3rem] border border-slate-100 shadow-sm max-w-xl mx-auto">
        <p class="text-slate-400 font-bold">Belum ada partner yang terdaftar.</p>
        <p class="text-slate-400 text-xs mt-1 font-medium">Platform ini menyambut kerjasama dengan berbagai pihak sponsor & mitra kampus.</p>
    </div>
    @endif
</section>

@endsection
