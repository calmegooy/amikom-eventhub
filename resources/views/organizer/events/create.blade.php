@extends('layouts.organizer', ['title' => 'Tambah Event'])

@section('content')
<h1 class="text-3xl font-black mb-1">Tambah Event Baru</h1>
<p class="text-slate-500 font-medium mb-8">Isi detail acara yang akan Anda selenggarakan.</p>

<form action="{{ route('organizer.events.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm p-10 max-w-3xl space-y-6">
    @csrf

    <div>
        <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Judul Event</label>
        <input type="text" name="title" required value="{{ old('title') }}" class="w-full border-2 border-slate-100 rounded-xl px-4 py-3 focus:border-emerald-500 focus:outline-none">
    </div>

    <div>
        <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Kategori</label>
        <select name="category_id" required class="w-full border-2 border-slate-100 rounded-xl px-4 py-3 focus:border-emerald-500 focus:outline-none">
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Deskripsi</label>
        <textarea name="description" rows="4" required class="w-full border-2 border-slate-100 rounded-xl px-4 py-3 focus:border-emerald-500 focus:outline-none">{{ old('description') }}</textarea>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Tanggal & Waktu</label>
            <input type="datetime-local" name="date" required class="w-full border-2 border-slate-100 rounded-xl px-4 py-3 focus:border-emerald-500 focus:outline-none">
        </div>
        <div>
            <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Lokasi</label>
            <input type="text" name="location" required class="w-full border-2 border-slate-100 rounded-xl px-4 py-3 focus:border-emerald-500 focus:outline-none">
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Harga Regular (Rp) — isi 0 untuk gratis</label>
            <input type="number" name="price" required value="0" class="w-full border-2 border-slate-100 rounded-xl px-4 py-3 focus:border-emerald-500 focus:outline-none">
        </div>
        <div>
            <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Stok Tiket</label>
            <input type="number" name="stock" required class="w-full border-2 border-slate-100 rounded-xl px-4 py-3 focus:border-emerald-500 focus:outline-none">
        </div>
    </div>

    <div class="bg-emerald-50 rounded-2xl p-6 space-y-4">
        <p class="font-black text-emerald-700 text-sm">💰 Dynamic Pricing (Opsional)</p>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Harga Early Bird</label>
                <input type="number" name="early_bird_price" class="w-full border-2 border-white rounded-xl px-4 py-3 focus:border-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Berlaku Sampai</label>
                <input type="datetime-local" name="early_bird_until" class="w-full border-2 border-white rounded-xl px-4 py-3 focus:border-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Harga Presale</label>
                <input type="number" name="presale_price" class="w-full border-2 border-white rounded-xl px-4 py-3 focus:border-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Berlaku Sampai</label>
                <input type="datetime-local" name="presale_until" class="w-full border-2 border-white rounded-xl px-4 py-3 focus:border-emerald-500 focus:outline-none">
            </div>
        </div>
    </div>

    <div>
        <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Poster Event</label>
        <input type="file" name="poster" required accept="image/*" class="w-full border-2 border-slate-100 rounded-xl px-4 py-3">
    </div>

    <button type="submit" class="w-full py-4 bg-emerald-600 text-white rounded-2xl font-bold hover:bg-emerald-700 transition">Simpan Event</button>
</form>
@endsection
