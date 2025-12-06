<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;
    // Izinkan semua kolom diisi kecuali ID
    protected $guarded = ['id'];

    // Relasi: 1 Kategori punya banyak Buku
    public function books(): HasMany
    {
        return $this->hasMany(Book::class, 'kategori_id');
    }
}
