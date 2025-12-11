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

        if ($request->has('status') && $request->status == 'archived') {
            $query->onlyTrashed(); // Hanya ambil yang sudah dihapus
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
        // 1. Validasi Input
        $validated = $request->validate([
            'kode_buku'    => 'required|unique:books,kode_buku',
            'judul'        => 'required',
            'pengarang'    => 'required',
            'penerbit'     => 'nullable',
            'tahun_terbit' => 'nullable|integer',
            'stok_total'   => 'required|integer|min:1',
            'kategori_id'  => 'nullable|exists:categories,id',
            'rak_id'       => 'nullable|exists:shelves,id',
        ]);

        // 2. Simpan Data (Otomatis isi stok_tersedia sama dengan stok_total)
        $validated['stok_tersedia'] = $validated['stok_total'];

        \App\Models\Book::create($validated);

        // --- 3. LOGIKA TOMBOL (INI YANG KURANG KEMARIN) ---

        // Cek apakah tombol "Simpan & Tambah Lagi" yang ditekan?
        if ($request->input('action') == 'save_and_create') {
            // Redirect KEMBALI ke halaman create (Form kosong lagi) + Pesan Sukses
            return redirect()->route('books.create')
                ->with('success', 'Buku "' . $request->judul . '" berhasil disimpan. Silakan input buku selanjutnya.');
        }

        // Jika tombol "Simpan Buku" biasa (Default)
        return redirect()->route('books.index')
            ->with('success', 'Buku berhasil ditambahkan.');
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
        $book = Book::findOrFail($id);

        // --- LOGIC PENGECEKAN (SATPAM) ---
        // Cek apakah buku ini ada di tabel loan_details dengan status 'dipinjam'
        $sedangDipinjam = $book->loanDetails()
                               ->where('status_item', 'dipinjam')
                               ->exists();

        if ($sedangDipinjam) {
            // Jika ada, batalkan penghapusan dan kirim pesan error
            return back()->with('error', 'GAGAL: Buku ini sedang dipinjam oleh siswa! Harap proses pengembalian terlebih dahulu.');
        }
        // ----------------------------------

        $book->delete();

        return redirect()->route('books.index')->with('success', 'Buku berhasil dipindahkan ke Sampah.');
    }

    // 1. Tampilkan Halaman Sampah
    public function trash()
    {
        // Ambil HANYA yang sudah dihapus (onlyTrashed)
        $books = Book::onlyTrashed()->with(['category', 'shelf'])->latest()->paginate(10);
        return view('books.trash', compact('books'));
    }

    // 2. Pulihkan Data (Restore)
    public function restore($id)
    {
        // Cari di tong sampah, lalu restore
        $book = Book::withTrashed()->findOrFail($id);
        $book->restore();

        return redirect()->route('books.trash')->with('success', 'Buku berhasil dipulihkan kembali ke katalog aktif.');
    }

    // 3. Hapus Permanen (Force Delete)
    public function forceDelete($id)
    {
        $book = Book::withTrashed()->findOrFail($id);

        if ($book->loanDetails()->count() > 0) {
            return back()->with('error', 'GAGAL: Buku ini memiliki riwayat peminjaman (Histori). Data tidak bisa dihapus permanen demi integritas laporan.');
        }

        $book->forceDelete(); // Hapus selamanya dari DB

        return redirect()->route('books.trash')->with('success', 'Buku berhasil dihapus permanen.');
    }
}
