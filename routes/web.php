<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ShelfController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\PaymentCallbackController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
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


    // --- MODUL PEMINJAMAN ---
    Route::get('/loans', [LoanController::class, 'index'])->name('loans.index');
    Route::get('/loans/create', [LoanController::class, 'create'])->name('loans.create');
    Route::post('/loans', [LoanController::class, 'store'])->name('loans.store');
    Route::get('/loans/refresh-all', [LoanController::class, 'refreshAllStatus'])->name('loans.refresh_all');
    Route::get('/loans', [LoanController::class, 'index'])->name('loans.index');
    Route::put('/loans/{id}/return', [LoanController::class, 'returnLoan'])->name('loans.return');
    Route::put('/loans/{id}/check-status', [LoanController::class, 'checkPaymentStatus'])->name('loans.check_status');


    // --- MODUL MASTER DATA (Kategori & Rak) ---
    Route::resource('categories', CategoryController::class);
    Route::resource('shelves', ShelfController::class);


    // --- MODUL LAPORAN ---
    Route::get('/reports/print', [ReportController::class, 'print'])->name('reports.print');

    // MODUL STOCK OPNAME
    Route::resource('stock-opnames', \App\Http\Controllers\StockOpnameController::class);


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
