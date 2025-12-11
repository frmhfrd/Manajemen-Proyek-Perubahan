<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">
                Detail Laporan: {{ $opname->kode_opname }}
            </h2>
            <a href="{{ route('stock-opnames.index') }}" class="text-gray-500 hover:text-gray-700 font-bold">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Info Header --}}
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <span class="block text-gray-500">Tanggal Cek</span>
                        <span class="font-bold text-lg dark:text-white">{{ date('d M Y', strtotime($opname->tgl_opname)) }}</span>
                    </div>
                    <div>
                        <span class="block text-gray-500">Petugas</span>
                        <span class="font-bold text-lg dark:text-white">{{ $opname->user->name }}</span>
                    </div>
                    <div class="md:col-span-2">
                        <span class="block text-gray-500">Catatan</span>
                        <span class="font-medium dark:text-gray-300">{{ $opname->catatan ?? '-' }}</span>
                    </div>
                </div>
            </div>

            {{-- Ringkasan Statistik --}}
            @php
                $totalSistem = $opname->details->sum('stok_sistem');
                $totalFisik = $opname->details->sum('stok_fisik');
                $selisih = $totalFisik - $totalSistem;
                $itemHilang = $opname->details->where('selisih', '<', 0)->count();
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                {{-- Card 1 --}}
                <div class="bg-blue-100 p-4 rounded-lg border-l-4 border-blue-500">
                    <p class="text-blue-600 font-bold text-xs uppercase">Total Fisik</p>
                    <p class="text-2xl font-bold text-blue-900">{{ $totalFisik }} Buku</p>
                </div>
                {{-- Card 2 --}}
                <div class="bg-indigo-100 p-4 rounded-lg border-l-4 border-indigo-500">
                    <p class="text-indigo-600 font-bold text-xs uppercase">Total Sistem</p>
                    <p class="text-2xl font-bold text-indigo-900">{{ $totalSistem }} Buku</p>
                </div>
                {{-- Card 3 (Alert) --}}
                <div class="{{ $selisih < 0 ? 'bg-red-100 border-red-500' : 'bg-green-100 border-green-500' }} p-4 rounded-lg border-l-4">
                    <p class="{{ $selisih < 0 ? 'text-red-600' : 'text-green-600' }} font-bold text-xs uppercase">
                        Discrepancy (Selisih)
                    </p>
                    <p class="text-2xl font-bold {{ $selisih < 0 ? 'text-red-900' : 'text-green-900' }}">
                        {{ $selisih }} Unit
                    </p>
                    @if($itemHilang > 0)
                        <p class="text-xs text-red-600 mt-1 font-bold">⚠️ Ada {{ $itemHilang }} Judul Buku yang kurang/hilang!</p>
                    @endif
                </div>
            </div>

            {{-- Tabel Detail --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="font-bold text-lg mb-4 text-gray-800 dark:text-white">Rincian Per Item</h3>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs uppercase bg-gray-100 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3">Judul Buku</th>
                                    <th class="px-4 py-3 text-center">Stok Sistem</th>
                                    <th class="px-4 py-3 text-center">Stok Fisik</th>
                                    <th class="px-4 py-3 text-center">Selisih</th>
                                    <th class="px-4 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($opname->details as $detail)
                                {{-- Beri warna merah muda jika selisih tidak 0 --}}
                                <tr class="border-b dark:border-gray-700 {{ $detail->selisih != 0 ? 'bg-red-50 dark:bg-red-900/20' : '' }}">
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-gray-900 dark:text-white">{{ $detail->book->judul }}</div>
                                        <div class="text-xs">{{ $detail->book->kode_buku }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-center font-mono">{{ $detail->stok_sistem }}</td>
                                    <td class="px-4 py-3 text-center font-bold font-mono">{{ $detail->stok_fisik }}</td>
                                    <td class="px-4 py-3 text-center font-bold {{ $detail->selisih < 0 ? 'text-red-600' : ($detail->selisih > 0 ? 'text-blue-600' : 'text-gray-400') }}">
                                        {{ $detail->selisih > 0 ? '+' : '' }}{{ $detail->selisih }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($detail->selisih == 0)
                                            <span class="bg-green-100 text-green-800 text-xs px-2 py-0.5 rounded">Sesuai</span>
                                        @elseif($detail->selisih < 0)
                                            <span class="bg-red-100 text-red-800 text-xs px-2 py-0.5 rounded font-bold">Hilang/Kurang</span>
                                        @else
                                            <span class="bg-blue-100 text-blue-800 text-xs px-2 py-0.5 rounded">Lebih</span>
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
</x-app-layout>
