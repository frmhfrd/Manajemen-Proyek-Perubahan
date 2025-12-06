<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Book extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];

    // Relasi kebalikannya: Buku milik 1 Kategori
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'kategori_id');
    }

    // Relasi: Buku ada di 1 Rak
    public function shelf(): BelongsTo
    {
        return $this->belongsTo(Shelf::class, 'rak_id');
    }

    // Relasi: Buku bisa ada di banyak history peminjaman
    public function loanDetails(): HasMany
    {
        return $this->hasMany(LoanDetail::class, 'book_id');
    }
}
