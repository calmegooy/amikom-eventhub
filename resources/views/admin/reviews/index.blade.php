@extends('layouts.admin')

@section('title', 'Manajemen Ulasan - Admin')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800">Manajemen Ulasan</h1>
            <p class="text-sm text-slate-500 mt-1">Pantau dan moderasi semua ulasan pelanggan dari seluruh event.</p>
        </div>
        <div class="flex gap-3">
            <div class="bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-center shadow-sm">
                <p class="text-xs text-slate-500">Total Ulasan</p>
                <p class="text-xl font-black text-indigo-600">{{ $totalReviews }}</p>
            </div>
            <div class="bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-center shadow-sm">
                <p class="text-xs text-slate-500">Rata-rata Rating</p>
                <p class="text-xl font-black text-amber-500">⭐ {{ $avgRating ? number_format($avgRating, 1) : '-' }}</p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 font-bold text-sm rounded-xl">
            {{ session('success') }}
        </div>
    @endif

    {{-- Table --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-4">Pelanggan</th>
                        <th class="px-5 py-4">Event</th>
                        <th class="px-5 py-4">Rating</th>
                        <th class="px-5 py-4">Komentar</th>
                        <th class="px-5 py-4">Tanggal</th>
                        <th class="px-5 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($reviews as $review)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-4">
                                <p class="font-bold text-slate-800">{{ $review->user?->name ?? 'Anonim' }}</p>
                                <p class="text-xs text-slate-400">{{ $review->user?->email }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <p class="font-semibold text-slate-700 line-clamp-1">{{ $review->event?->title ?? '-' }}</p>
                                <p class="text-xs text-slate-400">{{ $review->event?->organization?->name ?? 'Admin' }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span class="{{ $i <= $review->rating ? 'text-amber-400' : 'text-slate-200' }} text-lg">★</span>
                                    @endfor
                                </div>
                                <p class="text-xs text-slate-500 mt-0.5">{{ $review->rating }}/5</p>
                            </td>
                            <td class="px-5 py-4 max-w-xs">
                                <p class="text-slate-600 text-xs line-clamp-2">{{ $review->comment ?: '<em class="text-slate-400">Tidak ada komentar</em>' }}</p>
                            </td>
                            <td class="px-5 py-4 text-xs text-slate-500 whitespace-nowrap">
                                {{ $review->created_at->format('d M Y, H:i') }}
                            </td>
                            <td class="px-5 py-4 text-center">
                                <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST"
                                      onsubmit="return confirm('Hapus ulasan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="px-3 py-1.5 bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white border border-rose-200 font-bold text-xs rounded-lg transition">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-16 text-center text-slate-400">
                                <div class="text-4xl mb-3">💬</div>
                                <p class="font-bold">Belum ada ulasan masuk.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reviews->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">
                {{ $reviews->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
