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
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Form Mulai --}}
                    <form action="{{ route('books.store') }}" method="POST">
                        @csrf {{-- Token Keamanan Wajib Laravel --}}

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            {{-- Kode Buku --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Kode Buku / ISBN</label>
                                <input type="text" name="kode_buku" value="{{ old('kode_buku') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                @error('kode_buku') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            {{-- Judul --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Judul Buku</label>
                                <input type="text" name="judul" value="{{ old('judul') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>

                            {{-- Pengarang --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Pengarang</label>
                                <input type="text" name="pengarang" value="{{ old('pengarang') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>

                            {{-- Penerbit --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Penerbit</label>
                                <input type="text" name="penerbit" value="{{ old('penerbit') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            {{-- Tahun Terbit --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Tahun Terbit</label>
                                <input type="number" name="tahun_terbit" value="{{ old('tahun_terbit') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            {{-- Stok Awal --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Jumlah Stok</label>
                                <input type="number" name="stok_total" value="{{ old('stok_total', 1) }}" min="1"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>

                            {{-- Kategori (Dropdown) --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Kategori</label>
                                <select name="kategori_id" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Rak (Dropdown) --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Lokasi Rak</label>
                                <select name="rak_id" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach($shelves as $shelf)
                                        <option value="{{ $shelf->id }}">{{ $shelf->nama_rak }} - {{ $shelf->lokasi }}</option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                        <div class="mt-6 flex justify-end gap-2">
                            <a href="{{ route('books.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Batal
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Simpan Buku
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
