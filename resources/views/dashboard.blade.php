<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard Perpustakaan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Bagian 1: Kartu Statistik (Grid 4 Kolom) --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">

                {{-- Kartu 1: Total Buku --}}
                <div class="bg-blue-500 overflow-hidden shadow-sm sm:rounded-lg text-white">
                    <div class="p-6">
                        <div class="text-lg font-bold">Total Judul Buku</div>
                        <div class="text-4xl font-extrabold mt-2">{{ $totalBuku }}</div>
                        <div class="text-sm mt-2 text-blue-100">Koleksi Pustaka</div>
                    </div>
                </div>

                {{-- Kartu 2: Anggota Aktif --}}
                <div class="bg-green-500 overflow-hidden shadow-sm sm:rounded-lg text-white">
                    <div class="p-6">
                        <div class="text-lg font-bold">Anggota Aktif</div>
                        <div class="text-4xl font-extrabold mt-2">{{ $totalAnggota }}</div>
                        <div class="text-sm mt-2 text-green-100">Siswa & Guru</div>
                    </div>
                </div>

                {{-- Kartu 3: Sedang Dipinjam --}}
                <div class="bg-yellow-500 overflow-hidden shadow-sm sm:rounded-lg text-white">
                    <div class="p-6">
                        <div class="text-lg font-bold">Sedang Dipinjam</div>
                        <div class="text-4xl font-extrabold mt-2">{{ $transaksiAktif }}</div>
                        <div class="text-sm mt-2 text-yellow-100">Belum Dikembalikan</div>
                    </div>
                </div>

                {{-- Kartu 4: Kembali Hari Ini --}}
                <div class="bg-purple-500 overflow-hidden shadow-sm sm:rounded-lg text-white">
                    <div class="p-6">
                        <div class="text-lg font-bold">Kembali Hari Ini</div>
                        <div class="text-4xl font-extrabold mt-2">{{ $kembaliHariIni }}</div>
                        <div class="text-sm mt-2 text-purple-100">Transaksi Selesai</div>
                    </div>
                </div>

            </div>

            {{-- Bagian 2: Aktivitas Terakhir --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-bold mb-4">Aktivitas Peminjaman Terakhir</h3>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-3">Peminjam</th>
                                    <th class="px-4 py-3">Tanggal Pinjam</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3">Petugas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentLoans as $loan)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                                    <td class="px-4 py-3 font-medium">
                                        {{ $loan->member->nama_lengkap }}
                                        <div class="text-xs text-gray-500">{{ $loan->member->kelas }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ $loan->tgl_pinjam->format('d M Y') }}
                                        <div class="text-xs text-gray-500">{{ $loan->tgl_pinjam->diffForHumans() }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($loan->status_transaksi == 'berjalan')
                                            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">Sedang Pinjam</span>
                                        @elseif($loan->status_transaksi == 'selesai')
                                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Selesai</span>
                                        @else
                                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">Terlambat</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">{{ $loan->user->name }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-3 text-center text-gray-500">Belum ada aktivitas transaksi.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
