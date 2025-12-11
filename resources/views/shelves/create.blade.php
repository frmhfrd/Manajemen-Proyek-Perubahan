<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Tambah Rak Baru') }}
            </h2>
            <a href="{{ route('shelves.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Alert Sukses --}}
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Berhasil!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <form action="{{ route('shelves.store') }}" method="POST">
                        @csrf

                        {{-- Nama Rak --}}
                        <div class="mb-4">
                            <label for="nama_rak" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nama Rak</label>
                            <input type="text" name="nama_rak" id="nama_rak" class="w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="Contoh: Rak A-01, Lemari Besi..." required>
                            @error('nama_rak')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Lokasi --}}
                        <div class="mb-4">
                            <label for="lokasi" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Lokasi Fisik</label>
                            <input type="text" name="lokasi" id="lokasi" class="w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-700 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="Contoh: Lantai 1 - Pojok Kanan" required>
                            @error('lokasi')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Tombol Aksi (DENGAN 3 PILIHAN) --}}
                        <div class="flex justify-end gap-3 mt-6">
                            <a href="{{ route('shelves.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded transition">
                                Batal
                            </a>

                            {{-- Tombol Hijau: Simpan & Tambah Lagi --}}
                            <button type="submit" name="action" value="save_and_create" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition">
                                Simpan & Tambah Lagi
                            </button>

                            {{-- Tombol Biru: Simpan --}}
                            <button type="submit" name="action" value="save" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition">
                                Simpan
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
