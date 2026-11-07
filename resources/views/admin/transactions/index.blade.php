@extends('layouts.admin')

@section('title', 'Rekap Transaksi')

@section('content')
<div class="space-y-6">
    <!-- Header Title & Export Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Pusat Kendali Transaksi</h1>
            <p class="text-sm text-slate-500">Audit, rekap, dan kelola seluruh status pembayaran tiket di platform.</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak Laporan
            </button>
        </div>
    </div>

    <!-- Main Card Container -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <!-- Filter Search Bar -->
        <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-wrap items-center justify-between gap-3">
            <form method="GET" class="flex items-center gap-2 w-full sm:w-auto">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Order ID / Nama Pembeli..." class="text-xs bg-white border border-slate-200 rounded-xl px-3 py-2 w-64 focus:outline-none focus:border-indigo-500">
                <button type="submit" class="px-3 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold hover:bg-indigo-700 transition">Cari</button>
            </form>
            <div class="text-xs font-semibold text-slate-500">
                Total Transaksi: <span class="text-indigo-600 font-bold">{{ count($transactions) }}</span>
            </div>
        </div>

        <!-- Table Body -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/30 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                        <th class="py-4 px-6">Order ID</th>
                        <th class="py-4 px-6">Pembeli</th>
                        <th class="py-4 px-6">Event</th>
                        <th class="py-4 px-6">Tgl Transaksi</th>
                        <th class="py-4 px-6 text-center">Status</th>
                        <th class="py-4 px-6 text-right">Total</th>
                        <th class="py-4 px-6 text-center">Aksi Kendali</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($transactions as $trx)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-4 px-6 font-mono font-bold text-indigo-600">
                                <span class="px-2.5 py-1 bg-indigo-50 border border-indigo-100 rounded-lg text-xs">
                                    {{ $trx->order_id ?? $trx->code ?? 'TRX-'.$trx->id }}
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-bold text-slate-800">{{ $trx->name ?? $trx->user_name ?? 'Budi Santoso' }}</div>
                                <div class="text-xs text-slate-400 font-medium">{{ $trx->email ?? 'budi@example.com' }}</div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-bold text-slate-700 max-w-xs truncate">
                                    {{ $trx->event->title ?? $trx->event_title ?? 'Event Amikom 2026' }}
                                </div>
                            </td>
                            <td class="py-4 px-6 text-slate-500 text-xs font-medium">
                                {{ \Carbon\Carbon::parse($trx->created_at)->translatedFormat('d M Y, H:i') }}
                            </td>
                            <td class="py-4 px-6 text-center">
                                @php $status = strtolower($trx->status ?? 'success'); @endphp
                                @if(in_array($status, ['success', 'settlement', 'paid']))
                                    <span class="px-3 py-1 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-full text-[10px] font-bold uppercase tracking-wider">
                                        Success
                                    </span>
                                @elseif($status == 'pending')
                                    <span class="px-3 py-1 bg-amber-50 text-amber-600 border border-amber-100 rounded-full text-[10px] font-bold uppercase tracking-wider">
                                        Pending
                                    </span>
                                @else
                                    <span class="px-3 py-1 bg-rose-50 text-rose-600 border border-rose-100 rounded-full text-[10px] font-bold uppercase tracking-wider">
                                        {{ $status }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right font-black text-slate-800">
                                Rp {{ number_format($trx->amount ?? $trx->total_price ?? 55000, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-6 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <!-- Link langsung buka E-Tiket milik Peserta -->
                                    <a href="{{ route('ticket', $trx->order_id ?? 'TRX-DEMO-001') }}" target="_blank" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-600 text-indigo-600 hover:text-white font-bold text-xs rounded-xl border border-indigo-100 transition">
                                        Cek E-Tiket
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400 font-medium">
                                Belum ada data transaksi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection