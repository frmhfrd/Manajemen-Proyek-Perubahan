<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Edit Buku') }}
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

                    {{-- Form EDIT --}}
                    {{-- Perubahan 1: Route mengarah ke UPDATE dengan ID --}}
                    <form action="{{ route('books.update', $book->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            {{-- Kode Buku --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Kode Buku / ISBN</label>
                                {{-- Perubahan 3: Value mengambil dari $book->kode_buku --}}
                                <input type="text" name="kode_buku" value="{{ old('kode_buku', $book->kode_buku) }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                @error('kode_buku') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            {{-- Judul --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Judul Buku</label>
                                <input type="text" name="judul" value="{{ old('judul', $book->judul) }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>

                            {{-- Pengarang --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Pengarang</label>
                                <input type="text" name="pengarang" value="{{ old('pengarang', $book->pengarang) }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>

                            {{-- Penerbit --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Penerbit</label>
                                <input type="text" name="penerbit" value="{{ old('penerbit', $book->penerbit) }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            {{-- Input Harga Edit --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Harga Buku (Rp)</label>
                                <div class="relative mt-1 rounded-md shadow-sm">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-gray-500 sm:text-sm">Rp</span>
                                    </div>
                                    <input type="number" name="harga" value="{{ old('harga', $book->harga) }}" min="0"
                                        class="block w-full rounded-md border-gray-300 pl-10 focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600 sm:text-sm" required>
                                </div>
                            </div>

                            {{-- Tahun Terbit --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Tahun Terbit</label>
                                <input type="number" name="tahun_terbit" value="{{ old('tahun_terbit', $book->tahun_terbit) }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            {{-- Stok Total --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Jumlah Stok Total</label>
                                <input type="number" name="stok_total" value="{{ old('stok_total', $book->stok_total) }}" min="1"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <p class="text-xs text-gray-500 mt-1">Mengubah stok total akan otomatis menyesuaikan stok tersedia.</p>
                            </div>

                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Foto Sampul Buku</label>

                                {{-- Pratinjau Gambar Lama --}}
                                @if($book->cover_image)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $book->cover_image) }}" alt="Cover Buku" class="h-32 w-24 object-cover rounded shadow">
                                    </div>
                                @endif

                                <input type="file" name="cover_image" accept="image/*"
                                    class="mt-1 block w-full ... (style sama seperti create) ...">
                                <p class="text-xs text-gray-500 mt-1">Biarkan kosong jika tidak ingin mengubah gambar.</p>
                            </div>

                            {{-- Kategori (Dropdown) --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Kategori</label>
                                <select name="kategori_id" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach($categories as $cat)
                                        {{-- Logic Selected: Jika ID kategori sama dengan ID kategori buku ini, maka pilih --}}
                                        <option value="{{ $cat->id }}" {{ $book->kategori_id == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Rak (Dropdown) --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Lokasi Rak</label>
                                <select name="rak_id" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach($shelves as $shelf)
                                        <option value="{{ $shelf->id }}" {{ $book->rak_id == $shelf->id ? 'selected' : '' }}>
                                            {{ $shelf->nama_rak }} - {{ $shelf->lokasi }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                        <div class="mt-6 flex justify-end gap-2">
                            <a href="{{ route('books.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Batal
                            </a>
                            <button type="submit" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                                Update Perubahan
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
