<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockOpname extends Model
{
    protected $guarded = [];

    // Relasi ke User (Petugas)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Detail
    public function details()
    {
        return $this->hasMany(StockOpnameDetail::class);
    }
}
