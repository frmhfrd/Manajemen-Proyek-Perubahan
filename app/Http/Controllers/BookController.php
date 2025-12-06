<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Shelf;

class BookController extends Controller
{
    public function index(Request $request)
    {
        // Mulai Query
        $query = Book::with(['category', 'shelf']);

        // Jika ada input pencarian (search)
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('kode_buku', 'like', "%{$search}%")
                  ->orWhere('pengarang', 'like', "%{$search}%");
            });
        }

        // Ambil data (paginate 10)
        $books = $query->latest()->paginate(10);

        return view('books.index', compact('books'));
    }

    public function create()
    {
        // Kita butuh data Kategori & Rak untuk pilihan Dropdown (Select Option)
        $categories = Category::all();
        $shelves = Shelf::all();

        return view('books.create', compact('categories', 'shelves'));
    }

    public function store(Request $request)
    {
        // 1. Validasi Input (Security Guard)
        $validated = $request->validate([
            'kode_buku'    => 'required|unique:books,kode_buku|max:50',
            'judul'        => 'required|string|max:255',
            'pengarang'    => 'required|string|max:100',
            'penerbit'     => 'nullable|string|max:100',
            'tahun_terbit' => 'nullable|integer|min:1900|max:'.(date('Y')+1),
            'kategori_id'  => 'required|exists:categories,id',
            'rak_id'       => 'required|exists:shelves,id',
            'stok_total'   => 'required|integer|min:1',
        ]);

        // 2. Logika Stok Awal
        // Saat buku baru masuk, stok tersedia = stok total. Rusak/Hilang = 0.
        $validated['stok_tersedia'] = $validated['stok_total'];
        $validated['stok_rusak']    = 0;
        $validated['stok_hilang']   = 0;

        // 3. Simpan ke Database
        Book::create($validated);

        // 4. Kembali ke halaman list dengan pesan sukses
        return redirect()->route('books.index')->with('success', 'Buku berhasil ditambahkan!');
    }

    // Tampilkan Form Edit
    public function edit(string $id)
    {
        // Cari buku berdasarkan ID, kalau gak ketemu otomatis 404
        $book = Book::findOrFail($id);

        // Kita butuh data kategori & rak lagi buat dropdown
        $categories = Category::all();
        $shelves = Shelf::all();

        return view('books.edit', compact('book', 'categories', 'shelves'));
    }

    // Proses Simpan Perubahan
    public function update(Request $request, string $id)
    {
        $book = Book::findOrFail($id);

        // 1. Validasi
        $validated = $request->validate([
            // PERHATIKAN: 'unique:books,kode_buku,'.$id
            // Artinya: Kode buku harus unik, KECUALI untuk ID buku ini sendiri.
            'kode_buku'    => 'required|max:50|unique:books,kode_buku,'.$id,
            'judul'        => 'required|string|max:255',
            'pengarang'    => 'required|string|max:100',
            'penerbit'     => 'nullable|string|max:100',
            'tahun_terbit' => 'nullable|integer',
            'kategori_id'  => 'required|exists:categories,id',
            'rak_id'       => 'required|exists:shelves,id',
            'stok_total'   => 'required|integer|min:1',
        ]);

        // 2. Logika Update Stok (Jika Admin merubah jumlah stok total)
        if ($request->stok_total != $book->stok_total) {
            // Hitung selisih (misal: stok lama 10, stok baru 15, selisih +5)
            $selisih = $request->stok_total - $book->stok_total;
            // Tambahkan selisih ke stok tersedia
            $validated['stok_tersedia'] = $book->stok_tersedia + $selisih;
        }

        // 3. Update Data
        $book->update($validated);

        // 4. Kembali
        return redirect()->route('books.index')->with('success', 'Data buku berhasil diperbarui!');
    }

    public function destroy(string $id)
    {
        // 1. Cari buku
        $book = Book::findOrFail($id);

        // 2. Hapus
        $book->delete();

        // 3. Kembali dengan pesan sukses
        return redirect()->route('books.index')->with('success', 'Buku berhasil dihapus permanen!');
    }
}
