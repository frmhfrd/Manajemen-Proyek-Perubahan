<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Registrasi Anggota Baru') }}
            </h2>
            <a href="{{ route('members.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            {{-- Alert Sukses --}}
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Berhasil!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <form action="{{ route('members.store') }}" method="POST">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- ... (Input Form Tetap Sama) ... --}}

                            {{-- Kode Anggota --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Nomor Induk (NIS/NIP)</label>
                                <input type="text" name="kode_anggota" value="{{ old('kode_anggota') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                @error('kode_anggota') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            {{-- Nama Lengkap --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>

                            {{-- Tipe Anggota --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Tipe Anggota</label>
                                <select name="tipe_anggota" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="siswa">Siswa</option>
                                    <option value="guru">Guru</option>
                                    <option value="staf">Staf TU/Karyawan</option>
                                </select>
                            </div>

                            {{-- Kelas --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Kelas (Jika Siswa)</label>
                                <input type="text" name="kelas" value="{{ old('kelas') }}" placeholder="Contoh: 4A, 6B"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            {{-- No Telepon --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">No. Telepon / WA</label>
                                <input type="text" name="no_telepon" value="{{ old('no_telepon') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            {{-- Alamat --}}
                            <div class="md:col-span-2">
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Alamat Lengkap</label>
                                <textarea name="alamat" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('alamat') }}</textarea>
                            </div>
                        </div>

                        {{-- Update Bagian Tombol --}}
                        <div class="mt-8 flex justify-end gap-3">
                            <a href="{{ route('members.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded transition">
                                Batal
                            </a>

                            <button type="submit" name="action" value="save_and_create" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition">
                                Simpan & Daftar Lagi
                            </button>

                            <button type="submit" name="action" value="save" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition">
                                Simpan Anggota
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
