<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Form Stock Opname (Cek Fisik)</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('stock-opnames.store') }}" method="POST">
                @csrf

                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow mb-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300">Tanggal Cek</label>
                            <input type="date" name="tgl_opname" value="{{ date('Y-m-d') }}" class="w-full rounded bg-gray-50">
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300">Catatan</label>
                            <input type="text" name="catatan" placeholder="Contoh: Cek rutin akhir tahun" class="w-full rounded bg-gray-50">
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-bold text-lg mb-4 text-gray-800 dark:text-white">Input Data Fisik Buku</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs uppercase bg-gray-100 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3">Judul Buku</th>
                                    <th class="px-4 py-3">Rak</th>
                                    <th class="px-4 py-3 text-center">Stok Komputer</th>
                                    <th class="px-4 py-3 text-center" width="150">Stok Fisik (Nyata)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($books as $book)
                                <tr class="border-b dark:border-gray-700">
                                    <td class="px-4 py-2">
                                        <div class="font-bold text-gray-900 dark:text-white">{{ $book->judul }}</div>
                                        <div class="text-xs">{{ $book->kode_buku }}</div>
                                    </td>
                                    <td class="px-4 py-2">{{ $book->shelf->nama_rak ?? '-' }}</td>
                                    <td class="px-4 py-2 text-center font-bold text-blue-600">
                                        {{ $book->stok_tersedia }}
                                    </td>
                                    <td class="px-4 py-2">
                                        {{-- INPUT ARRAY: name="fisik[ID_BUKU]" --}}
                                        <input type="number" name="fisik[{{ $book->id }}]" value="{{ $book->stok_tersedia }}"
                                            class="w-full text-center font-bold border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded shadow-lg" onclick="return confirm('Apakah data fisik sudah benar? Laporan ini tidak bisa diedit.')">
                            Simpan Laporan Opname
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
