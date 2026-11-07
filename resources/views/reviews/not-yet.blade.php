@extends('layouts.app')

@section('title', 'Ulasan Belum Bisa Ditulis')

@section('content')
<main class="max-w-2xl mx-auto px-6 py-32 text-center">
    <div class="text-8xl mb-8">⏳</div>
    <h1 class="text-4xl font-extrabold mb-4">Event Belum Selesai</h1>
    <p class="text-lg text-slate-500 mb-8">
        Ulasan untuk <strong>{{ $transaction->event->title }}</strong> baru bisa ditulis 
        setelah event selesai, yaitu mulai
        <strong class="text-indigo-600">{{ $transaction->event->date->copy()->addDay()->format('d M Y') }}</strong>.
    </p>
    <a href="{{ route('home') }}"
        class="inline-block px-8 py-4 bg-indigo-600 text-white rounded-2xl font-bold hover:bg-indigo-700 transition">
        Kembali ke Beranda
    </a>
</main>
@endsection
