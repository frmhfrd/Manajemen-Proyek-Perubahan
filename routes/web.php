<?php

use App\Http\Controllers\PublicController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ShelfController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\PaymentCallbackController;
use Illuminate\Support\Facades\Route;


// ==============================================================================
// GROUP 0: PUBLIC (Akses Umum / Homepage)
// ==============================================================================
Route::get('/', [PublicController::class, 'index'])->name('home');
Route::get('/buku/{id}', [PublicController::class, 'showBook'])->name('public.book.show');

// ==============================================================================
// GROUP KIOSK (DILINDUNGI IP ADDRESS) 🔒
// ==============================================================================
Route::middleware(['kiosk.guard'])->group(function () {

    // Fitur Pinjam Mandiri
    Route::get('/pinjam-mandiri/{id}', [PublicController::class, 'kiosk'])->name('public.kiosk');
    Route::post('/pinjam-mandiri-proses', [PublicController::class, 'processSelfLoan'])->name('public.kiosk.process');

    // Anjungan Mandiri (Scan to Borrow)
    Route::get('/anjungan-mandiri', [PublicController::class, 'standbyKiosk'])->name('public.kiosk-standby');
    Route::post('/check-book', [PublicController::class, 'checkBook'])->name('public.check-book');

    // Pengembalian Mandiri
    Route::get('/kembali-mandiri', [PublicController::class, 'returnKiosk'])->name('public.kiosk-return');
    Route::post('/cek-pinjaman-member', [PublicController::class, 'checkMemberLoans'])->name('public.check-member-loans');
    Route::post('/proses-kembali-mandiri', [PublicController::class, 'processSelfReturn'])->name('public.process-return');

});

// ==============================================================================
// GROUP 1: LOGIN REQUIRED (Bisa Diakses Admin & Pustakawan)
// ==============================================================================
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // --- MODUL BUKU ---
    // PENTING: Route statis (seperti trash, create) harus DI ATAS route dinamis ({id})
    Route::get('/books/trash', [BookController::class, 'trash'])->name('books.trash');
    Route::put('/books/{id}/restore', [BookController::class, 'restore'])->name('books.restore');

    // Standar CRUD Buku
    Route::get('/books', [BookController::class, 'index'])->name('books.index');
    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
    Route::post('/books', [BookController::class, 'store'])->name('books.store');
    Route::get('/books/{id}/edit', [BookController::class, 'edit'])->name('books.edit');
    Route::put('/books/{id}', [BookController::class, 'update'])->name('books.update');
    Route::delete('/books/{id}', [BookController::class, 'destroy'])->name('books.destroy');


    // --- MODUL ANGGOTA ---
    Route::get('/members/trash', [MemberController::class, 'trash'])->name('members.trash');
    Route::put('/members/{id}/restore', [MemberController::class, 'restore'])->name('members.restore');
    Route::get('/members/{id}/card', [MemberController::class, 'printCard'])->name('members.card');
    Route::resource('members', MemberController::class);


    // --- MANAJEMEN PEMINJAMAN (LOANS) ---
    Route::get('/loans', [App\Http\Controllers\LoanController::class, 'index'])->name('loans.index');
    Route::get('/loans/create', [App\Http\Controllers\LoanController::class, 'create'])->name('loans.create');
    Route::post('/loans', [App\Http\Controllers\LoanController::class, 'store'])->name('loans.store');

    // Refresh Status Midtrans (Massal)
    Route::get('/loans/refresh-all', [App\Http\Controllers\LoanController::class, 'refreshAllStatus'])->name('loans.refresh_all');

    // Cek Status Midtrans (Per Item)
    Route::put('/loans/{id}/check-status', [App\Http\Controllers\LoanController::class, 'checkPaymentStatus'])->name('loans.check_status');

    // Pengembalian Buku
    Route::put('/loans/{id}/return', [App\Http\Controllers\LoanController::class, 'returnLoan'])->name('loans.return');

    // Bayar Denda Susulan (Tunai)
    Route::put('/loans/{id}/pay-fine', [App\Http\Controllers\LoanController::class, 'payLateFine'])->name('loans.pay-late-fine');


    // --- MODUL MASTER DATA (Kategori & Rak) ---
    Route::resource('categories', CategoryController::class);
    Route::resource('shelves', ShelfController::class);


    // --- MODUL LAPORAN PEMINJAMAN ---
    Route::get('/reports/loans', [ReportController::class, 'loanIndex'])->name('reports.loans.index');
    Route::get('/reports/loans/print', [ReportController::class, 'loanPrint'])->name('reports.loans.print');

    // --- MODUL LAPORAN DENDA ---
    Route::get('/fines', [ReportController::class, 'finesIndex'])->name('reports.fines.index');
    Route::get('/fines/print', [ReportController::class, 'finesPrint'])->name('reports.fines.print');

    // --- MODUL STOCK OPNAME ---
    Route::resource('stock-opnames', StockOpnameController::class);
    Route::get('/stock-opnames/{id}/export-pdf', [StockOpnameController::class, 'exportPdf'])->name('stock-opnames.export-pdf');


    // ==============================================================================
    // GROUP 2: ADMIN ONLY (Hanya Admin, Pustakawan DILARANG Masuk)
    // ==============================================================================
    Route::middleware(['role:admin'])->group(function () {

        // 1. Pengaturan Sistem (Denda & Durasi)
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');

        // 2. Hapus Permanen (Force Delete) - Bahaya jika diakses sembarang orang
        Route::delete('/books/{id}/force-delete', [BookController::class, 'forceDelete'])->name('books.force_delete');
        Route::delete('/members/{id}/force-delete', [MemberController::class, 'forceDelete'])->name('members.force_delete');

    });
});

// ==============================================================================
// GROUP 3: PROFILE (Auth Only)
// ==============================================================================
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Route Khusus Webhook Midtrans (Bisa diakses publik oleh server Midtrans)
Route::post('/midtrans-callback', [PaymentCallbackController::class, 'handle']);

require __DIR__.'/auth.php';
