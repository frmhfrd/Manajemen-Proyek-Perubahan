<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center print:hidden">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Laporan Statistik Peminjaman') }}
            </h2>
            <div class="flex gap-2">
                {{-- Tombol Cetak PDF --}}
                <a href="{{ route('reports.loans.print', request()->all()) }}" target="_blank" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-bold flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Cetak PDF
                </a>

                <a href="{{ route('loans.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition">
                    &larr; Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- NAVIGASI TAB ANTAR LAPORAN --}}
            <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    {{-- Tab Aktif (Ada garis biru di bawah) --}}
                    <a href="{{ route('reports.loans.index') }}" class="border-indigo-500 text-indigo-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Sirkulasi Peminjaman
                    </a>

                    {{-- Tab Tidak Aktif --}}
                    <a href="{{ route('reports.fines.index') }}" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Keuangan & Denda
                    </a>
                </nav>
            </div>

            {{-- CARD FILTER --}}
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow mb-6">
                {{-- Tambahkan md:items-end agar di HP dia default (stretch), di Laptop baru rata bawah --}}
                <form action="{{ route('reports.loans.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 md:items-end">

                    {{-- Tanggal Awal --}}
                    <div class="w-full md:w-1/4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dari Tanggal</label>
                        <input type="date" name="start_date" value="{{ $startDate }}" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    {{-- Tanggal Akhir --}}
                    <div class="w-full md:w-1/4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    {{-- Status --}}
                    <div class="w-full md:w-1/4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                        <select name="status" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                            <option value="all" {{ $status == 'all' ? 'selected' : '' }}>Semua Status</option>
                            <option value="borrowed" {{ $status == 'borrowed' ? 'selected' : '' }}>Sedang Dipinjam</option>
                            <option value="returned" {{ $status == 'returned' ? 'selected' : '' }}>Sudah Kembali</option>
                        </select>
                    </div>

                    {{-- Tombol Action --}}
                    {{-- Tambahkan w-full md:w-auto agar di HP tombolnya lebar penuh --}}
                    <div class="w-full md:w-auto">
                        <button type="submit" class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-bold shadow transition flex justify-center items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            Tampilkan
                        </button>
                    </div>
                </form>
            </div>

            {{-- DASHBOARD MINI --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-indigo-50 p-4 rounded-lg border-l-4 border-indigo-500">
                    <span class="text-xs font-bold text-indigo-600 uppercase">Total Transaksi</span>
                    <p class="text-2xl font-extrabold text-indigo-900">{{ $stats['total_transaksi'] }}</p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg border-l-4 border-green-500">
                    <span class="text-xs font-bold text-green-600 uppercase">Sudah Kembali</span>
                    <p class="text-2xl font-extrabold text-green-900">{{ $stats['kembali'] }}</p>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg border-l-4 border-yellow-500">
                    <span class="text-xs font-bold text-yellow-600 uppercase">Sedang Dipinjam</span>
                    <p class="text-2xl font-extrabold text-yellow-900">{{ $stats['dipinjam'] }}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg border-l-4 border-gray-500">
                    <span class="text-xs font-bold text-gray-600 uppercase">Buku Keluar</span>
                    <p class="text-2xl font-extrabold text-gray-900">{{ $stats['total_buku'] }}</p>
                </div>
            </div>

            {{-- TABEL PREVIEW --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="font-bold text-lg mb-4 text-gray-700 dark:text-gray-300">Preview Data</h3>
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs uppercase bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3">Tgl Pinjam</th>
                            <th class="px-4 py-3">Peminjam</th>
                            <th class="px-4 py-3">Jml Buku</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Tgl Kembali</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($loans as $loan)
                        <tr class="border-b dark:border-gray-700 hover:bg-gray-50">
                            {{-- 1. TGL PINJAM (Ganti loan_date jadi tgl_pinjam) --}}
                            <td class="px-4 py-3">
                                {{ date('d/m/Y', strtotime($loan->tgl_pinjam)) }}
                            </td>

                            {{-- 2. PEMINJAM --}}
                            <td class="px-4 py-3 font-bold">
                                {{ $loan->member->nama_lengkap ?? 'Member Terhapus' }}
                                <br>
                                <span class="text-xs font-normal text-gray-500">{{ $loan->member->kode_anggota ?? '-' }}</span>
                            </td>

                            {{-- 3. JML BUKU --}}
                            <td class="px-4 py-3">{{ $loan->details->count() }} Buku</td>

                            {{-- 4. STATUS (Ganti status jadi status_transaksi & value-nya) --}}
                            <td class="px-4 py-3">
                                @if($loan->status_transaksi == 'berjalan')
                                    <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded">Pinjam</span>
                                @elseif($loan->status_transaksi == 'selesai')
                                    <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">Kembali</span>
                                @else
                                    {{-- Jaga-jaga ada status lain (misal: denda/overdue) --}}
                                    <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded">{{ $loan->status_transaksi }}</span>
                                @endif
                            </td>

                            {{-- 5. TGL KEMBALI (Ganti return_date jadi tgl_kembali) --}}
                            <td class="px-4 py-3">
                                {{ $loan->tgl_kembali ? date('d/m/Y', strtotime($loan->tgl_kembali)) : '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-gray-400">Tidak ada data pada periode ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>
