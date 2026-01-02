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
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 shadow-sm rounded flex justify-between items-center">
                    <span>{{ session('success') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-green-700 font-bold">&times;</button>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <form action="{{ route('settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            {{-- === CARD 1: ATURAN DENDA (FULL WIDTH) === --}}
                            {{-- Tambahkan class 'md:col-span-2' agar memanjang ke samping --}}
                            <div class="p-4 border rounded-lg bg-red-50 dark:bg-gray-700 dark:border-gray-600 md:col-span-2">
                                <h3 class="font-bold text-lg mb-4 text-red-600 border-b border-red-200 pb-2">Aturan Sanksi & Denda</h3>

                                {{-- Grid Internal untuk Denda (Biar Denda Harian & Rusak sejajar) --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                    {{-- 1. Denda Keterlambatan --}}
                                    <div class="flex flex-col gap-2">
                                        <label class="font-medium text-sm">Denda Keterlambatan (Per Hari)</label>
                                        <div class="flex items-center">
                                            <span class="px-3 py-2 bg-gray-200 border border-r-0 border-gray-300 rounded-l-md text-gray-600">Rp</span>
                                            <input type="number" name="denda_harian" value="{{ $settings['denda_harian'] ?? 500 }}"
                                                class="w-full border-gray-300 dark:bg-gray-900 rounded-r-md focus:ring-red-500 focus:border-red-500">
                                        </div>
                                        <p class="text-xs text-gray-500">Dihitung otomatis per hari telat.</p>
                                    </div>

                                    {{-- 2. Denda Buku Rusak --}}
                                    <div class="flex flex-col gap-2">
                                        <label class="font-medium text-sm">Denda Buku Rusak (Flat)</label>
                                        <div class="flex items-center">
                                            <span class="px-3 py-2 bg-gray-200 border border-r-0 border-gray-300 rounded-l-md text-gray-600">Rp</span>
                                            <input type="number" name="denda_rusak" value="{{ $settings['denda_rusak'] ?? 10000 }}"
                                                class="w-full border-gray-300 dark:bg-gray-900 rounded-r-md focus:ring-red-500 focus:border-red-500">
                                        </div>
                                        <p class="text-xs text-gray-500">Biaya ganti rugi kerusakan fisik buku.</p>
                                    </div>

                                </div>
                            </div>

                            {{-- === CARD 2: DURASI PINJAM (SEBELAH KIRI) === --}}
                            <div class="p-4 border rounded-lg bg-blue-50 dark:bg-gray-700 dark:border-gray-600">
                                <h3 class="font-bold text-lg mb-2 text-blue-600">Durasi Peminjaman</h3>
                                <div class="flex flex-col gap-2">
                                    <label class="font-medium text-sm">Jatah Waktu Pinjam</label>
                                    <div class="flex items-center">
                                        <input type="number" name="max_lama_pinjam" value="{{ $settings['max_lama_pinjam'] ?? 7 }}"
                                            class="w-full border-gray-300 dark:bg-gray-900 rounded-l-md focus:ring-blue-500 focus:border-blue-500 text-center font-bold">
                                        <span class="px-3 py-2 bg-gray-200 border border-l-0 border-gray-300 rounded-r-md text-gray-600">Hari</span>
                                    </div>
                                    <p class="text-xs text-gray-500">Default tanggal kembali otomatis.</p>
                                </div>
                            </div>

                            {{-- === CARD 3: KUOTA PEMINJAMAN (SEBELAH KANAN) === --}}
                            <div class="p-4 border rounded-lg bg-green-50 dark:bg-gray-700 dark:border-gray-600">
                                <h3 class="font-bold text-lg mb-2 text-green-600">Kuota Peminjaman</h3>
                                <div class="flex flex-col gap-2">
                                    <label class="font-medium text-sm">Maksimal Buku per Siswa</label>
                                    <div class="flex items-center">
                                        <input type="number" name="max_buku_pinjam" value="{{ $settings['max_buku_pinjam'] ?? 3 }}"
                                            class="w-full border-gray-300 dark:bg-gray-900 rounded-l-md focus:ring-green-500 focus:border-green-500 text-center font-bold">
                                        <span class="px-3 py-2 bg-gray-200 border border-l-0 border-gray-300 rounded-r-md text-gray-600">Buku</span>
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        Sistem memblokir jika melebihi batas ini.
                                    </p>
                                </div>
                            </div>

                        </div>

                        <div class="mt-6 flex justify-end pt-4 border-t border-gray-100">
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-lg shadow transition transform hover:scale-105">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
