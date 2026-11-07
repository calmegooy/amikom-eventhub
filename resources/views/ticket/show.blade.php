@extends('layouts.app')

@section('title', 'E-Ticket - ' . $transaction->order_id)

@section('content')
{{-- CSS FORCE PRINT: Sembunyikan SEMUA elemen induk dari layouts.app saat print --}}
<style>
    @media print {
        /* Sembunyikan SELURUH elemen luar dari layouts.app (Navbar, Footer, Sidebar) */
        body > *:not(.ticket-print-area) {
            display: none !important;
        }

        /* Sembunyikan elemen bawaan HTML/Layout */
        nav, header, footer, aside, .no-print {
            display: none !important;
        }

        /* Atur ukuran kertas */
        @page {
            size: A4 portrait;
            margin: 0; /* Hilangkan margin kertas agar 100% pas 1 halaman */
        }

        /* Reset Total untuk Body saat Print */
        html, body {
            background: #ffffff !important;
            margin: 0 !important;
            padding: 0 !important;
            height: auto !important;
            overflow: visible !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Container Tiket Dipaksa Tampil Pas 1 Halaman A4 */
        .ticket-print-area {
            display: block !important;
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            width: 100% !important;
            padding: 20px !important;
            margin: 0 auto !important;
            background: #ffffff !important;
        }

        .ticket-card {
            border: 2px solid #e2e8f0 !important;
            box-shadow: none !important;
            border-radius: 1rem !important;
            width: 100% !important;
            max-width: 480px !important;
            margin: 0 auto !important;
            page-break-inside: avoid !important;
        }
    }
</style>

<!-- Pembungkus Tiket Utama -->
<div class="ticket-print-area min-h-[85vh] py-8 px-4 sm:px-6 bg-slate-50 text-slate-800 flex items-center justify-center">
    <div class="ticket-card max-w-md w-full bg-white border border-slate-200/80 rounded-3xl shadow-xl overflow-hidden">
        
        <!-- Header Tiket -->
        <div class="bg-indigo-600 p-6 text-white text-center relative">
            <div class="inline-block px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-[10px] font-bold uppercase tracking-wider mb-2">
                E-Ticket Resmi
            </div>
            <h1 class="text-xl font-extrabold tracking-tight">{{ $transaction->event->title ?? 'Judul Event' }}</h1>
            <p class="text-xs text-indigo-100 mt-1">Order ID: #{{ $transaction->order_id }}</p>
        </div>

        <!-- Body Tiket -->
        <div class="p-6 space-y-5">
            
            <!-- Area QR Code -->
            <div class="flex flex-col items-center justify-center p-4 bg-slate-50 border border-slate-200 rounded-2xl">
                @php($primaryTicket = $transaction->tickets->first())
                @if($primaryTicket)
                    <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-sm mb-2">
                        <img src="{{ app(\App\Services\TicketQrCodeService::class)->dataUri($primaryTicket->qr_token) }}"
                             alt="QR {{ $primaryTicket->code }}" class="w-36 h-36 object-contain">
                    </div>
                @endif
                <span class="text-[11px] font-mono font-bold text-slate-600 uppercase tracking-widest">
                    {{ $primaryTicket?->code ?? $transaction->order_id }}
                </span>
                <span class="text-[10px] text-slate-400 mt-1">Tunjukkan QR Code ini kepada panitia di lokasi</span>
            </div>

            <!-- Detail Pemesan & Event -->
            <div class="space-y-2.5 text-xs border-t border-b border-slate-100 py-3">
                <div class="flex justify-between">
                    <span class="text-slate-500 font-medium">Nama Pemesan</span>
                    <span class="font-bold text-slate-900">{{ $transaction->customer_name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 font-medium">Jumlah Tiket</span>
                    <span class="font-bold text-slate-900">{{ $transaction->quantity }} Tiket</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 font-medium">Tanggal Event</span>
                    <span class="font-bold text-slate-900">
                        {{ \Carbon\Carbon::parse($transaction->event->date ?? $transaction->event->event_date ?? now())->translatedFormat('d M Y, H:i') }} WIB
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 font-medium">Lokasi</span>
                    <span class="font-bold text-slate-900">{{ $transaction->event->location ?? '-' }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-slate-500 font-medium">Status Tiket</span>
                    @if($transaction->is_checked_in ?? false)
                        <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 font-bold rounded-full text-[10px]">SUDAH CHECK-IN</span>
                    @else
                        <span class="px-2 py-0.5 bg-indigo-100 text-indigo-700 font-bold rounded-full text-[10px]">VALID / READY</span>
                    @endif
                </div>
            </div>

            <!-- Tombol Aksi (Otomatis Hilang Saat Print/PDF) -->
            <div class="grid grid-cols-2 gap-3 no-print pt-2">
                <button onclick="window.print()" class="w-full py-3 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl text-xs transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Cetak / Simpan
                </button>
                <a href="{{ route('home') }}" class="w-full py-3 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 font-bold rounded-xl text-xs transition text-center flex items-center justify-center">
                    Kembali ke Beranda
                </a>
            </div>

        </div>
    </div>
</div>
@endsection
