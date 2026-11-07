@extends('layouts.app')

@section('title', 'E-Ticket - ' . $transaction->event->title)

@section('content')
<main class="max-w-4xl mx-auto px-6 py-12 md:py-20 print:py-0 print:px-0">
    
    <!-- Bagian Navigasi & Action (Disembunyikan saat dicetak) -->
    <div class="flex flex-col sm:flex-row justify-between items-center mb-8 gap-4 print:hidden">
        <a href="{{ route('home') }}" class="text-indigo-600 font-bold flex items-center gap-2 hover:text-indigo-800 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Kembali ke Beranda
        </a>
        <button onclick="window.print()" class="px-6 py-3 bg-indigo-600 text-white rounded-xl font-bold shadow-lg shadow-indigo-200 hover:bg-indigo-700 active:scale-95 transition-all flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            Cetak / Simpan PDF
        </button>
    </div>

    <!-- Container Utama Tiket -->
    <div class="relative bg-white rounded-3xl shadow-2xl shadow-slate-200/50 flex flex-col md:flex-row overflow-hidden print:shadow-none print:border print:border-slate-300">
        
        <!-- Sisi Kiri: Detail Informasi -->
        <div class="p-8 md:p-12 flex-1 relative">
            <!-- Label Lunas -->
            <div class="absolute top-0 right-0 bg-emerald-500 text-white text-xs font-black px-4 py-2 rounded-bl-2xl uppercase tracking-wider print:border-b print:border-l print:border-slate-300">
                LUNAS
            </div>

            <p class="text-xs text-indigo-600 font-black uppercase tracking-widest mb-2">E-Ticket Resmi</p>
            <h1 class="text-3xl md:text-4xl font-extrabold text-slate-800 leading-tight mb-8">{{ $transaction->event->title }}</h1>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-8">
                <div>
                    <h2 class="text-xs text-slate-400 uppercase font-bold tracking-wider mb-1">Nama Pemesan</h2>
                    <p class="text-lg font-bold text-slate-800">{{ $transaction->customer_name }}</p>
                </div>
                <div>
                    <h2 class="text-xs text-slate-400 uppercase font-bold tracking-wider mb-1">Tanggal Pembelian</h2>
                    <p class="text-lg font-bold text-slate-800">{{ $transaction->created_at->format('d M Y, H:i') }}</p>
                </div>
                <div>
                    <h2 class="text-xs text-slate-400 uppercase font-bold tracking-wider mb-1">Jumlah Tiket</h2>
                    <div class="inline-block bg-indigo-50 text-indigo-700 font-black px-3 py-1 rounded-lg border border-indigo-100">
                        {{ $transaction->quantity }} Orang
                    </div>
                </div>
                <div>
                    <h2 class="text-xs text-slate-400 uppercase font-bold tracking-wider mb-1">Status Pembayaran</h2>
                    <p class="text-lg font-bold text-emerald-600 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Terkonfirmasi
                    </p>
                </div>
            </div>

            <div class="mt-10 pt-6 border-t border-slate-100 text-sm font-medium text-slate-500 bg-slate-50 p-4 rounded-xl print:bg-transparent print:p-0 print:border-t-2 print:border-dashed">
                <span class="font-bold text-slate-700">Catatan:</span> Harap tunjukkan Order ID atau scan QR Code pada petugas registrasi di lokasi acara. Satu E-Ticket ini berlaku untuk <strong>{{ $transaction->quantity }}</strong> orang sesuai pesanan Anda.
            </div>
        </div>

        <!-- Garis Pemisah (Efek Sobekan Tiket) -->
        <div class="hidden md:flex flex-col justify-center items-center relative border-l-2 border-dashed border-slate-200 print:border-slate-400">
            <!-- Setengah lingkaran atas -->
            <div class="w-8 h-8 bg-slate-50 rounded-full absolute -top-4 -left-4 shadow-inner print:hidden"></div>
            <!-- Setengah lingkaran bawah -->
            <div class="w-8 h-8 bg-slate-50 rounded-full absolute -bottom-4 -left-4 shadow-inner print:hidden"></div>
        </div>

        <!-- Sisi Kanan: Area Scan / Barcode -->
        <div class="bg-indigo-600 p-8 md:p-12 flex flex-col items-center justify-center text-center w-full md:w-80 print:bg-white print:border-l print:border-slate-300">
            <p class="text-xs text-indigo-200 uppercase font-bold mb-4 tracking-widest print:text-slate-500">Scan di Pintu Masuk</p>
            
            @php($primaryTicket = $transaction->tickets->first())
            @if($primaryTicket)
                <div class="bg-white p-4 rounded-2xl shadow-lg print:shadow-none print:border print:border-slate-800 print:p-2 mb-4">
                    <img src="{{ app(\App\Services\TicketQrCodeService::class)->dataUri($primaryTicket->qr_token) }}"
                         alt="QR {{ $primaryTicket->code }}"
                         class="w-32 h-32 md:w-40 md:h-40 object-contain">
                </div>
            @endif
            
            <p class="text-xs text-indigo-200 uppercase font-semibold tracking-wider mb-1 print:text-slate-400">Kode Tiket</p>
            <p class="text-xl font-mono font-black text-white tracking-widest print:text-slate-800">{{ $primaryTicket?->code ?? $transaction->order_id }}</p>
        </div>

    </div>
</main>
@endsection
