@extends('layouts.organizer')

@section('title', 'Profil Organisasi')
@section('content')
<div class="max-w-4xl">
    <h1 class="text-2xl sm:text-3xl font-black mb-1">Profil Organisasi</h1>
    <p class="text-slate-500 font-medium mb-8">
        Informasi ini menjadi identitas tenant pada event dan halaman profil publik.
    </p>

    @if($errors->any())
        <div class="mb-6 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-2xl text-sm">
            <p class="font-bold mb-1">Periksa kembali data yang Anda isi.</p>
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('organizer.organization.update') }}"
          method="POST"
          enctype="multipart/form-data"
          class="bg-white rounded-[2rem] sm:rounded-[2.5rem] border border-slate-100 shadow-sm p-5 sm:p-8 lg:p-10 space-y-6">
        @csrf
        @method('PUT')

        <div class="flex flex-col sm:flex-row sm:items-center gap-5 pb-6 border-b border-slate-100">
            @if($organization->logo_path)
                <img src="{{ asset('storage/'.$organization->logo_path) }}"
                     alt="Logo {{ $organization->name }}"
                     class="w-20 h-20 rounded-2xl object-cover border border-slate-100">
            @else
                <div class="w-20 h-20 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-3xl font-black">
                    {{ strtoupper(substr($organization->name, 0, 1)) }}
                </div>
            @endif
            <div class="flex-1">
                <label for="logo" class="text-xs font-bold text-slate-500 uppercase mb-2 block">Logo Organisasi</label>
                <input id="logo"
                       type="file"
                       name="logo"
                       accept=".jpg,.jpeg,.png,.webp"
                       class="block w-full text-sm text-slate-500 file:mr-4 file:rounded-xl file:border-0 file:bg-emerald-50 file:px-4 file:py-2 file:font-bold file:text-emerald-700 hover:file:bg-emerald-100">
                <p class="text-xs text-slate-400 mt-2">JPG, PNG, atau WebP. Maksimal 2 MB.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div>
                <label for="name" class="text-xs font-bold text-slate-500 uppercase mb-1 block">Nama Organisasi</label>
                <input id="name" type="text" name="name" required
                       value="{{ old('name', $organization->name) }}"
                       class="w-full border-2 border-slate-100 rounded-xl px-4 py-3 focus:border-emerald-500 focus:outline-none">
            </div>
            <div>
                <label for="slug" class="text-xs font-bold text-slate-500 uppercase mb-1 block">Slug Profil</label>
                <input id="slug" type="text" name="slug" required
                       value="{{ old('slug', $organization->slug) }}"
                       class="w-full border-2 border-slate-100 rounded-xl px-4 py-3 focus:border-emerald-500 focus:outline-none">
                <p class="text-xs text-slate-400 mt-2">Gunakan huruf, angka, tanda minus, atau garis bawah.</p>
            </div>
        </div>

        <div>
            <label for="description" class="text-xs font-bold text-slate-500 uppercase mb-1 block">Deskripsi</label>
            <textarea id="description" name="description" rows="5"
                      class="w-full border-2 border-slate-100 rounded-xl px-4 py-3 focus:border-emerald-500 focus:outline-none">{{ old('description', $organization->description ?? $organization->bio) }}</textarea>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div>
                <label for="email" class="text-xs font-bold text-slate-500 uppercase mb-1 block">Email Publik</label>
                <input id="email" type="email" name="email"
                       value="{{ old('email', $organization->email) }}"
                       class="w-full border-2 border-slate-100 rounded-xl px-4 py-3 focus:border-emerald-500 focus:outline-none">
            </div>
            <div>
                <label for="phone" class="text-xs font-bold text-slate-500 uppercase mb-1 block">Nomor Telepon</label>
                <input id="phone" type="text" name="phone"
                       value="{{ old('phone', $organization->phone) }}"
                       class="w-full border-2 border-slate-100 rounded-xl px-4 py-3 focus:border-emerald-500 focus:outline-none">
            </div>
        </div>

        <div>
            <label for="address" class="text-xs font-bold text-slate-500 uppercase mb-1 block">Alamat</label>
            <textarea id="address" name="address" rows="3"
                      class="w-full border-2 border-slate-100 rounded-xl px-4 py-3 focus:border-emerald-500 focus:outline-none">{{ old('address', $organization->address) }}</textarea>
        </div>

        <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 pt-2">
            <a href="{{ route('organizer.profile', $organization->slug) }}"
               class="text-center px-5 py-3 rounded-xl border border-slate-200 text-slate-600 font-bold hover:bg-slate-50 transition">
                Lihat Profil Publik
            </a>
            <button type="submit"
                    class="px-6 py-3 bg-emerald-600 text-white rounded-xl font-bold hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-100 transition">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
