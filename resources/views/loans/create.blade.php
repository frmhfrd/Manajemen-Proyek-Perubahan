<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Buat Peminjaman Baru') }}
            </h2>
            <a href="{{ route('loans.index') }}" class="text-gray-400 hover:text-gray-200 font-bold">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Error Alert --}}
                    @if(session('error'))
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                            <p class="font-bold">Gagal</p>
                            <p>{{ session('error') }}</p>
                        </div>
                    @endif

                    <form action="{{ route('loans.store') }}" method="POST">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                            {{-- KOLOM KIRI: Pilih Siswa --}}
                            <div class="md:col-span-1 space-y-4">
                                <div>
                                    <label class="block font-bold text-lg text-gray-700 dark:text-gray-300 mb-2">1. Pilih Anggota</label>
                                    <select name="member_id" class="w-full rounded-md border-gray-300 dark:bg-gray-900 focus:ring-indigo-500 h-12" required>
                                        <option value="">-- Klik untuk memilih --</option>
                                        @foreach($members as $member)
                                            <option value="{{ $member->id }}">{{ $member->nama_lengkap }} ({{ $member->kelas }})</option>
                                        @endforeach
                                    </select>
                                    <p class="text-xs text-gray-500 mt-2">Pastikan siswa statusnya aktif.</p>
                                </div>

                                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200 text-sm text-blue-800">
                                    <strong>Info:</strong><br>
                                    Durasi peminjaman default adalah 7 hari. Denda akan dihitung setelah tanggal jatuh tempo.
                                </div>
                            </div>

                            {{-- KOLOM KANAN: Pilih Buku (Checkbox Grid) --}}
                            <div class="md:col-span-2">
                                <label class="block font-bold text-lg text-gray-700 dark:text-gray-300 mb-2">2. Pilih Buku (Centang)</label>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-[500px] overflow-y-auto p-2 border rounded-md bg-gray-50 dark:bg-gray-900">
                                    @foreach($books as $book)
                                    <label class="relative flex items-center p-3 rounded-lg border border-gray-200 bg-white hover:bg-indigo-50 cursor-pointer transition shadow-sm">
                                        <input type="checkbox" name="book_ids[]" value="{{ $book->id }}" class="h-5 w-5 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                                        <div class="ml-3">
                                            <span class="block text-sm font-medium text-gray-900">{{ $book->judul }}</span>
                                            <span class="block text-xs text-gray-500">{{ $book->kode_buku }} | Stok: {{ $book->stok_tersedia }}</span>
                                        </div>
                                    </label>
                                    @endforeach
                                </div>
                                <p class="text-xs text-gray-500 mt-2 text-right">Hanya buku dengan stok > 0 yang ditampilkan.</p>
                            </div>

                        </div>

                        {{-- Tombol Aksi --}}
                        <div class="mt-8 border-t pt-6 flex justify-end">
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-lg shadow-lg transition transform hover:-translate-y-1">
                                Proses Transaksi
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
