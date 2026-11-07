@extends('layouts.app')

@section('title', 'Tulis Ulasan - ' . $transaction->event->title)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-indigo-50/30 to-slate-50 py-16 px-4">
<div class="max-w-2xl mx-auto">

    {{-- Back Button --}}
    <a href="{{ route('user.tickets') }}" class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-800 font-bold text-sm mb-8 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke Tiket Saya
    </a>

    {{-- Event Info Card --}}
    <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 rounded-3xl p-6 mb-6 text-white shadow-xl shadow-indigo-500/20">
        <p class="text-indigo-200 text-xs font-bold uppercase tracking-widest mb-1">Anda menilai event</p>
        <h1 class="text-2xl font-black leading-tight">{{ $transaction->event->title }}</h1>
        <p class="text-indigo-200 text-sm mt-2 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            {{ $transaction->event->date->translatedFormat('d F Y') }}
        </p>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 font-bold text-sm rounded-2xl flex items-center gap-3">
            <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            {{ session('success') }}
        </div>
    @endif

    {{-- Review Form --}}
    <div class="bg-white rounded-3xl border border-slate-100 shadow-lg p-8">
        <h2 class="text-xl font-black text-slate-800 mb-6">Bagikan Pengalaman Anda</h2>

        <form action="{{ route('review.store', $transaction->order_id) }}" method="POST">
            @csrf

            {{-- Star Rating --}}
            <div class="mb-8">
                <label class="block text-sm font-bold text-slate-700 mb-4">Penilaian Anda <span class="text-rose-500">*</span></label>
                <div class="flex gap-3 justify-center" id="star-container">
                    @for($i = 1; $i <= 5; $i++)
                        <button type="button" data-value="{{ $i }}"
                            class="star-btn text-5xl text-slate-200 transition-all duration-150 hover:scale-110 focus:outline-none select-none"
                            aria-label="{{ $i }} bintang">★</button>
                    @endfor
                </div>
                <p id="star-label" class="text-center text-sm text-slate-400 mt-3 h-5"></p>
                <input type="hidden" name="rating" id="rating-value" value="{{ old('rating') }}" required>
                @error('rating') <p class="text-rose-500 text-sm mt-2 text-center font-bold">{{ $message }}</p> @enderror
            </div>

            {{-- Comment --}}
            <div class="mb-6">
                <label class="block text-sm font-bold text-slate-700 mb-2">
                    Komentar <span class="text-slate-400 font-normal">(opsional)</span>
                </label>
                <textarea name="comment" id="comment" rows="5"
                    placeholder="Ceritakan pengalaman Anda di event ini..."
                    class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 focus:bg-white outline-none transition font-medium resize-none text-sm text-slate-700 placeholder-slate-400"
                >{{ old('comment') }}</textarea>
                @error('comment') <p class="text-rose-500 text-sm mt-1 font-bold">{{ $message }}</p> @enderror
            </div>

            <button type="submit"
                class="w-full py-4 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white rounded-2xl font-black text-base shadow-lg shadow-indigo-500/30 hover:from-indigo-700 hover:to-indigo-800 active:scale-[0.99] transition-all duration-150">
                Kirim Ulasan Saya
            </button>
        </form>
    </div>

</div>
</div>

@push('scripts')
<script>
const stars = document.querySelectorAll('.star-btn');
const ratingInput = document.getElementById('rating-value');
const starLabel = document.getElementById('star-label');
const labels = ['', 'Sangat Buruk', 'Buruk', 'Cukup', 'Bagus', 'Sangat Bagus!'];

function updateStars(value) {
    stars.forEach((star, i) => {
        if (i < value) {
            star.classList.remove('text-slate-200');
            star.classList.add('text-amber-400');
        } else {
            star.classList.remove('text-amber-400');
            star.classList.add('text-slate-200');
        }
    });
    starLabel.textContent = value ? labels[value] : '';
    starLabel.className = value ? 'text-center text-sm font-bold text-amber-600 mt-3 h-5' : 'text-center text-sm text-slate-400 mt-3 h-5';
}

if (ratingInput.value) updateStars(parseInt(ratingInput.value));

stars.forEach((star) => {
    star.addEventListener('click', () => {
        const val = parseInt(star.dataset.value);
        ratingInput.value = val;
        updateStars(val);
    });
    star.addEventListener('mouseover', () => updateStars(parseInt(star.dataset.value)));
    star.addEventListener('mouseout', () => updateStars(parseInt(ratingInput.value) || 0));
});
</script>
@endpush
@endsection
