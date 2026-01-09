<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Manajemen Buku') }}
            </h2>

            <div class="flex gap-2">
                {{-- Tombol Sampah (Abu-abu) --}}
                <a href="{{ route('books.trash') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg shadow-md flex items-center gap-2 transition text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    Sampah
                </a>

                {{-- [BARU] Tombol Buku Rusak (Kuning/Orange) --}}
                <a href="{{ route('books.rusak') }}" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-4 rounded-lg shadow-md flex items-center gap-2 transition text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Buku Rusak
                </a>

                {{-- Tombol Tambah (Biru) --}}
                <a href="{{ route('books.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition text-sm flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Tambah Buku
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
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 shadow-sm rounded" role="alert">
                    <p class="font-bold">Sukses</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            {{-- Notifikasi Error --}}
            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 shadow-sm rounded" role="alert">
                    <div class="flex items-center">
                        <svg class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="font-bold">Aksi Ditolak</p>
                            <p>{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Search Bar Section (UPGRADED) --}}
                    <div class="mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
                        <form action="{{ route('books.index') }}" method="GET" class="w-full md:w-1/2" id="searchForm">
                            <div class="flex gap-2">
                                {{-- Input Search --}}
                                <div class="relative flex-1">
                                    <input type="text" id="searchInput" name="search" value="{{ request('search') }}"
                                        placeholder="Scan Barcode / Ketik Judul..."
                                        class="w-full rounded-md border-gray-300 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pl-10 h-10">
                                </div>

                                {{-- Tombol Scan --}}
                                <button type="button" onclick="startSearchScanner()" class="bg-yellow-500 text-white w-10 h-10 rounded-md hover:bg-yellow-600 shadow-sm transition flex items-center justify-center flex-shrink-0" title="Scan Barcode Buku">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                                </button>

                                <button type="submit" class="bg-gray-800 hover:bg-gray-700 text-white px-4 py-2 rounded-md h-10 transition">
                                    Cari
                                </button>

                                @if(request('search'))
                                    <a href="{{ route('books.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md flex items-center h-10 transition">Reset</a>
                                @endif
                            </div>
                        </form>
                    </div>

                    {{-- Tabel Modern --}}
                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Buku Info</th>
                                    <th scope="col" class="px-6 py-3">Lokasi</th>
                                    <th scope="col" class="px-6 py-3 text-center">Stok</th>
                                    <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($books as $book)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition">

                                    {{-- Kolom Info Buku --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            {{-- LOGIKA TAMPILAN GAMBAR --}}
                                            <div class="flex-shrink-0 h-14 w-10 mr-3">
                                                @if($book->cover_image)
                                                    <img class="h-14 w-10 object-cover rounded shadow cursor-pointer hover:scale-150 transition transform origin-left"
                                                        src="{{ asset('storage/' . $book->cover_image) }}"
                                                        alt="{{ $book->judul }}">
                                                @else
                                                    {{-- Placeholder jika tidak ada gambar --}}
                                                    <div class="h-14 w-10 bg-indigo-100 text-indigo-500 rounded flex items-center justify-center font-bold text-xs border border-indigo-200">
                                                        {{ strtoupper(substr($book->judul, 0, 1)) }}
                                                    </div>
                                                @endif
                                            </div>

                                            <div>
                                                <div class="text-base font-semibold text-gray-900 dark:text-white">{{ $book->judul }}</div>
                                                <div class="text-xs text-gray-500 font-mono">ISBN: {{ $book->kode_buku }}</div>
                                                <div class="text-xs font-bold text-green-600 mt-1">Rp {{ number_format($book->harga, 0, ',', '.') }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Kolom Lokasi --}}
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $book->shelf->nama_rak }}</div>
                                        <div class="text-xs text-gray-500">{{ $book->shelf->lokasi }}</div>
                                    </td>

                                    {{-- Kolom Stok --}}
                                    <td class="px-6 py-4 text-center">
                                        @if($book->stok_tersedia == 0)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Habis
                                            </span>
                                        @elseif($book->stok_tersedia < 3)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Sisa {{ $book->stok_tersedia }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                {{ $book->stok_tersedia }} / {{ $book->stok_total }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Kolom Aksi --}}
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex justify-center space-x-2">
                                            <a href="{{ route('books.edit', $book->id) }}" class="text-white bg-yellow-400 hover:bg-yellow-500 focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium rounded-lg px-3 py-1.5 text-center dark:focus:ring-yellow-900 transition">
                                                Edit
                                            </a>

                                            {{-- Tombol Hapus dengan Trigger Modal --}}
                                            <button type="button"
                                                onclick="openDeleteModal('{{ route('books.destroy', $book->id) }}', '{{ $book->judul }}')"
                                                class="text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg px-3 py-1.5 text-center transition">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                        Tidak ada data buku yang ditemukan.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-4">
                        {{ $books->withQueryString()->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- MODAL DELETE (Sama seperti sebelumnya) --}}
    <div id="deleteModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeDeleteModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
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
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Hapus Data Buku</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-300">
                                        Apakah Anda yakin ingin memindahkan <span id="deleteName" class="font-bold"></span> ke Sampah?
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 sm:ml-3 sm:w-auto sm:text-sm">Ya, Hapus</button>
                        <button type="button" onclick="closeDeleteModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL SCANNER PENCARIAN --}}
    <div id="searchScannerModal" class="hidden fixed inset-0 bg-black/90 z-[60] flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-md overflow-hidden relative shadow-2xl">
            <div class="bg-gray-100 p-4 flex justify-between items-center border-b">
                <h3 class="font-bold text-lg text-gray-800">Scan Barcode Buku</h3>
                <button onclick="stopSearchScanner()" class="text-gray-500 hover:text-red-500 font-bold text-2xl">&times;</button>
            </div>
            <div id="searchReader" class="w-full h-64 bg-black"></div>
            <div class="p-6 text-center bg-white">
                <p class="text-sm text-gray-500 mb-4">Arahkan barcode buku ke kamera.</p>
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
                    html5QrCodeSearch.start(devices[0].id, { fps: 10, qrbox: {width: 250, height: 150} }, // Bentuk kotak landscape untuk barcode buku
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
