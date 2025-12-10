<?php

namespace App\Http\Controllers;

use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockOpnameController extends Controller
{
    // Halaman Riwayat Opname
    public function index()
    {
        $opnames = StockOpname::with('user')->latest()->paginate(10);
        return view('stock_opnames.index', compact('opnames'));
    }

    // Halaman Form Cek Fisik (Tampilkan Semua Buku)
    public function create()
    {
        // Ambil semua buku urutkan per rak biar gampang ngeceknya
        $books = Book::with(['shelf', 'category'])->orderBy('rak_id')->get();
        return view('stock_opnames.create', compact('books'));
    }

    // Simpan Laporan
    public function store(Request $request)
    {
        $request->validate([
            'tgl_opname' => 'required|date',
            'fisik'      => 'required|array', // Array stok fisik dari form
        ]);

        try {
            DB::transaction(function () use ($request) {

                // 1. Buat Header Laporan
                $opname = StockOpname::create([
                    'kode_opname' => 'SO-' . date('YmdHis'),
                    'tgl_opname'  => $request->tgl_opname,
                    'user_id'     => Auth::id(),
                    'catatan'     => $request->catatan,
                ]);

                // 2. Loop Semua Buku Inputan
                foreach ($request->fisik as $bookId => $jumlahFisik) {
                    $book = Book::find($bookId);

                    // Hitung Selisih
                    $selisih = $jumlahFisik - $book->stok_tersedia;

                    // Simpan Detail
                    StockOpnameDetail::create([
                        'stock_opname_id' => $opname->id,
                        'book_id'         => $bookId,
                        'stok_sistem'     => $book->stok_tersedia,
                        'stok_fisik'      => $jumlahFisik,
                        'selisih'         => $selisih,
                        'keterangan'      => $selisih == 0 ? 'Sesuai' : ($selisih < 0 ? 'Hilang/Kurang' : 'Lebih'),
                    ]);

                    // OPSIONAL: Update Stok Asli di Master Buku?
                    // Biasanya Opname mengupdate stok master agar sinkron
                    // $book->update(['stok_tersedia' => $jumlahFisik]);
                }
            });

            return redirect()->route('stock-opnames.index')->with('success', 'Laporan Stock Opname berhasil disimpan!');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    // Lihat Detail Laporan
    public function show($id)
    {
        $opname = StockOpname::with(['details.book', 'user'])->findOrFail($id);
        return view('stock_opnames.show', compact('opname'));
    }
}
