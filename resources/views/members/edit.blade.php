<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Data Anggota') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <form action="{{ route('members.update', $member->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            {{-- Kode Anggota --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Nomor Induk (NIS/NIP)</label>
                                <input type="text" name="kode_anggota" value="{{ old('kode_anggota', $member->kode_anggota) }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                @error('kode_anggota') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            {{-- Nama Lengkap --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $member->nama_lengkap) }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>

                            {{-- Tipe Anggota --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Tipe Anggota</label>
                                <select name="tipe_anggota" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="siswa" {{ $member->tipe_anggota == 'siswa' ? 'selected' : '' }}>Siswa</option>
                                    <option value="guru" {{ $member->tipe_anggota == 'guru' ? 'selected' : '' }}>Guru</option>
                                    <option value="staf" {{ $member->tipe_anggota == 'staf' ? 'selected' : '' }}>Staf</option>
                                </select>
                            </div>

                            {{-- Kelas --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Kelas</label>
                                <input type="text" name="kelas" value="{{ old('kelas', $member->kelas) }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            {{-- No Telepon --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">No. Telepon</label>
                                <input type="text" name="no_telepon" value="{{ old('no_telepon', $member->no_telepon) }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            {{-- Status Aktif --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Status Keanggotaan</label>
                                <select name="status_aktif" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="1" {{ $member->status_aktif == 1 ? 'selected' : '' }}>Aktif</option>
                                    <option value="0" {{ $member->status_aktif == 0 ? 'selected' : '' }}>Non-Aktif (Lulus/Pindah)</option>
                                </select>
                            </div>

                            {{-- Alamat --}}
                            <div class="md:col-span-2">
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Alamat Lengkap</label>
                                <textarea name="alamat" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('alamat', $member->alamat) }}</textarea>
                            </div>

                        </div>

                        <div class="mt-6 flex justify-end gap-2">
                            <a href="{{ route('members.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Batal</a>
                            <button type="submit" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">Update Data</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
