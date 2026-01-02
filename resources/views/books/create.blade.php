<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Tambah Buku Baru') }}
            </h2>
            <a href="{{ route('books.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            {{-- Tambahkan Alert Sukses di sini agar muncul saat 'Simpan & Lanjut' --}}
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Berhasil!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <form action="{{ route('books.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            {{-- Kode Buku --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Kode Buku / ISBN</label>
                                <input type="text" name="kode_buku" value="{{ old('kode_buku') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                @error('kode_buku') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            {{-- Judul --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Judul Buku</label>
                                <input type="text" name="judul" value="{{ old('judul') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>

                            {{-- Pengarang --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Pengarang</label>
                                <input type="text" name="pengarang" value="{{ old('pengarang') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>

                            {{-- Penerbit --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Penerbit</label>
                                <input type="text" name="penerbit" value="{{ old('penerbit') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            {{-- Input Harga --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Harga Buku (Rp)</label>
                                <div class="relative mt-1 rounded-md shadow-sm">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-gray-500 sm:text-sm">Rp</span>
                                    </div>
                                    <input type="number" name="harga" value="{{ old('harga', 0) }}" min="0"
                                        class="block w-full rounded-md border-gray-300 pl-10 focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600 sm:text-sm" required>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Digunakan untuk hitungan denda jika buku hilang.</p>
                            </div>

                            {{-- Tahun Terbit --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Tahun Terbit</label>
                                <input type="number" name="tahun_terbit" value="{{ old('tahun_terbit') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            {{-- Stok Awal --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Jumlah Stok</label>
                                <input type="number" name="stok_total" value="{{ old('stok_total', 1) }}" min="1"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>

                            {{-- Foto Sampul Buku --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Foto Sampul Buku (Opsional)</label>
                                <input type="file" name="cover_image" accept="image/*"
                                    class="mt-1 block w-full text-sm text-gray-500
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-full file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-indigo-50 file:text-indigo-700
                                    hover:file:bg-indigo-100 dark:file:bg-gray-700 dark:file:text-gray-300">
                                <p class="text-xs text-gray-500 mt-1">Format: JPG/PNG, Maks: 2MB</p>
                                @error('cover_image') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            {{-- Kategori --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Kategori</label>
                                <select name="kategori_id" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Rak --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Lokasi Rak</label>
                                <select name="rak_id" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach($shelves as $shelf)
                                        <option value="{{ $shelf->id }}">{{ $shelf->nama_rak }} - {{ $shelf->lokasi }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Update Bagian Tombol --}}
                        <div class="mt-8 flex justify-end gap-3">
                            {{-- 1. Tombol Batal --}}
                            <a href="{{ route('books.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded transition">
                                Batal
                            </a>

                            {{-- 2. Tombol Simpan & Lanjut --}}
                            <button type="submit" name="action" value="save_and_create" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition">
                                Simpan & Tambah Lagi
                            </button>

                            {{-- 3. Tombol Simpan (Default) --}}
                            <button type="submit" name="action" value="save" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition">
                                Simpan Buku
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
