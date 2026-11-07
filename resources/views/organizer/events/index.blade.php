@extends('layouts.app')

@section('title', 'Manajemen Event - Organizer Panel')

@section('content')
<div class="min-h-screen bg-gray-50 text-gray-900 py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-8">

        <!-- Header Page -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-gray-200 pb-6">
            <div>
                <span class="px-3 py-1 bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 font-bold uppercase tracking-widest text-[10px] rounded-full">
                    Organizer Workspace
                </span>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight mt-2">Daftar Event Saya</h1>
                <p class="text-xs text-gray-500 mt-1">Kelola event, pantau peserta, dan unduh laporan transaksi event kamu.</p>
            </div>
            <div>
                <a href="{{ route('organizer.events.create') }}" 
                   class="inline-flex items-center gap-2 px-5 py-3 bg-indigo-600 hover:bg-indigo-500 text-gray-900 font-bold text-xs rounded-xl transition shadow-lg shadow-indigo-600/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Buat Event Baru
                </a>
            </div>
        </div>

        <!-- Alert Notification -->
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-500/30 text-emerald-600 font-bold text-xs rounded-xl flex items-center justify-between">
                <span>{{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-white">&times;</button>
            </div>
        @endif

        <!-- Daftar Event Grid / Table -->
        <div class="bg-white backdrop-blur-xl border border-gray-200 rounded-3xl p-6 shadow-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-gray-700">
                    <thead class="bg-gray-50/80 text-gray-500 uppercase font-bold border-b border-gray-200">
                        <tr>
                            <th class="py-4 px-4">Info Event</th>
                            <th class="py-4 px-4">Tanggal & Waktu</th>
                            <th class="py-4 px-4">Harga Tiket</th>
                            <th class="py-4 px-4 text-center">Aksi & Fitur Penyelenggara</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($events as $event)
                            <tr class="hover:bg-gray-50 transition">
                                <!-- Banner & Judul -->
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $event->poster_url }}" 
                                             alt="{{ $event->title }}" 
                                             class="w-14 h-14 object-cover rounded-xl border border-gray-200 bg-gray-100"
                                             onerror="this.onerror=null;this.src='{{ asset('assets/workshop.png') }}';">
                                        <div>
                                            <h3 class="font-bold text-gray-900 text-sm line-clamp-1">{{ $event->title }}</h3>
                                            <p class="text-[11px] text-gray-500 mt-0.5">Lokasi: {{ $event->location }}</p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Tanggal -->
                                <td class="py-4 px-4">
                                    <span class="font-bold text-gray-800">{{ \Carbon\Carbon::parse($event->date)->translatedFormat('d M Y') }}</span><br>
                                    <span class="text-[11px] text-gray-500">{{ \Carbon\Carbon::parse($event->date)->format('H:i') }} WIB</span>
                                </td>

                                <!-- Harga -->
                                <td class="py-4 px-4">
                                    @if($event->price == 0)
                                        <span class="px-2.5 py-1 bg-emerald-50 text-emerald-600 border border-emerald-500/20 font-bold text-[10px] rounded-full">Gratis</span>
                                    @else
                                        <span class="font-bold text-gray-800">Rp {{ number_format($event->price, 0, ',', '.') }}</span>
                                    @endif
                                </td>

                                <!-- Action Buttons -->
                                <td class="py-4 px-4">
                                    <div class="flex flex-wrap items-center justify-center gap-2">
                                        
                                        <!-- TOMBOL EXPORT EXCEL / CSV PESERTA -->
                                        <a href="{{ route('organizer.events.export-attendees', $event->id) }}" 
                                           title="Unduh Daftar Peserta CSV"
                                           class="inline-flex items-center gap-1.5 px-3 py-2 bg-emerald-100 hover:bg-emerald-600 text-emerald-600 hover:text-white border border-emerald-500/30 font-bold text-[11px] rounded-xl transition shadow-lg shadow-emerald-600/10">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            Export CSV
                                        </a>

                                        <!-- Tombol Live QR Scanner -->
                                        <a href="{{ route('organizer.scanner', $event->id) }}" 
                                           title="Buka QR Scanner Check-in"
                                           class="inline-flex items-center gap-1.5 px-3 py-2 bg-indigo-100 hover:bg-indigo-600 text-indigo-600 hover:text-white border border-indigo-500/30 font-bold text-[11px] rounded-xl transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                                            Scanner
                                        </a>

                                        <!-- Edit -->
                                        <a href="{{ route('organizer.events.edit', $event->id) }}" 
                                           class="px-3 py-2 bg-gray-100 hover:bg-slate-700 text-gray-700 font-bold text-[11px] rounded-xl transition">
                                            Edit
                                        </a>

                                        <!-- Hapus -->
                                        <form action="{{ route('organizer.events.destroy', $event->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus event ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-2 bg-rose-100 hover:bg-rose-600 text-rose-600 hover:text-white border border-rose-500/20 font-bold text-[11px] rounded-xl transition">
                                                Hapus
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-12 text-center text-gray-400">
                                    <div class="max-w-sm mx-auto space-y-3">
                                        <p class="text-sm font-bold text-gray-500">Belum Ada Event Dipublikasikan</p>
                                        <p class="text-xs text-gray-400">Mulai buat event pertama kamu untuk membuka penjualan tiket & absensi kehadiran.</p>
                                        <a href="{{ route('organizer.events.create') }}" class="inline-block px-4 py-2 bg-indigo-600 text-gray-900 font-bold text-xs rounded-xl">
                                            Buat Event Sekarang
                                        </a>
                                    </div>
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
