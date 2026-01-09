<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Manajemen Buku Rusak') }}
            </h2>
            <a href="{{ route('books.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Alert Notifikasi --}}
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm">
                    <p class="font-bold">Berhasil</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm">
                    <p class="font-bold">Gagal</p>
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Tabel --}}
                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-3">Judul Buku</th>
                                    <th class="px-4 py-3 text-center">Stok Rusak</th>
                                    <th class="px-4 py-3 text-center" width="250">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                @forelse($books as $book)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    {{-- Kolom Judul --}}
                                    <td class="px-4 py-3 align-middle">
                                        <div class="font-bold text-gray-900 dark:text-white">{{ $book->judul }}</div>
                                        <div class="text-xs font-mono text-gray-500">{{ $book->kode_buku ?? '-' }}</div>
                                    </td>

                                    {{-- Kolom Stok --}}
                                    <td class="px-4 py-3 text-center align-middle">
                                        <span class="inline-flex items-center justify-center px-3 py-1 rounded-full font-bold text-xs bg-red-100 text-red-700 border border-red-200">
                                            {{ $book->stok_rusak }}
                                        </span>
                                    </td>

                                    {{-- Kolom Aksi --}}
                                    <td class="px-4 py-3 text-center align-middle">
                                        <form action="{{ route('books.process-rusak', $book->id) }}" method="POST" class="flex items-center justify-center gap-2">
                                            @csrf

                                            {{-- Input Qty --}}
                                            <input type="number" name="qty" value="1" min="1" max="{{ $book->stok_rusak }}"
                                                class="w-16 rounded-lg border-gray-300 dark:bg-gray-900 dark:border-gray-600 text-sm text-center focus:ring-indigo-500 focus:border-indigo-500 p-2 shadow-sm"
                                                title="Jumlah diproses">

                                            {{-- Tombol Perbaiki (Hijau) --}}
                                            <button type="submit" name="action" value="repair"
                                                class="group relative inline-flex items-center justify-center p-2 rounded-lg bg-green-600 text-white hover:bg-green-700 shadow-md transition-all duration-200"
                                                title="Perbaiki (Kembali ke Rak)">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </button>

                                            {{-- Tombol Musnahkan (Merah) - Admin Only --}}
                                            @if(auth()->user()->role == 'admin')
                                            <button type="submit" name="action" value="destroy"
                                                class="group relative inline-flex items-center justify-center p-2 rounded-lg bg-red-600 text-white hover:bg-red-700 shadow-md transition-all duration-200"
                                                title="Musnahkan (Hapus Aset)"
                                                onclick="return confirm('Yakin musnahkan aset ini? Stok akan berkurang permanen dan tidak bisa dikembalikan.')">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                            @endif
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-10 text-center text-gray-400 bg-gray-50 dark:bg-gray-900">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <span>Tidak ada buku rusak saat ini. Semua aman!</span>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
