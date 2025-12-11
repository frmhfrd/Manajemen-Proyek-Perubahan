<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Member;
use App\Models\Loan;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // Tambahkan ini agar selectRaw jalan

class DashboardController extends Controller
{
    public function index()
    {
        // 1. STATISTIK UTAMA (Gabungan Lama & Baru)
        $totalBuku      = Book::count(); // Total Judul Buku
        $totalAnggota   = Member::where('status_aktif', true)->count();

        // Transaksi Aktif (Berjalan + Terlambat)
        $transaksiAktif = Loan::whereIn('status_transaksi', ['berjalan', 'terlambat'])->count();

        // Buku Kembali Hari Ini
        $kembaliHariIni = Loan::where('status_transaksi', 'selesai')
                              ->whereDate('updated_at', Carbon::now())
                              ->count();

        // --- TAMBAHAN BARU (FITUR DENDA & TELAT) ---
        $pendapatanDenda = Loan::where('status_pembayaran', 'paid')->sum('total_denda');

        $telat = Loan::where('status_transaksi', 'berjalan')
                     ->whereDate('tgl_wajib_kembali', '<', Carbon::now())
                     ->count();
        // -------------------------------------------

        // 2. DATA UNTUK GRAFIK (WAJIB ADA agar tidak error)
        $chartData = Loan::selectRaw('MONTH(tgl_pinjam) as bulan, COUNT(*) as total')
                        ->whereYear('tgl_pinjam', date('Y'))
                        ->groupBy('bulan')
                        ->orderBy('bulan')
                        ->pluck('total', 'bulan');

        $labels = [];
        $data = [];
        // Loop bulan 1 sampai 12
        for ($i = 1; $i <= 12; $i++) {
            $monthName = date('F', mktime(0, 0, 0, $i, 1)); // Jan, Feb...
            $labels[] = $monthName;
            $data[] = $chartData[$i] ?? 0;
        }

        // 3. RECENT LOANS (Tabel Bawah)
        $recentLoans = Loan::with(['member', 'user', 'details.book'])->latest()->take(5)->get();

        return view('dashboard', compact(
            'totalBuku',
            'totalAnggota',
            'transaksiAktif',
            'kembaliHariIni',
            'pendapatanDenda', // Variabel Baru
            'telat',           // Variabel Baru
            'recentLoans',
            'labels',          // Wajib untuk Grafik
            'data'             // Wajib untuk Grafik
        ));
    }
}
