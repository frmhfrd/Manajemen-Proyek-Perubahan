<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    // Relasi: 1 Anggota bisa punya banyak Transaksi Peminjaman
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class, 'member_id');
    }
}
