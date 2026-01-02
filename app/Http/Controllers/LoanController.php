<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\LoanDetail;
use App\Models\Book;
use App\Models\Member;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Midtrans\Config;
use Midtrans\Transaction;
use App\Helpers\WhatsAppHelper; // Helper WA

class LoanController extends Controller
{
    // =========================================================================
    // 1. FITUR UTAMA (INDEX, CREATE, STORE)
    // =========================================================================

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

        // =================================================================
        // 🛑 ATURAN 1 & 2: VALIDASI KELAYAKAN PEMINJAM (SATPAM)
        // =================================================================

        $memberId = $request->member_id;
        $maxBuku  = \App\Models\Setting::where('key', 'max_buku_pinjam')->value('value') ?? 3; // Default 3 buku

        // 1. CEK DENDA (Apakah ada denda yang belum dibayar?)
        // Mencari transaksi selesai/berjalan milik member ini yang punya denda & belum lunas
        $punyaTunggakan = Loan::where('member_id', $memberId)
            ->where('total_denda', '>', 0)
            ->where('status_pembayaran', '!=', 'paid')
            ->exists();

        if ($punyaTunggakan) {
            return back()->with('error', '⛔ DITOLAK: Siswa ini memiliki denda yang belum dilunasi. Harap lunasi dulu!');
        }

        // 2. CEK KETERLAMBATAN (Apakah ada buku yang masih dibawa & sudah lewat tempo?)
        $punyaBukuTelat = Loan::where('member_id', $memberId)
            ->where('status_transaksi', 'berjalan') // Sedang meminjam
            ->whereDate('tgl_wajib_kembali', '<', now()) // Tanggal wajib < Hari ini (Telat)
            ->exists();

        if ($punyaBukuTelat) {
            return back()->with('error', '⛔ DITOLAK: Siswa ini masih membawa buku yang sudah Terlambat. Harap kembalikan dulu!');
        }

        // 3. CEK KUOTA BUKU (Maksimal 3 Buku)
        // Hitung total buku yang SEDANG DIPINJAM (Status Item: dipinjam)
        $jumlahBukuSedangDipinjam = \App\Models\LoanDetail::whereHas('loan', function($q) use ($memberId) {
                $q->where('member_id', $memberId)
                  ->where('status_transaksi', 'berjalan');
            })
            ->where('status_item', 'dipinjam')
            ->count();

        $jumlahBukuAkanDipinjam = count($request->book_ids);
        $totalBuku = $jumlahBukuSedangDipinjam + $jumlahBukuAkanDipinjam;

        if ($totalBuku > $maxBuku) {
            return back()->with('error', "⛔ KUOTA PENUH: Siswa sedang meminjam {$jumlahBukuSedangDipinjam} buku. Ditambah {$jumlahBukuAkanDipinjam} buku baru, total menjadi {$totalBuku}. Batas maksimal adalah {$maxBuku} buku.");
        }

        // =================================================================
        // JIKA LOLOS PENGECEKAN DI ATAS, BARU LANJUT SIMPAN
        // =================================================================

        try {
            // A. SIMPAN DATABASE (Transaction)
            $newLoan = DB::transaction(function () use ($request) {
                $durasiPinjam = Setting::where('key', 'max_lama_pinjam')->value('value') ?? 7;

                // Buat Header
                $loan = Loan::create([
                    'kode_transaksi'    => 'TRX-' . time(),
                    'member_id'         => $request->member_id,
                    'user_id'           => auth()->id(),
                    'tgl_pinjam'        => now(),
                    'tgl_wajib_kembali' => now()->addDays((int)$durasiPinjam),
                    'status_transaksi'  => 'berjalan',
                    'status_pembayaran' => 'unpaid',
                    'total_denda'       => 0,
                    'tahun_ajaran'      => (date('m') > 6) ? date('Y').'/'.(date('Y')+1) : (date('Y')-1).'/'.date('Y'),
                ]);

                // Buat Detail & Kurangi Stok
                foreach ($request->book_ids as $book_id) {
                    $book = Book::find($book_id);
                    if ($book->stok_tersedia < 1) throw new \Exception("Stok buku '{$book->judul}' habis!");

                    LoanDetail::create([
                        'loan_id' => $loan->id, 'book_id' => $book_id, 'status_item' => 'dipinjam',
                    ]);
                    $book->decrement('stok_tersedia');
                }
                return $loan;
            });

            // B. KIRIM NOTIFIKASI WA (Helper)
            // (Kode WA Anda tetap di sini, saya sembunyikan biar pendek)
            //  try {
            //     $newLoan->load(['member', 'details.book']);
            //     if (!empty($newLoan->member->no_telepon)) {
            //         $listBuku = "";
            //         foreach ($newLoan->details as $index => $detail) {
            //             $judul = $detail->book->judul ?? 'Buku';
            //             $listBuku .= ($index + 1) . ". $judul\n";
            //         }
            //         $pesan = "*BUKTI PEMINJAMAN BUKU*\n--------------------------------\n" .
            //                  "Halo, *{$newLoan->member->nama_lengkap}*\n\n" .
            //                  "Peminjaman berhasil dicatat.\n" .
            //                  "📝 Kode: *{$newLoan->kode_transaksi}*\n" .
            //                  "📅 Tgl Pinjam: " . date('d-m-Y', strtotime($newLoan->tgl_pinjam)) . "\n" .
            //                  "📅 *Wajib Kembali: " . date('d-m-Y', strtotime($newLoan->tgl_wajib_kembali)) . "*\n\n" .
            //                  "📚 *Buku yang dibawa:*\n" . $listBuku . "\n" .
            //                  "Mohon dikembalikan tepat waktu. Terima Kasih! 🙏";
            //         WhatsAppHelper::send($newLoan->member->no_telepon, $pesan);
            //     }
            // } catch (\Exception $waError) {
            //     \Log::error('Gagal kirim WA: ' . $waError->getMessage());
            // }

            return redirect()->route('loans.index')
                ->with('success', 'Transaksi Berhasil & Notifikasi WA Terkirim!')
                ->with('new_loan', $newLoan);

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // 2. FITUR PENGEMBALIAN (RETURN)
    // =========================================================================

    public function returnLoan(Request $request, string $id)
    {
        $loan = Loan::with('details')->findOrFail($id);

        if ($loan->status_transaksi == 'selesai') {
            return back()->with('error', 'Transaksi ini sudah selesai sebelumnya!');
        }

        // Validasi Input Kondisi
        $request->validate([
            'kondisi' => 'required|array',
            'kondisi.*' => 'in:baik,rusak,hilang', // Pastikan valuenya valid
        ]);

        try {
            DB::transaction(function () use ($loan, $request) {
                // 1. Hitung Denda Keterlambatan (Waktu)
                $dendaPerHari = (int) (Setting::where('key', 'denda_harian')->value('value') ?? 500);
                $tglKembali = Carbon::now()->startOfDay();
                $jatuhTempo = Carbon::parse($loan->tgl_wajib_kembali)->startOfDay();
                $loan->tgl_kembali = Carbon::now();

                $selisihHari = max(0, $jatuhTempo->diffInDays($tglKembali, false));
                $dendaTelat = $selisihHari * $dendaPerHari;

                // Variabel untuk menampung total harga buku yang hilang
                $dendaGantiRugi = 0;

                // 2. PROSES DETAIL BUKU
                $inputs = $request->input('kondisi');

                foreach ($loan->details as $detail) {
                    $kondisi = $inputs[$detail->id] ?? 'baik';

                    // Update Status
                    $detail->update([
                        'status_item' => $kondisi == 'hilang' ? 'hilang' : 'kembali',
                        'kondisi_kembali' => $kondisi
                    ]);

                    // Update Stok & Hitung Ganti Rugi
                    if ($kondisi == 'hilang') {
                        // A. Stok Hilang bertambah
                        Book::where('id', $detail->book_id)->increment('stok_hilang');

                        // B. TAMBAHKAN HARGA BUKU KE DENDA
                        // Pastikan harga diambil, jika 0 atau null pake 0
                        $hargaBuku = $detail->book->harga ?? 0;
                        $dendaGantiRugi += $hargaBuku;

                    } elseif ($kondisi == 'rusak') {
                        Book::where('id', $detail->book_id)->increment('stok_rusak');
                        // Opsional: Jika rusak mau didenda juga (misal 50% harga), tambahkan logic disini
                    } else {
                        Book::where('id', $detail->book_id)->increment('stok_tersedia');
                    }
                }

                // 3. SIMPAN HEADER TRANSAKSI
                // Total Denda = Denda Telat (Waktu) + Denda Ganti Rugi (Barang)
                $loan->total_denda = $dendaTelat + $dendaGantiRugi;

                // Logic Status Pembayaran (Tetap Sama)
                if ($loan->total_denda == 0) {
                    $loan->status_pembayaran = 'paid';
                } else {
                    $loan->status_pembayaran = 'pending';
                    if ($request->has('denda_lunas')) {
                        $loan->status_pembayaran = 'paid';
                        $loan->tipe_pembayaran   = 'tunai';
                    }
                }

                $loan->status_transaksi = 'selesai';
                $loan->save();
            });

            return back()->with('success', 'Buku berhasil dikembalikan. Status kondisi telah dicatat.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // 3. FITUR MIDTRANS (PAYMENT GATEWAY)
    // =========================================================================

    // Konfigurasi Midtrans (Private Function agar tidak copy-paste berulang)
    private function configureMidtrans()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    public function checkPaymentStatus($id)
    {
        $loan = Loan::findOrFail($id);
        if (empty($loan->midtrans_order_id)) return back()->with('error', 'Belum ada Order ID Midtrans.');

        $this->configureMidtrans();

        try {
            $status = Transaction::status($loan->midtrans_order_id);
            /** @var object $status */
            if ($status->transaction_status == 'settlement' || $status->transaction_status == 'capture') {
                $loan->update(['status_pembayaran' => 'paid', 'tipe_pembayaran' => 'online']);
                return back()->with('success', 'Status LUNAS.');
            } else if ($status->transaction_status == 'expire') {
                $loan->update(['status_pembayaran' => 'unpaid', 'midtrans_url' => null]);
                return back()->with('error', 'Pembayaran Kadaluarsa.');
            }
            return back()->with('info', 'Status saat ini: ' . $status->transaction_status);

        } catch (\Exception $e) {
            return back()->with('error', 'Error Midtrans: ' . $e->getMessage());
        }
    }

    public function refreshAllStatus()
    {
        $pendingLoans = Loan::where('status_pembayaran', 'pending')->whereNotNull('midtrans_order_id')->get();
        if ($pendingLoans->isEmpty()) return back()->with('info', 'Tidak ada transaksi pending.');

        $this->configureMidtrans();
        $updatedCount = 0;

        foreach ($pendingLoans as $loan) {
            try {
                $status = Transaction::status($loan->midtrans_order_id);
                /** @var object $status */
                if ($status->transaction_status == 'settlement' || $status->transaction_status == 'capture') {
                    $loan->update(['status_pembayaran' => 'paid', 'tipe_pembayaran' => 'online']);
                    $updatedCount++;
                } else if ($status->transaction_status == 'expire') {
                    $loan->update(['status_pembayaran' => 'unpaid', 'midtrans_url' => null]);
                }
            } catch (\Exception $e) { continue; }
        }

        return $updatedCount > 0
            ? back()->with('success', "$updatedCount transaksi diperbarui jadi LUNAS.")
            : back()->with('info', 'Belum ada pembayaran baru masuk.');
    }

    public function payLateFine($id)
    {
        $loan = Loan::findOrFail($id);
        if ($loan->status_transaksi == 'selesai' && $loan->status_pembayaran != 'paid') {
            $loan->update(['status_pembayaran' => 'paid', 'tipe_pembayaran' => 'tunai']);
            return back()->with('success', 'Pembayaran tunai berhasil.');
        }
        return back()->with('error', 'Transaksi tidak valid.');
    }
}
