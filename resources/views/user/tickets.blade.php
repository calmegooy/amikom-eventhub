@extends('layouts.app')

@section('title', 'Tiket Saya - AmikomEventHub')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <div class="mb-8">
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">Tiket Saya</h1>
        <p class="text-slate-500 text-sm mt-1">Daftar semua riwayat pemesanan dan tiket event milikmu.</p>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 font-bold text-sm rounded-2xl flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @if($tickets->isEmpty())
        <div class="bg-white rounded-3xl p-12 text-center border border-slate-100 shadow-sm max-w-md mx-auto">
            <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800">Belum Ada Tiket</h3>
            <p class="text-slate-400 text-xs mt-1 mb-6">Kamu belum memesan tiket event apapun saat ini.</p>
            <a href="{{ route('katalog') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md shadow-indigo-600/20 transition">Jelajahi Event</a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($tickets as $ticket)
                @php $isPaid = in_array(strtolower($ticket->status), ['paid', 'settlement', 'success']); @endphp
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-lg transition-all duration-200 overflow-hidden flex flex-col justify-between">
                    <div class="h-1.5 w-full {{ $isPaid ? 'bg-gradient-to-r from-indigo-500 to-indigo-700' : (strtolower($ticket->status) === 'pending' ? 'bg-amber-400' : 'bg-slate-300') }}"></div>
                    <div class="p-6 flex-1">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-[10px] font-mono font-bold text-slate-400 uppercase tracking-wider truncate max-w-[60%]">#{{ $ticket->order_id }}</span>
                            @if($isPaid)
                                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-lg text-[10px] font-bold uppercase tracking-wider flex items-center gap-1"><span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span> Lunas</span>
                            @elseif(strtolower($ticket->status) === 'pending')
                                <span class="px-2.5 py-1 bg-amber-50 text-amber-600 border border-amber-100 rounded-lg text-[10px] font-bold uppercase tracking-wider flex items-center gap-1"><span class="w-1.5 h-1.5 bg-amber-400 rounded-full animate-pulse"></span> Pending</span>
                            @else
                                <span class="px-2.5 py-1 bg-rose-50 text-rose-600 border border-rose-100 rounded-lg text-[10px] font-bold uppercase tracking-wider">Batal</span>
                            @endif
                        </div>
                        <div class="flex items-start gap-3 mb-4">
                            @if($ticket->event?->poster_url)
                                <img src="{{ $ticket->event->poster_url }}" alt="{{ $ticket->event->title }}" class="w-14 h-14 rounded-xl object-cover border border-slate-100 flex-shrink-0">
                            @endif
                            <div class="flex-1 min-w-0">
                                <h3 class="text-sm font-black text-slate-900 line-clamp-2 leading-tight">{{ $ticket->event->title ?? 'Event Tidak Ditemukan' }}</h3>
                                @if($ticket->event)
                                <p class="text-[11px] text-slate-400 mt-1 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    {{ $ticket->event->date->translatedFormat('d M Y') }}
                                </p>
                                @endif
                            </div>
                        </div>
                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-xs text-slate-400 font-medium">Total Bayar</span>
                            <span class="text-base font-black text-indigo-600">Rp {{ number_format($ticket->gross_amount ?? $ticket->amount ?? $ticket->total_price ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <p class="text-[10px] text-slate-400 mt-2">Dipesan: {{ $ticket->created_at->format('d M Y, H:i') }}</p>
                    </div>
                    <div class="px-4 pb-4 space-y-2">
                        @if(strtolower($ticket->status) === 'pending')
                            <a href="{{ route('checkout.payment', $ticket->order_id) }}" class="w-full py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-xl flex items-center justify-center gap-2 shadow-sm transition">Bayar Sekarang</a>
                        @elseif($isPaid)
                            <a href="{{ route('user.tickets.show', $ticket->order_id) }}" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl flex items-center justify-center gap-2 shadow-sm transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                                Lihat E-Tiket
                            </a>
                            <a href="{{ route('review.create', $ticket->order_id) }}" class="w-full py-2.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 font-bold text-xs rounded-xl flex items-center justify-center gap-2 transition">
                                <svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                Tulis Ulasan
                            </a>
                        @else
                            <button disabled class="w-full py-2.5 bg-slate-100 text-slate-400 font-bold text-xs rounded-xl cursor-not-allowed">Transaksi Batal</button>
                        @endif
                        <form action="{{ route('user.tickets.destroy', $ticket->order_id) }}" method="POST" onsubmit="return confirm('Hapus transaksi ini? Tindakan ini tidak bisa dibatalkan.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full py-2 bg-white hover:bg-rose-50 text-rose-400 hover:text-rose-600 border border-rose-100 hover:border-rose-200 font-bold text-xs rounded-xl flex items-center justify-center gap-1.5 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Hapus Transaksi
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection
