<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    // Agar kolom tanggal otomatis jadi objek Carbon (mudah diformat tgl)
    protected $casts = [
        'tgl_pinjam' => 'date',
        'tgl_wajib_kembali' => 'date',
        'tgl_kembali' => 'date',
    ];

    // Relasi: Transaksi ini milik siapa?
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    // Relasi: Transaksi ini dilayani oleh petugas siapa?
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi: Transaksi ini isinya buku apa saja? (Header ke Detail)
    public function details(): HasMany
    {
        return $this->hasMany(LoanDetail::class, 'loan_id');
    }
}
