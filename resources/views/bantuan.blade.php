@extends('layouts.app')

@section('title', 'Bantuan - AmikomEventHub')

@section('styles')
<style>
    details summary::-webkit-details-marker {
        display: none;
    }
</style>
@endsection

@section('content')
<div class="max-w-4xl mx-auto px-6 py-24 min-h-screen">
    <div class="text-center mb-20">
        <span class="text-indigo-600 font-bold uppercase tracking-widest text-[10px]">Pertanyaan Umum</span>
        <h1 class="text-5xl font-black mt-4">Pusat Bantuan</h1>
        <p class="text-slate-500 mt-6 font-medium text-lg">Cari solusi kendala pendaftaran atau pembayaran event Anda di sini.</p>
    </div>

    <div class="space-y-6">
        <details class="group bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden transition hover:shadow-md" open>
            <summary class="flex justify-between items-center p-10 cursor-pointer list-none">
                <h3 class="text-xl font-bold">Bagaimana proses verifikasi pembayaran?</h3>
                <svg class="w-6 h-6 text-slate-400 group-open:rotate-180 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </summary>
            <div class="px-10 pb-10 pt-0 text-slate-500 leading-relaxed font-medium">
                Pembayaran melalui sistem Midtrans akan terverifikasi secara otomatis dalam hitungan detik setelah transaksi berhasil. Tiket akan langsung dikirim ke email Anda.
            </div>
        </details>
    </div>
</div>
@endsection