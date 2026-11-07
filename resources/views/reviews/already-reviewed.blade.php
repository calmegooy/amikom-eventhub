@extends('layouts.app')

@section('title', 'Ulasan Sudah Dikirim')

@section('content')
<main class="max-w-2xl mx-auto px-6 py-32 text-center">
    <div class="text-8xl mb-8">⭐</div>
    <h1 class="text-4xl font-extrabold mb-4">Terima Kasih!</h1>
    <p class="text-lg text-slate-500 mb-4">
        Anda sudah memberikan ulasan untuk <strong>{{ $transaction->event->title }}</strong>.
    </p>

    {{-- Tampilkan ulasan yang sudah diberikan --}}
    @if($transaction->review)
    <div class="bg-white rounded-3xl border border-slate-200 p-6 mb-8 text-left shadow-sm">
        <div class="flex items-center gap-2 mb-3">
            @for($i = 1; $i <= 5; $i++)
                <span class="text-2xl {{ $i <= $transaction->review->rating ? 'text-amber-400' : 'text-slate-200' }}">★</span>
            @endfor
            <span class="text-sm text-slate-500 ml-2">{{ $transaction->review->rating }}/5</span>
        </div>
        @if($transaction->review->comment)
        <p class="text-slate-600">{{ $transaction->review->comment }}</p>
        @endif
        <p class="text-xs text-slate-400 mt-2">{{ $transaction->review->created_at->diffForHumans() }}</p>
    </div>
    @endif

    <a href="{{ route('home') }}"
        class="inline-block px-8 py-4 bg-indigo-600 text-white rounded-2xl font-bold hover:bg-indigo-700 transition">
        Kembali ke Beranda
    </a>
</main>
@endsection
