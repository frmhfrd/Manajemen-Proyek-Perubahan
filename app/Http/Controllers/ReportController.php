<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf; // Panggil Facade PDF

class ReportController extends Controller
{
    public function index()
    {
        // Tampilkan halaman filter tanggal (opsional, tapi kita langsung cetak semua dulu biar cepat)
    }

    public function print()
    {
        // 1. Ambil Data Transaksi (Kita ambil semua, urutkan tanggal terbaru)
        $loans = Loan::with(['member', 'user', 'details'])
                    ->orderBy('tgl_pinjam', 'desc')
                    ->get();

        // 2. Load View khusus PDF (bukan view blade biasa)
        // Kita kirim data $loans ke view tersebut
        $pdf = Pdf::loadView('reports.pdf', compact('loans'));

        // 3. Set Ukuran Kertas (A4, Potrait/Landscape)
        $pdf->setPaper('a4', 'landscape');

        // 4. Download file / Stream (tampilkan di browser)
        return $pdf->stream('laporan-sirkulasi.pdf');
    }
}   
