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

        // 1. PENCARIAN
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('kode_transaksi', 'like', "%{$search}%")
                  ->orWhereHas('member', function($q) use ($search) {
                      $q->where('nama_lengkap', 'like', "%{$search}%");
                  });
        }

        // 2. FILTER STATUS & KONDISI (LOGIKA DIPERBARUI)
        if ($request->has('filter') && $request->filter != '') {
            $filter = $request->filter;

            if ($filter == 'selesai') {
                // ✅ Lunas
                $query->where('status_transaksi', 'selesai')
                      ->where('status_pembayaran', 'paid');
            }
            elseif ($filter == 'telat_only') {
                // ⏳ Telat SAJA (Fisik Aman)
                $query->where('status_transaksi', 'selesai')
                      ->where('status_pembayaran', '!=', 'paid')
                      ->whereRaw('DATE(tgl_kembali) > DATE(tgl_wajib_kembali)')
                      ->whereDoesntHave('details', function($q) {
                          $q->whereIn('kondisi_kembali', ['rusak', 'hilang']);
                      });
            }
            elseif ($filter == 'telat_rusak') {
                // ⏳⚠️ Telat + Rusak (Double Denda)
                $query->where('status_transaksi', 'selesai')
                      ->where('status_pembayaran', '!=', 'paid')
                      ->whereRaw('DATE(tgl_kembali) > DATE(tgl_wajib_kembali)')
                      ->whereHas('details', function($q) {
                          $q->where('kondisi_kembali', 'rusak');
                      });
            }
            elseif ($filter == 'telat_hilang') {
                // ⏳❌ Telat + Hilang (Double Denda)
                $query->where('status_transaksi', 'selesai')
                      ->where('status_pembayaran', '!=', 'paid')
                      ->whereRaw('DATE(tgl_kembali) > DATE(tgl_wajib_kembali)')
                      ->whereHas('details', function($q) {
                          $q->where('kondisi_kembali', 'hilang');
                      });
            }
            elseif ($filter == 'rusak_only') {
                // ⚠️ Rusak SAJA (Tepat Waktu)
                $query->where('status_transaksi', 'selesai')
                      ->where('status_pembayaran', '!=', 'paid')
                      ->whereRaw('DATE(tgl_kembali) <= DATE(tgl_wajib_kembali)')
                      ->whereHas('details', function($q) {
                          $q->where('kondisi_kembali', 'rusak');
                      });
            }
            elseif ($filter == 'hilang_only') {
                // ❌ Hilang SAJA (Tepat Waktu)
                $query->where('status_transaksi', 'selesai')
                      ->where('status_pembayaran', '!=', 'paid')
                      ->whereRaw('DATE(tgl_kembali) <= DATE(tgl_wajib_kembali)')
                      ->whereHas('details', function($q) {
                          $q->where('kondisi_kembali', 'hilang');
                      });
            }
        }

        $loans = $query->orderBy('id', 'desc')->paginate(10);

        $dendaPerHari = Setting::where('key', 'denda_harian')->value('value') ?? 500;
        $dendaRusak   = Setting::where('key', 'denda_rusak')->value('value') ?? 10000;

        return view('loans.index', compact('loans', 'dendaPerHari', 'dendaRusak'));
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
             try {
                $newLoan->load(['member', 'details.book']);
                if (!empty($newLoan->member->no_telepon)) {
                    $listBuku = "";
                    foreach ($newLoan->details as $index => $detail) {
                        $judul = $detail->book->judul ?? 'Buku';
                        $listBuku .= ($index + 1) . ". $judul\n";
                    }
                    $pesan = "*BUKTI PEMINJAMAN BUKU*\n--------------------------------\n" .
                             "Halo, *{$newLoan->member->nama_lengkap}*\n\n" .
                             "Peminjaman berhasil dicatat.\n" .
                             "📝 Kode: *{$newLoan->kode_transaksi}*\n" .
                             "📅 Tgl Pinjam: " . date('d-m-Y', strtotime($newLoan->tgl_pinjam)) . "\n" .
                             "📅 *Wajib Kembali: " . date('d-m-Y', strtotime($newLoan->tgl_wajib_kembali)) . "*\n\n" .
                             "📚 *Buku yang dibawa:*\n" . $listBuku . "\n" .
                             "Mohon dikembalikan tepat waktu. Terima Kasih! 🙏";
                    WhatsAppHelper::send($newLoan->member->no_telepon, $pesan);
                }
            } catch (\Exception $waError) {
                \Log::error('Gagal kirim WA: ' . $waError->getMessage());
            }

            return redirect()->route('loans.index')
                ->with('success', 'Transaksi Berhasil & Notifikasi WA Terkirim!')
                ->with('new_loan', $newLoan);

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // 2. FITUR PENGEMBALIAN (RETURN) - DENGAN NOTIFIKASI WA GANTI RUGI
    // =========================================================================

    public function returnLoan(Request $request, string $id)
    {
        $loan = Loan::with(['details.book', 'member'])->findOrFail($id);

        if ($loan->status_transaksi == 'selesai') {
            return back()->with('error', 'Transaksi ini sudah selesai sebelumnya!');
        }

        $request->validate([
            'items_to_return' => 'required|array|min:1',
            'kondisi' => 'array',
        ]);

        try {
            $pesanWa = "";
            $nomorHp = $loan->member->no_telepon;
            $totalTagihanFinal = 0; // Untuk menampung total denda

            // 1. PROSES DB TRANSACTION (Hitung Denda & Update Stok)
            DB::transaction(function () use ($loan, $request, &$pesanWa, &$totalTagihanFinal) {
                $dendaPerHari = (int) (Setting::where('key', 'denda_harian')->value('value') ?? 500);
                $nominalRusak = (int) (Setting::where('key', 'denda_rusak')->value('value') ?? 10000);
                $today = Carbon::now()->startOfDay();

                $selectedDetailIds = $request->input('items_to_return');
                $inputKondisi = $request->input('kondisi', []);

                $dendaGantiRugiSesiIni = 0;
                $listKerusakan = [];

                // A. LOOP ITEM
                foreach ($selectedDetailIds as $detailId) {
                    $detail = LoanDetail::with('book')->where('id', $detailId)
                                        ->where('loan_id', $loan->id)
                                        ->where('status_item', 'dipinjam')
                                        ->first();

                    if (!$detail) continue;

                    $kondisi = $inputKondisi[$detailId] ?? 'baik';

                    $detail->update([
                        'status_item' => ($kondisi == 'hilang') ? 'hilang' : 'kembali',
                        'kondisi_kembali' => $kondisi
                    ]);

                    if ($kondisi == 'hilang') {
                        Book::where('id', $detail->book_id)->increment('stok_hilang');
                        $hargaBuku = $detail->book->harga ?? 0;
                        $dendaGantiRugiSesiIni += $hargaBuku;
                        $listKerusakan[] = "❌ {$detail->book->judul} (HILANG) - Rp " . number_format($hargaBuku, 0, ',', '.');

                    } elseif ($kondisi == 'rusak') {
                        Book::where('id', $detail->book_id)->increment('stok_rusak');
                        $dendaGantiRugiSesiIni += $nominalRusak;
                        $listKerusakan[] = "⚠️ {$detail->book->judul} (RUSAK) - Rp " . number_format($nominalRusak, 0, ',', '.');

                    } else {
                        Book::where('id', $detail->book_id)->increment('stok_tersedia');
                    }
                }

                // B. CEK SISA & DENDA WAKTU
                $sisaBuku = LoanDetail::where('loan_id', $loan->id)->where('status_item', 'dipinjam')->count();
                $dendaWaktu = 0;

                if ($sisaBuku === 0) {
                    $jatuhTempo = Carbon::parse($loan->tgl_wajib_kembali)->startOfDay();
                    $selisihHari = max(0, $jatuhTempo->diffInDays($today, false));
                    $dendaWaktu = $selisihHari * $dendaPerHari;

                    $loan->tgl_kembali = now();
                    $loan->status_transaksi = 'selesai';

                    if($dendaWaktu > 0) {
                        $listKerusakan[] = "⏳ Keterlambatan ({$selisihHari} hari) - Rp " . number_format($dendaWaktu, 0, ',', '.');
                    }
                }

                // C. UPDATE TOTAL DENDA
                $totalTambahan = $dendaWaktu + $dendaGantiRugiSesiIni;
                if ($totalTambahan > 0) {
                    $loan->total_denda = ($loan->total_denda ?? 0) + $totalTambahan;
                }

                $totalTagihanFinal = $loan->total_denda; // Simpan ke var luar utk Midtrans

                // D. STATUS PEMBAYARAN
                if ($loan->total_denda > 0) {
                    $loan->status_pembayaran = 'pending';

                    // Cek jika Bayar Tunai
                    if ($request->has('denda_lunas') && $sisaBuku === 0) {
                        $loan->status_pembayaran = 'paid';
                        $loan->tipe_pembayaran = 'tunai';
                    }
                } elseif ($sisaBuku === 0) {
                    $loan->status_pembayaran = 'paid';
                }

                $loan->save();

                // E. SIAPKAN LIST TEXT WA (Link ditambahkan nanti di luar transaction)
                if (count($listKerusakan) > 0 || $totalTambahan > 0) {
                    $pesanWa = "*INVOICE PENGEMBALIAN BUKU*\n";
                    $pesanWa .= "Halo, {$loan->member->nama_lengkap}\n\n";
                    $pesanWa .= "Berikut rincian tagihan:\n";
                    $pesanWa .= implode("\n", $listKerusakan);
                    $pesanWa .= "\n--------------------------------\n";
                    $pesanWa .= "*Total Tagihan: Rp " . number_format($totalTagihanFinal, 0, ',', '.') . "*\n";
                }
            });

            // 2. GENERATE MIDTRANS LINK (JIKA BELUM LUNAS)
            // Dilakukan di luar transaction DB agar tidak menahan koneksi database saat request ke API Midtrans
            if ($loan->status_pembayaran == 'pending' && $totalTagihanFinal > 0) {

                $this->configureMidtrans(); // Panggil konfigurasi

                $orderId = 'DENDA-RETURN-' . $loan->id . '-' . time(); // ID Unik

                $params = [
                    'transaction_details' => [
                        'order_id' => $orderId,
                        'gross_amount' => $totalTagihanFinal,
                    ],
                    'customer_details' => [
                        'first_name' => $loan->member->nama_lengkap,
                        'phone' => $loan->member->no_telepon,
                    ],
                    'item_details' => [[
                        'id' => 'TAGIHAN-DENDA',
                        'price' => $totalTagihanFinal,
                        'quantity' => 1,
                        'name' => "Total Denda & Ganti Rugi"
                    ]],
                    'expiry' => [
                        'start_time' => date("Y-m-d H:i:s O"),
                        'unit' => 'days',
                        'duration' => 7
                    ],
                ];

                try {
                    $paymentUrl = \Midtrans\Snap::createTransaction($params)->redirect_url;

                    // Simpan Link ke DB
                    $loan->update([
                        'midtrans_order_id' => $orderId,
                        'midtrans_url' => $paymentUrl,
                        'tipe_pembayaran' => 'online'
                    ]);

                    // Tambahkan Link ke Pesan WA
                    $pesanWa .= "Status: *BELUM LUNAS*\n\n";
                    $pesanWa .= "👉 *Klik Link untuk Bayar (Gopay/Transfer):*\n";
                    $pesanWa .= $paymentUrl . "\n\n";

                } catch (\Exception $e) {
                    \Log::error("Gagal Generate Link Midtrans: " . $e->getMessage());
                    $pesanWa .= "Status: *BELUM LUNAS* (Silakan bayar tunai di perpustakaan)\n\n";
                }

            } elseif ($loan->status_pembayaran == 'paid') {
                $pesanWa .= "Status: *LUNAS (TUNAI)* ✅\n\n";
            }

            // 3. KIRIM WA FINAL
            if (!empty($pesanWa) && !empty($nomorHp)) {
                $pesanWa .= "Terima Kasih 🙏";
                try {
                    WhatsAppHelper::send($nomorHp, $pesanWa);
                } catch (\Exception $e) {
                    \Log::error("Gagal kirim WA: " . $e->getMessage());
                }
            }

            return back()->with('success', 'Buku berhasil dikembalikan. Notifikasi tagihan terkirim.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // 3. FITUR MIDTRANS (PAYMENT GATEWAY)
    // =========================================================================

    private function configureMidtrans()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    public function checkPaymentStatus($id)
    {
        $loan = Loan::with('member')->findOrFail($id); // Load member untuk ambil No HP
        if (empty($loan->midtrans_order_id)) return back()->with('error', 'Belum ada Order ID Midtrans.');

        $this->configureMidtrans();

        try {
            $status = Transaction::status($loan->midtrans_order_id);
            /** @var object $status */
            if ($status->transaction_status == 'settlement' || $status->transaction_status == 'capture') {

                // Update Status
                $loan->update(['status_pembayaran' => 'paid', 'tipe_pembayaran' => 'online']);

                // [BARU] Kirim Notifikasi LUNAS
                $this->sendLunasWA($loan);

                return back()->with('success', 'Status LUNAS. Notifikasi WA terkirim.');
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
        // Load member juga
        $pendingLoans = Loan::with('member')->where('status_pembayaran', 'pending')->whereNotNull('midtrans_order_id')->get();
        if ($pendingLoans->isEmpty()) return back()->with('info', 'Tidak ada transaksi pending.');

        $this->configureMidtrans();
        $updatedCount = 0;

        foreach ($pendingLoans as $loan) {
            try {
                $status = Transaction::status($loan->midtrans_order_id);
                /** @var object $status */
                if ($status->transaction_status == 'settlement' || $status->transaction_status == 'capture') {

                    $loan->update(['status_pembayaran' => 'paid', 'tipe_pembayaran' => 'online']);

                    // [BARU] Kirim Notifikasi LUNAS
                    $this->sendLunasWA($loan);

                    $updatedCount++;
                } else if ($status->transaction_status == 'expire') {
                    $loan->update(['status_pembayaran' => 'unpaid', 'midtrans_url' => null]);
                }
            } catch (\Exception $e) { continue; }
        }

        return $updatedCount > 0
            ? back()->with('success', "$updatedCount transaksi diperbarui jadi LUNAS & Notifikasi dikirim.")
            : back()->with('info', 'Belum ada pembayaran baru masuk.');
    }

    // --- [METODE TAMBAHAN] Helper Kirim WA Lunas ---
    private function sendLunasWA($loan) {
        if (!empty($loan->member->no_telepon)) {
            $pesan = "*PEMBAYARAN DITERIMA* 💰\n\n";
            $pesan .= "Halo, {$loan->member->nama_lengkap}\n";
            $pesan .= "Terima kasih, pembayaran denda untuk Transaksi *{$loan->kode_transaksi}* telah kami terima via Online.\n\n";
            $pesan .= "Nominal: Rp " . number_format($loan->total_denda, 0, ',', '.') . "\n";
            $pesan .= "Status: *LUNAS* ✅\n\n";
            $pesan .= "Sistem Perpustakaan.";

            try {
                WhatsAppHelper::send($loan->member->no_telepon, $pesan);
            } catch (\Exception $e) {
                // Biarkan lanjut meski WA gagal
                \Log::error("Gagal kirim WA Lunas: " . $e->getMessage());
            }
        }
    }

    public function payLateFine($id)
    {
        // [UPDATE] Tambahkan with('member') agar data no_telepon terbaca
        $loan = Loan::with('member')->findOrFail($id);

        if ($loan->status_transaksi == 'selesai' && $loan->status_pembayaran != 'paid') {

            $loan->update([
                'status_pembayaran' => 'paid',
                'tipe_pembayaran' => 'tunai'
            ]);

            // [BARU] Panggil Helper WA Lunas (Sama seperti Midtrans)
            $this->sendLunasWA($loan);

            return back()->with('success', 'Pembayaran tunai berhasil dicatat & Notifikasi WA terkirim.');
        }

        return back()->with('error', 'Transaksi tidak valid atau sudah lunas.');
    }
}
