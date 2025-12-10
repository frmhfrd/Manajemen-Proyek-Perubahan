<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-red-600 leading-tight">
                {{ __('Sampah Buku (Deleted Items)') }}
            </h2>
           <a href="{{ route('books.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 shadow-sm rounded">
                    <p class="font-bold">Sukses</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border-t-4 border-red-500">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                        Data di bawah ini adalah buku yang telah dihapus. Anda dapat memulihkannya atau menghapusnya secara permanen.
                    </p>

                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th class="px-6 py-3">Buku Info</th>
                                    <th class="px-6 py-3">Dihapus Tanggal</th>
                                    <th class="px-6 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($books as $book)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-red-50 dark:hover:bg-red-900 transition">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900 dark:text-white">{{ $book->judul }}</div>
                                        <div class="text-xs">{{ $book->kode_buku }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-red-600 font-medium">
                                        {{ $book->deleted_at->format('d M Y H:i') }}
                                        <div class="text-xs text-gray-500">{{ $book->deleted_at->diffForHumans() }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex justify-center space-x-2">
                                            {{-- Tombol Pulihkan --}}
                                            <form action="{{ route('books.restore', $book->id) }}" method="POST">
                                                @csrf @method('PUT')
                                                <button type="submit" class="text-white bg-green-600 hover:bg-green-700 font-medium rounded-lg text-xs px-3 py-1.5 shadow">
                                                    Pulihkan
                                                </button>
                                            </form>

                                            {{-- Tombol Hapus Permanen --}}
                                            @if(Auth::user()->role == 'admin')
                                                <form action="{{ route('books.force_delete', $book->id) }}" method="POST" onsubmit="return confirm('PERINGATAN: Buku ini akan hilang selamanya! Lanjutkan?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-white bg-red-700 hover:bg-red-800 font-medium rounded-lg text-xs px-3 py-1.5 shadow">
                                                        Hapus Permanen
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                                        Tempat sampah kosong. Tidak ada data yang dihapus.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $books->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
