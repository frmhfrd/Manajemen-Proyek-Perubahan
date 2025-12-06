<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('books')->latest()->paginate(10); // withCount untuk lihat ada berapa buku di kategori ini
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:50|unique:categories,nama'
        ]);

        Category::create($request->all());
        return redirect()->route('categories.index')->with('success', 'Kategori baru berhasil ditambahkan.');
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'nama' => 'required|string|max:50|unique:categories,nama,'.$category->id
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
