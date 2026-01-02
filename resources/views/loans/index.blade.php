<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Sirkulasi Peminjaman') }}
            </h2>

            <div class="flex gap-2">
                {{-- Tombol Laporan Statistik --}}
                <a href="{{ route('reports.loans.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition flex items-center gap-2">
                    {{-- Icon Chart/Grafik --}}
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Laporan & Statistik
                </a>
                {{-- Tombol Laporan Denda --}}
                <a href="{{ route('reports.fines.index') }}" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition flex items-center gap-2">
                    {{-- Icon Denda --}}
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    Laporan Denda
                </a>

                {{-- Tombol Tambah (Biru) - Sudah Ada --}}
                <a href="{{ route('loans.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition">
                    + Transaksi Baru
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Load Library Scanner --}}
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Alert Sukses --}}
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm">
                    <p class="font-bold">Berhasil</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            {{-- Alert Error --}}
            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm">
                    <p class="font-bold">Gagal</p>
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Header Tools: Search & Refresh --}}
                    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">

                        {{-- Kiri: Search Bar + Scan Button --}}
                        <form action="{{ route('loans.index') }}" method="GET" class="w-full md:w-1/2" id="searchForm">
                            <div class="flex gap-2">
                                <div class="relative flex-1">
                                    <input type="text" id="searchInput" name="search" value="{{ request('search') }}" placeholder="Cari Kode / Nama Siswa..."
                                        class="w-full rounded-md border-gray-300 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pl-10 h-10">
                                </div>

                                {{-- Tombol Scan --}}
                                <button type="button" onclick="startSearchScanner()" class="bg-yellow-500 text-white w-10 h-10 rounded-md hover:bg-yellow-600 shadow-sm transition flex items-center justify-center flex-shrink-0" title="Scan Kartu/Barcode">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>

                                </button>

                                <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-md hover:bg-gray-700 h-10">Cari</button>
                            </div>
                        </form>

                        {{-- Kanan: Tombol Refresh Status Massal --}}
                        <a href="{{ route('loans.refresh_all') }}" class="flex items-center gap-2 bg-indigo-100 hover:bg-indigo-200 text-indigo-700 border border-indigo-300 font-bold py-2 px-4 rounded-lg transition shadow-sm h-10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Refresh Status Bayar
                        </a>
                    </div>

                    {{-- Tabel Modern & Rapi --}}
                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-3">Transaksi</th>
                                    <th class="px-4 py-3">Peminjam</th>
                                    <th class="px-4 py-3 text-center">Tempo</th>
                                    <th class="px-4 py-3 text-center">Buku</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                    <th class="px-4 py-3 text-center" width="100">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @forelse($loans as $loan)
                                <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition">

                                    {{-- 1. Transaksi Info --}}
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-gray-900 dark:text-white">{{ $loan->kode_transaksi }}</div>
                                        <div class="mt-1">
                                            @if($loan->user_id == 3)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-purple-100 text-purple-800">
                                                    🤖 Kiosk
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-600">
                                                    👤 Admin
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- 2. Peminjam --}}
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $loan->member->nama_lengkap }}</div>
                                        <div class="text-xs text-gray-500">{{ $loan->member->kode_anggota }}</div>
                                        <span class="inline-block mt-1 px-2 py-0.5 text-[10px] font-semibold rounded bg-blue-50 text-blue-600 border border-blue-100">
                                            {{ $loan->member->kelas ?? '-' }}
                                        </span>
                                    </td>

                                    {{-- 3. Jatuh Tempo --}}
                                    <td class="px-4 py-3 text-center">
                                        <div class="text-sm font-bold {{ $loan->tgl_wajib_kembali->isPast() && $loan->status_transaksi == 'berjalan' ? 'text-red-600' : 'text-gray-700' }}">
                                            {{ $loan->tgl_wajib_kembali->format('d M') }}
                                        </div>
                                        <div class="text-[10px] text-gray-400">
                                            {{ $loan->tgl_wajib_kembali->format('Y') }}
                                        </div>
                                    </td>

                                    {{-- 4. Jumlah Buku --}}
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-50 text-indigo-600 font-bold text-xs border border-indigo-100">
                                            {{ $loan->details->count() }}
                                        </span>
                                    </td>

                                    {{-- 5. Status --}}
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex flex-col gap-1 items-center">
                                            @if($loan->status_transaksi == 'selesai')
                                                <span class="px-2 py-1 text-[10px] font-bold rounded-full bg-green-100 text-green-700 border border-green-200">
                                                    ✅ Kembali
                                                </span>
                                            @elseif($loan->tgl_wajib_kembali->isPast())
                                                <span class="px-2 py-1 text-[10px] font-bold rounded-full bg-red-100 text-red-700 border border-red-200 animate-pulse">
                                                    🔥 Terlambat
                                                </span>
                                            @else
                                                <span class="px-2 py-1 text-[10px] font-bold rounded-full bg-yellow-100 text-yellow-700 border border-yellow-200">
                                                    ⏳ Dipinjam
                                                </span>
                                            @endif

                                            @if($loan->status_pembayaran == 'pending')
                                                <span class="text-[10px] font-bold text-orange-600 bg-orange-50 px-1 rounded border border-orange-100">Belum Lunas</span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- 6. Aksi (Ikon Minimalis) --}}
                                    <td class="px-4 py-3 text-center align-middle">

                                        {{-- KONDISI 1: PROSES PENGEMBALIAN (Tombol Biru) --}}
                                        @if($loan->status_transaksi != 'selesai')
                                            <button type="button"
                                                onclick="openReturnModal(
                                                    '{{ route('loans.return', $loan->id) }}',
                                                    '{{ $loan->kode_transaksi }}',
                                                    '{{ $loan->member->nama_lengkap }}',
                                                    '{{ $loan->tgl_wajib_kembali->format('Y-m-d') }}',
                                                    '{{ $loan->status_pembayaran }}',
                                                    {{-- PERHATIKAN BARIS DI BAWAH INI --}}
                                                    {{ json_encode($loan->details->map(fn($d) => [
                                                        'id' => $d->id,
                                                        'judul' => $d->book->judul,
                                                        'harga' => $d->book->harga ?? 0 // <--- Kirim harga buku (default 0 jika null)
                                                    ])) }}
                                                )"
                                                class="group relative inline-flex items-center justify-center p-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 shadow-md transition-all duration-200"
                                                title="Proses Pengembalian">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                                            </button>

                                        {{-- KONDISI 2: BAYAR DENDA (Tombol Hijau - MEMBUKA MODAL) --}}
                                        @elseif($loan->status_transaksi == 'selesai' && $loan->status_pembayaran != 'paid' && $loan->total_denda > 0)
                                            <button type="button"
                                                onclick="openPayModal('{{ route('loans.pay-late-fine', $loan->id) }}', '{{ $loan->member->nama_lengkap }}', '{{ $loan->total_denda }}')"
                                                class="group relative inline-flex items-center justify-center p-2 rounded-lg bg-green-600 text-white hover:bg-green-700 shadow-md transition-all duration-200 hover:scale-105 active:scale-95 animate-pulse"
                                                title="Bayar Denda">
                                                {{-- Icon Uang --}}
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>

                                                {{-- Badge Merah Kecil --}}
                                                <span class="absolute top-0 right-0 -mt-1 -mr-1 flex h-3 w-3">
                                                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                                  <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                                                </span>
                                            </button>

                                        {{-- KONDISI 3: SELESAI (Ikon Centang) --}}
                                        @else
                                            <div class="flex justify-center items-center h-9 w-9 mx-auto rounded-full bg-gray-100 dark:bg-gray-700 text-gray-400" title="Selesai">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            </div>
                                        @endif

                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-gray-400 bg-gray-50">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <span>Belum ada data transaksi.</span>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $loans->withQueryString()->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PENGEMBALIAN --}}
    <div id="returnModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeReturnModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="returnForm" method="POST" action="">
                    @csrf
                    @method('PUT')

                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Proses Pengembalian</h3>
                                    <div class="mt-2 text-sm text-gray-500 dark:text-gray-300 mb-4">
                                        <p>Kode: <span id="modalKode" class="font-bold"></span></p>
                                        <p>Peminjam: <span id="modalNama" class="font-bold"></span></p>
                                    </div>

                                    {{-- AREA INPUT KONDISI BUKU --}}
                                    <div class="mb-4">
                                        <p class="text-xs font-bold text-gray-700 uppercase mb-2">Cek Kondisi Buku:</p>
                                        <div id="bookListContainer" class="space-y-2 max-h-40 overflow-y-auto bg-gray-50 p-2 rounded border">
                                            {{-- Javascript akan menyuntikkan daftar buku di sini --}}
                                        </div>
                                    </div>

                                {{-- Area Denda --}}
                                <div id="dendaArea" class="hidden mt-4 bg-red-50 border border-red-200 rounded p-3">
                                    <p class="text-red-700 font-bold text-md">⚠️ TERLAMBAT <span id="telatHari">0</span> HARI</p>
                                    <p class="text-gray-600 text-sm mt-1">Total Denda:</p>
                                    <p class="text-2xl font-extrabold text-red-600 mt-1">Rp <span id="nominalDenda">0</span></p>

                                    <div class="mt-3 flex items-start">
                                        <div class="flex items-center h-5">
                                            <input id="konfirmasiBayar" type="checkbox" name="denda_lunas" required class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label id="labelBayar" for="konfirmasiBayar" class="font-medium text-gray-700">
                                                Saya menyatakan siswa SUDAH membayar lunas denda di atas.
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div id="infoNormal" class="mt-4 text-sm text-green-600 font-bold hidden">
                                    ✅ Tepat Waktu. Tidak ada denda.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm">
                            Proses Pengembalian
                        </button>
                        <button type="button" onclick="closeReturnModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL BAYAR DENDA --}}
    <div id="payModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closePayModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                <form id="payForm" method="POST" action="">
                    @csrf
                    @method('PUT')

                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            {{-- Icon Dollar Hijau --}}
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>

                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Pembayaran Denda</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-300">
                                        Menerima pembayaran tunai dari:
                                    </p>
                                    <p class="text-lg font-bold text-gray-900 dark:text-white mt-1" id="payNama">-</p>

                                    {{-- Box Nominal --}}
                                    <div class="mt-4 bg-green-50 border border-green-200 rounded-lg p-4 text-center">
                                        <p class="text-xs text-green-600 uppercase font-bold tracking-wider">Total Tagihan</p>
                                        <p class="text-3xl font-extrabold text-green-700 mt-1">
                                            Rp <span id="payNominal">0</span>
                                        </p>
                                    </div>

                                    <p class="text-xs text-gray-400 mt-3 text-center">
                                        Pastikan uang tunai sudah diterima sebelum konfirmasi.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Footer Tombol --}}
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 sm:ml-3 sm:w-auto sm:text-sm">
                            ✅ Terima Pembayaran
                        </button>
                        <button type="button" onclick="closePayModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL SCANNER SEARCH --}}
    <div id="searchScannerModal" class="hidden fixed inset-0 bg-black/90 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-md overflow-hidden relative shadow-2xl">
            <div class="bg-gray-100 p-4 flex justify-between items-center border-b">
                <h3 class="font-bold text-lg text-gray-800">Scan Barcode Pencarian</h3>
                <button onclick="stopSearchScanner()" class="text-gray-500 hover:text-red-500 font-bold text-2xl">&times;</button>
            </div>
            <div id="searchReader" class="w-full h-64 bg-black"></div>
            <div class="p-6 text-center bg-white">
                <p class="text-sm text-gray-500 mb-4">Arahkan kode atau kartu anggota ke kamera.</p>
                <button onclick="stopSearchScanner()" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg font-bold hover:bg-gray-300 w-full">Batal</button>
            </div>
        </div>
    </div>

    {{-- MODAL SUKSES TRANSAKSI --}}
    @if(session('new_loan'))
        @php $newLoan = session('new_loan'); @endphp

        <div id="successTxModal" class="fixed inset-0 z-[70] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                {{-- Backdrop --}}
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeSuccessTxModal()"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                {{-- Konten Modal --}}
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border-4 border-green-500">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start justify-center text-center">
                            <div class="mt-3 w-full">

                                {{-- Icon Ceklis Animasi --}}
                                <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-100 mb-4 animate-bounce">
                                    <svg class="h-10 w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>

                                <h3 class="text-2xl leading-6 font-extrabold text-gray-900 dark:text-white" id="modal-title">
                                    Transaksi Berhasil!
                                </h3>

                                <div class="mt-4 bg-gray-50 dark:bg-gray-700 p-4 rounded-xl border border-gray-200 dark:border-gray-600">
                                    <p class="text-sm text-gray-500 dark:text-gray-300">Kode Transaksi:</p>
                                    <p class="text-xl font-mono font-bold text-indigo-600 dark:text-indigo-400 mb-2">
                                        {{ $newLoan->kode_transaksi }}
                                    </p>

                                    <p class="text-sm text-gray-500 dark:text-gray-300">Peminjam:</p>
                                    <p class="font-bold text-gray-800 dark:text-white">
                                        {{ $newLoan->member->nama_lengkap }}
                                    </p>

                                    <p class="text-xs text-gray-400 mt-2">
                                        Jatuh Tempo: {{ \Carbon\Carbon::parse($newLoan->tgl_wajib_kembali)->format('d M Y') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Footer Tombol --}}
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                        <button type="button" onclick="closeSuccessTxModal()" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-md px-4 py-3 bg-green-600 text-base font-bold text-white hover:bg-green-700 sm:w-auto sm:text-sm transition transform hover:scale-105">
                            Selesai (Tutup)
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Fungsi Tutup Modal
            function closeSuccessTxModal() {
                document.getElementById('successTxModal').classList.add('hidden');
            }
        </script>
    @endif

    <script>
        const tarifDenda = {{ $dendaPerHari ?? 500 }};

        // --- SCANNER LOGIC ---
        let html5QrCodeSearch = null;

        function startSearchScanner() {
            document.getElementById('searchScannerModal').classList.remove('hidden');
            html5QrCodeSearch = new Html5Qrcode("searchReader");
            Html5Qrcode.getCameras().then(devices => {
                if(devices && devices.length) {
                    html5QrCodeSearch.start(devices[0].id, { fps: 10, qrbox: {width: 250, height: 250} },
                        (decodedText) => {
                            // Hasil Scan Masuk ke Input Search
                            document.getElementById('searchInput').value = decodedText;
                            // Stop Kamera
                            stopSearchScanner();
                            // Submit Form Otomatis
                            document.getElementById('searchForm').submit();
                        },
                        () => {}
                    );
                }
            });
        }

        function stopSearchScanner() {
            if(html5QrCodeSearch) {
                html5QrCodeSearch.stop().then(() => {
                    document.getElementById('searchScannerModal').classList.add('hidden');
                }).catch(() => {
                    document.getElementById('searchScannerModal').classList.add('hidden');
                });
            } else {
                document.getElementById('searchScannerModal').classList.add('hidden');
            }
        }

        // --- RETURN MODAL LOGIC ---
        function openReturnModal(url, kode, nama, tglTempo, statusBayar, books) {
            document.getElementById('returnForm').action = url;
            document.getElementById('modalKode').innerText = kode;
            document.getElementById('modalNama').innerText = nama;

            // 1. HITUNG DENDA TELAT (WAKTU)
            // const tarifDenda diambil dari variabel global PHP di atas script ini
            const today = new Date(); today.setHours(0,0,0,0);
            const tempo = new Date(tglTempo); tempo.setHours(0,0,0,0);
            const diffTime = today - tempo;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            let dendaTelat = 0;
            if (diffDays > 0) {
                dendaTelat = diffDays * tarifDenda;
                document.getElementById('telatHari').innerText = diffDays;
            } else {
                document.getElementById('telatHari').innerText = 0;
            }

            // 2. RENDER DAFTAR BUKU + LOGIKA DENDA GANTI RUGI
            const container = document.getElementById('bookListContainer');
            container.innerHTML = '';

            if (books && books.length > 0) {
                books.forEach((book, index) => {
                    // Format harga ke Rupiah biar Admin tau harga bukunya
                    let hargaFormatted = new Intl.NumberFormat('id-ID').format(book.harga);

                    const html = `
                        <div class="flex items-center justify-between text-sm border-b pb-1 last:border-0 mb-2">
                            <div class="flex flex-col w-1/2">
                                <span class="font-medium text-gray-700 truncate">${index+1}. ${book.judul}</span>
                                <span class="text-[10px] text-gray-400">Harga: Rp ${hargaFormatted}</span>
                            </div>

                            <select name="kondisi[${book.id}]"
                                    class="kondisi-input text-xs border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"
                                    onchange="hitungTotalDenda(${dendaTelat})"
                                    data-harga="${book.harga}">
                                <option value="baik">✅ Baik</option>
                                <option value="rusak">⚠️ Rusak</option>
                                <option value="hilang">❌ Hilang (Denda)</option>
                            </select>
                        </div>
                    `;
                    container.insertAdjacentHTML('beforeend', html);
                });
            } else {
                container.innerHTML = '<span class="text-xs text-red-500">Gagal memuat daftar buku.</span>';
            }

            // Panggil hitungan pertama kali
            hitungTotalDenda(dendaTelat, statusBayar);

            document.getElementById('returnModal').classList.remove('hidden');
        }

        // --- FUNGSI BARU: HITUNG TOTAL GABUNGAN (TELAT + HILANG) ---
        function hitungTotalDenda(dendaTelat, statusBayar = null) {
            let dendaGantiRugi = 0;

            // Loop semua dropdown kondisi
            document.querySelectorAll('.kondisi-input').forEach(select => {
                if (select.value === 'hilang') {
                    // Ambil harga dari atribut data-harga
                    dendaGantiRugi += parseFloat(select.getAttribute('data-harga') || 0);
                }
            });

            // Total Akhir
            let grandTotal = dendaTelat + dendaGantiRugi;

            // Tampilkan ke UI
            const dendaArea = document.getElementById('dendaArea');
            const infoNormal = document.getElementById('infoNormal');
            const labelNominal = document.getElementById('nominalDenda');
            const checkbox = document.getElementById('konfirmasiBayar');
            const labelBayar = document.getElementById('labelBayar');

            labelNominal.innerText = new Intl.NumberFormat('id-ID').format(grandTotal);

            // Logika Tampil/Sembunyi Kotak Merah
            if (grandTotal > 0) {
                dendaArea.classList.remove('hidden');
                infoNormal.classList.add('hidden');

                // Reset Checkbox state
                if (statusBayar === 'paid' && grandTotal === dendaTelat) {
                    // Kalau sudah lunas (dan tidak ada tambahan denda hilang), kunci checkbox
                    checkbox.checked = true;
                    checkbox.disabled = true;
                    labelBayar.innerHTML = "<span class='text-green-600 font-bold'>SUDAH LUNAS ✅</span>";
                } else {
                    // Kalau ada tambahan denda baru (Ganti Rugi), buka checkbox biar bisa bayar lagi
                    checkbox.disabled = false;
                    checkbox.checked = false;
                    labelBayar.innerText = "Bayar tunai sekarang (Centang jika lunas)";
                }
            } else {
                dendaArea.classList.add('hidden');
                infoNormal.classList.remove('hidden');
            }
        }

        // --- FUNGSI BARU: PAY MODAL ---
        function openPayModal(url, nama, nominal) {
            // 1. Set URL Form
            document.getElementById('payForm').action = url;

            // 2. Isi Nama Peminjam
            document.getElementById('payNama').innerText = nama;

            // 3. Format Nominal Rupiah
            let formatted = new Intl.NumberFormat('id-ID').format(nominal);
            document.getElementById('payNominal').innerText = formatted;

            // 4. Tampilkan Modal
            document.getElementById('payModal').classList.remove('hidden');
        }

        function closePayModal() {
            document.getElementById('payModal').classList.add('hidden');
        }

        function closeReturnModal() {
            document.getElementById('returnModal').classList.add('hidden');
        }
    </script>
</x-app-layout>
