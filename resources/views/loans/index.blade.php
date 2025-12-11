<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Sirkulasi Peminjaman') }}
            </h2>

            <div class="flex gap-2">
                {{-- Tombol Tambah (Biru) --}}
                <a href="{{ route('loans.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition">
                    + Transaksi Baru
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Alert Sukses --}}
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm">
                    <p class="font-bold">Berhasil</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            {{-- Alert Error --}}
            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm">
                    <p class="font-bold">Gagal</p>
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Header Tools: Search & Refresh --}}
                    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">

                        {{-- Kiri: Search Bar --}}
                        <form action="{{ route('loans.index') }}" method="GET" class="w-full md:w-1/2">
                            <div class="flex gap-2">
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Kode / Nama Siswa..."
                                    class="w-full rounded-md border-gray-300 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-md hover:bg-gray-700">Cari</button>
                            </div>
                        </form>

                        {{-- Kanan: Tombol Refresh Status Massal --}}
                        <a href="{{ route('loans.refresh_all') }}" class="flex items-center gap-2 bg-indigo-100 hover:bg-indigo-200 text-indigo-700 border border-indigo-300 font-bold py-2 px-4 rounded-lg transition shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Refresh Status Bayar
                        </a>
                    </div>

                    {{-- Tabel --}}
                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th class="px-6 py-3">Kode & Petugas</th>
                                    <th class="px-6 py-3">Peminjam</th>
                                    <th class="px-6 py-3">Jatuh Tempo</th>
                                    <th class="px-6 py-3 text-center">Buku</th>
                                    <th class="px-6 py-3 text-center">Status Sirkulasi</th>
                                    <th class="px-6 py-3 text-center">Status Denda</th>
                                    <th class="px-6 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($loans as $loan)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 transition">

                                    {{-- 1. Kode & Info Kiosk --}}
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900 dark:text-white">{{ $loan->kode_transaksi }}</div>
                                        <div class="text-xs mt-1">
                                            @if($loan->user_id == 3) {{-- ID User Kiosk --}}
                                                <span class="bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded border border-purple-200 font-bold">
                                                    🤖 Kiosk Mandiri
                                                </span>
                                            @else
                                                👤 {{ $loan->user->name }}
                                            @endif
                                        </div>
                                    </td>

                                    {{-- 2. Peminjam --}}
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $loan->member->nama_lengkap }}</div>
                                        <span class="text-xs bg-gray-100 px-2 py-0.5 rounded">{{ $loan->member->kelas }}</span>
                                    </td>

                                    {{-- 3. Tanggal --}}
                                    <td class="px-6 py-4">
                                        <div class="text-xs text-gray-400">Pinjam: {{ $loan->tgl_pinjam->format('d/m/y') }}</div>
                                        <div class="font-bold {{ $loan->tgl_wajib_kembali->isPast() && $loan->status_transaksi == 'berjalan' ? 'text-red-600' : 'text-blue-600' }}">
                                            {{ $loan->tgl_wajib_kembali->format('d M Y') }}
                                        </div>
                                    </td>

                                    {{-- 4. Jumlah Buku --}}
                                    <td class="px-6 py-4 text-center">
                                        <span class="bg-indigo-100 text-indigo-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                                            {{ $loan->details->count() }} Item
                                        </span>
                                    </td>

                                    {{-- 5. Status Sirkulasi --}}
                                    <td class="px-6 py-4 text-center">
                                        @if($loan->status_transaksi == 'selesai')
                                            <span class="inline-flex items-center gap-1 bg-green-100 text-green-800 text-xs font-bold px-2.5 py-1 rounded-full border border-green-200">
                                                Buku Kembali
                                            </span>
                                        @elseif($loan->tgl_wajib_kembali->isPast() && $loan->status_transaksi == 'berjalan')
                                            <span class="inline-flex items-center gap-1 bg-red-100 text-red-800 text-xs font-bold px-2.5 py-1 rounded-full border border-red-200 animate-pulse">
                                                Terlambat
                                            </span>
                                        @else
                                            <span class="bg-yellow-100 text-yellow-800 text-xs font-bold px-2.5 py-1 rounded-full border border-yellow-200">
                                                Dipinjam
                                            </span>
                                        @endif
                                    </td>

                                    {{-- 6. Status Denda --}}
                                    <td class="px-6 py-4 text-center">
                                        @if($loan->status_pembayaran == 'paid')
                                            <span class="bg-blue-100 text-blue-800 text-[10px] font-bold px-2 py-1 rounded border border-blue-200 block w-fit mx-auto">
                                                LUNAS
                                            </span>
                                        @elseif($loan->status_pembayaran == 'pending')
                                            <span class="bg-orange-100 text-orange-800 text-[10px] font-bold px-2 py-1 rounded border border-orange-200 block w-fit mx-auto">
                                                Menunggu Bayar
                                            </span>
                                        @elseif($loan->tgl_wajib_kembali->isPast() && $loan->status_transaksi == 'berjalan')
                                            <span class="text-gray-400 text-[10px] italic">Belum Tagih</span>
                                        @else
                                            <span class="text-gray-300 text-xs">-</span>
                                        @endif
                                    </td>

                                    {{-- 7. Aksi (SUDAH DIUPDATE) --}}
                                    <td class="px-6 py-4 text-center">
                                        {{-- GOLONGAN 1: Buku Belum Dikembalikan --}}
                                        {{-- Aksi: Tampilkan Tombol "Kembalikan" --}}
                                        @if($loan->status_transaksi != 'selesai')
                                            <button type="button"
                                                onclick="openReturnModal('{{ route('loans.return', $loan->id) }}', '{{ $loan->kode_transaksi }}', '{{ $loan->member->nama_lengkap }}', '{{ $loan->tgl_wajib_kembali->format('Y-m-d') }}', '{{ $loan->status_pembayaran }}')"
                                                class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-3 py-1.5 shadow">
                                                Kembalikan
                                            </button>

                                        {{-- GOLONGAN 2: Buku Sudah Kembali, TAPI Punya Utang Denda --}}
                                        {{-- Syarat: Status Selesai + Belum Lunas + Nominal Denda > 0 --}}
                                        {{-- Aksi: Tampilkan Tombol "Bayar Tunai" --}}
                                        @elseif($loan->status_transaksi == 'selesai' && $loan->status_pembayaran != 'paid' && $loan->total_denda > 0)
                                            <div class="flex flex-col gap-1 items-center">
                                                <span class="text-[10px] text-red-500 font-bold bg-red-50 px-1 rounded border border-red-100">
                                                    Denda: Rp {{ number_format($loan->total_denda) }}
                                                </span>

                                                <form action="{{ route('loans.pay-late-fine', $loan->id) }}" method="POST" onsubmit="return confirm('Terima pembayaran tunai Rp {{ number_format($loan->total_denda) }}?')">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="bg-green-100 text-green-700 border border-green-300 px-3 py-1 rounded text-xs font-bold hover:bg-green-200 transition shadow-sm animate-pulse">
                                                        💰 Bayar Tunai
                                                    </button>
                                                </form>
                                            </div>

                                        {{-- GOLONGAN 3: Sisanya (Tepat Waktu / Sudah Lunas) --}}
                                        {{-- Aksi: Tampilkan label "Selesai" --}}
                                        @else
                                            <span class="text-gray-300 text-xs font-bold select-none">Selesai</span>
                                        @endif

                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                        Belum ada data transaksi.
                                    </td>
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

    {{-- MODAL PENGEMBALIAN --}}
    <div id="returnModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeReturnModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="returnForm" method="POST" action="">
                    @csrf
                    @method('PUT')

                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Proses Pengembalian</h3>
                                <div class="mt-2 text-sm text-gray-500 dark:text-gray-300">
                                    <p>Kode: <span id="modalKode" class="font-bold"></span></p>
                                    <p>Peminjam: <span id="modalNama" class="font-bold"></span></p>
                                </div>

                                {{-- Area Denda --}}
                                <div id="dendaArea" class="hidden mt-4 bg-red-50 border border-red-200 rounded p-3">
                                    <p class="text-red-700 font-bold text-md">⚠️ TERLAMBAT <span id="telatHari">0</span> HARI</p>
                                    <p class="text-gray-600 text-sm mt-1">Total Denda:</p>
                                    <p class="text-2xl font-extrabold text-red-600 mt-1">Rp <span id="nominalDenda">0</span></p>

                                    <div class="mt-3 flex items-start">
                                        <div class="flex items-center h-5">
                                            <input id="konfirmasiBayar" type="checkbox" name="denda_lunas" required class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label id="labelBayar" for="konfirmasiBayar" class="font-medium text-gray-700">
                                                Saya menyatakan siswa SUDAH membayar lunas denda di atas.
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div id="infoNormal" class="mt-4 text-sm text-green-600 font-bold hidden">
                                    ✅ Tepat Waktu. Tidak ada denda.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm">
                            Proses Pengembalian
                        </button>
                        <button type="button" onclick="closeReturnModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const tarifDenda = {{ $dendaPerHari ?? 500 }};

        function openReturnModal(url, kode, nama, tglTempo, statusBayar) {
            document.getElementById('returnForm').action = url;
            document.getElementById('modalKode').innerText = kode;
            document.getElementById('modalNama').innerText = nama;

            const today = new Date(); today.setHours(0,0,0,0);
            const tempo = new Date(tglTempo); tempo.setHours(0,0,0,0);
            const diffTime = today - tempo;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            const dendaArea = document.getElementById('dendaArea');
            const infoNormal = document.getElementById('infoNormal');
            const checkbox = document.getElementById('konfirmasiBayar');

            // Reset Default: Hapus centang & HAPUS REQUIRED (Biar opsional)
            checkbox.checked = false;
            checkbox.removeAttribute('required'); // <--- INI KUNCINYA
            checkbox.disabled = false;
            document.getElementById('labelBayar').innerText = "Bayar tunai sekarang (Centang jika lunas)";

            if (diffDays > 0) {
                // KENA DENDA
                const totalDenda = diffDays * tarifDenda;
                dendaArea.classList.remove('hidden');
                infoNormal.classList.add('hidden');
                document.getElementById('telatHari').innerText = diffDays;
                document.getElementById('nominalDenda').innerText = new Intl.NumberFormat('id-ID').format(totalDenda);

                // Logika Status Pembayaran
                if (statusBayar === 'paid') {
                    // Jika sudah bayar via Online sebelumnya
                    checkbox.checked = true;
                    checkbox.disabled = true;
                    document.getElementById('labelBayar').innerHTML = "<span class='text-green-600 font-bold'>SUDAH LUNAS VIA MIDTRANS ✅</span>";
                } else {
                    // Jika belum bayar:
                    // JANGAN DI-REQUIRED. Biarkan admin memilih mau centang (bayar) atau tidak (utang).
                    checkbox.removeAttribute('required');
                }

            } else {
                // TEPAT WAKTU
                dendaArea.classList.add('hidden');
                infoNormal.classList.remove('hidden');
            }
            document.getElementById('returnModal').classList.remove('hidden');
        }

        function closeReturnModal() {
            document.getElementById('returnModal').classList.add('hidden');
        }
    </script>
</x-app-layout>
