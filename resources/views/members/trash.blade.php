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
                                                <button type="submit" class="text-white bg-green-600 hover:bg-green-700 font-medium rounded-lg text-xs px-3 py-1.5 shadow transition">
                                                    Pulihkan
                                                </button>
                                            </form>

                                            {{-- Hapus Permanen (Trigger Modal) --}}
                                            @if(Auth::user()->role == 'admin')
                                                <button type="button"
                                                    onclick="openForceDeleteModal('{{ route('members.force_delete', $member->id) }}', '{{ $member->nama_lengkap }}')"
                                                    class="text-white bg-red-700 hover:bg-red-800 font-medium rounded-lg text-xs px-3 py-1.5 shadow transition">
                                                    Hapus Permanen
                                                </button>
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

    {{-- MODAL FORCE DELETE --}}
    <div id="forceDeleteModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeForceDeleteModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            {{-- Konten Modal --}}
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border-2 border-red-500">
                <form id="forceDeleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')

                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            {{-- Icon Danger Merah --}}
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-bold text-red-600 dark:text-red-400" id="modal-title">
                                    HAPUS PERMANEN?
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-300">
                                        Anda akan menghapus data anggota <span id="deleteName" class="font-bold text-black dark:text-white"></span> secara permanen dari database.
                                    </p>
                                    <p class="text-xs text-red-600 font-bold mt-3 bg-red-50 p-2 rounded border border-red-200">
                                        ⚠️ PERINGATAN: Data ini beserta riwayatnya TIDAK BISA dipulihkan kembali!
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-700 text-base font-medium text-white hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Ya, Hapus Selamanya
                        </button>
                        <button type="button" onclick="closeForceDeleteModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openForceDeleteModal(url, name) {
            document.getElementById('forceDeleteForm').action = url;
            document.getElementById('deleteName').innerText = name;
            document.getElementById('forceDeleteModal').classList.remove('hidden');
        }

        function closeForceDeleteModal() {
            document.getElementById('forceDeleteModal').classList.add('hidden');
        }
    </script>
</x-app-layout>
