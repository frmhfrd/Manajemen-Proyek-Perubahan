<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center print:hidden">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">
                Detail Laporan Opname: <span class="font-mono text-indigo-600">{{ $opname->kode_opname }}</span>
            </h2>
            <div class="flex gap-2">
                {{-- Tombol Cetak PDF --}}
                <a href="{{ route('stock-opnames.export-pdf', $opname->id) }}" target="_blank" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition flex items-center gap-2 text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Export PDF
                </a>

                <a href="{{ route('stock-opnames.index') }}" class="flex items-center gap-2 bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition text-sm">
                    <span>&larr;</span> Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- 1. HEADER INFO & STATISTIK (DESAIN LAMA DIPERTAHANKAN) --}}
            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 text-sm">
                    <div>
                        <span class="block text-gray-500 mb-1">Tanggal Cek</span>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span class="font-bold text-lg text-gray-800 dark:text-white">{{ date('d M Y', strtotime($opname->tgl_opname)) }}</span>
                        </div>
                    </div>
                    <div>
                        <span class="block text-gray-500 mb-1">Petugas</span>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            <span class="font-bold text-lg text-gray-800 dark:text-white">{{ $opname->user->name }}</span>
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <span class="block text-gray-500 mb-1">Catatan Laporan</span>
                        <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 italic">
                            "{{ $opname->catatan ?? 'Tidak ada catatan khusus.' }}"
                        </div>
                    </div>
                </div>
            </div>

            {{-- Statistik Ringkas --}}
            @php
                $totalSistem = $opname->details->sum('stok_sistem');

                // Hitung total fisik real (Bagus + Rusak + Dipinjam)
                $totalFisikReal = 0;
                foreach($opname->details as $d){
                    $j = json_decode($d->keterangan, true);
                    $totalFisikReal += ($j['bagus']??0) + ($j['rusak']??0) + ($j['dipinjam']??0);
                }

                $netSelisih  = $totalFisikReal - $totalSistem;
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                {{-- Card 1: Total Aset --}}
                <div class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow-sm border-l-4 border-indigo-500 flex justify-between items-center">
                    <div>
                        <p class="text-indigo-500 font-bold text-xs uppercase tracking-wide">Total Aset (Sistem)</p>
                        <p class="text-3xl font-extrabold text-gray-900 dark:text-white mt-1">{{ $totalSistem }}</p>
                    </div>
                    <div class="p-3 bg-indigo-50 rounded-full">
                        <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    </div>
                </div>

                {{-- Card 2: Ditemukan --}}
                <div class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow-sm border-l-4 border-blue-500 flex justify-between items-center">
                    <div>
                        <p class="text-blue-500 font-bold text-xs uppercase tracking-wide">Total Ditemukan</p>
                        <p class="text-3xl font-extrabold text-gray-900 dark:text-white mt-1">{{ $totalFisikReal }}</p>
                        <p class="text-xs text-gray-400 mt-1">Fisik + Rusak + Pinjam</p>
                    </div>
                    <div class="p-3 bg-blue-50 rounded-full">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    </div>
                </div>

                {{-- Card 3: Selisih --}}
                <div class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow-sm border-l-4 {{ $netSelisih < 0 ? 'border-red-500' : 'border-green-500' }} flex justify-between items-center">
                    <div>
                        <p class="{{ $netSelisih < 0 ? 'text-red-500' : 'text-green-500' }} font-bold text-xs uppercase tracking-wide">
                            Selisih Akhir (Net)
                        </p>
                        <p class="text-3xl font-extrabold {{ $netSelisih < 0 ? 'text-red-600' : 'text-green-600' }} mt-1">
                            {{ $netSelisih > 0 ? '+' : '' }}{{ $netSelisih }}
                        </p>
                        <p class="text-xs {{ $netSelisih < 0 ? 'text-red-500' : 'text-green-500' }} font-bold mt-1">
                            {{ $netSelisih < 0 ? 'Ada buku hilang' : 'Semua Aman' }}
                        </p>
                    </div>
                    <div class="p-3 {{ $netSelisih < 0 ? 'bg-red-50' : 'bg-green-50' }} rounded-full">
                        <svg class="w-8 h-8 {{ $netSelisih < 0 ? 'text-red-600' : 'text-green-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    </div>
                </div>
            </div>

            {{-- 2. TABEL RINCIAN (UI DIPERBAIKI SESUAI PERMINTAAN) --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <h3 class="font-bold text-lg mb-4 text-gray-800 dark:text-white">Rincian Per Buku</h3>

                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-600">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-3 w-1/3">Buku</th>
                                    <th class="px-4 py-3 text-center">Stok Sistem</th>
                                    <th class="px-4 py-3 text-center">Di Rak</th>
                                    <th class="px-4 py-3 text-center">Dipinjam</th>
                                    <th class="px-4 py-3 text-center">Rusak</th>
                                    <th class="px-4 py-3 text-center">Selisih Akhir</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                @foreach($opname->details as $detail)
                                    @php
                                        $data = json_decode($detail->keterangan, true);
                                        $bagus = $data['bagus'] ?? 0;
                                        $rusak = $data['rusak'] ?? 0;
                                        $dipinjam = $data['dipinjam'] ?? 0;

                                        // Total Fisik Real = Bagus + Rusak + Dipinjam
                                        $totalReal = $bagus + $rusak + $dipinjam;
                                        $selisih = $totalReal - $detail->stok_sistem;
                                    @endphp
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                        {{-- 1. BUKU --}}
                                        <td class="px-4 py-3 align-middle">
                                            <div class="font-bold text-gray-900 dark:text-white">{{ $detail->book->judul }}</div>
                                            <div class="text-xs font-mono text-gray-500">{{ $detail->book->kode_buku }}</div>
                                        </td>

                                        {{-- 2. STOK SISTEM --}}
                                        <td class="px-4 py-3 text-center font-bold text-gray-500 bg-gray-50 dark:bg-gray-900/50 border-r dark:border-gray-700 text-base">
                                            {{ $detail->stok_sistem }}
                                        </td>

                                        {{-- 3. DI RAK (BAGUS) --}}
                                        <td class="px-4 py-3 text-center font-bold border-r dark:border-gray-700 {{ $bagus > 0 ? 'text-green-700 bg-green-50 dark:bg-green-900/20' : 'text-gray-300' }}">
                                            {{ $bagus > 0 ? $bagus : '-' }}
                                        </td>

                                        {{-- 4. DIPINJAM --}}
                                        <td class="px-4 py-3 text-center font-bold border-r dark:border-gray-700 {{ $dipinjam > 0 ? 'text-yellow-700 bg-yellow-50 dark:bg-yellow-900/20' : 'text-gray-300' }}">
                                            {{ $dipinjam > 0 ? $dipinjam : '-' }}
                                        </td>

                                        {{-- 5. RUSAK --}}
                                        <td class="px-4 py-3 text-center font-bold border-r dark:border-gray-700 {{ $rusak > 0 ? 'text-red-700 bg-red-50 dark:bg-red-900/20' : 'text-gray-300' }}">
                                            {{ $rusak > 0 ? $rusak : '-' }}
                                        </td>

                                        {{-- 6. SELISIH AKHIR --}}
                                        <td class="px-4 py-3 text-center font-bold">
                                            @if($selisih == 0)
                                                <span class="inline-flex items-center px-2 py-1 rounded bg-green-100 text-green-800 text-xs">
                                                    ✔ Pas
                                                </span>
                                            @elseif($selisih < 0)
                                                <span class="inline-flex items-center px-2 py-1 rounded bg-red-100 text-red-800 text-xs animate-pulse">
                                                    {{ $selisih }} (Hilang)
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 rounded bg-blue-100 text-blue-800 text-xs">
                                                    +{{ $selisih }} (Lebih)
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('print_now'))
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            window.open("{{ route('stock-opnames.export-pdf', $opname->id) }}", "_blank");
        });
    </script>
    @endif
</x-app-layout>
