@extends('layouts.app')

@section('title', 'Kontak - AmikomEventHub')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-24 min-h-screen">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-24 items-center">
        <div>
            <span class="text-indigo-600 font-black uppercase tracking-[0.3em] text-[10px]">Terhubung</span>
            <h1 class="text-6xl font-black mt-8 leading-tight">Butuh <span class="text-indigo-600">Bantuan</span> Cepat?</h1>
            <p class="text-slate-500 mt-8 text-xl font-medium leading-relaxed">Tim dukungan kami siap melayani pertanyaan Anda seputar teknis platform atau kerjasama event.</p>

            <div class="mt-16 space-y-10">
                <div class="flex items-center gap-8 group">
                    <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center border shadow-sm text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition duration-500">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Alamat Email</p>
                        <p class="text-xl font-bold mt-1">support@amikomevent.id</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white p-12 rounded-[3rem] border border-slate-100 shadow-2xl shadow-indigo-100/50">
            <form action="#" class="space-y-8">
                <div class="space-y-3">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Pesan Anda</label>
                    <textarea rows="6" placeholder="Apa yang bisa kami bantu hari ini?" class="w-full px-8 py-6 bg-slate-50 rounded-3xl outline-none focus:ring-2 focus:ring-indigo-600 transition border-transparent border font-medium"></textarea>
                </div>
                <button class="w-full py-6 bg-indigo-600 text-white rounded-[2.5rem] font-bold text-lg shadow-xl shadow-indigo-200 transition hover:scale-[1.02]">Kirim Pesan Sekarang</button>
            </form>
        </div>
    </div>
</div>
@endsection