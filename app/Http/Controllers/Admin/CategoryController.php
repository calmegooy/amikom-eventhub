<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

// ✅ PERBAIKAN: Nama kelas diubah dari "Controller" menjadi "CategoryController"
// ❌ SEBELUMNYA: class   Controller extends Controller
// ✅ SESUDAHNYA: class CategoryController extends Controller
class CategoryController extends Controller
{
    // READ + SEARCH (Soal 1 & Soal 3)
    public function index(Request $request)
    {
        // 1. Ambil input dari kolom search html (Soal 3)
        $search = $request->query('search');

        // 2. Jika ada input pencarian, saring data dengan klausa LIKE (Soal 3)
        if ($search) {
            $categories = Category::where('name', 'LIKE', "%{$search}%")->get();
        } else {
            // Jika tidak ada pencarian, tampilkan semua data kategori seperti biasa
            $categories = Category::all();
        }

        // 3. Kirim data ke view index kategori
        return view('admin.categories.index', compact('categories'));
    }

    // CREATE
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori "' . $request->name . '" berhasil ditambahkan!');
    }

    // UPDATE
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
        ]);

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori "' . $request->name . '" berhasil diperbarui!');
    }

    // DELETE
    public function destroy(Category $category)
    {
        $name = $category->name;
        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori "' . $name . '" berhasil dihapus!');
    }
}