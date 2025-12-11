<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard Perpustakaan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- BARIS 1: STATISTIK CARD --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

                {{-- Card 1: Total Buku --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-blue-500">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm font-medium uppercase">Koleksi Buku</p>
                            <p class="text-2xl font-bold text-gray-800 dark:text-gray-200">{{ $totalBuku }} <span class="text-sm text-gray-400">({{ $totalBuku }} Eks)</span></p>
                        </div>
                    </div>
                </div>

                {{-- Card 2: Sedang Dipinjam --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-yellow-500">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm font-medium uppercase">Sedang Dipinjam</p>
                            <p class="text-2xl font-bold text-gray-800 dark:text-gray-200">{{ $transaksiAktif }}</p>
                        </div>
                    </div>
                </div>

                {{-- Card 3: Telat (BARU) --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-red-500">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-red-100 text-red-600 mr-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm font-medium uppercase">Telat Kembali</p>
                            <p class="text-2xl font-bold text-red-600">{{ $telat }}</p>
                        </div>
                    </div>
                </div>

                {{-- Card 4: Pendapatan Denda (BARU) --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-green-500">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm font-medium uppercase">Kas Denda</p>
                            <p class="text-2xl font-bold text-green-600">Rp {{ number_format($pendapatanDenda, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- BARIS 2: GRAFIK & TABEL --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Kiri: Grafik Peminjaman --}}
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-bold text-lg text-gray-800 dark:text-white mb-4">Statistik Peminjaman ({{ date('Y') }})</h3>
                    <div class="h-64">
                        <canvas id="loanChart"></canvas>
                    </div>
                </div>

                {{-- Kanan: Aktivitas Terbaru --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-bold text-lg text-gray-800 dark:text-white mb-4">Transaksi Terbaru</h3>
                    <ul class="space-y-4">
                        @forelse($recentLoans as $loan)
                        <li class="flex justify-between items-center border-b border-gray-100 pb-2">
                            <div>
                                <p class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ $loan->member->nama_lengkap }}</p>
                                <p class="text-xs text-gray-500">{{ $loan->details->count() }} Buku • {{ $loan->created_at->diffForHumans() }}</p>
                            </div>
                            <span class="px-2 py-1 text-xs rounded {{ $loan->status_transaksi == 'berjalan' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                                {{ ucfirst($loan->status_transaksi) }}
                            </span>
                        </li>
                        @empty
                        <li class="text-gray-500 text-sm text-center">Belum ada data.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

        </div>
    </div>

    {{-- Script Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('loanChart').getContext('2d');
        const loanChart = new Chart(ctx, {
            type: 'line', // Bisa ganti 'bar'
            data: {
                labels: @json($labels), // Data Bulan dari Controller
                datasets: [{
                    label: 'Jumlah Peminjaman',
                    data: @json($data), // Data Angka dari Controller
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.2)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    </script>
</x-app-layout>
