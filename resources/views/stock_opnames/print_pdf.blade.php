<!DOCTYPE html>
<html>
<head>
    <title>Laporan Stock Opname - {{ $opname->kode_opname }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; }
        .meta-table { width: 100%; margin-bottom: 20px; border: none; }
        .meta-table td { padding: 5px; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #000; padding: 6px; text-align: left; }
        table.data th { background-color: #f0f0f0; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .badge { padding: 2px 5px; border-radius: 4px; font-weight: bold; }
        .danger { color: red; }
        .success { color: green; }
        .footer { margin-top: 40px; width: 100%; }
        .ttd { width: 30%; float: right; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN STOCK OPNAME PERPUSTAKAAN</h2>
        <p>SD NEGERI CONTOH (Ganti dengan Kop Sekolah Anda)</p>
        <hr>
    </div>

    <table class="meta-table">
        <tr>
            <td width="15%">Kode Opname</td>
            <td width="35%">: <strong>{{ $opname->kode_opname }}</strong></td>
            <td width="15%">Petugas</td>
            <td width="35%">: {{ $opname->user->name }}</td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td>: {{ date('d M Y', strtotime($opname->tgl_opname)) }}</td>
            <td>Catatan</td>
            <td>: {{ $opname->catatan ?? '-' }}</td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 40%">Judul Buku / Kode</th>
                <th class="text-center">Sistem</th>
                <th class="text-center">Fisik</th>
                <th class="text-center">Selisih</th>
                <th style="width: 25%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($opname->details as $index => $detail)
            @php
                $ket = json_decode($detail->keterangan, true);
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    {{ $detail->book->judul }} <br>
                    <small>{{ $detail->book->kode_buku }}</small>
                </td>
                <td class="text-center">{{ $detail->stok_sistem }}</td>
                <td class="text-center">{{ $detail->stok_fisik }}</td>
                <td class="text-center">
                    @if($detail->selisih < 0)
                        <span class="danger">{{ $detail->selisih }}</span>
                    @elseif($detail->selisih > 0)
                        <span class="success">+{{ $detail->selisih }}</span>
                    @else
                        0
                    @endif
                </td>
                <td>
                    @if($ket['rusak'] > 0) Rusak: {{ $ket['rusak'] }}, @endif
                    @if($ket['hilang'] > 0) Hilang: {{ $ket['hilang'] }}, @endif
                    Dipinjam: {{ $ket['dipinjam'] ?? 0 }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div class="ttd">
            <p>Ciamis, {{ date('d M Y') }}</p>
            <p>Mengetahui, <br>Kepala Perpustakaan</p>
            <br><br><br>
            <p>( ...................................... )</p>
        </div>
    </div>
</body>
</html>
