<?php

namespace App\Http\Controllers;

use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf; // Pastikan library ini sudah diinstall

class StockOpnameController extends Controller
{

    public function index()
    {
        $opnames = StockOpname::with('user')->latest()->paginate(10);
        return view('stock_opnames.index', compact('opnames'));
    }

    public function create()
    {
        $books = Book::with(['shelf', 'category'])->orderBy('rak_id')->get();

        // CEK APAKAH HARI INI SUDAH ADA DATA?
        $todayOpname = StockOpname::with('details')
                        ->whereDate('tgl_opname', date('Y-m-d'))
                        ->where('user_id', Auth::id()) // Opsional: batasi per user
                        ->first();

        // Siapkan array untuk mengisi form value
        $riwayatInput = [];

        if ($todayOpname) {
            foreach ($todayOpname->details as $detail) {
                $ket = json_decode($detail->keterangan, true);
                $riwayatInput[$detail->book_id] = [
                    'bagus' => $ket['bagus'] ?? 0,
                    'rusak' => $ket['rusak'] ?? 0,
                ];
            }
        }

        return view('stock_opnames.create', compact('books', 'riwayatInput', 'todayOpname'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tgl_opname'  => 'required|date',
            'fisik_bagus' => 'required|array',
            'fisik_rusak' => 'required|array',
        ]);

        try {
            // Kita tampung hasil transaksi ke variabel $opname agar ID-nya bisa dipakai di redirect
            $opname = DB::transaction(function () use ($request) {

                // 1. SIAPKAN HEADER LAPORAN (Hapus yang lama jika hari ini sudah ada)
                StockOpname::whereDate('tgl_opname', $request->tgl_opname)->delete();

                // Buat Header Baru
                $newOpname = StockOpname::create([
                    'kode_opname' => 'SO-' . date('YmdHis'),
                    'tgl_opname'  => $request->tgl_opname,
                    'user_id'     => Auth::id(),
                    'catatan'     => $request->catatan,
                ]);

                // 2. LOOP & HITUNG ULANG STOK
                foreach ($request->fisik_bagus as $bookId => $jumlahBagus) {
                    $book = Book::find($bookId);
                    if (!$book) continue;

                    // AMBIL DATA KUNCI
                    $jumlahRusak = (int) ($request->fisik_rusak[$bookId] ?? 0);
                    $totalAset   = $book->stok_total;

                    // Hitung yang sedang dipinjam
                    $sedangDipinjam = \App\Models\LoanDetail::where('book_id', $bookId)
                                        ->where('status_item', 'dipinjam')
                                        ->count();

                    // --- RUMUS MATEMATIKA ---
                    $totalDiketahui = $jumlahBagus + $jumlahRusak + $sedangDipinjam;
                    $jumlahHilang = $totalAset - $totalDiketahui;

                    if ($jumlahHilang < 0) {
                        $jumlahHilang = 0;
                    }

                    // --- UPDATE MASTER BUKU ---
                    $book->update([
                        'stok_tersedia' => $jumlahBagus,
                        'stok_rusak'    => $jumlahRusak,
                        'stok_hilang'   => $jumlahHilang
                    ]);

                    // Simpan Detail Laporan
                    StockOpnameDetail::create([
                        'stock_opname_id' => $newOpname->id,
                        'book_id'         => $bookId,
                        'stok_sistem'     => $totalAset,
                        'stok_fisik'      => $jumlahBagus + $jumlahRusak,
                        'selisih'         => $totalDiketahui - $totalAset,
                        'keterangan'      => json_encode([
                            'dipinjam' => $sedangDipinjam,
                            'bagus'    => $jumlahBagus,
                            'rusak'    => $jumlahRusak,
                            'hilang'   => $jumlahHilang
                        ]),
                    ]);
                }

                // Return objek opname agar bisa diterima variabel $opname di luar transaction
                return $newOpname;
            });

            // --- LOGIKA REDIRECT (PERUBAHAN DISINI) ---

            // Jika user menekan tombol "Simpan & Cetak PDF"
            if ($request->input('action') === 'save_print') {
                return redirect()->route('stock-opnames.show', $opname->id)
                    ->with('success', 'Laporan berhasil disimpan! Menyiapkan dokumen cetak...')
                    ->with('print_now', true); // Flag ini akan memicu JS di view Show
            }

            // Jika user hanya menekan "Simpan Saja"
            return redirect()->route('stock-opnames.show', $opname->id)
                ->with('success', 'Laporan Opname berhasil diperbarui!');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $opname = StockOpname::with(['details.book', 'user'])->findOrFail($id);

        // Dekode JSON (Sama seperti kode lama Anda)
        foreach($opname->details as $detail) {
            $data = json_decode($detail->keterangan, true);
            if(json_last_error() === JSON_ERROR_NONE) {
                $txt = [];
                if(($data['rusak'] ?? 0) > 0) $txt[] = "Rusak: " . $data['rusak'];
                if(($data['hilang'] ?? 0) > 0) $txt[] = "Hilang: " . $data['hilang'];
                if(($data['dipinjam'] ?? 0) > 0) $txt[] = "Dipinjam: " . $data['dipinjam'];
                $detail->keterangan_human = empty($txt) ? 'Lengkap' : implode(', ', $txt);
            } else {
                $detail->keterangan_human = $detail->keterangan;
            }
        }

        return view('stock_opnames.show', compact('opname'));
    }

    // --- TAMBAHAN BARU: FUNGSI CETAK PDF ---
    public function exportPdf($id)
    {
        // Ambil data beserta relasinya
        $opname = StockOpname::with(['user', 'details.book'])->findOrFail($id);

        // Load View khusus PDF (pastikan file view ini sudah dibuat)
        $pdf = Pdf::loadView('stock_opnames.print_pdf', compact('opname'));

        // Setup Ukuran Kertas (Opsional, default A4)
        $pdf->setPaper('a4', 'portrait');

        // Stream (Buka di tab browser)
        return $pdf->stream('Laporan-Opname-' . $opname->kode_opname . '.pdf');
    }

    public function assetReport()
    {
        $books = \App\Models\Book::orderBy('judul')->get();
        return view('reports.asset', compact('books'));
    }
}
