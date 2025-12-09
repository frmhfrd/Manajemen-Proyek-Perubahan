<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        // Ambil semua setting dan ubah jadi format Array biar mudah dipanggil
        // Contoh: ['denda_harian' => 500, 'max_buku' => 3]
        $settings = Setting::all()->pluck('value', 'key');

        return view('settings.index', compact('settings'));
    }

    // App\Http\Controllers\SettingController.php

    public function update(Request $request)
    {
        $request->validate([
            'denda_harian' => 'required|numeric|min:0',
            'max_lama_pinjam' => 'required|numeric|min:1',
        ]);

        // PERBAIKAN: Gunakan updateOrCreate agar data terbuat jika belum ada di database
        Setting::updateOrCreate(
            ['key' => 'denda_harian'], // Pencarian berdasarkan key
            ['value' => $request->denda_harian, 'type' => 'number'] // Nilai yang disimpan
        );

        Setting::updateOrCreate(
            ['key' => 'max_lama_pinjam'],
            ['value' => $request->max_lama_pinjam, 'type' => 'number']
        );

        return back()->with('success', 'Pengaturan sistem berhasil diperbarui.');
    }
}
