@extends('layouts.admin')

@section('title', 'Manajemen Partner')

@section('content')
<header class="flex justify-between items-center mb-10">
    <div>
        <h1 class="text-3xl font-black">Manajemen Partner</h1>
        <p class="text-slate-500 font-medium">Kelola daftar partner yang mendukung platform ini.</p>
    </div>
    <button onclick="openAddModal()"
        class="px-6 py-3 bg-indigo-600 text-white rounded-2xl font-bold shadow-lg shadow-indigo-100 hover:bg-indigo-700 active:scale-95 transition">
        + Tambah Partner
    </button>
</header>

{{-- Alert Success --}}
@if(session('success'))
<div class="mb-6 px-6 py-4 bg-green-50 border border-green-200 text-green-700 rounded-2xl font-semibold flex items-center gap-3">
    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
    </svg>
    {{ session('success') }}
</div>
@endif

{{-- Search Form (Soal 3) --}}
    <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-8 py-6 bg-slate-50/50 border-b flex gap-4">
            <form method="GET" action="{{ route('admin.partners.index') }}" class="relative flex-1 flex gap-3">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-4 flex items-center text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari nama partner..."
                        class="w-full pl-12 pr-5 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition font-medium">
                </div>
                <button type="submit" class="px-6 py-3 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition">
                    Cari
                </button>
                @if(request('search'))
                <a href="{{ route('admin.partners.index') }}" class="px-6 py-3 border border-slate-200 text-slate-600 rounded-xl font-bold hover:bg-slate-50 transition flex items-center justify-center">
                    Reset
                </a>
                @endif
            </form>
        </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50 text-slate-400 uppercase text-[10px] font-black tracking-widest">
                <tr>
                    <th class="px-8 py-4 w-16 text-center">No</th>
                    <th class="px-8 py-4">Nama Partner</th>
                    <th class="px-8 py-4">Logo URL</th>
                    <th class="px-8 py-4">Dibuat</th>
                    <th class="px-8 py-4">Diperbarui</th>
                    <th class="px-8 py-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y border-t">
                @forelse($partners as $index => $partner)
                <tr class="hover:bg-slate-50/50 transition">
                    <td class="px-8 py-5 font-bold text-slate-400 text-center">{{ $index + 1 }}</td>
                    <td class="px-8 py-5">
                        <div class="flex items-center gap-3">
                            @if($partner->logo_url)
                            <img src="{{ $partner->logo_url }}" alt="{{ $partner->name }}"
                                class="w-10 h-10 rounded-xl object-contain border border-slate-100"
                                onerror="this.src=''; this.onerror=null; this.parentElement.querySelector('.fallback').classList.remove('hidden'); this.classList.add('hidden');">
                            @endif
                            <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center font-bold text-sm uppercase fallback {{ $partner->logo_url ? 'hidden' : '' }}">
                                {{ substr($partner->name, 0, 1) }}
                            </div>
                            <p class="font-black text-slate-800">{{ $partner->name }}</p>
                        </div>
                    </td>
                    <td class="px-8 py-5 text-sm text-slate-500 max-w-xs truncate">
                        {{ $partner->logo_url ?? '-' }}
                    </td>
                    <td class="px-8 py-5 text-sm text-slate-500">{{ $partner->created_at->format('d M Y') }}</td>
                    <td class="px-8 py-5 text-sm text-slate-500">{{ $partner->updated_at->format('d M Y') }}</td>
                    <td class="px-8 py-5 text-center">
                        <div class="flex justify-center gap-2">
                            <button onclick="openEditModal({{ $partner->id }}, '{{ addslashes($partner->name) }}', '{{ addslashes($partner->logo_url ?? '') }}')"
                                class="p-2.5 bg-yellow-50 text-yellow-600 rounded-xl hover:bg-yellow-500 hover:text-white transition shadow-sm" title="Edit">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <button onclick="openDeleteModal({{ $partner->id }}, '{{ addslashes($partner->name) }}')"
                                class="p-2.5 bg-rose-50 text-rose-600 rounded-xl hover:bg-rose-600 hover:text-white transition shadow-sm" title="Hapus">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-8 py-16 text-center text-slate-400 font-medium">
                        @if(request('search'))
                            Tidak ada partner yang cocok dengan pencarian "<strong>{{ request('search') }}</strong>".
                        @else
                            Belum ada partner. Tambahkan partner pertama Anda!
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-8 py-5 bg-slate-50/50 border-t">
        <p class="text-sm text-slate-500 font-medium">Menampilkan {{ $partners->count() }} partner</p>
    </div>
</div>

{{-- Modal Tambah/Edit --}}
<div id="partnerModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden items-center justify-center p-6">
    <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden">
        <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-8 py-6 flex justify-between items-center">
            <h2 id="modalTitle" class="text-xl font-bold text-white">Tambah Partner</h2>
            <button onclick="closeModal()" class="text-white hover:bg-indigo-800 p-2 rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="p-8">
            <form id="partnerForm" method="POST">
                @csrf
                <span id="methodField"></span>
                <div class="mb-5">
                    <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide">Nama Partner *</label>
                    <input id="partnerName" name="name" type="text" placeholder="Contoh: Google Indonesia"
                        class="w-full px-5 py-3 border-2 border-slate-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 outline-none transition font-medium" required>
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide">Logo URL</label>
                    <input id="partnerLogo" name="logo_url" type="url" placeholder="https://example.com/logo.png"
                        class="w-full px-5 py-3 border-2 border-slate-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 outline-none transition font-medium">
                    <p class="text-xs text-slate-400 mt-1">Opsional. Masukkan URL gambar logo partner.</p>
                </div>
                <div class="flex gap-4 justify-end">
                    <button type="button" onclick="closeModal()"
                        class="px-6 py-3 border-2 border-slate-200 text-slate-700 rounded-xl font-bold hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-8 py-3 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition shadow-lg shadow-indigo-200">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Hapus --}}
<div id="deleteModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden items-center justify-center p-6">
    <div class="bg-white w-full max-w-sm rounded-3xl shadow-2xl overflow-hidden">
        <div class="bg-gradient-to-r from-rose-600 to-rose-700 px-8 py-6">
            <h2 class="text-xl font-bold text-white">Konfirmasi Hapus</h2>
        </div>
        <div class="p-8 text-center">
            <div class="w-16 h-16 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>
            <p class="text-slate-600 mb-1">Yakin ingin menghapus partner:</p>
            <p id="deleteItemName" class="text-lg font-black text-slate-900 mb-4"></p>
            <p class="text-sm text-slate-400 mb-6">Tindakan ini tidak dapat dibatalkan.</p>
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="flex gap-3 justify-center">
                    <button type="button" onclick="closeDeleteModal()"
                        class="px-6 py-3 border-2 border-slate-200 text-slate-700 rounded-xl font-bold hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-6 py-3 bg-rose-600 text-white rounded-xl font-bold hover:bg-rose-700 transition">
                        Ya, Hapus
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('extra-scripts')
<script>
    const storeUrl = "{{ route('admin.partners.store') }}";

    function openAddModal() {
        document.getElementById('modalTitle').textContent = 'Tambah Partner Baru';
        document.getElementById('partnerName').value = '';
        document.getElementById('partnerLogo').value = '';
        document.getElementById('partnerForm').action = storeUrl;
        document.getElementById('methodField').innerHTML = '';
        document.getElementById('partnerModal').classList.remove('hidden');
        document.getElementById('partnerModal').classList.add('flex');
    }

    function openEditModal(id, name, logo) {
        document.getElementById('modalTitle').textContent = 'Edit Partner';
        document.getElementById('partnerName').value = name;
        document.getElementById('partnerLogo').value = logo;
        document.getElementById('partnerForm').action = `/admin/partners/${id}`;
        document.getElementById('methodField').innerHTML = '@method("PUT")';
        document.getElementById('partnerModal').classList.remove('hidden');
        document.getElementById('partnerModal').classList.add('flex');
    }

    function closeModal() {
        document.getElementById('partnerModal').classList.add('hidden');
        document.getElementById('partnerModal').classList.remove('flex');
    }

    function openDeleteModal(id, name) {
        document.getElementById('deleteItemName').textContent = name;
        document.getElementById('deleteForm').action = `/admin/partners/${id}`;
        document.getElementById('deleteModal').classList.remove('hidden');
        document.getElementById('deleteModal').classList.add('flex');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
        document.getElementById('deleteModal').classList.remove('flex');
    }
</script>
@endsection
