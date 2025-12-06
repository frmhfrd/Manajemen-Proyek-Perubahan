<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Group route yang membutuhkan autentikasi DAN verifikasi email
Route::middleware(['auth', 'verified'])->group(function () {

    // Route Dashboard dipindahkan dan dipertahankan di sini
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // DAFTAR ROUTE BUKU (Sesuai permintaan)
    // Route untuk menampilkan daftar semua buku
    Route::get('/books', [BookController::class, 'index'])->name('books.index');
    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
    Route::post('/books', [BookController::class, 'store'])->name('books.store');
    Route::get('/books/{id}/edit', [BookController::class, 'edit'])->name('books.edit');
    Route::put('/books/{id}', [BookController::class, 'update'])->name('books.update');
    Route::delete('/books/{id}', [BookController::class, 'destroy'])->name('books.destroy');

    // Catatan: Route resource lainnya (create, store, edit, update, destroy)
    // akan ditambahkan di sini nantinya jika diperlukan.
});

// Group route yang HANYA membutuhkan autentikasi (untuk profile)
// Profile tidak selalu memerlukan verifikasi email, jadi dipisahkan.
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
