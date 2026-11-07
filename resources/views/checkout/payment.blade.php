@extends('layouts.app')

@section('title', 'Pembayaran Tiket')

@section('content')
<div class="max-w-md mx-auto my-12 p-6 bg-white rounded-2xl shadow-sm border border-gray-100 text-center">
    <h2 class="text-xl font-bold text-gray-800">Selesaikan Pembayaran</h2>
    <p class="text-xs text-gray-400 font-mono mt-1">ORDER ID: {{ $transaction->order_id }}</p>

    <div id="reservation-summary" data-reservation-deadline="{{ $transaction->reserved_until?->toIso8601String() }}" class="bg-gray-50 rounded-xl p-4 my-6 text-left text-sm space-y-2">
        <div class="flex justify-between gap-4">
            <span class="text-gray-500">Event</span>
            <span class="font-semibold text-gray-800 text-right">{{ $transaction->event->title }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-500">Jumlah</span>
            <span class="font-semibold">{{ $transaction->quantity }} tiket</span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-500">Reservasi berakhir</span>
            <span class="font-semibold">{{ $transaction->reserved_until?->format('d M Y, H:i') }}</span>
        </div>
        <div class="flex justify-between items-center">
            <span class="text-gray-500">Sisa waktu pembayaran</span>
            <span id="reservation-countdown" class="font-bold text-amber-700 tabular-nums" aria-live="polite">Memuat...</span>
        </div>
        <div class="flex justify-between items-center">
            <span class="text-gray-500">Status</span>
            <span class="bg-amber-100 text-amber-700 text-xs px-2.5 py-0.5 rounded-full font-semibold">
                {{ strtoupper($transaction->payment_status) }}
            </span>
        </div>
        <hr class="border-gray-200 my-2">
        <div class="flex justify-between text-base">
            <span class="font-bold text-gray-700">TOTAL</span>
            <span class="font-bold text-indigo-600">Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</span>
        </div>
    </div>

    @if($snapToken)
        <button id="pay-button" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-xl transition shadow-sm mb-3">
            Bayar Sekarang &rarr;
        </button>
    @else
        <div class="rounded-xl bg-rose-50 border border-rose-200 p-3 text-xs text-rose-700">
            Layanan pembayaran belum tersedia. Coba muat ulang setelah konfigurasi Midtrans diperiksa.
        </div>
    @endif

    @env(['local', 'testing'])
        <form method="POST" action="{{ route('dev.pay.success', $transaction->order_id) }}">
            @csrf
            <button type="submit"
                    class="block w-full bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-semibold py-2.5 rounded-xl text-xs transition">
                [DEV ONLY] Simulasikan Pembayaran Lunas
            </button>
        </form>
    @endenv
</div>

<script src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
        data-client-key="{{ config('midtrans.client_key') }}"></script>
<script>
    const payButton = document.getElementById('pay-button');
    const reservationSummary = document.getElementById('reservation-summary');
    const reservationCountdown = document.getElementById('reservation-countdown');
    const reservationDeadline = Date.parse(reservationSummary?.dataset.reservationDeadline || '');

    function updateReservationCountdown() {
        if (! Number.isFinite(reservationDeadline) || ! reservationCountdown) {
            return;
        }

        const remainingSeconds = Math.ceil((reservationDeadline - Date.now()) / 1000);
        if (remainingSeconds <= 0) {
            reservationCountdown.textContent = 'Kedaluwarsa';
            reservationCountdown.classList.remove('text-amber-700');
            reservationCountdown.classList.add('text-rose-600');

            if (payButton) {
                payButton.disabled = true;
                payButton.textContent = 'Reservasi Kedaluwarsa';
                payButton.classList.add('cursor-not-allowed', 'opacity-60');
            }

            window.setTimeout(() => window.location.reload(), 1000);

            return;
        }

        const minutes = Math.floor(remainingSeconds / 60);
        const seconds = remainingSeconds % 60;
        reservationCountdown.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    }

    updateReservationCountdown();
    window.setInterval(updateReservationCountdown, 1000);

    if (payButton && @json((bool) $snapToken)) {
        payButton.addEventListener('click', function () {
            if (Number.isFinite(reservationDeadline) && reservationDeadline <= Date.now()) {
                return;
            }

            snap.pay(@json($snapToken), {
                onSuccess: function () {
                    window.location.href = @json(route('checkout.success', $transaction->order_id));
                },
                onPending: function () {
                    window.location.reload();
                },
                onError: function () {
                    alert('Pembayaran gagal diproses.');
                }
            });
        });
    }
</script>
@endsection
