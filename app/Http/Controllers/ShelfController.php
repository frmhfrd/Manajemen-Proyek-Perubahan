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
        $request->validate([
            'nama_rak' => 'required|string|max:50',
            'lokasi'   => 'required|string|max:100',
        ]);

        Shelf::create($request->all());
        return redirect()->route('shelves.index')->with('success', 'Rak baru berhasil dibuat.');
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
