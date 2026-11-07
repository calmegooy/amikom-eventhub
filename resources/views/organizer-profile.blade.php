@extends('layouts.app')

@section('title', $organization->name . ' - AmikomEventHub')

@section('content')
<main class="max-w-6xl mx-auto px-6 py-16">
    <section class="bg-gradient-to-br from-emerald-600 to-emerald-900 rounded-[2.5rem] p-10 text-white mb-12 shadow-2xl shadow-emerald-200">
        <div class="flex flex-col md:flex-row items-center md:items-start gap-8">
            @if($organization->logo_path)
                <img src="{{ asset('storage/'.$organization->logo_path) }}"
                     alt="Logo {{ $organization->name }}"
                     class="w-24 h-24 rounded-3xl object-cover bg-white/20 border border-white/20">
            @else
                <div class="w-24 h-24 rounded-3xl bg-white/20 flex items-center justify-center text-4xl font-black">
                    {{ strtoupper(substr($organization->name, 0, 1)) }}
                </div>
            @endif

            <div class="text-center md:text-left flex-1">
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-3">
                    <h1 class="text-3xl md:text-4xl font-black">{{ $organization->name }}</h1>
                    <span class="px-3 py-1 rounded-full bg-emerald-300/20 text-emerald-100 text-xs font-bold">
                        Organizer Terverifikasi
                    </span>
                </div>
                <p class="text-emerald-300 font-semibold mt-1">{{ '@'.$organization->slug }}</p>
                @if($organization->description || $organization->bio)
                    <p class="text-emerald-100 mt-4 max-w-2xl">{{ $organization->description ?? $organization->bio }}</p>
                @endif
                @if($organization->email || $organization->phone || $organization->address)
                    <div class="flex flex-wrap justify-center md:justify-start gap-x-4 gap-y-1 text-xs text-emerald-200 mt-4">
                        @if($organization->email)<span>{{ $organization->email }}</span>@endif
                        @if($organization->phone)<span>{{ $organization->phone }}</span>@endif
                        @if($organization->address)<span>{{ $organization->address }}</span>@endif
                    </div>
                @endif
                @if($organization->owner)
                    <p class="text-xs text-emerald-200 mt-4">Dikelola oleh {{ $organization->owner->name }}</p>
                @endif
            </div>

            <div class="text-center bg-white/10 rounded-2xl px-6 py-4">
                <div class="text-3xl font-black text-amber-300">
                    ★ {{ $totalReviews ? number_format($avgRating, 1) : '-' }} / 5
                </div>
                <p class="text-emerald-200 text-sm">dari {{ $totalReviews }} ulasan terverifikasi</p>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <section class="lg:col-span-2">
            <h2 class="text-2xl font-black mb-6">Event Organizer</h2>
            @forelse($events as $event)
                <a href="{{ route('events.show', $event->id) }}"
                   class="flex gap-5 bg-white rounded-2xl border border-slate-100 p-5 mb-4 hover:shadow-md transition group">
                    <img src="{{ $event->poster_url }}"
                         alt="{{ $event->title }}" class="w-20 h-20 rounded-xl object-cover">
                    <div class="flex-1 min-w-0">
                        <h3 class="font-bold text-slate-800 group-hover:text-indigo-600">{{ $event->title }}</h3>
                        <p class="text-sm text-slate-500 mt-1">
                            {{ $event->date->translatedFormat('d M Y') }} · {{ $event->location }}
                        </p>
                        <p class="text-indigo-600 font-bold text-sm mt-2">
                            {{ $event->price > 0 ? 'Rp '.number_format($event->price, 0, ',', '.') : 'GRATIS' }}
                        </p>
                    </div>
                </a>
            @empty
                <div class="bg-white rounded-2xl border border-slate-100 p-10 text-center text-slate-400">
                    Belum ada event yang dipublikasikan.
                </div>
            @endforelse
        </section>

        <section>
            <h2 class="text-2xl font-black mb-6">Testimoni Peserta</h2>
            @forelse($reviews->take(5) as $review)
                <article class="bg-white rounded-2xl border border-slate-100 p-5 mb-4">
                    <div class="text-amber-400 text-lg mb-2" aria-label="{{ $review->rating }} dari 5 bintang">
                        {{ str_repeat('★', $review->rating) }}<span class="text-slate-200">{{ str_repeat('★', 5 - $review->rating) }}</span>
                    </div>
                    @if($review->comment)
                        <p class="text-slate-600 text-sm">{{ $review->comment }}</p>
                    @endif
                    <p class="text-xs text-slate-400 mt-2">
                        {{ $review->user->name ?? $review->transaction->customer_name ?? 'Peserta' }}
                        · {{ $review->event->title }}
                    </p>
                </article>
            @empty
                <div class="bg-white rounded-2xl border border-slate-100 p-8 text-center text-slate-400">
                    Belum ada ulasan yang memenuhi aturan H+1.
                </div>
            @endforelse
        </section>
    </div>
</main>
@endsection
