<!DOCTYPE html>
<html>
<head>
    <title>Laporan Peminjaman - {{ date('d M Y') }}</title>
    <style>
        /* Menggunakan Style yang sama dengan Stock Opname agar konsisten */
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; }
        .meta-table { width: 100%; margin-bottom: 20px; border: none; }
        .meta-table td { padding: 5px; vertical-align: top; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #000; padding: 6px; text-align: left; }
        table.data th { background-color: #f0f0f0; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .badge { padding: 2px 5px; border-radius: 4px; font-weight: bold; font-size: 10px; }

        /* Warna Status Custom untuk PDF */
        .status-pinjam { color: #d97706; font-weight: bold; } /* Orange */
        .status-kembali { color: #059669; font-weight: bold; } /* Green */

        .footer { margin-top: 40px; width: 100%; }
        .ttd { width: 30%; float: right; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN STATISTIK PEMINJAMAN</h2>
        <p>SD NEGERI CONTOH (Ganti dengan Kop Sekolah Anda)</p>
        <hr>
    </div>

    {{-- Informasi Filter Laporan --}}
    <table class="meta-table">
        <tr>
            <td width="15%">Periode Laporan</td>
            <td width="40%">:
                <strong>{{ date('d M Y', strtotime($startDate)) }}</strong>
                s/d
                <strong>{{ date('d M Y', strtotime($endDate)) }}</strong>
            </td>
            <td width="15%">Total Transaksi</td>
            <td width="30%">: {{ $loans->count() }} Data</td>
        </tr>
        <tr>
            <td>Tanggal Cetak</td>
            <td>: {{ date('d M Y H:i') }}</td>
            <td>Petugas Cetak</td>
            <td>: {{ Auth::user()->name ?? 'Admin' }}</td>
        </tr>
    </table>

    {{-- Tabel Data Utama --}}
    <table class="data">
        <thead>
            <tr>
                <th style="width: 5%" class="text-center">No</th>
                <th style="width: 15%">Tgl Pinjam</th>
                <th style="width: 25%">Nama Peminjam</th>
                <th style="width: 10%" class="text-center">Jml Buku</th>
                <th style="width: 20%" class="text-center">Status</th>
                <th style="width: 25%">Tgl Kembali / Batas</th>
            </tr>
        </thead>
        <tbody>
            @foreach($loans as $index => $loan)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    {{ date('d/m/Y', strtotime($loan->tgl_pinjam)) }}
                    <br>
                    <small style="color: #666;">ID: {{ $loan->kode_transaksi ?? '-' }}</small>
                </td>
                <td>
                    {{ $loan->member->nama_lengkap ?? 'Member Terhapus' }}
                    <br>
                    <small style="color: #666;">NIS: {{ $loan->member->kode_anggota ?? '-' }}</small>
                </td>
                <td class="text-center">{{ $loan->details->count() }}</td>
                <td class="text-center">
                    @if($loan->status_transaksi == 'berjalan')
                        <span class="status-pinjam">Sedang Dipinjam</span>
                    @elseif($loan->status_transaksi == 'selesai')
                        <span class="status-kembali">Sudah Kembali</span>
                    @else
                        {{ ucfirst($loan->status_transaksi) }}
                    @endif
                </td>
                <td>
                    @if($loan->tgl_kembali)
                        {{ date('d/m/Y', strtotime($loan->tgl_kembali)) }}
                    @else
                        <small>Batas: {{ date('d/m/Y', strtotime($loan->tgl_wajib_kembali)) }}</small>
                    @endif
                </td>
            </tr>
            @endforeach

            @if($loans->isEmpty())
            <tr>
                <td colspan="6" class="text-center" style="padding: 20px;">Tidak ada data pada periode ini.</td>
            </tr>
            @endif
        </tbody>
    </table>

    {{-- Footer Tanda Tangan --}}
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
