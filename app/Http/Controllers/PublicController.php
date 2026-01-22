<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Member;
use App\Models\Loan;
use App\Models\LoanDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Setting;
use App\Helpers\WhatsAppHelper;
use Midtrans\Snap;
use Midtrans\Config;

class PublicController extends Controller
{
    // Halaman Depan (Marketplace Style)
    public function index(Request $request)
    {
        $query = Book::with(['category', 'shelf'])->where('stok_tersedia', '>', 0);

        if ($request->has('q') && $request->q != '') {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('pengarang', 'like', "%{$search}%");
            });
        }

        if ($request->has('cat')) {
            $query->where('kategori_id', $request->cat);
        }

        $books = $query->latest()->paginate(12);
        $categories = \App\Models\Category::withCount('books')->get();

        return view('welcome', compact('books', 'categories'));
    }

    public function showBook($id)
    {
        // 1. Ambil Data Buku Utama
        $book = Book::with(['category', 'shelf'])->findOrFail($id);

        // 2. Ambil Buku Lain di Kategori yang Sama (Rekomendasi)
        $relatedBooks = Book::where('kategori_id', $book->kategori_id)
                            ->where('id', '!=', $id) // Jangan tampilkan buku yang sedang dibuka
                            ->inRandomOrder()
                            ->take(4) // Ambil 4 buku
                            ->get();

        return view('public.book-detail', compact('book', 'relatedBooks'));
    }

    public function kiosk($bookId)
    {
        $book = Book::findOrFail($bookId);
        return view('public.kiosk', compact('book'));
    }

    // --- FUNGSI PROSES PEMINJAMAN ---
    public function processSelfLoan(Request $request)
    {
        // 1. Validasi Input
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'kode_anggota' => 'required|exists:members,kode_anggota',
            'book_id'      => 'required|exists:books,id',
            'durasi'       => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi Gagal: ' . $validator->errors()->first()
            ]);
        }

        try {
            // 2. Cek Member
            $member = Member::where('kode_anggota', $request->kode_anggota)->first();

            if (!$member->status_aktif) {
                throw new \Exception('Kartu Anggota Tidak Aktif / Diblokir.');
            }

            // --- [LOGIKA BLACKLIST CEK] ---

            // A. Cek Buku Telat (Fisik belum balik & Tanggal lewat)
            $bukuTelat = Loan::where('member_id', $member->id)
                            ->where('status_transaksi', 'berjalan')
                            ->whereDate('tgl_wajib_kembali', '<', Carbon::now()) // Jatuh tempo < Hari ini
                            ->exists();

            if ($bukuTelat) {
                throw new \Exception('DITOLAK: Anda masih meminjam buku yang sudah lewat jatuh tempo. Harap kembalikan dulu!');
            }

            // B. Cek Denda Belum Lunas
            $dendaNunggak = Loan::where('member_id', $member->id)
                                ->whereIn('status_pembayaran', ['unpaid', 'pending'])
                                ->where('total_denda', '>', 0)
                                ->exists();

            if ($dendaNunggak) {
                $totalHutang = Loan::where('member_id', $member->id)
                                ->whereIn('status_pembayaran', ['unpaid', 'pending'])
                                ->sum('total_denda');
                throw new \Exception('DITOLAK: Ada tunggakan denda Rp ' . number_format($totalHutang) . '. Lunasi dulu ya!');
            }

            // C. Cek Batas Maksimal Pinjam
            $maxPinjam = Setting::where('key', 'max_buku_pinjam')->value('value') ?? 3;

            // Hitung jumlah BUKU FISIK yang sedang dipinjam
            $jumlahBukuSedangDipinjam = LoanDetail::whereHas('loan', function($q) use ($member) {
                                            $q->where('member_id', $member->id)
                                            ->where('status_transaksi', 'berjalan');
                                        })
                                        ->where('status_item', 'dipinjam')
                                        ->count();

            // Jika buku yang dibawa sudah sama dengan atau lebih dari batas, TOLAK.
            if ($jumlahBukuSedangDipinjam >= $maxPinjam) {
                throw new \Exception("DITOLAK: Kuota Penuh! Anda sedang meminjam {$jumlahBukuSedangDipinjam} buku. Batas maksimal adalah {$maxPinjam} buku.");
            }

            // -------------------------------------

            // 3. Cek Stok Buku
            $book = Book::find($request->book_id);
            if ($book->stok_tersedia < 1) {
                throw new \Exception('Maaf, stok buku ini baru saja habis.');
            }

            // 4. Cari Petugas Default
            $petugas = User::first(); // Ambil user pertama sebagai default system handler
            if (!$petugas) $petugasId = 1; else $petugasId = $petugas->id;

            DB::transaction(function () use ($request, $member, $petugasId) {
                // Buat Loan
                $loan = Loan::create([
                    'kode_transaksi' => 'SELF-' . time(),
                    'member_id'      => $member->id,
                    'user_id'        => $petugasId,
                    'tgl_pinjam'     => Carbon::now(),
                    'tgl_wajib_kembali' => Carbon::now()->addDays((int)$request->durasi),
                    'status_transaksi' => 'berjalan',
                    'tahun_ajaran'   => (date('m') > 6) ? date('Y').'/'.(date('Y')+1) : (date('Y')-1).'/'.date('Y'),
                ]);

                // Buat Detail
                LoanDetail::create([
                    'loan_id' => $loan->id,
                    'book_id' => $request->book_id,
                    'status_item' => 'dipinjam'
                ]);

                // Kurangi Stok
                Book::where('id', $request->book_id)->decrement('stok_tersedia');
            });

            // --- [NEW] KIRIM NOTIFIKASI WA SETELAH TRANSAKSI SUKSES ---
            try {
                if ($member->no_hp) {
                    $tglKembali = Carbon::now()->addDays((int)$request->durasi)->format('d-m-Y');

                    $message = "Halo *{$member->nama_lengkap}*,\n\n";
                    $message .= "Peminjaman Mandiri Berhasil! ✅\n";
                    $message .= "Judul: *{$book->judul}*\n";
                    $message .= "Wajib Kembali: {$tglKembali}\n\n";
                    $message .= "Harap dijaga dengan baik ya! 📚";

                    WhatsAppHelper::sendMessage($member->no_hp, $message);
                }
            } catch (\Exception $e) {
                Log::error("Gagal kirim WA Peminjaman: " . $e->getMessage());
            }
            // ----------------------------------------------------------

            return response()->json(['status' => 'success', 'message' => 'Peminjaman Berhasil! Silakan ambil bukunya.']);

        } catch (\Exception $e) {
            Log::error("Kiosk Error: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    // Halaman Anjungan Mandiri (Standby Mode)
    public function standbyKiosk()
    {
        return view('public.kiosk-standby');
    }

    // Cek Buku via Scan
    public function checkBook(Request $request)
    {
        $book = Book::with(['category', 'shelf'])->find($request->book_code);

        if (!$book) {
            $book = Book::with(['category', 'shelf'])->where('kode_buku', $request->book_code)->first();
        }

        if (!$book) {
            return response()->json(['status' => 'error', 'message' => 'Buku tidak ditemukan!']);
        }

        if ($book->stok_tersedia < 1) {
            return response()->json(['status' => 'error', 'message' => 'Stok buku ini habis di sistem.']);
        }

        return response()->json(['status' => 'success', 'data' => $book]);
    }

    // --- FITUR PENGEMBALIAN MANDIRI (KIOSK RETURN) ---

    // 1. Halaman Kiosk Pengembalian
    public function returnKiosk()
    {
        // AMBIL SETTING DARI DATABASE
        $dendaPerHari = \App\Models\Setting::where('key', 'denda_harian')->value('value') ?? 500;
        $dendaRusak   = \App\Models\Setting::where('key', 'denda_rusak')->value('value') ?? 10000;

        // [PERBAIKAN] Penulisan compact yang benar: dipisah koma & tanpa dollar di string
        return view('public.kiosk-return', compact('dendaPerHari', 'dendaRusak'));
    }

    // 2. API: Cek Member & Buku yang sedang dipinjam
    public function checkMemberLoans(Request $request)
    {
        $member = Member::where('kode_anggota', $request->member_code)->first();

        if (!$member) {
            return response()->json(['status' => 'error', 'message' => 'Kartu Anggota Tidak Dikenali.']);
        }

        // Ambil transaksi yang BELUM kembali
        $activeLoans = Loan::with(['details.book'])
                            ->where('member_id', $member->id)
                            ->whereIn('status_transaksi', ['berjalan', 'terlambat'])
                            ->get();

        if ($activeLoans->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'Halo ' . $member->nama_lengkap . ', Anda tidak sedang meminjam buku apapun.']);
        }

        return response()->json([
            'status' => 'success',
            'member' => $member,
            'loans'  => $activeLoans
        ]);
    }

   // 3. API: Proses Pengembalian (REVISI FINAL: DIRECT DB CHECK)
    public function processSelfReturn(Request $request)
    {
        $items = $request->items;
        $totalTagihan = 0;

        try {
            DB::transaction(function () use ($items, &$totalTagihan) {

                $dendaPerHari = (int) (Setting::where('key', 'denda_harian')->value('value') ?? 500);
                $nominalRusak = (int) (Setting::where('key', 'denda_rusak')->value('value') ?? 10000);
                $today = Carbon::now()->startOfDay();

                // Grouping items by Loan ID
                $groupedItems = collect($items)->groupBy('loan_id');

                foreach ($groupedItems as $loanId => $detailsToReturn) {
                    // Lock for Update agar aman dari race condition
                    $loan = Loan::where('id', $loanId)->lockForUpdate()->first();

                    // Skip jika loan tidak valid atau sudah selesai
                    if (!$loan || $loan->status_transaksi == 'selesai') continue;

                    $dendaGantiRugi = 0;

                    // --- STEP 1: UPDATE STATUS ITEM ---
                    foreach ($detailsToReturn as $item) {
                        // Cari detail spesifik LANGSUNG KE DB
                        $dbDetail = LoanDetail::where('id', $item['detail_id'])
                                            ->where('loan_id', $loan->id)
                                            ->where('status_item', 'dipinjam') // Pastikan hanya update yang dipinjam
                                            ->first();

                        if (!$dbDetail) continue;

                        $kondisi = $item['status']; // 'kembali', 'rusak', 'hilang'

                        // Update Status di Database
                        $dbDetail->update([
                            'status_item' => ($kondisi == 'hilang') ? 'hilang' : 'kembali',
                            'kondisi_kembali' => $kondisi
                        ]);

                        // Logika Stok
                        if ($kondisi == 'hilang') {
                            Book::where('id', $dbDetail->book_id)->increment('stok_hilang');
                            $dendaGantiRugi += ($dbDetail->book->harga ?? 0);
                        } elseif ($kondisi == 'rusak') {
                            Book::where('id', $dbDetail->book_id)->increment('stok_rusak');
                            $dendaGantiRugi += $nominalRusak;
                        } else {
                            Book::where('id', $dbDetail->book_id)->increment('stok_tersedia');
                        }
                    }

                    // --- STEP 2: HITUNG SISA BUKU (DIRECT QUERY) ---
                    // Jangan pakai $loan->details (cache), tapi query baru ke tabel loan_details
                    $sisaBuku = LoanDetail::where('loan_id', $loan->id)
                                        ->where('status_item', 'dipinjam')
                                        ->count();

                    // --- STEP 3: KEPUTUSAN FINAL ---
                    if ($sisaBuku === 0) {
                        // HABIS -> TUTUP TRANSAKSI
                        $jatuhTempo = Carbon::parse($loan->tgl_wajib_kembali)->startOfDay();
                        $selisihHari = $jatuhTempo->diffInDays($today, false);
                        $telatHari = max(0, $selisihHari);
                        $dendaWaktu = $telatHari * $dendaPerHari;

                        $loan->tgl_kembali = now();
                        $loan->status_transaksi = 'selesai';
                        $loan->total_denda = $dendaWaktu + $dendaGantiRugi; // Set total final

                        $totalTagihan += ($dendaWaktu + $dendaGantiRugi);

                    } else {
                        // MASIH ADA -> JANGAN TUTUP
                        // Tambahkan denda ganti rugi (jika ada) ke total berjalan
                        if ($dendaGantiRugi > 0) {
                            $loan->total_denda += $dendaGantiRugi;
                            $totalTagihan += $dendaGantiRugi;
                        }
                    }

                    // Status Bayar
                    if ($loan->total_denda > 0) {
                        $loan->status_pembayaran = 'pending';
                    } elseif ($sisaBuku === 0 && $loan->total_denda == 0) {
                        $loan->status_pembayaran = 'paid';
                    }

                    $loan->save();
                }
            });

            $msg = 'Buku berhasil diproses.';
            if ($totalTagihan > 0) {
                $msg .= ' Tagihan denda sesi ini: Rp ' . number_format($totalTagihan, 0, ',', '.') . '. Harap lunasi di admin.';
            }

            // --- [NEW] KIRIM NOTIFIKASI WA SETELAH TRANSAKSI SUKSES ---
            try {
                // Ambil data member (kita perlu fetch ulang karena di request hanya ada ID)
                $member = Member::find($request->member_id);

                if ($member && $member->no_hp) {
                    $waMsg = "Halo *{$member->nama_lengkap}*,\n\n";
                    $waMsg .= "Terima kasih sudah mengembalikan buku via Anjungan Mandiri. 🔄\n\n";

                    if ($totalTagihan > 0) {
                        $formattedDenda = number_format($totalTagihan, 0, ',', '.');
                        $waMsg .= "⚠️ *Catatan:* Terdapat tagihan denda/ganti rugi sebesar *Rp {$formattedDenda}*.\n";
                        $waMsg .= "Mohon segera selesaikan administrasi di petugas perpustakaan.";
                    } else {
                        $waMsg .= "Semua buku telah kembali dengan baik. Terima kasih! 👍";
                    }

                    WhatsAppHelper::sendMessage($member->no_hp, $waMsg);
                }
            } catch (\Exception $e) {
                Log::error("Gagal kirim WA Pengembalian: " . $e->getMessage());
            }
            // ----------------------------------------------------------

            return response()->json(['status' => 'success', 'message' => $msg]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}
