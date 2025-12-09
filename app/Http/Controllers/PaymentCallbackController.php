<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Notification;

class PaymentCallbackController extends Controller
{
    public function handle(Request $request)
    {
        // 1. Konfigurasi Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');

        try {
            // 2. Baca Notifikasi dari Midtrans
            $notif = new Notification();

            $transaction = $notif->transaction_status;
            $type = $notif->payment_type;
            $orderId = $notif->order_id;
            $fraud = $notif->fraud_status;

            // 3. Cari Transaksi di Database kita
            // Order ID kita formatnya: DENDA-1-17000000. Kita butuh ID Loan-nya.
            // Tapi kita simpan full order_id di kolom 'midtrans_order_id', jadi cari pake itu aja.
            $loan = Loan::where('midtrans_order_id', $orderId)->first();

            if (!$loan) {
                return response()->json(['message' => 'Loan not found'], 404);
            }

            // 4. Logika Update Status
            if ($transaction == 'capture') {
                if ($type == 'credit_card') {
                    if ($fraud == 'challenge') {
                        $loan->update(['status_pembayaran' => 'pending']);
                    } else {
                        $loan->update(['status_pembayaran' => 'paid']);
                    }
                }
            } else if ($transaction == 'settlement') {
                // SETTLEMENT = UANG SUDAH MASUK (LUNAS)
                $loan->update([
                    'status_pembayaran' => 'paid',
                    'tipe_pembayaran'   => 'online'
                ]);
            } else if ($transaction == 'pending') {
                $loan->update(['status_pembayaran' => 'pending']);
            } else if ($transaction == 'deny') {
                $loan->update(['status_pembayaran' => 'unpaid']);
            } else if ($transaction == 'expire') {
                $loan->update(['status_pembayaran' => 'unpaid']);
            } else if ($transaction == 'cancel') {
                $loan->update(['status_pembayaran' => 'unpaid']);
            }

            return response()->json(['message' => 'Notification processed']);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
