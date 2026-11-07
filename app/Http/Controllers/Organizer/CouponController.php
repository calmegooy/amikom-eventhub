<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CouponController extends Controller
{
    /** Hanya tampilkan kupon milik organizer ini */
    public function index()
    {
        $user = Auth::user();
        $coupons = Coupon::query()->managedBy($user)->with('event')->latest()->get();
        $events = Event::query()->managedBy($user)->latest()->get();

        return view('organizer.coupons.index', compact('coupons', 'events'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code',
            'event_id' => 'nullable|exists:events,id',
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric|min:1',
            'max_usage' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
        ]);

        // Pastikan jika event_id diisi, event tersebut benar milik organizer ini
        $event = null;
        if (! empty($validated['event_id'])) {
            $event = Event::query()
                ->managedBy(Auth::user())
                ->whereKey($validated['event_id'])
                ->first();
            if (! $event) {
                abort(403, 'Event tidak ditemukan di akun Anda.');
            }
        }

        $organization = $event?->organization ?: Auth::user()->activeOrganization();

        Coupon::create([
            'organizer_id' => Auth::id(),
            'organization_id' => $organization?->id,
            'event_id' => $validated['event_id'] ?? null,
            'code' => strtoupper($validated['code']),
            'type' => $validated['type'],
            'value' => $validated['value'],
            'max_usage' => $validated['max_usage'] ?? null,
            'used_count' => 0,
            'expires_at' => $validated['expires_at'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->route('organizer.coupons.index')->with('success', 'Kupon berhasil dibuat!');
    }

    public function destroy($id)
    {
        $coupon = Coupon::query()->managedBy(Auth::user())->findOrFail($id);

        $coupon->delete();

        return redirect()->route('organizer.coupons.index')->with('success', 'Kupon berhasil dihapus.');
    }

    public function toggle($id)
    {
        $coupon = Coupon::query()->managedBy(Auth::user())->findOrFail($id);

        $coupon->update(['is_active' => ! $coupon->is_active]);
        $status = $coupon->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('organizer.coupons.index')->with('success', "Kupon berhasil {$status}.");
    }
}
