@extends('layouts.app')

@section('title', 'Tiket Saya - AmikomEventHub')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">Tiket Saya</h1>
        <p class="text-slate-500 text-sm mt-1">Daftar semua tiket event yang telah kamu pesan.</p>
    </div>

    <!-- Ticket List Grid -->
    @if($tickets->isEmpty())
        <div class="bg-white rounded-3xl p-12 text-center border border-slate-100 shadow-sm max-w-md mx-auto">
            <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800">Belum Ada Tiket</h3>
            <p class="text-slate-400 text-xs mt-1 mb-6">Kamu belum memesan tiket event apapun saat ini.</p>
            <a href="{{ route('katalog') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md shadow-indigo-600/20 transition">
                Jelajahi Event
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($tickets as $ticket)
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition overflow-hidden flex flex-col justify-between">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-[11px] font-mono font-bold text-slate-400 uppercase tracking-wider">
                                #{{ $ticket->order_id }}
                            </span>
                            @if($ticket->status === 'paid' || $ticket->status === 'success')
                                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-lg text-[10px] font-bold uppercase tracking-wider">
                                    Lunas
                                </span>
                            @else
                                <span class="px-2.5 py-1 bg-amber-50 text-amber-600 border border-amber-100 rounded-lg text-[10px] font-bold uppercase tracking-wider">
                                    {{ $ticket->status }}
                                </span>
                            @endif
                        </div>

                        <h3 class="text-lg font-bold text-slate-900 line-clamp-2 mb-2">
                            {{ $ticket->event->title ?? 'Event Tidak Ditemukan' }}
                        </h3>

                        <div class="flex items-center gap-1.5 text-xs text-slate-500 mb-1">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            {{ $ticket->event ? $ticket->event->date->translatedFormat('d M Y, H:i') . ' WIB' : '-' }}
                        </div>

                        <p class="text-xs text-slate-500 font-medium mb-4">
                            Dipesan: {{ $ticket->created_at->format('d M Y, H:i') }}
                        </p>

                        <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-xs text-slate-400 font-medium">Total Harga</span>
                            <span class="text-base font-black text-indigo-600">
                                Rp {{ number_format($ticket->amount ?? $ticket->total_price ?? 0, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    <div class="p-4 bg-slate-50 border-t border-slate-100 flex flex-col gap-2">
                        @if($ticket->status === 'pending')
                            <a href="{{ route('checkout.payment', $ticket->id) }}" class="w-full py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-xl flex items-center justify-center gap-2 transition">
                                💳 Bayar Sekarang
                            </a>
                        @else
                            <a href="{{ route('user.tickets.show', $ticket->order_id) }}" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl flex items-center justify-center gap-2 transition">
                                🎟️ Lihat E-Ticket
                            </a>
                            @if(in_array($ticket->status, ['paid', 'success']))
                                <a href="{{ route('review.create', $ticket->order_id) }}" class="w-full py-2.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 font-bold text-xs rounded-xl flex items-center justify-center gap-2 transition">
                                    ⭐ Tulis Ulasan
                                </a>
                            @endif
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection