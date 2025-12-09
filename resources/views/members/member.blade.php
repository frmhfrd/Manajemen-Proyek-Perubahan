<!DOCTYPE html>
<html>
<head>
    <title>Kartu Anggota Perpustakaan</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            background-color: #fff;
        }
        .card-container {
            width: 100%;
            height: 100%;
            border: 1px solid #ddd;
            position: relative;
            background: linear-gradient(135deg, #e0f2fe 0%, #ffffff 100%); /* Gradasi Biru Muda */
        }
        .header {
            background-color: #2563eb; /* Biru Indigo */
            color: white;
            padding: 10px;
            text-align: center;
        }
        .header h2 {
            margin: 0;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header p {
            margin: 2px 0 0;
            font-size: 8px;
        }
        .content {
            padding: 15px;
            display: table;
            width: 100%;
        }
        .photo-area {
            display: table-cell;
            width: 60px;
            vertical-align: top;
        }
        .photo-box {
            width: 50px;
            height: 60px;
            background-color: #ccc;
            border: 1px solid #999;
            text-align: center;
            line-height: 60px;
            font-size: 8px;
            color: #555;
        }
        .info-area {
            display: table-cell;
            vertical-align: top;
            padding-left: 10px;
        }
        .info-row {
            margin-bottom: 4px;
        }
        .label {
            font-size: 9px;
            color: #555;
            font-weight: bold;
        }
        .value {
            font-size: 11px;
            color: #000;
            font-weight: bold;
            display: block;
        }
        .footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            text-align: center;
            padding: 5px 0;
            border-top: 2px solid #2563eb;
            background-color: #fff;
        }
        .barcode {
            font-family: 'Courier New', Courier, monospace;
            font-size: 10px;
            letter-spacing: 3px;
            font-weight: bold;
        }
        .note {
            font-size: 7px;
            color: #777;
            margin-top: 2px;
        }
    </style>
</head>
<body>
    <div class="card-container">

        {{-- KOP KARTU --}}
        <div class="header">
            <h2>Kartu Anggota</h2>
            <p>PERPUSTAKAAN SD NEGERI CONTOH</p>
        </div>

        {{-- ISI DATA --}}
        <div class="content">
            {{-- Kotak Foto (Placeholder) --}}
            <div class="photo-area">
                <div class="photo-box">
                    FOTO
                    <br>2x3
                </div>
            </div>

            {{-- Detail Siswa --}}
            <div class="info-area">
                <div class="info-row">
                    <span class="label">NAMA LENGKAP</span>
                    <span class="value">{{ strtoupper($member->nama_lengkap) }}</span>
                </div>
                <div class="info-row">
                    <span class="label">NOMOR INDUK</span>
                    <span class="value">{{ $member->kode_anggota }}</span>
                </div>
                <div class="info-row">
                    <span class="label">TIPE / KELAS</span>
                    <span class="value">
                        {{ ucfirst($member->tipe_anggota) }}
                        @if($member->kelas) - {{ $member->kelas }} @endif
                    </span>
                </div>
            </div>
        </div>

        {{-- FOOTER / BARCODE SIMULASI --}}
        <div class="footer">
            {{-- Simulasi Barcode dengan Garis --}}
            <div class="barcode">
                ||| || ||| || ||| || |||
            </div>
            <div class="note" style="margin-top: -2px;">{{ $member->kode_anggota }}</div>
            <div class="note">Kartu ini wajib dibawa saat peminjaman</div>
        </div>

    </div>
</body>
</html>
