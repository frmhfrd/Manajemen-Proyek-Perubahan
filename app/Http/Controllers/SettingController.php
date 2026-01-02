<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        // Ambil semua setting
        $settings = Setting::all()->pluck('value', 'key');

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        // 1. Tambahkan validasi max_buku_pinjam
        $request->validate([
            'denda_harian'    => 'required|numeric|min:0',
            'max_lama_pinjam' => 'required|numeric|min:1',
            'max_buku_pinjam' => 'required|numeric|min:1', // Validasi Baru
        ]);

        // 2. Simpan Denda
        Setting::updateOrCreate(
            ['key' => 'denda_harian'],
            ['value' => $request->denda_harian, 'type' => 'number']
        );

        // 3. Simpan Lama Pinjam
        Setting::updateOrCreate(
            ['key' => 'max_lama_pinjam'],
            ['value' => $request->max_lama_pinjam, 'type' => 'number']
        );

        // 4. Simpan Max Buku
        Setting::updateOrCreate(
            ['key' => 'max_buku_pinjam'],
            ['value' => $request->max_buku_pinjam, 'type' => 'number']
        );

        // Simpan Denda Rusak
        Setting::updateOrCreate(
            ['key' => 'denda_rusak'],
            ['value' => $request->denda_rusak, 'type' => 'number']
        );

        return back()->with('success', 'Pengaturan sistem berhasil diperbarui.');
    }
}
