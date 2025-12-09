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

        // OPTIMASI: Ambil nilai denda SEKALI saja di sini, lalu kirim ke View
        // Agar tidak query berulang-ulang di dalam looping blade
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
                    'status_transaksi' => 'berjalan', // Default BERJALAN
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

                // HAPUS BAGIAN INI:
                // Jangan hitung denda atau set 'selesai' saat BARU meminjam!
                // Kode lama yang salah saya hapus dari sini.
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

        // --- SATPAM (PENCEGAH BUG STOK) ---
        // Jika status sudah selesai, tolak proses!
        if ($loan->status_transaksi == 'selesai') {
            return back()->with('error', 'Transaksi ini sudah diselesaikan sebelumnya!');
        }
        // ----------------------------------

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
                    // Hitung hari telat
                    $selisihHari = $tanggalKembali->diffInDays($jatuhTempo);
                    $loan->total_denda = $selisihHari * $dendaPerHari;
                }

                // 3. PENTING: Status AKHIR harus selalu 'selesai'
                // Karena buku sudah diterima fisik oleh admin.
                // Masalah bayar denda, itu urusan kasir (UI) yang memastikan uang diterima sebelum klik tombol ini.
                $loan->status_transaksi = 'selesai';

                $loan->save();

                // 4. Balikin Stok Buku
                foreach ($loan->details as $detail) {
                    if ($detail->status_item !== 'kembali') { // Cek double protection
                        $detail->update(['status_item' => 'kembali']);
                        Book::where('id', $detail->book_id)->increment('stok_tersedia');
                    }
                }
            });

            // Kirim Pesan WA "Terima Kasih" (Opsional, Logic ada di bawah)
            // $this->sendWhatsApp($loan->member->no_telepon, "Terima kasih, buku sudah dikembalikan.");

            return back()->with('success', 'Buku berhasil dikembalikan & Stok diperbarui.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }
}
