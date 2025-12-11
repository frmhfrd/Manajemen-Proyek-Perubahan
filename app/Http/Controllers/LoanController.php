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
            // Gunakan variabel untuk menangkap hasil return dari dalam transaction
            $newLoan = DB::transaction(function () use ($request) {

                // Ambil setting durasi (default 7 hari)
                $durasiPinjam = \App\Models\Setting::where('key', 'max_lama_pinjam')->value('value') ?? 7;

                // 1. Buat Header Transaksi
                $loan = \App\Models\Loan::create([
                    'kode_transaksi'    => 'TRX-' . time(),
                    'member_id'         => $request->member_id,
                    'user_id'           => auth()->id(),
                    'tgl_pinjam'        => now(),
                    'tgl_wajib_kembali' => now()->addDays((int)$durasiPinjam),
                    'status_transaksi'  => 'berjalan',
                    'status_pembayaran' => 'unpaid',
                    'total_denda'       => 0,
                    // Logika Tahun Ajaran (Juli ke atas = Tahun Depan, Januari-Juni = Tahun Lalu)
                    'tahun_ajaran'      => (date('m') > 6) ? date('Y').'/'.(date('Y')+1) : (date('Y')-1).'/'.date('Y'),
                ]);

                // 2. Loop Buku & Kurangi Stok
                foreach ($request->book_ids as $book_id) {
                    $book = \App\Models\Book::find($book_id);

                    // Cek stok lagi untuk keamanan ganda
                    if ($book->stok_tersedia < 1) {
                        throw new \Exception("Stok buku '{$book->judul}' habis!");
                    }

                    \App\Models\LoanDetail::create([
                        'loan_id'     => $loan->id,
                        'book_id'     => $book_id,
                        'status_item' => 'dipinjam',
                    ]);

                    $book->decrement('stok_tersedia');
                }

                // PENTING: Kembalikan objek loan agar bisa dipakai di luar transaction
                return $loan;
            });

            // Redirect ke Index membawa data 'new_loan' untuk memicu Modal
            return redirect()->route('loans.index')
                ->with('success', 'Transaksi Peminjaman Berhasil!')
                ->with('new_loan', $newLoan); // <--- INI PENTING UNTUK MODAL

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses: ' . $e->getMessage());
        }
    }

    public function returnLoan(Request $request, string $id)
    {
        // 1. Ambil Data Transaksi
        $loan = Loan::with('details')->findOrFail($id);

        // Pencegahan: Jangan proses jika sudah selesai
        if ($loan->status_transaksi == 'selesai') {
            return back()->with('error', 'Transaksi ini sudah selesai sebelumnya!');
        }

        try {
            DB::transaction(function () use ($loan, $request) {

                // 2. Setup Variabel Perhitungan
                $dendaPerHari = (int) (Setting::where('key', 'denda_harian')->value('value') ?? 500);

                // Gunakan startOfDay() agar jam/menit tidak mempengaruhi (hanya tanggal)
                $tglKembali = Carbon::now()->startOfDay();
                $jatuhTempo = Carbon::parse($loan->tgl_wajib_kembali)->startOfDay();

                // Update tanggal kembali real di database (pake jam sekarang)
                $loan->tgl_kembali = Carbon::now();

                // 3. LOGIKA HITUNG DENDA (JURUS ANTI MINUS)
                // Hitung selisih: Dari Jatuh Tempo -> Ke Tanggal Kembali
                // Parameter 'false' agar menghasilkan nilai positif/negatif
                $selisihHari = $jatuhTempo->diffInDays($tglKembali, false);

                // Fungsi max(0, $var) akan membuang nilai negatif.
                // Jika selisih -5 (Cepat), diambil 0. Jika selisih 5 (Telat), diambil 5.
                $hariTelat = max(0, $selisihHari);

                // Hitung Total Nominal
                $loan->total_denda = $hariTelat * $dendaPerHari;

                // 4. LOGIKA STATUS PEMBAYARAN
                if ($loan->total_denda == 0) {
                    // Skenario A: Tepat Waktu / Lebih Cepat
                    $loan->status_pembayaran = 'paid'; // Otomatis Lunas
                } else {
                    // Skenario B: Terlambat (Punya Denda)
                    // Defaultnya Pending (Menunggu Bayar)
                    $loan->status_pembayaran = 'pending';

                    // Cek Checkbox "Bayar Tunai" dari Admin
                    // Jika dicentang, langsung ubah jadi Paid & Tunai
                    if ($request->has('denda_lunas')) {
                        $loan->status_pembayaran = 'paid';
                        $loan->tipe_pembayaran   = 'tunai';
                    }
                }

                // 5. Simpan Perubahan Header
                $loan->status_transaksi = 'selesai';
                $loan->save();

                // 6. Kembalikan Stok Buku (Looping Detail)
                foreach ($loan->details as $detail) {
                    if ($detail->status_item !== 'kembali') {
                        $detail->update(['status_item' => 'kembali']);
                        Book::where('id', $detail->book_id)->increment('stok_tersedia');
                    }
                }
            });

            return back()->with('success', 'Buku berhasil dikembalikan & Stok diperbarui.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses pengembalian: ' . $e->getMessage());
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

    public function payLateFine($id)
    {
        $loan = Loan::findOrFail($id);

        // Pastikan memang sudah selesai tapi belum bayar
        if ($loan->status_transaksi == 'selesai' && $loan->status_pembayaran != 'paid') {
            $loan->update([
                'status_pembayaran' => 'paid',
                'tipe_pembayaran'   => 'tunai' // Admin terima cash
            ]);
            return back()->with('success', 'Pembayaran denda tunai berhasil dicatat.');
        }

        return back()->with('error', 'Transaksi tidak valid untuk pembayaran susulan.');
    }
}
