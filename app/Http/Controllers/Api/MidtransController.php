<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Loan;
use App\Models\Setting;
use App\Helpers\WhatsAppHelper;
use Midtrans\Config;
use Midtrans\Notification;

class MidtransController extends Controller
{
    public function callback(Request $request)
    {
        // 1. Konfigurasi Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');

        try {
            // 2. Tangkap Notifikasi dari Midtrans
            $notif = new Notification();

            $transaction = $notif->transaction_status;
            $type = $notif->payment_type;
            $orderId = $notif->order_id;
            $fraud = $notif->fraud_status;

            // 3. Cari Data Transaksi Berdasarkan Order ID
            // Format Order ID kita: 'DENDA-{id}-{time}' atau 'DENDA-RETURN-{id}-{time}'
            $loan = Loan::where('midtrans_order_id', $orderId)->first();

            if (!$loan) {
                return response()->json(['message' => 'Order ID not found'], 404);
            }

            // Jika sudah lunas, abaikan (idempotency check)
            if ($loan->status_pembayaran == 'paid') {
                return response()->json(['message' => 'Already paid'], 200);
            }

            // 4. Cek Status Pembayaran
            if ($transaction == 'capture') {
                if ($type == 'credit_card') {
                    if ($fraud == 'challenge') {
                        $loan->update(['status_pembayaran' => 'pending']);
                    } else {
                        $this->setSuccess($loan);
                    }
                }
            } else if ($transaction == 'settlement') {
                // INI YANG PALING PENTING (Sukses Bayar)
                $this->setSuccess($loan);

            } else if ($transaction == 'pending') {
                $loan->update(['status_pembayaran' => 'pending']);

            } else if ($transaction == 'deny') {
                $loan->update(['status_pembayaran' => 'failed']);

            } else if ($transaction == 'expire') {
                $loan->update([
                    'status_pembayaran' => 'unpaid',
                    'midtrans_url' => null // Reset agar bisa generate link baru
                ]);

            } else if ($transaction == 'cancel') {
                $loan->update(['status_pembayaran' => 'failed']);
            }

            return response()->json(['message' => 'Notification processed'], 200);

        } catch (\Exception $e) {
            \Log::error('Midtrans Webhook Error: ' . $e->getMessage());
            return response()->json(['message' => 'Error'], 500);
        }
    }

    // Fungsi Private untuk Menandai Sukses & Kirim WA
    private function setSuccess($loan)
    {
        // Update Database
        $loan->update([
            'status_pembayaran' => 'paid',
            'tipe_pembayaran' => 'online'
        ]);

        // Kirim WA Lunas (Copy Logic dari LoanController)
        if (!empty($loan->member->no_telepon)) {
            $pesan = "*PEMBAYARAN DITERIMA* 💰\n\n";
            $pesan .= "Halo, {$loan->member->nama_lengkap}\n";
            $pesan .= "Terima kasih, pembayaran denda untuk Transaksi *{$loan->kode_transaksi}* telah kami terima via Online (Otomatis).\n\n";
            $pesan .= "Nominal: Rp " . number_format($loan->total_denda, 0, ',', '.') . "\n";
            $pesan .= "Status: *LUNAS* ✅\n\n";
            $pesan .= "Sistem Perpustakaan SD";

            try {
                WhatsAppHelper::send($loan->member->no_telepon, $pesan);
            } catch (\Exception $e) {
                \Log::error("Webhook WA Error: " . $e->getMessage());
            }
        }
    }
}
