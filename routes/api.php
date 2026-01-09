<?php

use App\Http\Controllers\Api\MidtransController;

// Route untuk menerima notifikasi dari Midtrans
Route::post('midtrans/callback', [MidtransController::class, 'callback']);
