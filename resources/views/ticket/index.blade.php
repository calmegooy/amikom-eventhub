@extends('layouts.app') {{-- Sesuaikan dengan nama layout utama projectmu --}}

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Tiket Saya</h1>
        <p class="text-gray-500 text-sm mt-1">Daftar semua e-ticket aktif dan riwayat pemesanan milikmu.</p>
    </div>

    {{-- SECTION 1: TIKET AKTIF (SUDAH LUNAS / SUCCESS) --}}
    <div class="mb-12">
        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
            <span>🎟️</span> Tiket Event Aktif
        </h2>

        @if($tickets->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($tickets as $ticket)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition">
                        <div class="p-5">
                            <div class="flex justify-between items-start mb-3">
                                <span class="bg-green-100 text-green-700 text-xs font-semibold px-3 py-1 rounded-full">
                                    BERHASIL
                                </span>
                                <span class="text-xs text-gray-400 font-mono">#{{ $ticket->order_id }}</span>
                            </div>

                            <h3 class="font-bold text-lg text-gray-800 mb-1 line-clamp-1">
                                {{ $ticket->event->title ?? 'Nama Event' }}
                            </h3>
                            <p class="text-xs text-gray-500 mb-4">
                                {{ $ticket->created_at->translatedFormat('d M Y, H:i') }} • <strong>{{ $ticket->quantity }} Tiket</strong>
                            </p>

                            <div class="bg-gray-50 rounded-xl p-3 mb-4 flex justify-between items-center text-xs">
                                <span class="text-gray-500">Total Pembayaran</span>
                                <span class="font-bold text-gray-800">Rp {{ number_format($ticket->total_price, 0, ',', '.') }}</span>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <a href="{{ route('ticket', $ticket->order_id) }}" 
                                   class="bg-indigo-600 hover:bg-indigo-700 text-white text-center py-2.5 rounded-xl text-xs font-semibold transition">
                                   Lihat E-Ticket
                                </a>
                                <a href="{{ route('ticket', $ticket->order_id) }}?download=pdf" 
                                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-center py-2.5 rounded-xl text-xs font-semibold transition">
                                   📥 Cetak / PDF
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            {{-- EMPTY STATE: Tampilan ketika User belum memiliki tiket sah --}}
            <div class="bg-white rounded-2xl border border-dashed border-gray-300 p-12 text-center">
                <div class="w-16 h-16 bg-indigo-50 text-indigo-500 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                    🎫
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-1">Belum Ada Tiket Sah</h3>
                <p class="text-gray-500 text-sm mb-6 max-w-md mx-auto">
                    Kamu belum memiliki tiket event apapun yang aktif. Tiket yang sudah kamu bayar akan secara otomatis muncul di sini.
                </p>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-xl text-sm font-semibold transition shadow-sm">
                    🔍 Jelajahi Event Sekarang
                </a>
            </div>
        @endif
    </div>

    {{-- SECTION 2: MENUNGGU PEMBAYARAN (PENDING) --}}
    @if(isset($pendingOrders) && $pendingOrders->count() > 0)
        <div class="mt-8 border-t border-gray-200 pt-8">
            <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <span>⏳</span> Menunggu Pembayaran
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($pendingOrders as $order)
                    <div class="bg-amber-50/60 rounded-2xl border border-amber-200 p-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="bg-amber-100 text-amber-800 text-xs px-2.5 py-0.5 rounded-full font-semibold">
                                PENDING
                            </span>
                            <span class="text-xs text-gray-400 font-mono">#{{ $order->order_id }}</span>
                        </div>
                        <h4 class="font-bold text-gray-800 text-sm line-clamp-1">{{ $order->event->title ?? 'Event' }}</h4>
                        <p class="text-xs text-gray-500 mt-1 mb-3">
                            Total: <strong class="text-gray-700">Rp {{ number_format($order->total_price, 0, ',', '.') }}</strong>
                        </p>
                        
                        <a href="{{ route('checkout.payment', $order->order_id) }}" 
                           class="block text-center bg-amber-500 hover:bg-amber-600 text-white text-xs py-2.5 rounded-xl font-semibold transition">
                            Bayar Sekarang
                        </a>

                        {{-- TOMBOL BYPASS UNTUK TESTING / DEV LOKAL --}}
                        <a href="{{ route('dev.pay.success', $order->order_id) }}" 
                           class="mt-2 block text-center text-xs text-emerald-600 font-medium hover:underline">
                            ⚡ [DEV] Simulasikan Lunas
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection