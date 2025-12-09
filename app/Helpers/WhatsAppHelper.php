<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class WhatsAppHelper
{
    public static function send($target, $message)
    {
        // PANGGIL DARI CONFIG (Bukan env langsung, biar aman saat cache)
        $token = config('services.fonnte.token');

        // Cek jika token kosong (biar ketahuan kalau lupa isi .env)
        if (empty($token)) {
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->post('https://api.fonnte.com/send', [
                'target' => $target,
                'message' => $message,
            ]);

            return $response->json();
        } catch (\Exception $e) {
            return false;
        }
    }
}
