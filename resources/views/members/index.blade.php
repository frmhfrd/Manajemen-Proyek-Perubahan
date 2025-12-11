<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Data Anggota') }}
            </h2>

            <div class="flex gap-2">
                {{-- Tombol Sampah --}}
                <a href="{{ route('members.trash') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg shadow-md flex items-center gap-2 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    Sampah
                </a>

                {{-- Tombol Tambah --}}
                <a href="{{ route('members.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition">
                    + Anggota Baru
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Load Library Scanner --}}
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Notifikasi Sukses --}}
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm">
                    <p class="font-bold">Berhasil</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            {{-- Notifikasi Error --}}
            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm">
                    <p class="font-bold">Gagal</p>
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Search Bar Section (UPGRADED) --}}
                    <div class="mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
                        <form action="{{ route('members.index') }}" method="GET" class="w-full md:w-1/2" id="searchForm">
                            <div class="flex gap-2">

                                {{-- Input Search --}}
                                <div class="relative flex-1">
                                    <input type="text" id="searchInput" name="search" value="{{ request('search') }}"
                                        placeholder="Scan Kartu / Cari Nama..."
                                        class="w-full rounded-md border-gray-300 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pl-10 h-10">
                                </div>

                                {{-- Tombol Scan --}}
                                <button type="button" onclick="startSearchScanner()" class="bg-yellow-500 text-white w-10 h-10 rounded-md hover:bg-yellow-600 shadow-sm transition flex items-center justify-center flex-shrink-0" title="Scan Kartu Anggota">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                                </button>

                                <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-md hover:bg-gray-700 h-10">
                                    Cari
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Tabel --}}
                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th class="px-6 py-3">Nama Lengkap</th>
                                    <th class="px-6 py-3">NIS/Identitas</th>
                                    <th class="px-6 py-3">Tipe/Kelas</th>
                                    <th class="px-6 py-3 text-center">Status</th>
                                    <th class="px-6 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($members as $member)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50">
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                        {{ $member->nama_lengkap }}
                                        <div class="text-xs text-gray-500">{{ $member->no_telepon ?? '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 font-mono">{{ $member->kode_anggota }}</td>
                                    <td class="px-6 py-4">
                                        <span class="bg-gray-100 text-gray-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded border border-gray-500">
                                            {{ ucfirst($member->tipe_anggota) }}
                                        </span>
                                        @if($member->kelas)
                                            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                                Kelas {{ $member->kelas }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($member->status_aktif)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Non-Aktif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex justify-center space-x-2">

                                            {{-- TOMBOL CETAK KARTU (Biru) --}}
                                            <a href="{{ route('members.card', $member->id) }}" target="_blank" class="text-white bg-blue-500 hover:bg-blue-600 font-medium rounded-lg text-xs px-3 py-1.5 shadow transition" title="Cetak Kartu">
                                                🪪 Cetak
                                            </a>

                                            <a href="{{ route('members.edit', $member->id) }}" class="text-white bg-yellow-400 hover:bg-yellow-500 font-medium rounded-lg text-xs px-3 py-1.5 transition">Edit</a>

                                            <button type="button"
                                                onclick="openDeleteModal('{{ route('members.destroy', $member->id) }}', '{{ $member->nama_lengkap }}')"
                                                class="text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-xs px-3 py-1.5 text-center dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-900 transition">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">Belum ada data anggota.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $members->withQueryString()->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- MODAL DELETE --}}
    <div id="deleteModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeDeleteModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')

                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                                    Hapus Data Anggota
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-300">
                                        Apakah Anda yakin ingin menghapus anggota <span id="deleteName" class="font-bold"></span>?
                                    </p>
                                    <p class="text-xs text-red-500 mt-2">
                                        *Jika anggota ini memiliki riwayat peminjaman, data tidak akan bisa dihapus (Database Protected).
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Ya, Hapus
                        </button>
                        <button type="button" onclick="closeDeleteModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL SCANNER PENCARIAN --}}
    <div id="searchScannerModal" class="hidden fixed inset-0 bg-black/90 z-[60] flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-md overflow-hidden relative shadow-2xl">
            <div class="bg-gray-100 p-4 flex justify-between items-center border-b">
                <h3 class="font-bold text-lg text-gray-800">Scan Kartu Anggota</h3>
                <button onclick="stopSearchScanner()" class="text-gray-500 hover:text-red-500 font-bold text-2xl">&times;</button>
            </div>
            <div id="searchReader" class="w-full h-64 bg-black"></div>
            <div class="p-6 text-center bg-white">
                <p class="text-sm text-gray-500 mb-4">Arahkan barcode kartu ke kamera.</p>
                <button onclick="stopSearchScanner()" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg font-bold hover:bg-gray-300 w-full">Batal</button>
            </div>
        </div>
    </div>

    <script>
        // --- DELETE MODAL LOGIC ---
        function openDeleteModal(url, name) {
            document.getElementById('deleteForm').action = url;
            document.getElementById('deleteName').innerText = name;
            document.getElementById('deleteModal').classList.remove('hidden');
        }
        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        // --- SCANNER LOGIC ---
        let html5QrCodeSearch = null;

        function startSearchScanner() {
            document.getElementById('searchScannerModal').classList.remove('hidden');
            html5QrCodeSearch = new Html5Qrcode("searchReader");
            Html5Qrcode.getCameras().then(devices => {
                if(devices && devices.length) {
                    html5QrCodeSearch.start(devices[0].id, { fps: 10, qrbox: {width: 250, height: 250} },
                        (decodedText) => {
                            // Hasil Scan Masuk ke Input Search
                            document.getElementById('searchInput').value = decodedText;
                            // Stop Kamera & Tutup Modal
                            stopSearchScanner();
                            // Submit Form Otomatis
                            document.getElementById('searchForm').submit();
                        },
                        () => {}
                    );
                }
            });
        }

        function stopSearchScanner() {
            if(html5QrCodeSearch) {
                html5QrCodeSearch.stop().then(() => {
                    document.getElementById('searchScannerModal').classList.add('hidden');
                }).catch(() => {
                    document.getElementById('searchScannerModal').classList.add('hidden');
                });
            } else {
                document.getElementById('searchScannerModal').classList.add('hidden');
            }
        }
    </script>
</x-app-layout>
