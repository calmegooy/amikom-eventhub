@extends('layouts.app')

@section('title', 'Checkout Tiket - ' . $event->title)

@section('content')
<div class="min-h-[85vh] py-12 px-4 sm:px-6 lg:px-8 bg-slate-50 text-slate-800 flex items-center justify-center relative">

    <div class="max-w-4xl w-full grid grid-cols-1 lg:grid-cols-12 gap-8 relative z-10">

        <!-- Left Column: Event Summary -->
        <div class="lg:col-span-5 flex flex-col justify-between bg-white border border-slate-200/80 rounded-3xl p-6 shadow-xl shadow-slate-200/50">
            <div>
                <div class="inline-block px-3 py-1 bg-indigo-50 border border-indigo-100 text-indigo-600 font-bold uppercase tracking-widest text-[10px] rounded-full mb-4">
                    Pemesanan Tiket
                </div>

                <!-- Poster Event dengan Handling Fallback Gambar Rusak -->
                <div class="aspect-video w-full rounded-2xl overflow-hidden mb-5 bg-slate-100 border border-slate-200">
                    <img src="{{ $event->poster_url }}"
                         onerror="this.onerror=null;this.src='{{ asset('assets/workshop.png') }}';"
                         alt="{{ $event->title }}"
                         class="w-full h-full object-cover">
                </div>

                <h1 class="text-2xl font-black text-slate-900 tracking-tight mb-2 leading-snug">{{ $event->title }}</h1>

                <div class="space-y-2 text-xs font-medium text-slate-600 mb-6">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <span>{{ \Carbon\Carbon::parse($event->date)->translatedFormat('l, d F Y - H:i') }} WIB</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                        <span>{{ $event->location }}</span>
                    </div>
                </div>
            </div>

            <!-- Price Card -->
            <div class="bg-slate-50 border border-slate-200/80 p-4 rounded-2xl flex items-center justify-between">
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider block mb-1">Harga Tiket per Orang</span>
                    <div class="flex items-center gap-2">
                        <div class="text-xl font-black text-emerald-600">
                            {{ $event->active_price > 0 ? 'Rp ' . number_format($event->active_price, 0, ',', '.') : 'GRATIS' }}
                        </div>
                        @if($event->active_price < $event->price)
                            <div class="text-sm font-medium text-slate-400 line-through">
                                Rp {{ number_format($event->price, 0, ',', '.') }}
                            </div>
                        @endif
                    </div>
                </div>
                <div>
                    @php
                        $badgeColors = [
                            'Early Bird' => 'bg-amber-100 text-amber-700 border-amber-200',
                            'Presale' => 'bg-blue-100 text-blue-700 border-blue-200',
                            'Regular' => 'bg-slate-100 text-slate-700 border-slate-200'
                        ];
                        $label = $event->active_price_label;
                        $colorClass = $badgeColors[$label] ?? $badgeColors['Regular'];
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold border {{ $colorClass }}">
                        {{ $label }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Right Column: Checkout Form -->
        <div class="lg:col-span-7 bg-white border border-slate-200/80 rounded-3xl p-6 md:p-8 shadow-xl shadow-slate-200/50">
            <h2 class="text-xl font-bold text-slate-900 mb-6">Informasi Pemesan</h2>

            @if(session('error'))
                <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-600 text-sm font-medium">
                    {{ session('error') }}
                </div>
            @endif

            <form id="checkout-form" action="{{ route('checkout.store', $event->id) }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="checkout_token" value="{{ $checkoutToken }}">

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Nama Lengkap</label>
                    <input type="text" name="customer_name" value="{{ old('customer_name', $user->name ?? '') }}" required
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20 transition">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Email</label>
                        <input type="email" name="customer_email" value="{{ old('customer_email', $user->email ?? '') }}" required
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">No. WhatsApp</label>
                        <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" placeholder="08123456789" required
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20 transition">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Jumlah Tiket</label>
                        <input type="number" id="quantity" name="quantity" min="1" max="{{ $event->available_stock }}" value="1" required
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20 transition">
                        <span class="text-[10px] text-slate-500 mt-1 block">Sisa stok: {{ $event->available_stock }}</span>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Kode Kupon</label>
                        <div class="flex gap-2">
                            <input type="text" id="coupon_code" name="coupon_code" placeholder="AMIKOM10"
                                class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-3 text-sm text-slate-900 uppercase placeholder-slate-400 focus:outline-none focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20 transition">
                            <button type="button" id="btn-apply-coupon" class="px-4 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl transition">
                                Pakai
                            </button>
                        </div>
                        <span id="coupon-message" class="text-[10px] mt-1 block font-medium"></span>
                    </div>
                </div>

                <!-- Rincian Biaya -->
                <div class="mt-6 p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-2 text-sm">
                    <div class="flex justify-between text-slate-600">
                        <span>Subtotal Tiket</span>
                        <span id="text-base-price" class="font-mono text-slate-900 font-semibold">Rp {{ number_format($event->active_price, 0, ',', '.') }}</span>
                    </div>
                    <div id="row-discount" class="hidden justify-between text-emerald-600 font-semibold">
                        <span>Diskon Kupon</span>
                        <span id="text-discount" class="font-mono">- Rp 0</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Biaya Penanganan</span>
                        <span id="text-admin-fee" class="font-mono text-slate-900 font-semibold">Rp {{ $event->active_price > 0 ? '5.000' : '0' }}</span>
                    </div>
                    <div class="border-t border-slate-200 pt-2 flex justify-between font-bold text-slate-900 text-base">
                        <span>Total Bayar</span>
                        <span id="text-total-price" class="text-indigo-600 font-mono">
                            Rp {{ number_format(($event->active_price > 0 ? $event->active_price + 5000 : 0), 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                <button id="checkout-submit-button" type="submit" class="w-full mt-6 py-4 bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-400 disabled:cursor-not-allowed text-white font-bold rounded-2xl transition shadow-lg shadow-indigo-600/20 active:scale-[0.99]">
                    Lanjutkan Pembayaran →
                </button>
            </form>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const basePrice = {{ $event->active_price }};
    const qtyInput = document.getElementById('quantity');
    const couponInput = document.getElementById('coupon_code');
    const btnApplyCoupon = document.getElementById('btn-apply-coupon');
    const couponMsg = document.getElementById('coupon-message');
    const rowDiscount = document.getElementById('row-discount');
    const checkoutForm = document.getElementById('checkout-form');
    const checkoutSubmitButton = document.getElementById('checkout-submit-button');

    let discountPerTicket = 0;

    function updateTotals() {
        const qty = parseInt(qtyInput.value) || 1;
        const subtotal = basePrice * qty;
        const totalDiscount = discountPerTicket * qty;
        const priceAfterDiscount = Math.max(0, subtotal - totalDiscount);
        const adminFee = priceAfterDiscount > 0 ? 5000 : 0;
        const grandTotal = priceAfterDiscount + adminFee;

        document.getElementById('text-base-price').innerText = 'Rp ' + subtotal.toLocaleString('id-ID');
        document.getElementById('text-admin-fee').innerText = 'Rp ' + adminFee.toLocaleString('id-ID');
        document.getElementById('text-total-price').innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');

        if (totalDiscount > 0) {
            rowDiscount.classList.remove('hidden');
            rowDiscount.classList.add('flex');
            document.getElementById('text-discount').innerText = '- Rp ' + totalDiscount.toLocaleString('id-ID');
        } else {
            rowDiscount.classList.add('hidden');
            rowDiscount.classList.remove('flex');
        }
    }

    qtyInput.addEventListener('input', updateTotals);

    btnApplyCoupon.addEventListener('click', function() {
        const code = couponInput.value.trim();
        if(!code) return;

        fetch("{{ route('checkout.coupon.check', $event->id) }}?code=${code}")
            .then(res => res.json())
            .then(data => {
                if(data.valid) {
                    discountPerTicket = data.discount;
                    couponMsg.className = "text-[10px] text-emerald-600 mt-1 block font-bold";
                    couponMsg.innerText = "✓ Kupon berhasil diterapkan!";
                } else {
                    discountPerTicket = 0;
                    couponMsg.className = "text-[10px] text-rose-600 mt-1 block font-bold";
                    couponMsg.innerText = data.message;
                }
                updateTotals();
        });
    });

    checkoutForm.addEventListener('submit', function(event) {
        if (checkoutForm.dataset.submitting === 'true') {
            event.preventDefault();
            return;
        }

        checkoutForm.dataset.submitting = 'true';
        checkoutSubmitButton.disabled = true;
        checkoutSubmitButton.textContent = 'Memproses Pesanan...';
    });
});
</script>
@endsection
