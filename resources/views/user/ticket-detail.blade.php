@extends('layouts.app')

@section('title', 'E-Ticket ' . $transaction->order_id)

@section('content')
<div class="max-w-4xl mx-auto px-4 py-10">
    <div class="bg-white rounded-3xl border border-slate-100 shadow-xl overflow-hidden">
        <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 text-white p-6 sm:p-8">
            <p class="text-xs text-indigo-200 uppercase font-bold tracking-widest">Pesanan {{ $transaction->order_id }}</p>
            <h1 class="text-2xl font-black mt-2">{{ $transaction->event->title }}</h1>
            <p class="text-sm text-indigo-100 mt-1">
                {{ $transaction->event->date->translatedFormat('d F Y, H:i') }} WIB · {{ $transaction->event->location }}
            </p>
        </div>

        <div class="p-6 sm:p-8">
            <div class="mb-6 text-sm">
                <p><strong>Pemesan:</strong> {{ $transaction->customer_name }}</p>
                <p><strong>Email:</strong> {{ $transaction->customer_email }}</p>
                <p><strong>Jumlah tiket:</strong> {{ $transaction->tickets->count() }}</p>
            </div>

            <div class="grid md:grid-cols-2 gap-5">
                @foreach($transaction->tickets as $ticket)
                    <article class="border-2 border-dashed border-slate-200 rounded-2xl p-5 text-center">
                        <p class="font-mono font-black text-indigo-700">{{ $ticket->code }}</p>
                        <p class="text-xs text-slate-500 mb-4">{{ $ticket->holder_name }}</p>

                        <div class="inline-block p-3 bg-white border rounded-xl">
                            <img src="{{ $qrCodes[$ticket->id] }}"
                                 alt="QR {{ $ticket->code }}" class="w-44 h-44">
                        </div>

                        <p class="mt-3 text-xs font-bold {{ $ticket->isUsed() ? 'text-rose-600' : 'text-emerald-600' }}">
                            {{ $ticket->isUsed() ? 'Sudah digunakan' : 'Belum digunakan' }}
                        </p>
                        @if($ticket->checked_in_at)
                            <p class="text-[11px] text-slate-400">{{ $ticket->checked_in_at->format('d M Y, H:i') }}</p>
                        @endif
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
