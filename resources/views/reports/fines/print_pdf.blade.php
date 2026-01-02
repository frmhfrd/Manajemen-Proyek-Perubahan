<!DOCTYPE html>
<html>
<head>
    <title>Laporan Keuangan Denda</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 6px; }
        th { background-color: #f0f0f0; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row td { font-weight: bold; background-color: #eee; }
        .footer { margin-top: 40px; }
        .ttd { width: 30%; float: right; text-align: center; }

        /* Styling Status */
        .status-lunas { color: #059669; font-weight: bold; }
        .status-belum { color: #dc2626; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN KEUANGAN & DENDA</h2>
        <p>Periode: {{ date('d M Y', strtotime($startDate)) }} s/d {{ date('d M Y', strtotime($endDate)) }}</p>
        <hr>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="20%">Tanggal</th>
                <th width="35%">Nama Anggota (NIS)</th>
                <th width="15%">Metode</th>
                <th width="10%">Status</th>
                <th width="15%">Nominal</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @foreach($fines as $index => $fine)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    {{ date('d/m/Y', strtotime($fine->updated_at)) }}
                    <br>
                    <small style="color: #666;">ID: {{ $fine->kode_transaksi ?? '-' }}</small>
                </td>
                <td>
                    {{ $fine->member->nama_lengkap ?? 'Member Hilang' }} <br>
                    <small style="color: #666;">NIS: {{ $fine->member->kode_anggota ?? '-' }}</small>
                </td>
                <td class="text-center">{{ ucfirst($fine->tipe_pembayaran ?? '-') }}</td>
                <td class="text-center">
                    @if($fine->status_pembayaran == 'paid')
                        <span class="status-lunas">Lunas</span>
                    @else
                        <span class="status-belum">Belum</span>
                    @endif
                </td>
                <td class="text-right">
                    Rp {{ number_format($fine->total_denda, 0, ',', '.') }}
                </td>
            </tr>
            @php
                // Hanya menjumlahkan yang statusnya PAID/LUNAS ke Grand Total Pendapatan
                if($fine->status_pembayaran == 'paid') {
                    $grandTotal += $fine->total_denda;
                }
            @endphp
            @endforeach

            {{-- Baris Total --}}
            <tr class="total-row">
                <td colspan="5" class="text-right">TOTAL PENDAPATAN (LUNAS)</td>
                <td class="text-right">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <div class="ttd">
            <p>Ciamis, {{ date('d M Y') }}</p>
            <p>Bendahara / Kepala Perpustakaan</p>
            <br><br><br>
            <p>( ...................................... )</p>
        </div>
    </div>
</body>
</html>
