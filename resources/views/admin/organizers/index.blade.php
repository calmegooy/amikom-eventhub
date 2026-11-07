@extends('layouts.admin')

@section('title', 'Kelola Organizer')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Persetujuan & Kelola Organizer</h1>
            <p class="text-sm text-slate-500">Kelola pendaftaran dan status akses organizer di platform ini.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-2xl font-medium">
            {{ session('success') }}
        </div>
    @endif

    <!-- Section 1: Permintaan Menunggu Persetujuan -->
    <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-bold text-amber-600 flex items-center gap-2">
                <span class="w-2.5 h-2.5 bg-amber-500 rounded-full animate-pulse"></span>
                Menunggu Verifikasi ({{ $pendingOrganizers->count() }})
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-400 uppercase text-xs font-bold border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">Nama Organizer / Instansi</th>
                        <th class="py-3 px-4">Email Account</th>
                        <th class="py-3 px-4">Tanggal Daftar</th>
                        <th class="py-3 px-4 text-center">Aksi Verifikasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pendingOrganizers as $organizer)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-4 px-4">
                                <p class="font-semibold text-slate-800">{{ $organizer->ownedOrganizations->first()?->name ?? $organizer->organization_name ?? $organizer->name }}</p>
                                <p class="text-xs text-slate-400 mt-1">Pemilik: {{ $organizer->name }}</p>
                            </td>
                            <td class="py-4 px-4 text-slate-500">{{ $organizer->email }}</td>
                            <td class="py-4 px-4 text-slate-500">{{ $organizer->created_at->format('d M Y, H:i') }}</td>
                            <td class="py-4 px-4">
                                <div class="flex items-center justify-center gap-2">
                                    <form action="{{ route('admin.organizers.approve', $organizer->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg text-xs transition shadow-sm">
                                            Setujui
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.organizers.reject', $organizer->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 font-semibold rounded-lg text-xs transition">
                                            Tolak
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-slate-400">
                                Tidak ada pendaftaran organizer yang menunggu persetujuan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 2: Daftar Organizer Terverifikasi -->
    <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <span class="w-2.5 h-2.5 bg-indigo-600 rounded-full"></span>
                Organizer Terverifikasi ({{ $approvedOrganizers->count() }})
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-400 uppercase text-xs font-bold border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">Nama Organizer</th>
                        <th class="py-3 px-4">Email Account</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($approvedOrganizers as $organizer)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-4 px-4">
                                <p class="font-semibold text-slate-800">{{ $organizer->ownedOrganizations->first()?->name ?? $organizer->organization_name ?? $organizer->name }}</p>
                                <p class="text-xs text-slate-400 mt-1">Pemilik: {{ $organizer->name }}</p>
                            </td>
                            <td class="py-4 px-4 text-slate-500">{{ $organizer->email }}</td>
                            <td class="py-4 px-4">
                                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-600 font-semibold text-xs rounded-full inline-flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Aktif
                                </span>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <form action="{{ route('admin.organizers.reject', $organizer->id) }}" method="POST" onsubmit="return confirm('Nonaktifkan organizer ini?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg transition" title="Nonaktifkan Organizer">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-slate-400">
                                Belum ada organizer aktif.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
