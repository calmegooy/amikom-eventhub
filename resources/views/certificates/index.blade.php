@extends('layouts.app')

@section('title', 'E-Certificate - ' . $transaction->customer_name)

@section('content')
<div class="min-h-[85vh] py-12 px-4 sm:px-6 lg:px-8 bg-slate-50 text-slate-800 flex items-center justify-center">
    <div class="max-w-3xl w-full bg-white border border-slate-200/80 rounded-3xl shadow-xl shadow-slate-200/50 overflow-hidden p-6 sm:p-8">
        
        <div class="text-center mb-8">
            <div class="inline-block px-3 py-1 bg-emerald-50 border border-emerald-200 text-emerald-600 font-bold uppercase tracking-widest text-[10px] rounded-full mb-3">
                ✓ Verified Certificate
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Portal E-Sertifikat</h1>
            <p class="text-xs text-slate-500 mt-1">Selamat! Anda telah menyelesaikan partisipasi dalam event ini.</p>
        </div>

        <!-- Preview Sertifikat -->
        <div class="relative bg-gradient-to-br from-indigo-900 via-slate-900 to-indigo-950 text-white rounded-2xl p-8 sm:p-12 border-4 border-amber-400/80 shadow-2xl overflow-hidden text-center mb-8">
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-indigo-500/10 rounded-full blur-2xl"></div>
            <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-amber-500/10 rounded-full blur-2xl"></div>

            <p class="text-[10px] tracking-[0.3em] font-semibold text-amber-400 uppercase mb-2">SERTIFIKAT PENGHARGAAN</p>
            <p class="text-xs text-slate-300 mb-6">Diberikan secara bangga kepada:</p>

            <h2 class="text-2xl sm:text-3xl font-black tracking-wide text-white border-b border-amber-400/30 pb-3 mb-4 inline-block px-6">
                {{ strtoupper($transaction->customer_name) }}
            </h2>

            <p class="text-xs text-slate-300 max-w-lg mx-auto leading-relaxed mb-6">
                Atas partisipasi dan kelulusannya sebagai Peserta pada acara <br>
                <span class="font-bold text-indigo-300">"{{ $transaction->event->title }}"</span>
            </p>

            <div class="flex items-center justify-between border-t border-slate-800 pt-6 text-[10px] text-slate-400">
                <div class="text-left">
                    <span>ID Sertifikat:</span><br>
                    <span class="font-mono text-slate-200 font-bold">CERT-{{ $transaction->order_id }}</span>
                </div>
                <div class="text-right">
                    <span>Tanggal Terbit:</span><br>
                    <span class="text-slate-200 font-bold">{{ \Carbon\Carbon::parse($transaction->event->date)->translatedFormat('d F Y') }}</span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('certificate.download', $transaction->order_id) }}" 
               class="px-6 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition shadow-lg shadow-indigo-600/20 text-center flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Unduh PDF Sertifikat
            </a>
            <a href="{{ route('ticket', $transaction->order_id) }}" 
               class="px-6 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition text-center flex items-center justify-center">
                Kembali ke Tiket
            </a>
        </div>

    </div>
</div>
@endsection
