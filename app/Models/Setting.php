<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $guarded = ['id'];
    public $timestamps = false; // Karena di migration setting ini pakai timestamp, tapi setting jarang berubah, opsional. (Koreksi: tadi di migration ada timestamps, jadi baris ini hapus saja jika mau pakai created_at).
}
