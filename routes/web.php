<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ShelfController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Group route yang membutuhkan autentikasi DAN verifikasi email
Route::middleware(['auth', 'verified'])->group(function () {

    // Route Dashboard dipindahkan dan dipertahankan di sini
    Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

    // DAFTAR ROUTE BUKU (Sesuai permintaan)
    // Route untuk menampilkan daftar semua buku
    Route::get('/books', [BookController::class, 'index'])->name('books.index');
    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
    Route::post('/books', [BookController::class, 'store'])->name('books.store');
    Route::get('/books/{id}/edit', [BookController::class, 'edit'])->name('books.edit');
    Route::get('/books/trash', [BookController::class, 'trash'])->name('books.trash');
    Route::put('/books/{id}/restore', [BookController::class, 'restore'])->name('books.restore');
    Route::delete('/books/{id}/force-delete', [BookController::class, 'forceDelete'])->name('books.force_delete');
    Route::put('/books/{id}', [BookController::class, 'update'])->name('books.update');
    Route::delete('/books/{id}', [BookController::class, 'destroy'])->name('books.destroy');

    // DAFTAR ROUTE PEMINJAMAN
    Route::get('/loans', [LoanController::class, 'index'])->name('loans.index');
    Route::get('/loans/create', [LoanController::class, 'create'])->name('loans.create');
    Route::post('/loans', [LoanController::class, 'store'])->name('loans.store');
    Route::put('/loans/{id}/return', [LoanController::class, 'returnLoan'])->name('loans.return');

    // DAFTAR ROUTE MEMBER
    Route::get('/members/trash', [MemberController::class, 'trash'])->name('members.trash');
    Route::put('/members/{id}/restore', [MemberController::class, 'restore'])->name('members.restore');
    Route::delete('/members/{id}/force-delete', [MemberController::class, 'forceDelete'])->name('members.force_delete');
    Route::resource('members', MemberController::class);

    // Laporan
    Route::get('/reports/print', [ReportController::class, 'print'])->name('reports.print');
    // Catatan: Route resource lainnya (create, store, edit, update, destroy)
    // akan ditambahkan di sini nantinya jika diperlukan.
});

// Group route yang HANYA membutuhkan autentikasi (untuk profile)
// Profile tidak selalu memerlukan verifikasi email, jadi dipisahkan.
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('categories', CategoryController::class);
    Route::resource('shelves', ShelfController::class);
});

require __DIR__.'/auth.php';
