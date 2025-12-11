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

            // --- [LOGIKA BARU: BLACKLIST CEK] ---

            // A. Cek Buku Telat (Fisik belum balik & Tanggal lewat)
            $bukuTelat = Loan::where('member_id', $member->id)
                            ->where('status_transaksi', 'berjalan')
                            ->whereDate('tgl_wajib_kembali', '<', Carbon::now()) // Jatuh tempo < Hari ini
                            ->exists();

            if ($bukuTelat) {
                throw new \Exception('DITOLAK: Anda masih meminjam buku yang sudah lewat jatuh tempo. Harap kembalikan dulu!');
            }

            // B. Cek Denda Belum Lunas (Ada tagihan Midtrans status unpaid/pending)
            $dendaNunggak = Loan::where('member_id', $member->id)
                                ->whereIn('status_pembayaran', ['unpaid', 'pending'])
                                ->where('total_denda', '>', 0) // <--- INI KUNCINYA
                                ->exists();

            if ($dendaNunggak) {
                // Ambil info denda biar pesan errornya jelas
                $totalHutang = Loan::where('member_id', $member->id)
                                   ->whereIn('status_pembayaran', ['unpaid', 'pending'])
                                   ->sum('total_denda');

                throw new \Exception('DITOLAK: Anda memiliki tunggakan denda sebesar Rp ' . number_format($totalHutang) . '. Harap lunasi di meja admin atau via online sebelum meminjam lagi.');
            }

            // C. Cek Batas Maksimal Pinjam (Misal max 3 buku)
            $jumlahPinjam = Loan::where('member_id', $member->id)
                                ->where('status_transaksi', 'berjalan')
                                ->count();

            // Angka 3 bisa diambil dari tabel settings jika mau dinamis
            if ($jumlahPinjam >= 3) {
                throw new \Exception('DITOLAK: Anda sudah meminjam 3 buku (Batas Maksimal). Kembalikan satu untuk meminjam lagi.');
            }

            // -------------------------------------

            // 3. Cek Stok Buku
            $book = Book::find($request->book_id);
            if ($book->stok_tersedia < 1) {
                throw new \Exception('Maaf, stok buku ini baru saja habis.');
            }

            // 4. Cari Petugas Default
            $petugas = User::find(3);
            if (!$petugas) {
                throw new \Exception('Error Sistem: Tidak ada data petugas.');
            }

            DB::transaction(function () use ($request, $member, $petugas) {
                // Buat Loan
                $loan = Loan::create([
                    'kode_transaksi' => 'SELF-' . time(),
                    'member_id'      => $member->id,
                    'user_id'        => $petugas->id,
                    'tgl_pinjam'     => Carbon::now(),
                    'tgl_wajib_kembali' => Carbon::now()->addDays((int)$request->durasi),
                    'status_transaksi' => 'berjalan',
                    // Gunakan guarded di Model, jadi aman tanpa fillable
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
                'message' => $e->getMessage() // Pesan error blacklist akan muncul di sini
            ], 500); // Gunakan 500 atau 400
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
        // Cari buku berdasarkan ID (QR Code buku isinya ID Buku)
        // Atau bisa berdasarkan kode_buku jika QR isinya kode unik
        $book = Book::with(['category', 'shelf'])->find($request->book_code);

        if (!$book) {
            // Coba cari by kode_buku (kalau QR isinya ISBN/Kode)
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
        return view('public.kiosk-return');
    }

    // 2. API: Cek Member & Buku yang sedang dipinjam
    public function checkMemberLoans(Request $request)
    {
        $member = Member::where('kode_anggota', $request->member_code)->first();

        if (!$member) {
            return response()->json(['status' => 'error', 'message' => 'Kartu Anggota Tidak Dikenali.']);
        }

        // Ambil transaksi yang BELUM kembali (status: berjalan / terlambat)
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

    // 3. API: Proses Pengembalian (Update: Hitung Denda & Kirim WA)
    public function processSelfReturn(Request $request)
    {
        // 1. Validasi Member
        $member = Member::find($request->member_id);

        if (!$member) {
            return response()->json(['status' => 'error', 'message' => 'Data Member tidak valid.']);
        }

        // 2. Ambil Setting Denda
        $dendaPerHari = (int) (\App\Models\Setting::where('key', 'denda_harian')->value('value') ?? 500);

        try {
            DB::transaction(function () use ($request, $dendaPerHari) {
                foreach ($request->loan_ids as $loanId) {
                    $loan = Loan::with('details')->find($loanId);

                    if ($loan && $loan->status_transaksi != 'selesai') {

                        // --- [LOGIKA BARU: JURUS ANTI MINUS] ---
                        // Gunakan startOfDay agar jam tidak mempengaruhi selisih
                        $tglKembali = Carbon::now()->startOfDay();
                        $jatuhTempo = Carbon::parse($loan->tgl_wajib_kembali)->startOfDay();

                        // Update tanggal real ke database
                        $loan->tgl_kembali = Carbon::now();

                        // Hitung Selisih: Dari Jatuh Tempo -> Ke Tgl Kembali
                        // false = agar bisa return nilai negatif (jika cepat) atau positif (jika telat)
                        $selisihHari = $jatuhTempo->diffInDays($tglKembali, false);

                        // Ambil nilai terbesar antara 0 dan selisih
                        // Jika selisih -5 (Cepat), diambil 0.
                        // Jika selisih 2 (Telat), diambil 2.
                        $hariTelat = max(0, $selisihHari);

                        // Hitung Nominal Denda
                        $denda = $hariTelat * $dendaPerHari;

                        // Tentukan Status Bayar
                        // Jika denda > 0 -> Pending (Utang). Jika 0 -> Paid (Lunas).
                        $statusBayar = ($denda > 0) ? 'pending' : 'paid';

                        // ----------------------------------------

                        // Update Loan Header
                        $loan->update([
                            'status_transaksi'  => 'selesai',
                            'total_denda'       => $denda,       // Pasti 0 atau Positif
                            'status_pembayaran' => $statusBayar
                        ]);

                        // Kembalikan Stok Buku
                        foreach ($loan->details as $detail) {
                            $detail->update(['status_item' => 'kembali']);
                            Book::where('id', $detail->book_id)->increment('stok_tersedia');
                        }
                    }
                }
            });

            return response()->json(['status' => 'success', 'message' => 'Buku berhasil dikembalikan. Silakan cek status denda Anda.']);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
