<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WebSettingController extends Controller
{
    public function index()
    {
        $settings = [
            'site_name'    => Setting::get('site_name', 'AmikomEventHub'),
            'hero_title'   => Setting::get('hero_title', 'Temukan & Ikuti Event Kampus Seru di AMIKOM'),
            'hero_subtitle'=> Setting::get('hero_subtitle', 'Platform terpadu pendaftaran workshop, seminar IT, dan perlombaan resmi mahasiswa.'),
            'contact_email'=> Setting::get('contact_email', 'support@amikomevent.ac.id'),
            'contact_wa'   => Setting::get('contact_wa', '081234567890'),
            'hero_banner'  => Setting::get('hero_banner'),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'site_name'     => 'required|string|max:255',
            'hero_title'    => 'required|string|max:255',
            'hero_subtitle' => 'required|string',
            'contact_email' => 'nullable|email',
            'contact_wa'    => 'nullable|string',
            'hero_banner'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        foreach (['site_name', 'hero_title', 'hero_subtitle', 'contact_email', 'contact_wa'] as $field) {
            Setting::updateOrCreate(['key' => $field], ['value' => $data[$field]]);
        }

        // Handle upload banner jika ada
        if ($request->hasFile('hero_banner')) {
            $path = $request->file('hero_banner')->store('web_assets', 'public');
            Setting::updateOrCreate(['key' => 'hero_banner'], ['value' => $path]);
        }

        return redirect()->back()->with('success', 'Tampilan Web View berhasil diperbarui!');
    }
}