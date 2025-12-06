<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Sirkulasi Peminjaman') }}
            </h2>
            <a href="{{ route('loans.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-150 ease-in-out">
                + Transaksi Baru
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm">
                    <p class="font-bold">Berhasil</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Search Bar --}}
                    <form action="{{ route('loans.index') }}" method="GET" class="mb-6">
                        <div class="flex gap-2 max-w-md">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Kode Transaksi / Nama Siswa..."
                                class="w-full rounded-md border-gray-300 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-md hover:bg-gray-700">Cari</button>
                        </div>
                    </form>

                    {{-- Tabel Modern --}}
                    <div class="    ">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th class="px-6 py-3">Kode & Petugas</th>
                                    <th class="px-6 py-3">Peminjam</th>
                                    <th class="px-6 py-3">Tanggal</th>
                                    <th class="px-6 py-3 text-center">Buku</th>
                                    <th class="px-6 py-3 text-center">Status</th>
                                    <th class="px-6 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($loans as $loan)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900 dark:text-white">{{ $loan->kode_transaksi }}</div>
                                        <div class="text-xs">{{ $loan->user->name }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $loan->member->nama_lengkap }}</div>
                                        <span class="text-xs bg-gray-100 px-2 py-0.5 rounded">{{ $loan->member->kelas }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-xs text-gray-500">Pinjam: {{ $loan->tgl_pinjam->format('d M Y') }}</div>
                                        <div class="text-xs font-bold {{ $loan->tgl_wajib_kembali->isPast() && $loan->status_transaksi != 'selesai' ? 'text-red-600' : 'text-blue-600' }}">
                                            Tempo: {{ $loan->tgl_wajib_kembali->format('d M Y') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="bg-indigo-100 text-indigo-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                                            {{ $loan->details->count() }} Item
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($loan->status_transaksi == 'selesai')
                                            <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">Selesai</span>
                                        @elseif($loan->status_transaksi == 'terlambat')
                                            <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded">Terlambat</span>
                                        @else
                                            <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded">Berjalan</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($loan->status_transaksi != 'selesai')
                                            {{-- Tombol Trigger Modal (Perhatikan onclick-nya) --}}
                                            <button type="button"
                                                onclick="openReturnModal('{{ route('loans.return', $loan->id) }}', '{{ $loan->kode_transaksi }}', '{{ $loan->member->nama_lengkap }}')"
                                                class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-3 py-1.5 text-center shadow">
                                                Kembalikan
                                            </button>
                                        @else
                                            <span class="text-gray-400 text-xs">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">Belum ada transaksi.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $loans->withQueryString()->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- MODAL POP UP (Hidden by Default) --}}
    <div id="returnModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        {{-- Backdrop Gelap --}}
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeReturnModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            {{-- Konten Modal --}}
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="returnForm" method="POST" action="">
                    @csrf
                    @method('PUT')

                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                {{-- Icon Info --}}
                                <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                                    Konfirmasi Pengembalian
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-300">
                                        Anda akan memproses pengembalian untuk transaksi <span id="modalKode" class="font-bold"></span> atas nama <span id="modalNama" class="font-bold"></span>.
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-300 mt-2">
                                        Stok buku akan otomatis ditambahkan kembali ke inventaris. Pastikan fisik buku sudah dicek.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Ya, Kembalikan
                        </button>
                        <button type="button" onclick="closeReturnModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Javascript Sederhana untuk Modal --}}
    <script>
        function openReturnModal(url, kode, nama) {
            // Set Action URL Form
            document.getElementById('returnForm').action = url;
            // Set Teks Informasi
            document.getElementById('modalKode').innerText = kode;
            document.getElementById('modalNama').innerText = nama;
            // Tampilkan Modal
            document.getElementById('returnModal').classList.remove('hidden');
        }

        function closeReturnModal() {
            // Sembunyikan Modal
            document.getElementById('returnModal').classList.add('hidden');
        }
    </script>
</x-app-layout>
