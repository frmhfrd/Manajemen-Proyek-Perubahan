<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Loan;
use App\Models\Setting;
use App\Helpers\WhatsAppHelper;
use Carbon\Carbon;
use Midtrans\Config;
use Midtrans\Snap;

class SendLoanReminders extends Command
{
    protected $signature = 'loans:send-reminders';
    protected $description = 'Kirim notifikasi WA tagihan denda + Link Midtrans';

    public function handle()
    {
        $this->info('Memulai pengecekan denda & generate link pembayaran...');

        // 1. Konfigurasi Midtrans (Panggil sekali saja di awal)
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');

        $dendaPerHari = (int) (Setting::where('key', 'denda_harian')->value('value') ?? 500);
        $hariIni = Carbon::now()->format('Y-m-d');

        // 2. AMBIL DATA YANG TELAT & BELUM LUNAS
        // Logic: Status 'berjalan' TAPI tanggal lewat HARI INI
        $loansTelat = Loan::whereDate('tgl_wajib_kembali', '<', $hariIni)
                          ->where('status_transaksi', 'berjalan')
                          ->with('member')
                          ->get();

        foreach ($loansTelat as $loan) {

            // Hitung Denda Realtime
            $jatuhTempo = Carbon::parse($loan->tgl_wajib_kembali)->startOfDay();
            $sekarang   = Carbon::now()->startOfDay();
            $telatHari = abs($sekarang->diffInDays($jatuhTempo));
            $totalDenda = $telatHari * $dendaPerHari;

            // --- TAMBAHAN DEBUGGING (HAPUS NANTI) ---
            $this->info("Cek Siswa: " . $loan->member->nama_lengkap);
            $this->info("Telat: " . $telatHari . " hari");
            $this->info("Tarif Denda: " . $dendaPerHari);
            $this->info("Total Denda: " . $totalDenda);

            if ($totalDenda > 0 && $loan->member->no_telepon) {

                // --- LOGIC BARU: CEK KADALUARSA ---
                // Jika URL sudah ada, TAPI sudah dibuat lebih dari 7 hari yang lalu
                // Maka anggap saja kosong (biar digenerate ulang)
                if (!empty($loan->midtrans_url)) {
                    $tanggalBuatLink = Carbon::parse($loan->updated_at);
                    if (now()->diffInDays($tanggalBuatLink) >= 7) {
                        $loan->midtrans_url = null; // Reset biar masuk ke if di bawah
                        $this->info("Link lama expired, generate ulang untuk: " . $loan->member->nama_lengkap);
                    }
                }

                // --- LOGIC MIDTRANS GENERATOR ---
                // Cek apakah link sudah ada? Jika belum/kosong, buat baru.
                if (empty($loan->midtrans_url)) {

                    // Gunakan time() agar order_id selalu unik setiap generate ulang
                    $orderId = 'DENDA-' . $loan->id . '-' . time();

                    $params = [
                        'transaction_details' => [
                            'order_id' => $orderId,
                            'gross_amount' => $totalDenda,
                        ],
                        'customer_details' => [
                            'first_name' => $loan->member->nama_lengkap,
                            'phone' => $loan->member->no_telepon,
                        ],
                        'item_details' => [[
                            'id' => 'DENDA',
                            'price' => $totalDenda,
                            'quantity' => 1,
                            'name' => "Denda Telat {$telatHari} Hari"
                        ]],
                        // SETTING EXPIRY (PENTING)
                        'expiry' => [
                            'start_time' => date("Y-m-d H:i:s O"),
                            'unit' => 'days',
                            'duration' => 7
                        ],
                    ];

                    try {
                        // Minta Link ke Midtrans
                        $paymentUrl = Snap::createTransaction($params)->redirect_url;

                        // Simpan ke Database
                        $loan->update([
                            'midtrans_order_id' => $orderId,
                            'midtrans_url' => $paymentUrl,
                            'status_pembayaran' => 'pending',
                            'tipe_pembayaran' => 'online'
                        ]);

                        $this->info("Link Generated untuk: " . $loan->member->nama_lengkap);

                    } catch (\Exception $e) {
                        $this->error("Gagal Midtrans: " . $e->getMessage());
                        continue;
                    }
                }

                // --- KIRIM WA ---
                // Ambil link dari database (entah baru dibuat atau yg lama)
                $linkBayar = $loan->midtrans_url;

                $pesan = "*PERINGATAN KETERLAMBATAN BUKU*\n\n";
                $pesan .= "Halo Wali Murid dari {$loan->member->nama_lengkap},\n";
                $pesan .= "Buku yang dipinjam telah melewati batas waktu ({$telatHari} hari).\n\n";
                $pesan .= "💰 *Total Denda: Rp " . number_format($totalDenda, 0,',','.') . "*\n";
                $pesan .= "Mohon segera lunasi denda agar buku dapat dikembalikan.\n\n";
                $pesan .= "👉 *Klik Link untuk Bayar Online (Gopay/Transfer):*\n";
                $pesan .= $linkBayar . "\n\n";
                $pesan .= "_Atau lakukan pembayaran tunai di Perpustakaan._\n";
                $pesan .= "- Sistem Perpustakaan SD";

                WhatsAppHelper::send($loan->member->no_telepon, $pesan);
                $this->info("WA Terkirim ke: " . $loan->member->no_telepon);
            }
        }

        $this->info('Selesai pengecekan denda.');
    }
}
