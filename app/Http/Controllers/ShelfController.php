<?php

namespace App\Http\Controllers;

use App\Models\Shelf;
use Illuminate\Http\Request;

class ShelfController extends Controller
{
    public function index()
    {
        $shelves = Shelf::withCount('books')->latest()->paginate(10);
        return view('shelves.index', compact('shelves'));
    }

    public function create()
    {
        return view('shelves.create');
    }

    public function store(Request $request)
    {
        // 1. Validasi
        $validated = $request->validate([
            'nama_rak' => 'required|string|max:50',
            'lokasi'   => 'required|string|max:100',
        ]);

        // 2. Simpan Data
        \App\Models\Shelf::create($validated);

        // 3. Cek Tombol Mana yang Ditekan
        if ($request->input('action') == 'save_and_create') {
            return redirect()->route('shelves.create')
                ->with('success', 'Rak "' . $request->nama_rak . '" berhasil disimpan. Silakan tambah lagi.');
        }

        // 4. Default Redirect
        return redirect()->route('shelves.index')
            ->with('success', 'Rak baru berhasil ditambahkan.');
    }

    public function edit(Shelf $shelf)
    {
        return view('shelves.edit', compact('shelf'));
    }

    public function update(Request $request, Shelf $shelf)
    {
        $request->validate([
            'nama_rak' => 'required|string|max:50',
            'lokasi'   => 'required|string|max:100',
        ]);

        $shelf->update($request->all());
        return redirect()->route('shelves.index')->with('success', 'Data rak diperbarui.');
    }

    public function destroy(Shelf $shelf)
    {
        if ($shelf->books()->count() > 0) {
            return back()->with('error', 'Gagal hapus: Masih ada buku yang tersimpan di rak ini.');
        }

        $shelf->delete();
        return redirect()->route('shelves.index')->with('success', 'Rak dihapus.');
    }
}
