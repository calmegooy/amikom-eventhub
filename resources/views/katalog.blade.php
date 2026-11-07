@extends('layouts.app')

@section('title', 'Katalog - AmikomEventHub')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-16 min-h-screen">
    <div class="flex flex-col md:flex-row justify-between items-end gap-6 mb-16">
        <div>
            <span class="text-indigo-600 font-bold uppercase tracking-widest text-xs">Eksplorasi</span>
            <h1 class="text-5xl font-black mt-2">Daftar Event</h1>
            <p class="text-slate-500 mt-4 max-w-md font-medium">Pilih kategori event yang sesuai dengan minat akademik dan hobi Anda.</p>
        </div>
        <form action="{{ route('katalog') }}" method="GET" class="flex bg-white p-2 rounded-2xl shadow-sm border w-full md:w-auto">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama event..." class="bg-transparent px-6 py-3 outline-none w-full md:w-80 font-medium">
            <button type="submit" class="bg-indigo-600 text-white px-8 rounded-xl font-bold transition hover:bg-indigo-700">Cari</button>
        </form>
    </div>

    @if($events->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
            @foreach($events as $event)
                <div class="group bg-white rounded-[2.5rem] border border-slate-100 overflow-hidden hover:shadow-2xl hover:shadow-indigo-100/50 transition-all duration-500">
                    <div class="relative aspect-[4/3] overflow-hidden bg-slate-100">
                        <img src="{{ $event->poster_url }}" alt="{{ $event->title }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-700" onerror="this.onerror=null;this.src='{{ asset('assets/workshop.png') }}';">
                        <div class="absolute top-6 left-6">
                            <span class="px-4 py-2 bg-white/90 backdrop-blur-md rounded-full text-[10px] font-black uppercase tracking-widest text-indigo-600 shadow-sm">
                                {{ $event->category->name ?? 'Event' }}
                            </span>
                        </div>
                    </div>
                    <div class="p-10">
                        <h3 class="text-2xl font-black mb-4 group-hover:text-indigo-600 transition">{{ $event->title }}</h3>
                        <div class="flex items-center gap-3 text-slate-400 font-bold text-xs uppercase tracking-widest mb-8">
                            <span>{{ $event->location }}</span>
                            <span class="w-1 h-1 bg-slate-300 rounded-full"></span>
                            <span>{{ \Carbon\Carbon::parse($event->date)->translatedFormat('d M Y') }}</span>
                        </div>
                        <div class="flex justify-between items-center pt-8 border-t">
                            <span class="text-indigo-600 font-black text-2xl">Rp {{ number_format($event->price, 0, ',', '.') }}</span>
                            <a href="{{ route('events.show', $event->id) }}" class="px-6 py-3 bg-slate-900 text-white rounded-xl font-bold text-xs hover:bg-indigo-600 transition">Detail</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-16 flex justify-center">
            {{ $events->appends(request()->query())->links() }}
        </div>
    @else
        <div class="text-center py-20 bg-white rounded-[2.5rem] border border-slate-100 shadow-sm">
            <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-slate-700">Tidak ada event ditemukan</h3>
            <p class="text-slate-500 mt-2">Coba gunakan kata kunci pencarian yang lain.</p>
        </div>
    @endif
</div>
@endsection
