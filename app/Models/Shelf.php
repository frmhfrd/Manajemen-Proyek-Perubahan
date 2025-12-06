<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shelf extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    // Relasi: 1 Rak punya banyak Buku
    public function books(): HasMany
    {
        return $this->hasMany(Book::class, 'rak_id');
    }
}
