<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-red-600 leading-tight">
                {{ __('Sampah Anggota (Deleted Members)') }}
            </h2>
            <a href="{{ route('members.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition">
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

            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 shadow-sm rounded">
                    <p class="font-bold">Gagal</p>
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border-t-4 border-red-500">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                        Daftar anggota yang telah dinonaktifkan/dihapus. Anda dapat memulihkan kembali jika diperlukan.
                    </p>

                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th class="px-6 py-3">Nama Anggota</th>
                                    <th class="px-6 py-3">Dihapus Tanggal</th>
                                    <th class="px-6 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($members as $member)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-red-50 dark:hover:bg-red-900 transition">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900 dark:text-white">{{ $member->nama_lengkap }}</div>
                                        <div class="text-xs">{{ $member->kode_anggota }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-red-600 font-medium">
                                        {{ $member->deleted_at->format('d M Y H:i') }}
                                        <div class="text-xs text-gray-500">{{ $member->deleted_at->diffForHumans() }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex justify-center space-x-2">
                                            {{-- Pulihkan --}}
                                            <form action="{{ route('members.restore', $member->id) }}" method="POST">
                                                @csrf @method('PUT')
                                                <button type="submit" class="text-white bg-green-600 hover:bg-green-700 font-medium rounded-lg text-xs px-3 py-1.5 shadow">
                                                    Pulihkan
                                                </button>
                                            </form>

                                            {{-- Hapus Permanen --}}
                                            @if(Auth::user()->role == 'admin')
                                                <form action="{{ route('members.force_delete', $member->id) }}" method="POST" onsubmit="return confirm('PERINGATAN: Data anggota ini akan hilang selamanya beserta riwayatnya! Lanjutkan?');">
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
                                        Tempat sampah kosong.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $members->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
