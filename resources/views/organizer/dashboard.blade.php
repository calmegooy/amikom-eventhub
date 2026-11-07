@extends('layouts.app')

@section('title', 'Dashboard Organizer - AMIKOM Event Hub')

@section('content')
<div class="min-h-screen bg-gray-50 text-gray-900 py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-8">
        
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-gray-200 pb-6">
            <div>
                <span class="px-3 py-1 bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 font-bold uppercase tracking-widest text-[10px] rounded-full">
                    Organizer Panel
                </span>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight mt-2">Dashboard Analytics</h1>
                <p class="text-xs text-gray-500 mt-1">Selamat datang kembali, <span class="text-indigo-400 font-semibold">{{ Auth::user()->name }}</span>!</p>
            </div>
            
            <a href="{{ route('organizer.events.create') }}" 
               class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl text-xs transition shadow-lg shadow-indigo-600/30 active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Buat Event Baru
            </a>
        </div>

        <!-- Metrics Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- Card 1: Total Event -->
            <div class="bg-white backdrop-blur-xl border border-gray-200 rounded-2xl p-5 relative overflow-hidden">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Total Event</span>
                    <div class="p-2 bg-indigo-500/10 text-indigo-400 rounded-xl border border-indigo-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                </div>
                <div class="text-3xl font-black text-gray-900 font-mono">{{ number_format($totalEvents) }}</div>
                <p class="text-[10px] text-gray-400 mt-2">Event yang dipublikasikan</p>
            </div>

            <!-- Card 2: Tiket Terjual -->
            <div class="bg-white backdrop-blur-xl border border-gray-200 rounded-2xl p-5 relative overflow-hidden">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Tiket Terjual</span>
                    <div class="p-2 bg-blue-500/10 text-blue-400 rounded-xl border border-blue-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 002 2h14a2 2 0 002-2V7a2 2 0 00-2-2H5z"></path></svg>
                    </div>
                </div>
                <div class="text-3xl font-black text-blue-400 font-mono">{{ number_format($totalTicketsSold) }}</div>
                <p class="text-[10px] text-gray-400 mt-2">Lembar tiket terkonfirmasi</p>
            </div>

            <!-- Card 3: Total Pendapatan -->
            <div class="bg-white backdrop-blur-xl border border-gray-200 rounded-2xl p-5 relative overflow-hidden">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Total Pendapatan</span>
                    <div class="p-2 bg-emerald-50 text-emerald-600 rounded-xl border border-emerald-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
                <div class="text-2xl font-black text-emerald-600 font-mono">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
                <p class="text-[10px] text-gray-400 mt-2">Bersih dari transaksi lunas</p>
            </div>

            <!-- Card 4: Peserta Check-in -->
            <div class="bg-white backdrop-blur-xl border border-gray-200 rounded-2xl p-5 relative overflow-hidden">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Peserta Check-in</span>
                    <div class="p-2 bg-purple-500/10 text-purple-400 rounded-xl border border-purple-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
                <div class="text-3xl font-black text-purple-400 font-mono">{{ number_format($totalCheckedIn) }}</div>
                <p class="text-[10px] text-gray-400 mt-2">Sudah hadir di lokasi</p>
            </div>
        </div>

        <!-- Event List Table -->
        <div class="bg-white backdrop-blur-xl border border-gray-200 rounded-3xl p-6 shadow-2xl">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Daftar Event Anda</h2>
                    <p class="text-xs text-gray-500">Kelola tiket, ketersediaan kuota, dan fitur scanner check-in</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-gray-700">
                    <thead class="bg-gray-50/80 text-gray-500 uppercase font-bold border-b border-gray-200">
                        <tr>
                            <th class="py-3 px-4">Event</th>
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4">Harga Tiket</th>
                            <th class="py-3 px-4">Stok / Terjual</th>
                            <th class="py-3 px-4 text-center">Aksi Pintas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($events as $event)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl overflow-hidden bg-gray-100 shrink-0 border border-gray-200">
                                            <img src="{{ $event->poster_url }}" 
                                                 alt="{{ $event->title }}" class="w-full h-full object-cover">
                                        </div>
                                        <div>
                                            <span class="font-bold text-gray-900 text-sm block">{{ $event->title }}</span>
                                            <span class="text-[10px] text-gray-400">{{ $event->location }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-4 font-medium text-gray-700">
                                    {{ \Carbon\Carbon::parse($event->date)->translatedFormat('d M Y, H:i') }} WIB
                                </td>
                                <td class="py-4 px-4 font-mono font-bold text-emerald-600">
                                    {{ $event->price > 0 ? 'Rp ' . number_format($event->price, 0, ',', '.') : 'GRATIS' }}
                                </td>
                                <td class="py-4 px-4">
                                    <span class="font-bold text-gray-900 font-mono">{{ $event->total_tickets_sold }}</span>
                                    <span class="text-gray-400">/ {{ $event->stock }} Tiket</span>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <!-- Tombol Live Scanner -->
                                        <a href="{{ route('organizer.scanner', $event->id) }}" 
                                           class="px-3 py-2 bg-indigo-100 border border-indigo-500/30 hover:bg-indigo-600 hover:text-white text-indigo-600 font-bold rounded-xl text-[11px] transition flex items-center gap-1.5"
                                           title="Buka QR Scanner Panitia">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                                            Scanner
                                        </a>

                                        <!-- Tombol Edit Event -->
                                        <a href="{{ route('organizer.events.edit', $event->id) }}" 
                                           class="px-3 py-2 bg-gray-100 hover:bg-slate-700 text-gray-700 font-bold rounded-xl text-[11px] transition">
                                            Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-gray-400">
                                    Belum ada event yang dibuat. Klik tombol **"Buat Event Baru"** di atas untuk mulai mempublikasikan acara!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection
