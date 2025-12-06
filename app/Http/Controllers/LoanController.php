<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\LoanDetail;
use App\Models\Book;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Wajib untuk Transaction
use Carbon\Carbon;

class LoanController extends Controller
{
    public function index(Request $request)
    {
        $query = Loan::with(['member', 'user', 'details']);

        // Fitur Pencarian (Cari Kode Transaksi atau Nama Anggota)
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('kode_transaksi', 'like', "%{$search}%")
                  ->orWhereHas('member', function($q) use ($search) {
                      $q->where('nama_lengkap', 'like', "%{$search}%");
                  });
        }

        $loans = $query->orderBy('id', 'desc')->paginate(10);

        return view('loans.index', compact('loans'));
    }

    // Halaman Form Peminjaman
    public function create()
    {
        // Ambil member yang aktif saja
        $members = Member::where('status_aktif', true)->orderBy('nama_lengkap')->get();

        // Ambil buku yang stoknya ADA saja
        $books = Book::where('stok_tersedia', '>', 0)->orderBy('judul')->get();

        return view('loans.create', compact('members', 'books'));
    }

    // Logic "THE BOSS" (Simpan Transaksi)
    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'member_id' => 'required|exists:members,id',
            'book_ids'  => 'required|array|min:1', // Harus pilih minimal 1 buku
            'book_ids.*'=> 'exists:books,id',       // Pastikan bukunya valid
        ]);

        // 2. Database Transaction (Bungkus logic biar aman)
        try {
            DB::transaction(function () use ($request) {

                // A. Buat Header Transaksi (Tabel Loans)
                $loan = Loan::create([
                    'kode_transaksi' => 'TRX-' . time(), // Generate kode unik simpel
                    'member_id' => $request->member_id,
                    'user_id'   => Auth::id(), // Petugas yang sedang login
                    'tgl_pinjam' => Carbon::now(),
                    'tgl_wajib_kembali' => Carbon::now()->addDays(7), // Pinjam 7 hari (bisa ambil dari settings nanti)
                    'tahun_ajaran' => '2024/2025', // Hardcode dulu, nanti bisa dinamis
                    'status_transaksi' => 'berjalan',
                ]);

                // B. Loop setiap buku yang dipilih
                foreach ($request->book_ids as $book_id) {

                    // 1. Ambil data buku
                    $book = Book::find($book_id);

                    // Cek lagi stoknya (takutnya diserobot orang lain detik itu juga)
                    if ($book->stok_tersedia < 1) {
                        throw new \Exception("Stok buku {$book->judul} habis!");
                    }

                    // 2. Masukkan ke Detail Transaksi (Tabel LoanDetails)
                    LoanDetail::create([
                        'loan_id' => $loan->id,
                        'book_id' => $book_id,
                        'status_item' => 'dipinjam',
                    ]);

                    // 3. Kurangi Stok Buku (Update Tabel Books)
                    $book->decrement('stok_tersedia');
                }
            });

            // Jika sukses semua
            return redirect()->route('loans.index')->with('success', 'Transaksi Peminjaman Berhasil!');

        } catch (\Exception $e) {
            // Jika ada error (stok habis/db error), kembalikan ke form
            return back()->with('error', 'Gagal memproses: ' . $e->getMessage());
        }
    }

    // Logic Pengembalian Buku
    public function returnLoan(string $id)
    {
        try {
            DB::transaction(function () use ($id) {
                // 1. Cari Data Transaksi
                $loan = Loan::with('details')->findOrFail($id);

                // Cek dulu, jangan sampai transaksi yang sudah selesai dikembalikan lagi (Bug prevention)
                if ($loan->status_transaksi == 'selesai') {
                    throw new \Exception("Transaksi ini sudah selesai!");
                }

                // 2. Update Header Transaksi
                $loan->tgl_kembali = Carbon::now(); // Tanggal hari ini
                $loan->status_transaksi = 'selesai';

                // Cek Keterlambatan (Simple Logic)
                // Jika hari ini > wajib kembali, maka status terlambat
                if (Carbon::now()->gt($loan->tgl_wajib_kembali)) {
                   $loan->status_transaksi = 'terlambat';
                   // Di sini bisa tambah logic hitung denda kalau mau (nanti saja)
                }

                $loan->save();

                // 3. Loop Detail Buku & Balikin Stok
                foreach ($loan->details as $detail) {
                    // Update status item jadi 'kembali'
                    $detail->update(['status_item' => 'kembali']);

                    // Balikin stok buku (Increment)
                    // Cari bukunya, lalu tambah stok tersedia +1
                    Book::where('id', $detail->book_id)->increment('stok_tersedia');
                }
            });

            return back()->with('success', 'Buku berhasil dikembalikan! Stok sudah diperbarui.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengembalikan: ' . $e->getMessage());
        }
    }
}
