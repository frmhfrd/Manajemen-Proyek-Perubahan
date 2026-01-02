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

            // Hitung jumlah BUKU FISIK yang sedang dipinjam (bukan jumlah transaksi)
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
    // 1. Halaman Kiosk Pengembalian
    public function returnKiosk()
    {
        // AMBIL SETTING DARI DATABASE
        // Jika tidak ada di db, baru default ke 500
        $dendaPerHari = \App\Models\Setting::where('key', 'denda_harian')->value('value') ?? 500;

        // Kirim variabel $dendaPerHari ke View
        return view('public.kiosk-return', compact('dendaPerHari'));
    }

    // 2. API: Cek Member & Buku yang sedang dipinjam
    public function checkMemberLoans(Request $request)
    {
        $member = Member::where('kode_anggota', $request->member_code)->first();

        if (!$member) {
            return response()->json(['status' => 'error', 'message' => 'Kartu Anggota Tidak Dikenali.']);
        }

        // Ambil transaksi yang BELUM kembali
        // Penting: Load 'details.book' agar harga buku terbawa ke frontend
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

    // 3. API: Proses Pengembalian (Update: Hitung Denda Waktu & Ganti Rugi)
    public function processSelfReturn(Request $request)
    {
        // Request menerima: member_id, items (Array of {loan_id, detail_id, status})
        $items = $request->items;
        $totalTagihan = 0;

        try {
            DB::transaction(function () use ($items, &$totalTagihan) {

                $dendaPerHari = (int) (Setting::where('key', 'denda_harian')->value('value') ?? 500);
                $today = Carbon::now()->startOfDay();

                // Grouping items by Loan ID agar hitungan denda waktu (header) tidak dobel
                $groupedItems = collect($items)->groupBy('loan_id');

                foreach ($groupedItems as $loanId => $details) {
                    $loan = Loan::with('details.book')->find($loanId);

                    if (!$loan || $loan->status_transaksi == 'selesai') continue;

                    // A. Hitung Denda Waktu (Per Transaksi)
                    $jatuhTempo = Carbon::parse($loan->tgl_wajib_kembali)->startOfDay();
                    $selisihHari = $jatuhTempo->diffInDays($today, false);
                    $telatHari = max(0, $selisihHari);
                    $dendaWaktu = $telatHari * $dendaPerHari;

                    // Update Header Loan
                    $loan->tgl_kembali = now();
                    $loan->status_transaksi = 'selesai';

                    // B. Proses Detail Buku (Stok & Ganti Rugi)
                    $dendaGantiRugi = 0;

                    foreach ($details as $item) {
                        // Cari detail spesifik
                        $dbDetail = $loan->details->where('id', $item['detail_id'])->first();
                        if (!$dbDetail) continue;

                        $kondisi = $item['status']; // 'kembali' atau 'hilang'

                        // Update Loan Detail
                        $dbDetail->update([
                            'status_item' => $kondisi, // update status item jadi 'hilang' atau 'kembali'
                            'kondisi_kembali' => ($kondisi == 'hilang') ? 'hilang' : 'baik'
                        ]);

                        // LOGIKA STOK & HARGA
                        if ($kondisi == 'hilang') {
                            // Stok Hilang +1
                            Book::where('id', $dbDetail->book_id)->increment('stok_hilang');
                            // Tambah Denda Harga Buku
                            $hargaBuku = $dbDetail->book->harga ?? 0;
                            $dendaGantiRugi += $hargaBuku;
                        } else {
                            // Buku Kembali (Normal) -> Stok Tersedia +1
                            Book::where('id', $dbDetail->book_id)->increment('stok_tersedia');
                        }
                    }

                    // C. Simpan Total Denda ke Database
                    $loan->total_denda = $dendaWaktu + $dendaGantiRugi;
                    $totalTagihan += $loan->total_denda;

                    // Tentukan Status Bayar
                    if ($loan->total_denda > 0) {
                        $loan->status_pembayaran = 'pending'; // Harus bayar ke admin (pending/unpaid)
                    } else {
                        $loan->status_pembayaran = 'paid';
                    }

                    $loan->save();
                }
            });

            // Susun Pesan Respon
            $msg = 'Pengembalian Berhasil!';
            if ($totalTagihan > 0) {
                $msg .= ' Ada tagihan denda (Keterlambatan/Ganti Rugi) sebesar Rp ' . number_format($totalTagihan, 0, ',', '.') . '. Harap lunasi di meja admin.';
            }

            return response()->json(['status' => 'success', 'message' => $msg]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
