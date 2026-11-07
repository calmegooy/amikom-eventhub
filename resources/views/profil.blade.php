@extends('layouts.app')

@section('title', 'Profil - AmikomEventHub')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-20 min-h-screen">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-12">
        <div class="md:col-span-4">
            <div class="bg-white p-10 rounded-[3rem] border border-slate-100 shadow-sm text-center">
                <div class="w-32 h-32 bg-indigo-100 rounded-[2.5rem] mx-auto mb-8 flex items-center justify-center overflow-hidden border-4 border-white shadow-xl">
                    @auth
                        @if(auth()->user()->avatar)
                            <img src="{{ auth()->user()->avatar }}" alt="Avatar" class="w-full h-full object-cover">
                        @else
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=4f46e5&color=fff&size=128" alt="Avatar">
                        @endif
                    @else
                        <img src="https://ui-avatars.com/api/?name=Yoga+Adiyatma&background=4f46e5&color=fff&size=128" alt="Avatar">
                    @endauth
                </div>
                <h2 class="text-2xl font-black tracking-tight">
                    @auth
                        {{ auth()->user()->name }}
                    @else
                        Yoga Adiyatma
                    @endauth
                </h2>
                <p class="text-indigo-600 font-black text-xs uppercase tracking-[0.2em] mt-3">
                    @auth
                        {{ auth()->user()->role }}
                    @else
                        Mahasiswa Aktif
                    @endauth
                </p>
                
                @auth
                    <form action="{{ route('user.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full mt-10 py-4 bg-rose-50 text-rose-600 rounded-2xl font-bold hover:bg-rose-600 hover:text-white transition duration-300">
                            Keluar Sistem
                        </button>
                    </form>
                @else
                    <a href="{{ route('user.login') }}" class="block text-center w-full mt-10 py-4 bg-indigo-600 text-white rounded-2xl font-bold hover:bg-indigo-700 transition duration-300 shadow-lg shadow-indigo-200">
                        Masuk / Login
                    </a>
                @endauth
            </div>
        </div>

        <div class="md:col-span-8 bg-white p-12 rounded-[3rem] border border-slate-100 shadow-sm">
            <div class="flex items-center gap-4 mb-12">
                <div class="w-1.5 h-8 bg-indigo-600 rounded-full"></div>
                <h1 class="text-3xl font-black">Informasi Akun</h1>
            </div>
            <div class="space-y-10">
                @auth
                    <div class="flex flex-col md:flex-row justify-between border-b border-slate-50 pb-6">
                        <span class="text-slate-400 font-black text-[10px] uppercase tracking-widest">Email</span>
                        <span class="text-indigo-600 font-bold text-lg">{{ auth()->user()->email }}</span>
                    </div>
                    <div class="flex flex-col md:flex-row justify-between border-b border-slate-50 pb-6">
                        <span class="text-slate-400 font-black text-[10px] uppercase tracking-widest">Nama Lengkap</span>
                        <span class="text-slate-900 font-bold text-lg">{{ auth()->user()->name }}</span>
                    </div>
                    <div class="flex flex-col md:flex-row justify-between border-b border-slate-50 pb-6">
                        <span class="text-slate-400 font-black text-[10px] uppercase tracking-widest">Hak Akses / Role</span>
                        <span class="text-slate-900 font-bold text-lg capitalize">{{ auth()->user()->role }}</span>
                    </div>
                    @if(auth()->user()->role === 'organizer' && auth()->user()->organization_name)
                        <div class="flex flex-col md:flex-row justify-between border-b border-slate-50 pb-6">
                            <span class="text-slate-400 font-black text-[10px] uppercase tracking-widest">Nama Organisasi</span>
                            <span class="text-slate-900 font-bold text-lg">{{ auth()->user()->organization_name }}</span>
                        </div>
                    @endif
                @else
                    <div class="flex flex-col md:flex-row justify-between border-b border-slate-50 pb-6">
                        <span class="text-slate-400 font-black text-[10px] uppercase tracking-widest">Nomor Induk Mahasiswa</span>
                        <span class="text-indigo-600 font-mono font-bold text-lg tracking-tighter">24.12.3320</span>
                    </div>
                    <div class="flex flex-col md:flex-row justify-between border-b border-slate-50 pb-6">
                        <span class="text-slate-400 font-black text-[10px] uppercase tracking-widest">Nama Mahasiswa</span>
                        <span class="text-slate-900 font-bold text-lg">Yoga Adiyatma</span>
                    </div>
                    <div class="flex flex-col md:flex-row justify-between border-b border-slate-50 pb-6">
                        <span class="text-slate-400 font-black text-[10px] uppercase tracking-widest">Program Studi</span>
                        <span class="text-slate-900 font-bold text-lg">S1 Sistem Informasi</span>
                    </div>
                @endauth
            </div>
        </div>
    </div>
</div>
@endsection