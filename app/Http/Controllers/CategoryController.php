<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('books')->latest()->paginate(10);
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        // 1. Validasi
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:categories,name'
        ]);

        // 2. Simpan Data
        \App\Models\Category::create($validated);

        // 3. Cek Tombol Mana yang Ditekan
        if ($request->input('action') == 'save_and_create') {
            return redirect()->route('categories.create')
                ->with('success', 'Kategori "' . $request->name . '" berhasil ditambahkan. Silakan tambah lagi.');
        }

        // 4. Default Redirect (Ke Index)
        return redirect()->route('categories.index')
            ->with('success', 'Kategori baru berhasil ditambahkan.');
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:categories,name,'.$category->id
        ]);

        $category->update($request->all());
        return redirect()->route('categories.index')->with('success', 'Nama kategori diperbarui.');
    }

    public function destroy(Category $category)
    {
        // Cek dulu, kalau ada buku yang pakai kategori ini, tolak hapus!
        if ($category->books()->count() > 0) {
            return back()->with('error', 'Gagal hapus: Masih ada buku yang menggunakan kategori ini.');
        }

        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Kategori dihapus.');
    }
}
