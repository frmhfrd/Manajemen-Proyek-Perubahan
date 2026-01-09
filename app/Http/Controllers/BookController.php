<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Shelf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage; // <--- PENTING: Import Storage

class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::with(['category', 'shelf']);

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('kode_buku', 'like', "%{$search}%")
                  ->orWhere('pengarang', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status == 'archived') {
            $query->onlyTrashed();
        }

        $books = $query->latest()->paginate(10);

        return view('books.index', compact('books'));
    }

    public function create()
    {
        $categories = Category::all();
        $shelves = Shelf::all();

        return view('books.create', compact('categories', 'shelves'));
    }

    public function store(Request $request)
    {
        // 1. Validasi Input (+ Validasi Gambar)
        $validated = $request->validate([
            'kode_buku'    => 'required|unique:books,kode_buku',
            'judul'        => 'required',
            'pengarang'    => 'required',
            'penerbit'     => 'nullable',
            'tahun_terbit' => 'nullable|integer',
            'harga'        => 'required|numeric|min:0',
            'stok_total'   => 'required|integer|min:1',
            'kategori_id'  => 'nullable|exists:categories,id',
            'rak_id'       => 'nullable|exists:shelves,id',
            // Validasi Foto: Harus Gambar, Max 2MB (2048 KB), Format jpeg/png/jpg
            'cover_image'  => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $validated['stok_tersedia'] = $validated['stok_total'];

        // --- 2. LOGIKA UPLOAD GAMBAR ---
        if ($request->hasFile('cover_image')) {
            // Simpan ke folder 'storage/app/public/books'
            // Hasilnya path seperti: 'books/namafileunik.jpg'
            $path = $request->file('cover_image')->store('books', 'public');
            $validated['cover_image'] = $path;
        }

        \App\Models\Book::create($validated);

        if ($request->input('action') == 'save_and_create') {
            return redirect()->route('books.create')
                ->with('success', 'Buku "' . $request->judul . '" berhasil disimpan. Silakan input selanjutnya.');
        }

        return redirect()->route('books.index')
            ->with('success', 'Buku berhasil ditambahkan.');
    }

    public function edit(string $id)
    {
        $book = Book::findOrFail($id);
        $categories = Category::all();
        $shelves = Shelf::all();

        return view('books.edit', compact('book', 'categories', 'shelves'));
    }

    public function update(Request $request, string $id)
    {
        $book = Book::findOrFail($id);

        $validated = $request->validate([
            'kode_buku'    => 'required|max:50|unique:books,kode_buku,'.$id,
            'judul'        => 'required|string|max:255',
            'pengarang'    => 'required|string|max:100',
            'penerbit'     => 'nullable|string|max:100',
            'tahun_terbit' => 'nullable|integer',
            'harga'        => 'required|numeric|min:0',
            'kategori_id'  => 'required|exists:categories,id',
            'rak_id'       => 'required|exists:shelves,id',
            'stok_total'   => 'required|integer|min:1',
            // Validasi Foto pada Edit
            'cover_image'  => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Logika Update Stok
        if ($request->stok_total != $book->stok_total) {
            $selisih = $request->stok_total - $book->stok_total;
            $validated['stok_tersedia'] = $book->stok_tersedia + $selisih;
        }

        // --- LOGIKA UPDATE GAMBAR ---
        if ($request->hasFile('cover_image')) {
            // 1. Hapus gambar lama jika ada (biar server gak penuh)
            if ($book->cover_image && Storage::disk('public')->exists($book->cover_image)) {
                Storage::disk('public')->delete($book->cover_image);
            }

            // 2. Upload gambar baru
            $path = $request->file('cover_image')->store('books', 'public');
            $validated['cover_image'] = $path;
        }

        $book->update($validated);

        return redirect()->route('books.index')->with('success', 'Data buku berhasil diperbarui!');
    }

    public function destroy(string $id)
    {
        $book = Book::findOrFail($id);

        $sedangDipinjam = $book->loanDetails()
                               ->where('status_item', 'dipinjam')
                               ->exists();

        if ($sedangDipinjam) {
            return back()->with('error', 'GAGAL: Buku ini sedang dipinjam oleh siswa!');
        }

        $book->delete(); // Soft Delete (Gambar belum dihapus, karena masih di Trash)

        return redirect()->route('books.index')->with('success', 'Buku berhasil dipindahkan ke Sampah.');
    }

    public function trash()
    {
        $books = Book::onlyTrashed()->with(['category', 'shelf'])->latest()->paginate(10);
        return view('books.trash', compact('books'));
    }

    public function restore($id)
    {
        $book = Book::withTrashed()->findOrFail($id);
        $book->restore();

        return redirect()->route('books.trash')->with('success', 'Buku berhasil dipulihkan.');
    }

    public function forceDelete($id)
    {
        $book = \App\Models\Book::withTrashed()->findOrFail($id);

        try {
            DB::transaction(function () use ($book) {
                // 1. Hapus file gambar fisik dari storage (Biar hemat penyimpanan)
                if ($book->cover_image && Storage::disk('public')->exists($book->cover_image)) {
                    Storage::disk('public')->delete($book->cover_image);
                }

                $book->loanDetails()->forceDelete();
                DB::table('stock_opname_details')->where('book_id', $book->id)->delete();
                $book->forceDelete();
            });

            return redirect()->route('books.trash')->with('success', 'Buku dan gambarnya berhasil dihapus permanen.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    // Menampilkan daftar buku yang rusak
    public function indexRusak()
    {
        // Ambil buku yang punya stok rusak > 0
        $books = \App\Models\Book::where('stok_rusak', '>', 0)->get();
        return view('books.rusak', compact('books'));
    }

    // Proses perbaikan atau pemusnahan
    public function processRusak(Request $request, $id)
    {
        $book = \App\Models\Book::findOrFail($id);
        $action = $request->action; // 'repair' atau 'destroy'
        $qty = $request->qty; // Jumlah buku yang diproses

        if ($qty > $book->stok_rusak) {
            return back()->with('error', 'Jumlah melebihi stok rusak yang ada!');
        }

        if ($action == 'repair') {
            // Perbaiki: Kurangi stok rusak, Tambah stok tersedia
            $book->decrement('stok_rusak', $qty);
            $book->increment('stok_tersedia', $qty);
            $msg = "$qty Buku berhasil diperbaiki dan kembali ke rak.";
        } else {
            // Musnahkan: Kurangi stok rusak, Tambah stok hilang (aset dihapus)
            $book->decrement('stok_rusak', $qty);
            $book->increment('stok_hilang', $qty); // Atau biarkan hilang selamanya
            $msg = "$qty Buku rusak telah dimusnahkan dari inventaris.";
        }

        return back()->with('success', $msg);
    }
}
