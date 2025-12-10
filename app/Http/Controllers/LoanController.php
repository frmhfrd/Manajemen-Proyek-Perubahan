<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\LoanDetail;
use App\Models\Book;
use App\Models\Member;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LoanController extends Controller
{
    public function index(Request $request)
    {
        $query = Loan::with(['member', 'user', 'details']);

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('kode_transaksi', 'like', "%{$search}%")
                  ->orWhereHas('member', function($q) use ($search) {
                      $q->where('nama_lengkap', 'like', "%{$search}%");
                  });
        }

        $loans = $query->orderBy('id', 'desc')->paginate(10);

        // OPTIMASI: Ambil nilai denda SEKALI saja
        $dendaPerHari = Setting::where('key', 'denda_harian')->value('value') ?? 500;

        return view('loans.index', compact('loans', 'dendaPerHari'));
    }

    public function create()
    {
        $members = Member::where('status_aktif', true)->orderBy('nama_lengkap')->get();
        $books = Book::where('stok_tersedia', '>', 0)->orderBy('judul')->get();

        return view('loans.create', compact('members', 'books'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'member_id' => 'required|exists:members,id',
            'book_ids'  => 'required|array|min:1',
            'book_ids.*'=> 'exists:books,id',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $durasiPinjam = Setting::where('key', 'max_lama_pinjam')->value('value') ?? 7;

                // 1. Buat Header Transaksi
                $loan = Loan::create([
                    'kode_transaksi' => 'TRX-' . time(),
                    'member_id' => $request->member_id,
                    'user_id'   => Auth::id(),
                    'tgl_pinjam' => Carbon::now(),
                    'tgl_wajib_kembali' => Carbon::now()->addDays((int)$durasiPinjam),
                    'tahun_ajaran' => '2024/2025',
                    'status_transaksi' => 'berjalan',
                ]);

                // 2. Loop Buku
                foreach ($request->book_ids as $book_id) {
                    $book = Book::find($book_id);
                    if ($book->stok_tersedia < 1) {
                        throw new \Exception("Stok buku {$book->judul} habis!");
                    }

                    LoanDetail::create([
                        'loan_id' => $loan->id,
                        'book_id' => $book_id,
                        'status_item' => 'dipinjam',
                    ]);

                    $book->decrement('stok_tersedia');
                }
            });

            return redirect()->route('loans.index')->with('success', 'Transaksi Peminjaman Berhasil!');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses: ' . $e->getMessage());
        }
    }

    public function returnLoan(Request $request, string $id)
    {
        // 1. Ambil Data Transaksi
        $loan = Loan::with('details')->findOrFail($id);

        // SATPAM: Jika status sudah selesai, tolak proses!
        if ($loan->status_transaksi == 'selesai') {
            return back()->with('error', 'Transaksi ini sudah diselesaikan sebelumnya!');
        }

        try {
            DB::transaction(function () use ($loan) {

                // 2. Logic Hitung Denda & Telat
                $dendaPerHari = (int) (Setting::where('key', 'denda_harian')->value('value') ?? 500);

                $tanggalKembali = Carbon::now()->startOfDay();
                $jatuhTempo     = $loan->tgl_wajib_kembali->startOfDay();

                $loan->tgl_kembali = Carbon::now();
                $loan->total_denda = 0;

                // Cek Telat
                if ($tanggalKembali->gt($jatuhTempo)) {
                    $selisihHari = $tanggalKembali->diffInDays($jatuhTempo);
                    $loan->total_denda = $selisihHari * $dendaPerHari;
                }

                // 3. Status AKHIR harus selalu 'selesai'
                $loan->status_transaksi = 'selesai';

                $loan->save();

                // 4. Balikin Stok Buku
                foreach ($loan->details as $detail) {
                    if ($detail->status_item !== 'kembali') {
                        $detail->update(['status_item' => 'kembali']);
                        Book::where('id', $detail->book_id)->increment('stok_tersedia');
                    }
                }
            });

            return back()->with('success', 'Buku berhasil dikembalikan & Stok diperbarui.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    // Cek Status Pembayaran Manual ke Midtrans (Per Transaksi)
    public function checkPaymentStatus($id)
    {
        $loan = Loan::findOrFail($id);

        if (empty($loan->midtrans_order_id)) {
            return back()->with('error', 'Transaksi ini belum memiliki Order ID Midtrans.');
        }

        // Konfigurasi Midtrans
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');
        \Midtrans\Config::$isSanitized = config('midtrans.is_sanitized');
        \Midtrans\Config::$is3ds = config('midtrans.is_3ds');

        try {
            // TANYA LANGSUNG KE MIDTRANS
            $status = \Midtrans\Transaction::status($loan->midtrans_order_id);

            // Logic Update Status Database
                /** @var object $status "Hei Editor, percayalah sama saya, variabel $status ini isinya OBJECT, bukan Array. Jadi jangan dikasih warna merah ya."*/
            if ($status->transaction_status == 'settlement' || $status->transaction_status == 'capture') {
                $loan->update([
                    'status_pembayaran' => 'paid',
                    'tipe_pembayaran'   => 'online'
                ]);
                return back()->with('success', 'Status berhasil diperbarui: LUNAS (Settlement).');
            }
            else if ($status->transaction_status == 'pending') {
                $loan->update(['status_pembayaran' => 'pending']);
                return back()->with('warning', 'Status di Midtrans masih PENDING.');
            }
            else if ($status->transaction_status == 'expire') {
                $loan->update(['status_pembayaran' => 'unpaid', 'midtrans_url' => null]);
                return back()->with('error', 'Link pembayaran sudah KADALUARSA. Silakan generate ulang.');
            }

            return back()->with('info', 'Status saat ini: ' . $status->transaction_status);

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal cek ke Midtrans: ' . $e->getMessage());
        }
    }

    // Refresh Semua Status Pending Sekaligus (Massal)
    public function refreshAllStatus()
    {
        // 1. Ambil semua transaksi yang 'pending' DAN punya Order ID
        $pendingLoans = Loan::where('status_pembayaran', 'pending')
                            ->whereNotNull('midtrans_order_id')
                            ->get();

        if ($pendingLoans->isEmpty()) {
            return back()->with('info', 'Tidak ada transaksi pending yang perlu dicek.');
        }

        // 2. Setup Midtrans
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');
        \Midtrans\Config::$isSanitized = config('midtrans.is_sanitized');
        \Midtrans\Config::$is3ds = config('midtrans.is_3ds');

        $updatedCount = 0;

        // 3. Loop dan Cek Satu per Satu
        foreach ($pendingLoans as $loan) {
            try {
                // Panggil API Midtrans
                $status = \Midtrans\Transaction::status($loan->midtrans_order_id);

                // Cek Statusnya
                /** @var object $status "Hei Editor, percayalah sama saya, variabel $status ini isinya OBJECT, bukan Array. Jadi jangan dikasih warna merah ya."*/
                if ($status->transaction_status == 'settlement' || $status->transaction_status == 'capture') {
                    $loan->update([
                        'status_pembayaran' => 'paid',
                        'tipe_pembayaran'   => 'online'
                    ]);
                    $updatedCount++;
                }
                else if ($status->transaction_status == 'expire') {
                    $loan->update(['status_pembayaran' => 'unpaid', 'midtrans_url' => null]);
                }
                // Jika masih pending, biarkan saja
            } catch (\Exception $e) {
                continue; // Skip kalau ada error koneksi di satu transaksi
            }
        }

        // 4. Return Pesan sesuai Hasil (PERBAIKAN LOGIKA PESAN)
        if ($updatedCount > 0) {
            return back()->with('success', "Sukses! {$updatedCount} transaksi berhasil diperbarui menjadi LUNAS.");
        } else {
            // Jika 0, beri pesan Info agar user tidak bingung
            return back()->with('info', 'Pengecekan selesai. Belum ada pembayaran baru yang masuk (Status masih Pending di Midtrans).');
        }
    }
}
