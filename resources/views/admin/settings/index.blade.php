@extends('layouts.admin')

@section('title', 'Pengaturan Web View')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Pengaturan Web View (CMS)</h1>
            <p class="text-sm text-slate-500">Kelola teks, banner hero, dan informasi umum yang tampil di halaman utama publik.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-2xl font-medium">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Card Branding & Hero Section -->
        <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm space-y-5">
            <h2 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path></svg>
                Halaman Utam / Landing Page Banner
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Platform Website</label>
                    <input type="text" name="site_name" value="{{ $settings['site_name'] }}" class="w-full text-sm border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Judul Utama (Hero Title)</label>
                    <input type="text" name="hero_title" value="{{ $settings['hero_title'] }}" class="w-full text-sm border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Sub-Judul Hero (Deskripsi Singkat)</label>
                <textarea name="hero_subtitle" rows="3" class="w-full text-sm border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-indigo-500">{{ $settings['hero_subtitle'] }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Banner Utama / Hero Image</label>
                @if($settings['hero_banner'])
                    <div class="mb-3">
                        <img src="{{ asset('storage/' . $settings['hero_banner']) }}" class="w-full h-40 object-cover rounded-xl border border-slate-200">
                    </div>
                @endif
                <input type="file" name="hero_banner" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100">
            </div>
        </div>

        <!-- Card Informasi Kontak & Bantuan -->
        <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm space-y-5">
            <h2 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                Kontak Footer & Bantuan
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email Support</label>
                    <input type="email" name="contact_email" value="{{ $settings['contact_email'] }}" class="w-full text-sm border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nomor WhatsApp CS</label>
                    <input type="text" name="contact_wa" value="{{ $settings['contact_wa'] }}" class="w-full text-sm border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-indigo-500">
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end">
            <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition shadow-lg shadow-indigo-600/20">
                Simpan Perubahan Tampilan
            </button>
        </div>
    </form>
</div>
@endsection