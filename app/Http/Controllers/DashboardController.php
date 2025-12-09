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
        // 1. Data Card (Yang Lama)
        $totalBuku      = Book::count();
        $totalAnggota   = Member::where('status_aktif', true)->count();
        $transaksiAktif = Loan::whereIn('status_transaksi', ['berjalan', 'terlambat'])->count();
        $kembaliHariIni = Loan::where('status_transaksi', 'selesai')
                              ->whereDate('updated_at', now())
                              ->count();

        $recentLoans = Loan::with(['member', 'user'])->latest()->take(5)->get();

        // 2. DATA UNTUK GRAFIK (Baru)
        // Mengambil jumlah peminjaman per bulan di tahun ini
        $chartData = Loan::selectRaw('MONTH(tgl_pinjam) as bulan, COUNT(*) as total')
                        ->whereYear('tgl_pinjam', date('Y'))
                        ->groupBy('bulan')
                        ->orderBy('bulan')
                        ->pluck('total', 'bulan');

        // Format data agar sesuai sumbu X (Bulan) dan Y (Jumlah)
        $labels = [];
        $data = [];
        // Loop bulan 1 sampai 12 agar grafik tetap muncul walau datanya 0
        for ($i = 1; $i <= 12; $i++) {
            $monthName = date('F', mktime(0, 0, 0, $i, 1)); // Jan, Feb, Mar...
            $labels[] = $monthName;
            $data[] = $chartData[$i] ?? 0; // Kalau gak ada data, isi 0
        }

        return view('dashboard', compact(
            'totalBuku',
            'totalAnggota',
            'transaksiAktif',
            'kembaliHariIni',
            'recentLoans',
            'labels', // Kirim ke View
            'data'    // Kirim ke View
        ));
    }
}
