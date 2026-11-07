<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventController extends Controller
{
    /** Daftar event milik organizer ini saja (multi-tenant scoping) */
    public function index()
    {
        $events = Event::query()
            ->managedBy(Auth::user())
            ->with('category')
            ->latest()
            ->paginate(10);

        return view('organizer.events.index', compact('events'));
    }

    public function create()
    {
        Gate::authorize('create', Event::class);
        $categories = Category::all();

        return view('organizer.events.create', compact('categories'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Event::class);

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date|after:now',
            'location' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:1',
            'poster' => 'nullable|image|max:2048',
            'early_bird_price' => 'nullable|numeric|min:0',
            'early_bird_until' => 'nullable|date',
            'presale_price' => 'nullable|numeric|min:0',
            'presale_until' => 'nullable|date',
        ]);

        $posterPath = null;
        if ($request->hasFile('poster')) {
            $posterPath = $request->file('poster')->store('posters', 'public');
        }

        $user = Auth::user();
        $organization = $user->activeOrganization();

        Event::create([
            'category_id' => $validated['category_id'],
            'organizer_id' => $user->id,
            'organization_id' => $organization->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'date' => $validated['date'],
            'location' => $validated['location'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'poster_path' => $posterPath,
            'early_bird_price' => $validated['early_bird_price'] ?? null,
            'early_bird_until' => $validated['early_bird_until'] ?? null,
            'presale_price' => $validated['presale_price'] ?? null,
            'presale_until' => $validated['presale_until'] ?? null,
        ]);

        return redirect()->route('organizer.events.index')->with('success', 'Event berhasil dibuat!');
    }

    /** Belum ada halaman detail terpisah untuk organizer — arahkan ke form edit */
    public function show($id)
    {
        $event = $this->managedEvent($id);

        return redirect()->route('organizer.events.edit', $event);
    }

    public function edit($id)
    {
        $event = $this->managedEvent($id);
        $categories = Category::all();

        return view('organizer.events.edit', compact('event', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $event = $this->managedEvent($id);

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date',
            'location' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'poster' => 'nullable|image|max:2048',
            'early_bird_price' => 'nullable|numeric|min:0',
            'early_bird_until' => 'nullable|date',
            'presale_price' => 'nullable|numeric|min:0',
            'presale_until' => 'nullable|date',
        ]);

        $posterPath = $event->poster_path;
        if ($request->hasFile('poster')) {
            if ($posterPath) {
                Storage::disk('public')->delete($posterPath);
            }
            $posterPath = $request->file('poster')->store('posters', 'public');
        }

        $event->update([
            'category_id' => $validated['category_id'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'date' => $validated['date'],
            'location' => $validated['location'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'poster_path' => $posterPath,
            'early_bird_price' => $validated['early_bird_price'] ?? null,
            'early_bird_until' => $validated['early_bird_until'] ?? null,
            'presale_price' => $validated['presale_price'] ?? null,
            'presale_until' => $validated['presale_until'] ?? null,
        ]);

        return redirect()->route('organizer.events.index')->with('success', 'Event berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $event = $this->managedEvent($id);
        Gate::authorize('delete', $event);

        if ($event->transactions()->exists()) {
            return back()->with('error', 'Event yang sudah memiliki transaksi tidak boleh dihapus karena riwayat penjualan harus dipertahankan.');
        }

        if ($event->poster_path) {
            Storage::disk('public')->delete($event->poster_path);
        }

        $event->delete();

        return redirect()->route('organizer.events.index')->with('success', 'Event berhasil dihapus.');
    }

    public function exportAttendees(Event $event)
    {
        Gate::authorize('update', $event);
        $event->load(['tickets' => fn ($query) => $query->with('transaction')->orderBy('code')]);

        return response()->streamDownload(function () use ($event) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Kode Tiket', 'Nama', 'Email', 'Status', 'Waktu Check-in']);

            foreach ($event->tickets as $ticket) {
                fputcsv($output, [
                    $ticket->code,
                    $ticket->holder_name,
                    $ticket->holder_email,
                    $ticket->status,
                    $ticket->checked_in_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($output);
        }, 'peserta-'.Str::slug($event->title).'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function managedEvent(int|string $id): Event
    {
        $event = Event::query()->managedBy(Auth::user())->findOrFail($id);
        Gate::authorize('update', $event);

        return $event;
    }
}
