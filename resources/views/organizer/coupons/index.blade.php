@extends('layouts.organizer', ['title' => 'Kupon & Voucher'])

@section('content')
<h1 class="text-3xl font-black mb-1">Kupon & Voucher</h1>
<p class="text-slate-500 font-medium mb-8">Buat kode diskon seperti <code class="bg-slate-100 px-2 py-1 rounded text-emerald-600">MAHASISWA50</code> untuk pembeli Anda.</p>

@if(session('success'))
<div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl font-semibold text-sm">{{ session('success') }}</div>
@endif

<div class="grid md:grid-cols-2 gap-8">
    <!-- Form Buat Kupon -->
    <form action="{{ route('organizer.coupons.store') }}" method="POST" class="bg-white rounded-3xl border border-slate-100 shadow-sm p-8 space-y-4 h-fit">
        @csrf
        <div>
            <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Kode Kupon</label>
            <input type="text" name="code" required placeholder="MAHASISWA50" class="w-full border-2 border-slate-100 rounded-xl px-4 py-3 focus:border-emerald-500 focus:outline-none uppercase">
        </div>
        <div>
            <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Berlaku untuk Event</label>
            <select name="event_id" class="w-full border-2 border-slate-100 rounded-xl px-4 py-3 focus:border-emerald-500 focus:outline-none">
                <option value="">Semua Event Saya</option>
                @foreach($events as $event)
                <option value="{{ $event->id }}">{{ $event->title }}</option>
                @endforeach
            </select>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Tipe</label>
                <select name="type" class="w-full border-2 border-slate-100 rounded-xl px-4 py-3 focus:border-emerald-500 focus:outline-none">
                    <option value="percent">Persen (%)</option>
                    <option value="fixed">Potongan Tetap (Rp)</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Nilai</label>
                <input type="number" name="value" required class="w-full border-2 border-slate-100 rounded-xl px-4 py-3 focus:border-emerald-500 focus:outline-none">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Maks. Pemakaian</label>
                <input type="number" name="max_usage" placeholder="Tanpa batas" class="w-full border-2 border-slate-100 rounded-xl px-4 py-3 focus:border-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Kedaluwarsa</label>
                <input type="date" name="expires_at" class="w-full border-2 border-slate-100 rounded-xl px-4 py-3 focus:border-emerald-500 focus:outline-none">
            </div>
        </div>
        <button type="submit" class="w-full py-4 bg-emerald-600 text-white rounded-2xl font-bold hover:bg-emerald-700 transition">Buat Kupon</button>
    </form>

    <!-- Daftar Kupon -->
    <div class="space-y-3">
        @forelse($coupons as $coupon)
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex justify-between items-center">
            <div>
                <p class="font-black text-emerald-600">{{ $coupon->code }}</p>
                <p class="text-xs text-slate-400">
                    {{ $coupon->type === 'percent' ? $coupon->value.'%' : 'Rp '.number_format($coupon->value) }} •
                    {{ $coupon->event->title ?? 'Semua Event' }} •
                    Dipakai {{ $coupon->used_count }}{{ $coupon->max_usage ? '/'.$coupon->max_usage : '' }}x
                </p>
            </div>
            <form action="{{ route('organizer.coupons.destroy', $coupon) }}" method="POST" onsubmit="return confirm('Hapus kupon ini?')">
                @csrf @method('DELETE')
                <button class="p-2 bg-rose-50 text-rose-600 rounded-xl hover:bg-rose-600 hover:text-white transition text-xs font-bold px-3">Hapus</button>
            </form>
        </div>
        @empty
        <p class="text-slate-400 text-sm">Anda belum membuat kupon.</p>
        @endforelse
    </div>
</div>
@endsection
