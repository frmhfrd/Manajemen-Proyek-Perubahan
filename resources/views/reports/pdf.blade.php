<!DOCTYPE html>
<html>
<head>
    <title>Laporan Sirkulasi Perpustakaan</title>
    <style>
        body { font-family: sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 20px; font-weight: bold; }
        .header p { margin: 2px 0; font-size: 12px; }
        .line { border-bottom: 2px solid black; margin-bottom: 5px; }

        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        th, td { border: 1px solid black; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }

        .footer { margin-top: 30px; text-align: right; font-size: 12px; }
        .status-selesai { color: green; font-weight: bold; }
        .status-telat { color: red; font-weight: bold; }
    </style>
</head>
<body>

    {{-- KOP SURAT --}}
    <div class="header">
        <h1>PERPUSTAKAAN SD NEGERI CONTOH</h1>
        <p>Jl. Pendidikan No. 123, Tasikmalaya, Jawa Barat</p>
        <p>Telp: (0265) 123456 | Email: perpus@sdnegeri.sch.id</p>
    </div>
    <div class="line"></div>

    <h3 style="text-align: center;">LAPORAN DATA PEMINJAMAN BUKU</h3>
    <p style="font-size: 12px;">Dicetak Tanggal: {{ date('d F Y') }}</p>

    {{-- TABEL DATA --}}
    <table>
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 15%">Kode Transaksi</th>
                <th style="width: 20%">Peminjam</th>
                <th style="width: 15%">Tgl Pinjam</th>
                <th style="width: 15%">Jatuh Tempo</th>
                <th style="width: 10%">Jml Buku</th>
                <th style="width: 10%">Status</th>
                <th style="width: 10%">Petugas</th>
            </tr>
        </thead>
        <tbody>
            @foreach($loans as $index => $loan)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ $loan->kode_transaksi }}</td>
                <td>
                    {{ $loan->member->nama_lengkap }}<br>
                    <small>({{ $loan->member->kelas }})</small>
                </td>
                <td>{{ $loan->tgl_pinjam->format('d/m/Y') }}</td>
                <td>{{ $loan->tgl_wajib_kembali->format('d/m/Y') }}</td>
                <td style="text-align: center;">{{ $loan->details->count() }}</td>
                <td>
                    @if($loan->status_transaksi == 'selesai')
                        <span class="status-selesai">Kembali</span>
                    @elseif($loan->status_transaksi == 'terlambat')
                        <span class="status-telat">Terlambat</span>
                    @else
                        Dipinjam
                    @endif
                </td>
                <td>{{ $loan->user->name }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Tanda Tangan (Opsional) --}}
    <div class="footer">
        <p>Tasikmalaya, {{ date('d F Y') }}</p>
        <br><br><br>
        <p><strong>( Kepala Perpustakaan )</strong></p>
    </div>

</body>
</html>
