<!DOCTYPE html>
<html>
<head>
    <title>ID Card {{ $member->kode_anggota }}</title>
    <style>
        /* 1. Reset Margin Halaman (Wajib untuk DOMPDF) */
        @page {
            margin: 0px;
            size: 153.07pt 243.78pt; /* Ukuran Pas Kartu */
        }

        body {
            margin: 0px;
            padding: 0px;
            font-family: 'Helvetica', sans-serif;
        }

        /* 2. Container Halaman */
        .card-page {
            width: 153.07pt;
            height: 243.78pt;
            position: relative;
            overflow: hidden; /* Mencegah konten tumpah ke halaman baru */
        }

        /* 3. Page Break (Pemisah Depan & Belakang) */
        .page-break {
            page-break-after: always;
        }

        /* --- DESAIN DEPAN --- */
        .bg-blue {
            background-color: #2563eb;
            height: 90px; /* Header Biru */
            width: 100%;
            position: absolute;
            top: 0;
            left: 0;
            z-index: -1;
        }

        .school-name {
            color: white;
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            padding-top: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .avatar-wrapper {
            text-align: center;
            margin-top: 20px;
        }

        .avatar-img {
            width: 65px;
            height: 65px;
            border-radius: 50%;
            border: 3px solid #fbbf24;
            background-color: #fff;
            object-fit: cover;
        }

        .student-info {
            text-align: center;
            margin-top: 10px;
        }

        .student-name {
            font-size: 11px;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .student-role {
            display: inline-block;
            background-color: #f3f4f6;
            color: #555;
            font-size: 8px;
            padding: 2px 8px;
            border-radius: 10px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .detail-table {
            width: 100%;
            padding: 0 15px;
            font-size: 9px;
        }
        .detail-table td {
            padding-bottom: 3px;
        }
        .label { color: #777; width: 40px; font-weight: bold;}
        .val { color: #000; font-weight: bold; }

        .signature-section {
            margin-top: 15px; /* Jarak dari tabel */
            padding-right: 15px;
            text-align: right;
        }

        .sign-date {
            font-size: 6px;
            color: #555;
            margin-bottom: 2px;
        }

        .sign-title {
            font-size: 6px;
            font-weight: bold;
            color: #000;
            margin-bottom: 25px; /* Ruang untuk Tanda Tangan/Stempel */
        }

        .sign-name {
            font-size: 7px;
            font-weight: bold;
            text-decoration: underline;
            color: #000;
        }

        .sign-nip {
            font-size: 7px;
            color: #555;
        }

        .footer-bar {
            position: absolute;
            bottom: 0;
            width: 100%;
            height: 8px;
            background-color: #2563eb;
        }

        /* --- DESAIN BELAKANG --- */
        .bg-pattern {
            background-image: radial-gradient(#2563eb 0.5px, transparent 0.5px);
            background-size: 10px 10px;
            background-color: #fff;
        }

        .qr-wrapper {
            text-align: center;
            margin-top: 40px; /* Posisi QR Code */
        }

        .qr-border {
            display: inline-block;
            padding: 10px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .scan-text {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
            padding-top: 20px;
        }

        .nis-text {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 2px;
            margin-top: 5px;
        }

        .terms {
            position: absolute;
            bottom: 20px;
            left: 0;
            width: 100%;
            text-align: left;
            font-size: 7px;
            color: #666;
            line-height: 1.4;
            padding: 0 10px;
            box-sizing: border-box;
        }

    </style>
</head>
<body>

    {{-- HALAMAN 1: DEPAN --}}
    <div class="card-page page-break">
        <div class="bg-blue"></div>

        <div class="school-name">
            PERPUSTAKAAN<br>SD NEGERI CONTOH
        </div>

        @php
            $style = ($member->tipe_anggota == 'guru') ? 'avataaars' : 'adventurer';
            $avatarUrl = "https://api.dicebear.com/7.x/{$style}/png?seed={$member->nama_lengkap}&backgroundColor=b6e3f4";
        @endphp

        <div class="avatar-wrapper">
            <img src="{{ $avatarUrl }}" class="avatar-img">
        </div>

        <div class="student-info">
            <div class="student-name">{{ $member->nama_lengkap }}</div>
            <div class="student-role">
                {{ strtoupper($member->tipe_anggota) }}
                @if($member->kelas) | {{ $member->kelas }} @endif
            </div>
        </div>

        <table class="detail-table">
            <tr>
                <td class="label">ID/NIS</td>
                <td class="val">: {{ $member->kode_anggota }}</td>
            </tr>
            <tr>
                <td class="label">Gender</td>
                <td class="val">: {{ $member->jenis_kelamin ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Berlaku</td>
                <td class="val">: S.d Lulus</td>
            </tr>
        </table>

        {{-- TAMBAHAN: AREA TANDA TANGAN --}}
        <div class="signature-section">
            <div class="sign-date">Tasikmalaya, {{ date('d M Y') }}</div>
            <div class="sign-title">Kepala Perpustakaan</div>

            {{-- Jika punya scan ttd, bisa pakai <img src="..."> disini --}}

            <div class="sign-name">Budi Pustakawan, S.Pd</div>
            <div class="sign-nip">NIP. 19800101 200012 1 001</div>
        </div>

        <div class="footer-bar"></div>
    </div>

    {{-- HALAMAN 2: BELAKANG --}}
    <div class="card-page bg-pattern">

        <div class="scan-text">SCAN KARTU INI</div>

        <div class="qr-wrapper">
            <div class="qr-border">
                {{-- QR Code Image --}}
                <img src="data:image/svg+xml;base64,{{ $qrCode }}" width="90" height="90">
            </div>
        </div>

        <div class="nis-text">{{ $member->kode_anggota }}</div>

        <div class="terms">
            <b>KETENTUAN:</b><br>
            1. Kartu ini milik Perpustakaan SDN 6 Singaparna.<br>
            2. Harap dibawa saat peminjaman & pengembalian.<br>
            3. Jika menemukan kartu ini, kembalikan ke sekolah.<br>
            4. Jika kartu hilang, lapor ke Kepala Perpustakaan.<br>
            5. Kartu tidak dapat digunakan untuk keperluan lain.
        </div>

        <div class="footer-bar"></div>
    </div>

</body>
</html>
