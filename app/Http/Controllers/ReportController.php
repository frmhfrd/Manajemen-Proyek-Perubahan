<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;


class ReportController extends Controller
{
    // === LAPORAN PEMINJAMAN ===
    public function loanIndex(Request $request)
    {
        // Default tanggal: Awal bulan ini s/d Hari ini
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate   = $request->input('end_date', date('Y-m-d'));
        $status    = $request->input('status', 'all');

        // 1. PERBAIKAN: Ubah 'loan_date' menjadi 'tgl_pinjam'
        $query = Loan::with(['member', 'details.book'])
                     ->whereBetween('tgl_pinjam', [$startDate, $endDate]);

        // 2. PERBAIKAN: Mapping Status dari Dropdown (borrowed/returned) ke Database (berjalan/selesai)
        if ($status !== 'all') {
            if ($status == 'borrowed') {
                $query->where('status_transaksi', 'berjalan');
            } elseif ($status == 'returned') {
                $query->where('status_transaksi', 'selesai');
            }
        }

        // Ambil Data
        $loans = $query->latest()->get();

        // Hitung Statistik
        $stats = [
            'total_transaksi' => $loans->count(),
            'total_buku'      => $loans->sum(fn($l) => $l->details->count()),
            // Sesuaikan hitungan status dengan value database
            'kembali'         => $loans->where('status_transaksi', 'selesai')->count(),
            'dipinjam'        => $loans->where('status_transaksi', 'berjalan')->count(),
        ];

        return view('reports.loans.index', compact('loans', 'stats', 'startDate', 'endDate', 'status'));
    }

    public function loanPrint(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $status    = $request->input('status');

        // 1. PERBAIKAN: Ubah 'loan_date' menjadi 'tgl_pinjam'
        $query = Loan::with(['member', 'details.book'])
                     ->whereBetween('tgl_pinjam', [$startDate, $endDate]);

        // 2. PERBAIKAN: Mapping Status
        if ($status !== 'all') {
            if ($status == 'borrowed') {
                $query->where('status_transaksi', 'berjalan');
            } elseif ($status == 'returned') {
                $query->where('status_transaksi', 'selesai');
            }
        }

        $loans = $query->latest()->get();

        $pdf = Pdf::loadView('reports.loans.print_pdf', compact('loans', 'startDate', 'endDate'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream('Laporan-Peminjaman-' . $startDate . '-' . $endDate . '.pdf');
    }


    // === LAPORAN DENDA ===
    public function finesIndex(Request $request)
    {
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate   = $request->input('end_date', date('Y-m-d'));
        $status    = $request->input('payment_status', 'all'); // paid, unpaid

        // Query Dasar: Cari yang ada dendanya saja
        $query = Loan::with('member')
                     ->where('total_denda', '>', 0)
                     ->whereBetween('updated_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                     // Catatan: Pakai updated_at karena itu waktu pembayaran biasanya terjadi

        // Filter Status Pembayaran
        if ($status !== 'all') {
            $query->where('status_pembayaran', $status);
            // pastikan value di database: 'paid'/'lunas' atau 'unpaid'/'belum_lunas'
        }

        $fines = $query->latest('updated_at')->get();

        // Hitung Statistik Keuangan
        $stats = [
            'total_denda' => $fines->sum('total_denda'),
            'sudah_dibayar' => $fines->where('status_pembayaran', 'paid')->sum('total_denda'), // Ganti 'paid' sesuai value DB Anda (misal: 'lunas')
            'belum_dibayar' => $fines->where('status_pembayaran', '!=', 'paid')->sum('total_denda'),
            'metode_tunai' => $fines->where('tipe_pembayaran', 'tunai')->where('status_pembayaran', 'paid')->sum('total_denda'),
            'metode_midtrans' => $fines->where('tipe_pembayaran', '!=', 'tunai')->where('status_pembayaran', 'paid')->sum('total_denda'),
        ];

        return view('reports.fines.index', compact('fines', 'stats', 'startDate', 'endDate', 'status'));
    }

    public function finesPrint(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $status    = $request->input('payment_status');

        $query = Loan::with('member')
                     ->where('total_denda', '>', 0)
                     ->whereBetween('updated_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($status !== 'all') {
            $query->where('status_pembayaran', $status);
        }

        $fines = $query->latest('updated_at')->get();
        $totalPendapatan = $fines->where('status_pembayaran', 'paid')->sum('total_denda');

        $pdf = Pdf::loadView('reports.fines.print_pdf', compact('fines', 'startDate', 'endDate', 'totalPendapatan'));
        $pdf->setPaper('a4', 'portrait'); // Potrait biasanya cukup untuk tabel keuangan

        return $pdf->stream('Laporan-Keuangan-Denda.pdf');
    }
}
