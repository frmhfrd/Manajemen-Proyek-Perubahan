<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Daftar Buku') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Tombol Tambah (Nanti kita fungsikan) --}}
                    <div class="mb-4">
                        <a href="{{ route('books.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            + Tambah Buku Baru
                        </a>
                    </div>

                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">Berhasil!</strong>
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    {{-- Tabel Buku --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-2 text-left">Kode</th>
                                    <th class="px-4 py-2 text-left">Judul</th>
                                    <th class="px-4 py-2 text-left">Kategori</th>
                                    <th class="px-4 py-2 text-left">Rak</th>
                                    <th class="px-4 py-2 text-center">Stok</th>
                                    <th class="px-4 py-2 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                @forelse ($books as $book)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-4 py-2">{{ $book->kode_buku }}</td>
                                    <td class="px-4 py-2 font-bold">{{ $book->judul }}</td>
                                    <td class="px-4 py-2">
                                        <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded">
                                            {{ $book->category->name }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">{{ $book->shelf->nama_rak }}</td>
                                    <td class="px-4 py-2 text-center">
                                        {{ $book->stok_tersedia }} / {{ $book->stok_total }}
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <a href="{{ route('books.edit', $book->id) }}" class="text-yellow-500 hover:text-yellow-700 mx-1">Edit</a>
                                        <form action="{{ route('books.destroy', $book->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus buku {{ $book->judul }}? Tindakan ini tidak bisa dibatalkan.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 mx-1">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-2 text-center">Belum ada data buku.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination (Navigasi Halaman) --}}
                    <div class="mt-4">
                        {{ $books->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
