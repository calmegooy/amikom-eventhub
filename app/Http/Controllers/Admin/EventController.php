<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::with('category')->latest()->get();

        return view('admin.events.index', compact('events'));
    }

    public function create()
    {
        $categories = Category::all();

        return view('admin.events.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required',
            'date' => 'required|date',
            'location' => 'required',
            'price' => 'required|integer|min:0',
            'stock' => 'required|integer|min:0',
            'poster' => 'required|image|mimes:jpg,png,jpeg,webp|max:2048',
        ]);

        $data = $request->only([
            'category_id', 'title', 'description',
            'date', 'location', 'price', 'stock',
        ]);
        $data['organizer_id'] = Auth::id();
        $data['organization_id'] = null;

        if ($request->hasFile('poster')) {
            $data['poster_path'] = $request->file('poster')
                ->store('posters', 'public');
        }

        Event::create($data);

        return redirect()->route('admin.events.index')
            ->with('success', 'Event berhasil dibuat.');
    }

    /** Belum ada halaman detail terpisah untuk admin — arahkan ke form edit */
    public function show(Event $event)
    {
        return redirect()->route('admin.events.edit', $event);
    }

    public function edit(Event $event)
    {
        $categories = Category::all();

        return view('admin.events.edit', compact('event', 'categories'));
    }

    public function update(Request $request, Event $event)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required',
            'date' => 'required|date',
            'location' => 'required',
            'price' => 'required|integer|min:0',
            'stock' => 'required|integer|min:0',
            'poster' => 'nullable|image|mimes:jpg,png,jpeg,webp|max:2048',
        ]);

        // Ambil hanya field yang diperlukan — TANPA poster_path
        $data = $request->only([
            'category_id', 'title', 'description',
            'date', 'location', 'price', 'stock',
        ]);

        // Ganti foto HANYA jika ada file baru
        if ($request->hasFile('poster')) {
            // Hapus foto lama dari storage
            if ($event->poster_path &&
                Storage::disk('public')->exists($event->poster_path)) {
                Storage::disk('public')->delete($event->poster_path);
            }
            // Simpan foto baru
            $data['poster_path'] = $request->file('poster')
                ->store('posters', 'public');
        }
        // Jika tidak ada file baru → poster_path TIDAK diubah (tetap foto lama)

        $event->update($data);

        return redirect()->route('admin.events.index')
            ->with('success', 'Event "'.$event->title.'" berhasil diperbarui.');
    }

    public function destroy(Event $event)
    {
        if ($event->transactions()->exists()) {
            return back()->with('error', 'Event yang sudah memiliki transaksi tidak boleh dihapus karena riwayat penjualan harus dipertahankan.');
        }

        if ($event->poster_path &&
            Storage::disk('public')->exists($event->poster_path)) {
            Storage::disk('public')->delete($event->poster_path);
        }
        $event->delete();

        return redirect()->route('admin.events.index')
            ->with('success', 'Event berhasil dihapus.');
    }
}
