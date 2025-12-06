<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Member;
use App\Models\Loan;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Hitung-hitungan Data (Statistik Ringkas)
        $totalBuku      = Book::count();
        $totalAnggota   = Member::where('status_aktif', true)->count();

        // Transaksi 'berjalan' atau 'terlambat' berarti buku belum kembali
        $transaksiAktif = Loan::whereIn('status_transaksi', ['berjalan', 'terlambat'])->count();

        // Hitung yang sudah selesai hari ini (opsional, buat penyemangat)
        $kembaliHariIni = Loan::where('status_transaksi', 'selesai')
                              ->whereDate('updated_at', now())
                              ->count();

        // 2. Ambil 5 Transaksi Terakhir (Buat tabel mini)
        $recentLoans = Loan::with(['member', 'user'])
                           ->latest() // Urutkan dari yang terbaru
                           ->take(5)  // Ambil 5 saja
                           ->get();

        return view('dashboard', compact(
            'totalBuku',
            'totalAnggota',
            'transaksiAktif',
            'kembaliHariIni',
            'recentLoans'
        ));
    }
}
