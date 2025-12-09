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

            {{-- Bagian 2: Grid Tabel & Grafik --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- KOLOM KIRI: Aktivitas Terakhir (Lebar 2/3) --}}
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-bold mb-4">Aktivitas Peminjaman Terakhir</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-gray-100 dark:bg-gray-700 uppercase text-xs">
                                    <tr>
                                        <th class="px-4 py-3">Peminjam</th>
                                        <th class="px-4 py-3">Tanggal</th>
                                        <th class="px-4 py-3">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentLoans as $loan)
                                    <tr class="border-b dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-4 py-3 font-medium">
                                            {{ $loan->member->nama_lengkap }}
                                        </td>
                                        <td class="px-4 py-3">
                                            {{ $loan->tgl_pinjam->format('d/m/y') }}
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($loan->status_transaksi == 'berjalan')
                                                <span class="text-yellow-600 font-bold text-xs">Pinjam</span>
                                            @elseif($loan->status_transaksi == 'selesai')
                                                <span class="text-green-600 font-bold text-xs">Selesai</span>
                                            @else
                                                <span class="text-red-600 font-bold text-xs">Telat</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="3" class="text-center py-2">Kosong</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- KOLOM KANAN: Grafik Statistik (Lebar 1/3) --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100 h-full">
                        <h3 class="text-lg font-bold mb-4">Tren Peminjaman {{ date('Y') }}</h3>
                        <div class="relative h-64 w-full">
                            <canvas id="loanChart"></canvas>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>

    {{-- Script Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const ctx = document.getElementById('loanChart');

        new Chart(ctx, {
            type: 'bar', // Bisa ganti 'line', 'pie', dll
            data: {
                // Ambil Label dari Controller (Jan, Feb, ...)
                labels: {!! json_encode($labels) !!},
                datasets: [{
                    label: 'Jumlah Transaksi',
                    // Ambil Data dari Controller (5, 10, 2...)
                    data: {!! json_encode($data) !!},
                    borderWidth: 1,
                    backgroundColor: 'rgba(59, 130, 246, 0.5)', // Warna Biru Transparan
                    borderColor: 'rgb(59, 130, 246)', // Warna Biru Garis
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1 // Agar angka di sumbu Y bulat (gak ada 1.5 buku)
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false // Sembunyikan legenda biar bersih
                    }
                }
            }
        });
    </script>
</x-app-layout>
