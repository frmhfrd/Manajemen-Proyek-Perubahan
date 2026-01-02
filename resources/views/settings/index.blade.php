<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Pengaturan Sistem') }}
            </h2>
            <a href="{{ route('dashboard') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 shadow-sm rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <form action="{{ route('settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            {{-- Card 1: Aturan Denda --}}
                            <div class="p-4 border rounded-lg bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
                                <h3 class="font-bold text-lg mb-2 text-red-600">Sanksi & Denda</h3>
                                <div class="flex flex-col gap-2">
                                    <label class="font-medium text-sm">Nominal Denda per Hari (Rupiah)</label>
                                    <div class="flex items-center">
                                        <span class="px-3 py-2 bg-gray-200 border border-r-0 border-gray-300 rounded-l-md text-gray-600">Rp</span>
                                        <input type="number" name="denda_harian" value="{{ $settings['denda_harian'] ?? 500 }}"
                                            class="w-full border-gray-300 dark:bg-gray-900 rounded-r-md focus:ring-indigo-500">
                                    </div>
                                    <p class="text-xs text-gray-500">Dihitung otomatis saat buku dikembalikan terlambat.</p>
                                </div>
                            </div>

                            {{-- Card 2: Durasi Pinjam --}}
                            <div class="p-4 border rounded-lg bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
                                <h3 class="font-bold text-lg mb-2 text-blue-600">Durasi Peminjaman</h3>
                                <div class="flex flex-col gap-2">
                                    <label class="font-medium text-sm">Maksimal Lama Pinjam (Hari)</label>
                                    <div class="flex items-center">
                                        <input type="number" name="max_lama_pinjam" value="{{ $settings['max_lama_pinjam'] ?? 7 }}"
                                            class="w-full border-gray-300 dark:bg-gray-900 rounded-l-md focus:ring-indigo-500">
                                        <span class="px-3 py-2 bg-gray-200 border border-l-0 border-gray-300 rounded-r-md text-gray-600">Hari</span>
                                    </div>
                                    <p class="text-xs text-gray-500">Default tanggal jatuh tempo saat transaksi baru.</p>
                                </div>
                            </div>

                            {{-- Card 3: Kuota Buku (BARU) --}}
                            <div class="p-4 border rounded-lg bg-gray-50 dark:bg-gray-700 dark:border-gray-600 md:col-span-2">
                                <h3 class="font-bold text-lg mb-2 text-green-600">Kuota Peminjaman</h3>
                                <div class="flex flex-col gap-2">
                                    <label class="font-medium text-sm">Maksimal Buku Dipinjam per Siswa</label>
                                    <div class="flex items-center">
                                        <input type="number" name="max_buku_pinjam" value="{{ $settings['max_buku_pinjam'] ?? 3 }}"
                                            class="w-full border-gray-300 dark:bg-gray-900 rounded-l-md focus:ring-indigo-500">
                                        <span class="px-3 py-2 bg-gray-200 border border-l-0 border-gray-300 rounded-r-md text-gray-600">Buku</span>
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        Jika siswa sudah meminjam jumlah ini, sistem akan menolak peminjaman baru sampai buku sebelumnya dikembalikan.
                                    </p>
                                </div>
                            </div>

                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-lg shadow">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
