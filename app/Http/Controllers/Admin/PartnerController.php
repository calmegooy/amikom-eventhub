<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    // READ + SEARCH (Soal 2 & Soal 3)
    public function index(Request $request)
    {
    // Mengambil input pencarian dari form (Soal 3)
    $search = $request->query('search');

    if ($search) {
        // Menggunakan sintaks pencarian Eloquent LIKE (Syarat UTS Soal 3)
        $partners = Partner::where('name', 'LIKE', "%{$search}%")->get();
    } else {
        $partners = Partner::all();
    }

    return view('admin.partners.index', compact('partners'));
    }

    // CREATE
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'logo_url' => 'nullable|url|max:500',
        ]);

        Partner::create($request->only('name', 'logo_url'));

        return redirect()->route('admin.partners.index')
            ->with('success', 'Partner "' . $request->name . '" berhasil ditambahkan!');
    }

    // UPDATE
    public function update(Request $request, Partner $partner)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'logo_url' => 'nullable|url|max:500',
        ]);

        $partner->update($request->only('name', 'logo_url'));

        return redirect()->route('admin.partners.index')
            ->with('success', 'Partner "' . $request->name . '" berhasil diperbarui!');
    }

    // DELETE
    public function destroy(Partner $partner)
    {
        $name = $partner->name;
        $partner->delete();

        return redirect()->route('admin.partners.index')
            ->with('success', 'Partner "' . $name . '" berhasil dihapus!');
    }
}
