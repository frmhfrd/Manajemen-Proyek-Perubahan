<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Sirkulasi Peminjaman') }}
            </h2>

            <div class="flex gap-2">
                <a href="{{ route('reports.loans.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Laporan & Statistik
                </a>
                <a href="{{ route('reports.fines.index') }}" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Laporan Denda
                </a>
                <a href="{{ route('loans.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition">
                    + Transaksi Baru
                </a>
            </div>
        </div>
    </x-slot>

    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- ALERT NOTIFIKASI --}}
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm">
                    <p class="font-bold">Berhasil</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm">
                    <p class="font-bold">Gagal</p>
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            {{-- CONTAINER UTAMA --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Header Tools --}}
                    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                        <form action="{{ route('loans.index') }}" method="GET" class="w-full md:w-2/3 flex flex-col md:flex-row gap-2" id="searchForm">

                            {{-- DROPDOWN FILTER LENGKAP --}}
                            <select name="filter" onchange="document.getElementById('searchForm').submit()"
                                    class="rounded-md border-gray-300 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2 px-3">
                                <option value="">Semua Transaksi</option>
                                <option value="selesai" {{ request('filter') == 'selesai' ? 'selected' : '' }}>Selesai (Lunas)</option>
                                <hr disabled>
                                <option value="telat_only" {{ request('filter') == 'telat_only' ? 'selected' : '' }}>Denda: Terlambat Saja</option>
                                <option value="telat_rusak" {{ request('filter') == 'telat_rusak' ? 'selected' : '' }}>Denda: Telat + Rusak</option>
                                <option value="telat_hilang" {{ request('filter') == 'telat_hilang' ? 'selected' : '' }}>Denda: Telat + Hilang</option>
                                <hr disabled>
                                <option value="rusak_only" {{ request('filter') == 'rusak_only' ? 'selected' : '' }}>Ganti Rugi: Buku Rusak (Tepat Waktu)</option>
                                <option value="hilang_only" {{ request('filter') == 'hilang_only' ? 'selected' : '' }}>Ganti Rugi: Buku Hilang (Tepat Waktu)</option>
                            </select>

                            {{-- INPUT SEARCH --}}
                            <div class="relative flex-1 flex gap-2">
                                <input type="text" id="searchInput" name="search" value="{{ request('search') }}" placeholder="Cari Kode / Nama Siswa..."
                                    class="w-full rounded-md border-gray-300 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pl-4 h-10">

                                <button type="button" onclick="startSearchScanner()" class="bg-yellow-500 text-white w-10 h-10 rounded-md hover:bg-yellow-600 shadow-sm flex items-center justify-center flex-shrink-0" title="Scan Barcode">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                                </button>

                                <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-md hover:bg-gray-700 h-10">Cari</button>
                            </div>
                        </form>

                        <a href="{{ route('loans.refresh_all') }}" class="flex items-center gap-2 bg-indigo-100 hover:bg-indigo-200 text-indigo-700 border border-indigo-300 font-bold py-2 px-4 rounded-lg transition shadow-sm h-10 text-sm whitespace-nowrap">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Refresh Status
                        </a>
                    </div>

                    {{-- TABEL DATA --}}
                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-600">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-3">Transaksi</th>
                                    <th class="px-4 py-3">Peminjam</th>
                                    <th class="px-4 py-3 text-center">Tempo</th>
                                    <th class="px-4 py-3 text-center">Progres</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                    <th class="px-4 py-3 text-center" width="100">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                @forelse($loans as $loan)
                                    @php
                                        $totalBuku = $loan->details->count();
                                        $sudahKembali = $loan->details->where('status_item', '!=', 'dipinjam')->count();
                                        $isParsial = ($sudahKembali > 0 && $sudahKembali < $totalBuku);
                                    @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    {{-- Transaksi --}}
                                    <td class="px-4 py-3 align-middle">
                                        <div class="font-bold text-gray-900 dark:text-white">{{ $loan->kode_transaksi }}</div>
                                        <div class="mt-1">
                                            @if($loan->user_id == 3) <span class="bg-purple-100 text-purple-800 px-1.5 py-0.5 rounded text-[10px] font-medium">🤖 Kiosk</span>
                                            @else <span class="bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded text-[10px] font-medium">👤 Admin</span> @endif
                                        </div>
                                    </td>

                                    {{-- Peminjam --}}
                                    <td class="px-4 py-3 align-middle">
                                        <div class="font-bold text-gray-900 dark:text-white">{{ $loan->member->nama_lengkap }}</div>
                                        <div class="text-xs font-mono text-gray-500">{{ $loan->member->kode_anggota }}</div>
                                    </td>

                                    {{-- Tempo --}}
                                    <td class="px-4 py-3 text-center align-middle">
                                        <div class="text-sm font-bold {{ $loan->tgl_wajib_kembali->isPast() && $loan->status_transaksi == 'berjalan' ? 'text-red-600' : 'text-gray-700' }}">
                                            {{ $loan->tgl_wajib_kembali->format('d M') }}
                                        </div>
                                    </td>

                                    {{-- Progres --}}
                                    <td class="px-4 py-3 text-center align-middle">
                                        <span class="inline-flex items-center justify-center px-3 py-1 rounded-full font-bold text-xs border {{ $isParsial ? 'bg-blue-100 text-blue-700 border-blue-200' : 'bg-indigo-50 text-indigo-600 border-indigo-100' }}">
                                            {{ $sudahKembali }} / {{ $totalBuku }}
                                        </span>
                                    </td>

                                    {{-- Status --}}
                                    <td class="px-4 py-3 text-center align-middle">
                                        <div class="flex flex-col gap-1 items-center">
                                            @if($loan->status_transaksi == 'selesai')
                                                <span class="px-2 py-1 text-[10px] font-bold rounded-full bg-green-100 text-green-700 border border-green-200">✅ Selesai</span>
                                            @elseif($loan->tgl_wajib_kembali->isPast())
                                                <span class="px-2 py-1 text-[10px] font-bold rounded-full bg-red-100 text-red-700 border border-red-200 animate-pulse">🔥 Terlambat</span>
                                            @elseif($isParsial)
                                                <span class="px-2 py-1 text-[10px] font-bold rounded-full bg-blue-100 text-blue-700 border border-blue-200">🌗 Sebagian</span>
                                            @else
                                                <span class="px-2 py-1 text-[10px] font-bold rounded-full bg-yellow-100 text-yellow-700 border border-yellow-200">⏳ Dipinjam</span>
                                            @endif
                                            @if($loan->status_pembayaran == 'pending')
                                                <span class="text-[10px] font-bold text-orange-600 bg-orange-50 px-1 rounded border border-orange-100">Belum Lunas</span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Aksi --}}
                                    <td class="px-4 py-3 text-center align-middle">
                                        @if($loan->status_transaksi != 'selesai')
                                            <button type="button"
                                                onclick="initReturnModal(
                                                    '{{ route('loans.return', $loan->id) }}',
                                                    '{{ $loan->kode_transaksi }}',
                                                    '{{ $loan->member->nama_lengkap }}',
                                                    '{{ $loan->tgl_wajib_kembali->format('Y-m-d') }}',
                                                    '{{ $loan->status_pembayaran }}',
                                                    {{ json_encode($loan->details->map(fn($d) => [
                                                        'id' => $d->id,
                                                        'judul' => $d->book->judul,
                                                        'harga' => $d->book->harga ?? 0,
                                                        'status' => $d->status_item
                                                    ])) }}
                                                )"
                                                class="group relative inline-flex items-center justify-center p-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 shadow-md transition-all duration-200"
                                                title="Proses Pengembalian">
                                                <svg class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                                            </button>
                                        @elseif($loan->status_transaksi == 'selesai' && $loan->status_pembayaran != 'paid' && $loan->total_denda > 0)
                                            <button type="button"
                                                onclick="initPayModal('{{ route('loans.pay-late-fine', $loan->id) }}', '{{ $loan->member->nama_lengkap }}', '{{ $loan->total_denda }}')"
                                                class="group relative inline-flex items-center justify-center p-2 rounded-lg bg-green-600 text-white hover:bg-green-700 shadow-md transition-all duration-200 hover:scale-105 active:scale-95 animate-pulse">
                                                <svg class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                            </button>
                                        @else
                                            <div class="flex justify-center items-center h-9 w-9 mx-auto rounded-full bg-gray-100 dark:bg-gray-700 text-gray-400">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="px-6 py-10 text-center text-gray-400">Belum ada data.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-6">
                        {{ $loans->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PENGEMBALIAN (CENTERED) --}}
    <div id="returnModal" class="fixed inset-0 z-[60] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex min-h-screen items-center justify-center p-4 text-center w-full">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeReturnModal()"></div>
            <div class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-left shadow-xl transition-all border-t-8 border-blue-500">
                <form id="returnForm" method="POST" action="">
                    @csrf @method('PUT')
                    <div class="bg-white dark:bg-gray-800 px-6 py-6">
                        {{-- Header Modal --}}
                        <div class="flex items-center gap-4 mb-6 border-b pb-4">
                            <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Proses Pengembalian</h3>
                                <div class="mt-1">
                                    <p class="text-sm text-gray-500">Kode: <span id="modalKode" class="font-mono font-bold text-indigo-600"></span></p>
                                    <p class="text-sm text-gray-500">Peminjam: <span id="modalNama" class="font-bold text-gray-800 dark:text-gray-200"></span></p>
                                </div>
                            </div>
                        </div>

                        {{-- List Buku --}}
                        <div class="mb-4">
                            <p class="text-xs font-bold text-gray-500 uppercase mb-2">Daftar Buku:</p>
                            <div id="bookListContainer" class="space-y-2 max-h-60 overflow-y-auto bg-gray-50 p-3 rounded-xl border border-gray-200"></div>
                        </div>

                        {{-- Alert Section (Dinamis via JS) --}}
                        <div id="dendaArea" class="hidden bg-red-50 border border-red-200 rounded-xl p-4 mb-3">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-red-800 font-bold text-sm">TOTAL TAGIHAN (Denda + Ganti Rugi)</p>
                                    <p class="text-xs text-red-600 mt-1">
                                        Detail: <span id="rincianDendaText" class="font-medium">-</span>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-2xl font-extrabold text-red-600">Rp <span id="nominalDenda">0</span></p>
                                </div>
                            </div>
                            {{-- Checkbox Bayar --}}
                            <div class="mt-3 bg-white p-2 rounded-lg border border-red-100 flex items-start gap-2">
                                <input id="konfirmasiBayar" type="checkbox" name="denda_lunas" class="mt-1 focus:ring-red-500 h-4 w-4 text-red-600 border-gray-300 rounded">
                                <label id="labelBayar" for="konfirmasiBayar" class="text-xs font-medium text-gray-700 cursor-pointer">Siswa membayar lunas tagihan ini sekarang.</label>
                            </div>
                        </div>

                        {{-- Info Aman --}}
                        <div id="infoNormal" class="hidden bg-green-50 text-green-700 px-4 py-3 rounded-xl text-sm font-bold border border-green-200 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Kondisi Baik & Tepat Waktu.
                        </div>
                    </div>

                    {{-- Footer Modal --}}
                    <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 flex flex-row-reverse gap-3">
                        <button type="submit" class="inline-flex justify-center rounded-xl border border-transparent shadow-sm px-5 py-2.5 bg-blue-600 text-base font-bold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:text-sm">
                            Simpan Perubahan
                        </button>
                        <button type="button" onclick="closeReturnModal()" class="inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-5 py-2.5 bg-white text-base font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL BAYAR --}}
    <div id="payModal" class="fixed inset-0 z-[60] hidden">
        <div class="flex min-h-screen items-center justify-center p-4 text-center w-full">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closePayModal()"></div>
            <div class="relative w-full max-w-md transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-left shadow-xl transition-all border-t-8 border-green-500">
                <form id="payForm" method="POST" action="">
                    @csrf @method('PUT')
                    <div class="bg-white px-6 py-6">
                        <div class="text-center">
                            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">Pembayaran Denda</h3>
                            <p class="text-sm text-gray-500 mt-1">Atas nama: <span id="payNama" class="font-bold text-gray-800"></span></p>
                            <div class="mt-6 bg-green-50 border border-green-200 rounded-xl p-4">
                                <p class="text-xs text-green-600 font-bold uppercase tracking-wider">Total Tagihan</p>
                                <p class="text-4xl font-extrabold text-green-700 mt-2">Rp <span id="payNominal">0</span></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex flex-col sm:flex-row-reverse gap-3">
                        <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-3 bg-green-600 text-base font-bold text-white hover:bg-green-700 sm:text-sm">Terima Pembayaran</button>
                        <button type="button" onclick="closePayModal()" class="w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-3 bg-white text-base font-medium text-gray-700 hover:bg-gray-100 sm:text-sm">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL SUKSES --}}
    @if(session('new_loan'))
        @php $newLoan = session('new_loan'); @endphp
        <div id="successTxModal" class="fixed inset-0 z-[70] flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeSuccessTxModal()"></div>
            <div class="relative w-full max-w-md transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all p-6 border-4 border-green-100">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-green-100 mb-4 animate-bounce">
                        <svg class="h-12 w-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <h3 class="text-2xl font-extrabold text-gray-900 mb-2">Transaksi Berhasil!</h3>
                    <div class="bg-gray-50 rounded-xl p-4 mb-6 text-left">
                        <p class="text-xs text-gray-500 uppercase font-bold">Kode:</p>
                        <p class="text-lg font-mono font-bold text-indigo-600 mb-3">{{ $newLoan->kode_transaksi }}</p>
                        <p class="text-xs text-gray-500 uppercase font-bold">Peminjam:</p>
                        <p class="font-bold text-gray-800">{{ $newLoan->member->nama_lengkap }}</p>
                    </div>
                    <button type="button" onclick="closeSuccessTxModal()" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-lg px-4 py-3 bg-green-600 text-lg font-bold text-white hover:bg-green-700 transform transition hover:scale-105">Selesai</button>
                </div>
            </div>
        </div>
        <script> function closeSuccessTxModal() { document.getElementById('successTxModal').classList.add('hidden'); } </script>
    @endif

    {{-- SCANNER MODAL --}}
    <div id="searchScannerModal" class="hidden fixed inset-0 bg-black/90 z-[80] flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl w-full max-w-md overflow-hidden relative shadow-2xl border-4 border-white">
            <div class="bg-gray-100 p-4 flex justify-between items-center border-b">
                <h3 class="font-bold text-lg text-gray-800">Scan Pencarian</h3>
                <button onclick="stopSearchScanner()" class="text-gray-500 hover:text-red-500 font-bold text-2xl">&times;</button>
            </div>
            <div id="searchReader" class="w-full h-80 bg-black"></div>
        </div>
    </div>

    {{-- SCRIPT UTAMA --}}
    <script>
        const tarifDenda = {{ $dendaPerHari ?? 500 }};
        const tarifRusak = {{ $dendaRusak ?? 10000 }};
        let html5QrCodeSearch = null;

        function startSearchScanner() {
            document.getElementById('searchScannerModal').classList.remove('hidden');
            html5QrCodeSearch = new Html5Qrcode("searchReader");
            Html5Qrcode.getCameras().then(devices => {
                if(devices.length) html5QrCodeSearch.start(devices[0].id, { fps: 10, qrbox: {width: 250, height: 250} }, decodedText => { document.getElementById('searchInput').value = decodedText; stopSearchScanner(); document.getElementById('searchForm').submit(); });
            });
        }
        function stopSearchScanner() { if(html5QrCodeSearch) html5QrCodeSearch.stop().then(() => document.getElementById('searchScannerModal').classList.add('hidden')); else document.getElementById('searchScannerModal').classList.add('hidden'); }

        function initReturnModal(url, kode, nama, tglTempo, statusBayar, books) {
            document.getElementById('returnForm').action = url;
            document.getElementById('modalKode').innerText = kode;
            document.getElementById('modalNama').innerText = nama;

            const today = new Date(); today.setHours(0,0,0,0);
            const tempo = new Date(tglTempo); tempo.setHours(0,0,0,0);
            const diffDays = Math.ceil((today - tempo) / (1000 * 60 * 60 * 24));

            window.currentDiffDays = diffDays > 0 ? diffDays : 0;

            const container = document.getElementById('bookListContainer');
            container.innerHTML = '';

            if (books && books.length > 0) {
                books.forEach((book, index) => {
                    let hargaFormatted = new Intl.NumberFormat('id-ID').format(book.harga);
                    let rusakFormatted = new Intl.NumberFormat('id-ID').format(tarifRusak);
                    let isDone = (book.status !== 'dipinjam');

                    let leftControl = isDone
                        ? `<span class="text-green-600 font-bold text-lg">✓</span>`
                        : `<input type="checkbox" name="items_to_return[]" value="${book.id}"
                                  class="item-checkbox w-5 h-5 text-blue-600 rounded focus:ring-blue-500 border-gray-300"
                                  onchange="toggleBookRow(this, ${book.id})">`;

                    let statusBadge = isDone ? `<span class="text-[10px] bg-gray-200 text-gray-600 px-2 py-0.5 rounded font-bold uppercase">${book.status}</span>` : '';
                    let opacityClass = isDone ? 'opacity-50' : '';

                    const html = `
                        <div class="flex items-center p-3 mb-2 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-50 transition ${opacityClass}">
                            <div class="mr-3">${leftControl}</div>
                            <div class="flex-1">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-sm font-bold text-gray-800 line-clamp-1">${index+1}. ${book.judul}</p>
                                        <p class="text-xs text-gray-500">Harga: Rp ${hargaFormatted}</p>
                                    </div>
                                    ${statusBadge}
                                </div>
                                ${!isDone ? `
                                <div class="mt-2">
                                    <select name="kondisi[${book.id}]" id="kondisi_${book.id}" disabled
                                            class="kondisi-input block w-full text-xs border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                            onchange="hitungTotalDenda()" data-harga="${book.harga}">
                                        <option value="baik">Kondisi: Baik</option>
                                        <option value="rusak">Rusak (Denda Rp ${rusakFormatted})</option>
                                        <option value="hilang">Hilang (Ganti Rp ${hargaFormatted})</option>
                                    </select>
                                </div>` : ''}
                            </div>
                        </div>
                    `;
                    container.insertAdjacentHTML('beforeend', html);
                });
            } else { container.innerHTML = '<span class="text-xs text-red-500">Gagal memuat buku.</span>'; }

            hitungTotalDenda();
            document.getElementById('returnModal').classList.remove('hidden');
        }

        function toggleBookRow(checkbox, bookId) {
            const select = document.getElementById('kondisi_' + bookId);
            if (select) {
                select.disabled = !checkbox.checked;
                if(!checkbox.checked) select.value = 'baik';
            }
            hitungTotalDenda();
        }

        // --- REVISI LOGIC: HITUNG TOTAL GABUNGAN (DENDA WAKTU + DENDA BARANG) ---
        function hitungTotalDenda() {
            let dendaBarang = 0;
            let checkedCount = 0;
            let hasRusak = false;
            let hasHilang = false;

            const checkboxes = document.querySelectorAll('.item-checkbox:checked');
            checkedCount = checkboxes.length;

            checkboxes.forEach(cb => {
                const bookId = cb.value;
                const select = document.getElementById('kondisi_' + bookId);
                if (select) {
                    if (select.value === 'hilang') {
                        dendaBarang += parseFloat(select.getAttribute('data-harga') || 0);
                        hasHilang = true;
                    } else if (select.value === 'rusak') {
                        dendaBarang += tarifRusak;
                        hasRusak = true;
                    }
                }
            });

            // Hitung Denda Waktu (Hanya estimasi visual, backend yg menentukan final)
            let dendaWaktu = 0;
            if (window.currentDiffDays > 0) {
                dendaWaktu = window.currentDiffDays * tarifDenda;
            }

            // TOTAL TAGIHAN (GABUNGAN)
            let grandTotal = dendaBarang + dendaWaktu;

            // Update UI Nominal
            document.getElementById('nominalDenda').innerText = new Intl.NumberFormat('id-ID').format(grandTotal);

            // Update Rincian Text
            let rincian = [];
            if(dendaWaktu > 0) rincian.push(`Keterlambatan (Rp ${new Intl.NumberFormat('id-ID').format(dendaWaktu)})`);
            if(hasRusak) rincian.push(`Kerusakan Barang`);
            if(hasHilang) rincian.push(`Kehilangan Barang`);

            document.getElementById('rincianDendaText').innerText = rincian.length > 0 ? rincian.join(' + ') : 'Tidak ada denda';

            const dendaArea = document.getElementById('dendaArea');
            const infoNormal = document.getElementById('infoNormal');
            const btnSubmit = document.querySelector('#returnForm button[type="submit"]');

            if (checkedCount === 0) {
                btnSubmit.disabled = true;
                btnSubmit.classList.add('opacity-50', 'cursor-not-allowed');
                btnSubmit.innerText = "Pilih buku dulu...";
            } else {
                btnSubmit.disabled = false;
                btnSubmit.classList.remove('opacity-50', 'cursor-not-allowed');
                btnSubmit.innerText = `Kembalikan (${checkedCount} Buku)`;
            }

            // Toggle Tampilan Area Denda
            if (grandTotal > 0) {
                dendaArea.classList.remove('hidden');
                infoNormal.classList.add('hidden');
            } else {
                dendaArea.classList.add('hidden');
                infoNormal.classList.remove('hidden');
            }
        }

        function initPayModal(url, nama, nominal) {
            document.getElementById('payForm').action = url;
            document.getElementById('payNama').innerText = nama;
            document.getElementById('payNominal').innerText = new Intl.NumberFormat('id-ID').format(nominal);
            document.getElementById('payModal').classList.remove('hidden');
        }
        function closePayModal() { document.getElementById('payModal').classList.add('hidden'); }
        function closeReturnModal() { document.getElementById('returnModal').classList.add('hidden'); }
    </script>
</x-app-layout>
