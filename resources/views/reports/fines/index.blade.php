<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center print:hidden">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">
                {{ __('Laporan Keuangan Denda') }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('reports.fines.print', request()->all()) }}" target="_blank" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-bold flex items-center gap-2">
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
                    {{-- Tab Tidak Aktif --}}
                    <a href="{{ route('reports.loans.index') }}" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Sirkulasi Peminjaman
                    </a>

                    {{-- Tab Aktif (Ada garis biru di bawah) --}}
                    <a href="{{ route('reports.fines.index') }}" class="border-indigo-500 text-indigo-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Keuangan & Denda
                    </a>
                </nav>
            </div>

            {{-- FILTER --}}
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow mb-6">
                <form action="{{ route('reports.fines.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 md:items-end">
                    <div class="w-full md:w-1/4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dari Tanggal</label>
                        <input type="date" name="start_date" value="{{ $startDate }}" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div class="w-full md:w-1/4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div class="w-full md:w-1/4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status Pembayaran</label>
                        <select name="payment_status" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-white">
                            <option value="all" {{ $status == 'all' ? 'selected' : '' }}>Semua</option>
                            <option value="paid" {{ $status == 'paid' ? 'selected' : '' }}>Lunas (Paid)</option>
                            <option value="unpaid" {{ $status == 'unpaid' ? 'selected' : '' }}>Belum Lunas</option>
                        </select>
                    </div>
                    <div class="w-full md:w-auto">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-bold">
                            Tampilkan
                        </button>
                    </div>
                </form>
            </div>

            {{-- STATISTIK UANG --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-green-50 p-4 rounded-lg border-l-4 border-green-500">
                    <span class="text-xs font-bold text-green-600 uppercase">Total Uang Masuk</span>
                    <p class="text-2xl font-extrabold text-green-900">Rp {{ number_format($stats['sudah_dibayar'], 0, ',', '.') }}</p>
                    <div class="mt-2 text-xs text-green-700 flex justify-between">
                        <span>Tunai: {{ number_format($stats['metode_tunai']) }}</span>
                        <span>Digital: {{ number_format($stats['metode_midtrans']) }}</span>
                    </div>
                </div>

                <div class="bg-red-50 p-4 rounded-lg border-l-4 border-red-500">
                    <span class="text-xs font-bold text-red-600 uppercase">Potensi (Belum Lunas)</span>
                    <p class="text-2xl font-extrabold text-red-900">Rp {{ number_format($stats['belum_dibayar'], 0, ',', '.') }}</p>
                </div>

                 <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-500">
                    <span class="text-xs font-bold text-blue-600 uppercase">Jml Transaksi Denda</span>
                    <p class="text-2xl font-extrabold text-blue-900">{{ $fines->count() }} Data</p>
                </div>
            </div>

            {{-- TABEL --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs uppercase bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3">Tgl Bayar/Update</th>
                            <th class="px-4 py-3">Nama Anggota</th>
                            <th class="px-4 py-3 text-right">Nominal Denda</th>
                            <th class="px-4 py-3 text-center">Tipe Bayar</th>
                            <th class="px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($fines as $fine)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3">{{ date('d/m/Y H:i', strtotime($fine->updated_at)) }}</td>
                            <td class="px-4 py-3 font-bold">
                                {{ $fine->member->nama_lengkap ?? 'Member Hilang' }} <br>
                                <span class="text-xs font-normal text-gray-400">{{ $fine->member->kode_anggota ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-gray-700">
                                Rp {{ number_format($fine->total_denda, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($fine->tipe_pembayaran == 'tunai')
                                    <span class="text-xs bg-gray-200 px-2 py-1 rounded">Tunai</span>
                                @elseif($fine->tipe_pembayaran)
                                    <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded">Midtrans/QRIS</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($fine->status_pembayaran == 'paid')
                                    <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded font-bold">LUNAS</span>
                                @else
                                    <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded">BELUM</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-gray-400">Tidak ada data denda pada periode ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
