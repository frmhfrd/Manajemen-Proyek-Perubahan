<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Manajemen Buku') }}
            </h2>
            <a href="{{ route('books.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-150 ease-in-out">
                + Tambah Buku
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Notifikasi Sukses --}}
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 shadow-sm rounded" role="alert">
                    <p class="font-bold">Sukses</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Search Bar Section --}}
                    <div class="mb-6 flex justify-between items-center">
                        <form action="{{ route('books.index') }}" method="GET" class="flex gap-2 w-full md:w-1/2">
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Cari Judul, Penulis, atau ISBN..."
                                class="w-full rounded-md border-gray-300 dark:bg-gray-900 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <button type="submit" class="bg-gray-800 hover:bg-gray-700 text-white px-4 py-2 rounded-md">
                                Cari
                            </button>
                            @if(request('search'))
                                <a href="{{ route('books.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md flex items-center">Reset</a>
                            @endif
                        </form>
                    </div>

                    {{-- Tabel Modern --}}
                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Buku Info</th>
                                    <th scope="col" class="px-6 py-3">Lokasi</th>
                                    <th scope="col" class="px-6 py-3 text-center">Stok</th>
                                    <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($books as $book)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition">

                                    {{-- Kolom Info Buku --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            {{-- Ikon Buku (Hiasan) --}}
                                            <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 text-indigo-500 rounded-full flex items-center justify-center mr-3 font-bold">
                                                {{ strtoupper(substr($book->judul ?? 'B', 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="text-base font-semibold text-gray-900 dark:text-white">{{ $book->judul }}</div>
                                                <div class="text-xs text-gray-500">ISBN: {{ $book->kode_buku }}</div>
                                                <div class="text-xs text-gray-500">{{ $book->pengarang }}</div>
                                                <span class="mt-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ $book->category->nama }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Kolom Lokasi --}}
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $book->shelf->nama_rak }}</div>
                                        <div class="text-xs text-gray-500">{{ $book->shelf->lokasi }}</div>
                                    </td>

                                    {{-- Kolom Stok (Dengan Warna Indikator) --}}
                                    <td class="px-6 py-4 text-center">
                                        @if($book->stok_tersedia == 0)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Habis
                                            </span>
                                        @elseif($book->stok_tersedia < 3)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Sisa {{ $book->stok_tersedia }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                {{ $book->stok_tersedia }} / {{ $book->stok_total }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Kolom Aksi --}}
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex justify-center space-x-2">
                                            <a href="{{ route('books.edit', $book->id) }}" class="text-white bg-yellow-400 hover:bg-yellow-500 focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium rounded-lg text-xs px-3 py-1.5 text-center dark:focus:ring-yellow-900">
                                                Edit
                                            </a>

                                            <form action="{{ route('books.destroy', $book->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus buku ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-xs px-3 py-1.5 text-center dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-900">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                        Tidak ada data buku yang ditemukan.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination Modern --}}
                    <div class="mt-4">
                        {{ $books->withQueryString()->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
