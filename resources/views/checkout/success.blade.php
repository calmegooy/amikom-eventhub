@extends('layouts.app')

@section('title', $transaction->isPaid() ? 'Pembayaran Berhasil' : 'Pembayaran Diproses')

@section('content')
<main class="max-w-3xl mx-auto px-6 py-20 text-center">
    <div class="bg-white rounded-3xl border border-slate-200 p-12 shadow-sm inline-block w-full max-w-md">
        <h2 class="text-3xl font-black mb-4">
            {{ $transaction->isPaid() ? 'Pembayaran Berhasil!' : 'Pembayaran Diproses' }}
        </h2>

        <p class="text-slate-500 mb-8 leading-relaxed">
            @if($transaction->isPaid())
                Pembayaran pesanan <strong>{{ $transaction->order_id }}</strong> telah dikonfirmasi.
                {{ $transaction->tickets->count() }} tiket unik telah diterbitkan.
            @else
                Pesanan <strong>{{ $transaction->order_id }}</strong> masih menunggu konfirmasi Midtrans.
                Tiket baru diterbitkan setelah webhook pembayaran terverifikasi.
            @endif
        </p>

        @if($transaction->isPaid())
            <a href="{{ route('user.tickets.show', $transaction->order_id) }}"
               class="inline-block px-8 py-4 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition">
                Lihat E-Tiket
            </a>
        @else
            <a href="{{ route('checkout.payment', $transaction->order_id) }}"
               class="inline-block px-8 py-4 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition">
                Kembali ke Pembayaran
            </a>
        @endif
    </div>
</main>
@endsection
