<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Form Stock Opname (Metode Pengecualian)</h2>
            <a href="{{ route('stock-opnames.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form id="opnameForm" action="{{ route('stock-opnames.store') }}" method="POST">
                @csrf

                @if(isset($todayOpname))
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 rounded shadow-sm">
                    <div class="flex">
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong>Mode Lanjutan:</strong> Melanjutkan laporan hari ini.
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Header Form --}}
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow mb-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 font-bold mb-2">Tanggal Cek</label>
                            <input type="date" name="tgl_opname"
                                value="{{ isset($todayOpname) ? $todayOpname->tgl_opname : date('Y-m-d') }}"
                                class="w-full rounded bg-gray-100 dark:bg-gray-700 dark:text-white border-gray-300 cursor-not-allowed"
                                readonly>
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 font-bold mb-2">Catatan</label>
                            <input type="text" name="catatan"
                                value="{{ isset($todayOpname) ? $todayOpname->catatan : '' }}"
                                placeholder="Contoh: Cek rutin akhir tahun"
                                class="w-full rounded bg-gray-50 dark:bg-gray-700 dark:text-white border-gray-300">
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 relative">

                    {{-- Search & Scan --}}
                    <div class="flex flex-col md:flex-row justify-between items-center mb-4 sticky top-0 bg-white dark:bg-gray-800 z-10 py-2 border-b dark:border-gray-700">
                        <h3 class="font-bold text-lg text-gray-800 dark:text-white">Input Data Pengecualian</h3>
                        <div class="flex gap-2 w-full md:w-1/2">
                            <input type="text" id="bookSearch" placeholder="Cari Judul..." class="w-full rounded-md border-gray-300 shadow-sm">
                            <button type="button" onclick="startScanner()" class="bg-yellow-500 text-white px-3 rounded-md hover:bg-yellow-600">Scan</button>
                        </div>
                    </div>

                    {{-- INFO CARA KERJA --}}
                    <div class="mb-4 bg-blue-50 text-blue-800 p-3 rounded text-sm flex gap-2 items-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>
                            <strong>Cara Input:</strong> Cukup isi kolom <strong>RUSAK</strong> dan <strong>HILANG</strong>. Kolom <strong>BAGUS</strong> akan terisi otomatis (Sisa dari Total).
                        </span>
                    </div>

                    {{-- TABEL INPUT --}}
                    <div class="overflow-x-auto max-h-[500px]">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-3">Buku</th>
                                    <th class="px-4 py-3 text-center">Total Aset</th>
                                    <th class="px-4 py-3 text-center">Dipinjam</th>
                                    <th class="px-4 py-3 text-center w-32">Fisik RUSAK</th>
                                    <th class="px-4 py-3 text-center w-32">HILANG</th>
                                    <th class="px-4 py-3 text-center w-32">Sisa BAGUS</th>
                                </tr>
                            </thead>
                            <tbody id="bookTableBody" class="bg-white dark:bg-gray-800">
                                @foreach($books as $book)
                                @php
                                    $dipinjam = \App\Models\LoanDetail::where('book_id', $book->id)->where('status_item', 'dipinjam')->count();

                                    // Logika Nilai Awal
                                    $valRusak = 0;
                                    $valBagus = max(0, $book->stok_total - $dipinjam - $book->stok_rusak);
                                    $valHilang = 0;

                                    if (isset($riwayatInput) && isset($riwayatInput[$book->id])) {
                                        $valBagus = $riwayatInput[$book->id]['bagus'];
                                        $valRusak = $riwayatInput[$book->id]['rusak'];
                                        $valHilang = max(0, $book->stok_total - $dipinjam - $valBagus - $valRusak);
                                    } else {
                                        $valRusak = $book->stok_rusak;
                                        $valHilang = $book->stok_hilang;
                                        $valBagus = max(0, $book->stok_total - $dipinjam - $valRusak - $valHilang);
                                    }
                                @endphp
                                <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition book-row"
                                    data-search="{{ strtolower($book->judul . ' ' . $book->kode_buku) }}">

                                    {{-- 1. BUKU --}}
                                    <td class="px-4 py-3 align-middle bg-white dark:bg-gray-800">
                                        <div class="font-bold text-gray-900 dark:text-white book-title text-base">{{ $book->judul }}</div>
                                        <div class="text-xs font-mono text-gray-500 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded w-fit mt-1">
                                            {{ $book->kode_buku }}
                                        </div>
                                    </td>

                                    {{-- 2. TOTAL ASET --}}
                                    <td class="px-4 py-3 text-center font-bold text-gray-400 bg-gray-50 dark:bg-gray-900/50 border-r dark:border-gray-700 text-lg">
                                        {{ $book->stok_total }}
                                    </td>

                                    {{-- 3. DIPINJAM --}}
                                    <td class="px-4 py-3 text-center font-bold border-r dark:border-gray-700 text-lg {{ $dipinjam > 0 ? 'text-orange-700 bg-orange-50 dark:bg-orange-900/20' : 'text-gray-300' }}">
                                        {{ $dipinjam }}
                                    </td>

                                    {{-- 4. INPUT RUSAK (Merah jika > 0) --}}
                                    <td class="px-4 py-2 border-r dark:border-gray-700 align-middle td-rusak {{ $valRusak > 0 ? 'bg-yellow-50 dark:bg-yellow-900/20' : '' }}">
                                        <input type="number" name="fisik_rusak[{{ $book->id }}]"
                                            value="{{ $valRusak }}" min="0"
                                            class="input-rusak w-full text-center font-bold border rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 bg-white shadow-sm transition-colors {{ $valRusak > 0 ? 'border-yellow-200 text-yellow-700' : 'border-gray-200 text-gray-300' }}"
                                            placeholder="0">
                                    </td>

                                    {{-- 5. INPUT HILANG (Merah pekat jika > 0) --}}
                                    <td class="px-4 py-2 border-r dark:border-gray-700 align-middle td-hilang {{ $valHilang > 0 ? 'bg-red-50 dark:bg-red-900/20' : '' }}">
                                        <input type="number"
                                            value="{{ $valHilang }}" min="0"
                                            class="input-hilang w-full text-center font-bold border rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 bg-white shadow-sm transition-colors {{ $valHilang > 0 ? 'border-red-300 text-red-700' : 'border-gray-200 text-gray-300' }}"
                                            placeholder="0">
                                    </td>

                                    {{-- 6. SISA BAGUS (Hijau jika > 0) --}}
                                    <td class="px-4 py-2 align-middle td-bagus {{ $valBagus > 0 ? 'bg-green-50 dark:bg-green-900/20' : '' }}">
                                        <div class="relative">
                                            <input type="number" name="fisik_bagus[{{ $book->id }}]"
                                                value="{{ $valBagus }}"
                                                class="input-bagus w-full text-center font-extrabold border-transparent bg-transparent text-xl cursor-not-allowed focus:ring-0 transition-colors {{ $valBagus > 0 ? 'text-green-700' : 'text-gray-300' }}"
                                                readonly tabindex="-1">

                                            {{-- Badge Error jika minus --}}
                                            <div class="msg-error absolute -top-2 -right-2 px-2 py-0.5 rounded-full text-[10px] font-bold shadow-md hidden z-10 bg-red-600 text-white animate-pulse">
                                                Minus!
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div id="noResult" class="hidden text-center py-8 text-gray-400">Buku tidak ditemukan.</div>
                    </div>

                    <div class="mt-6 flex justify-end pt-4 border-t">
                        <button type="button" onclick="validateAndOpenModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded shadow-lg flex items-center gap-2">
                            Simpan Laporan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL SCANNER --}}
    <div id="scannerModal" class="hidden fixed inset-0 bg-black/90 z-[60] flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-md overflow-hidden relative">
            <div id="reader" class="w-full h-64 bg-black"></div>
            <button onclick="stopScanner()" class="w-full bg-gray-200 py-4 font-bold">Tutup Scanner</button>
        </div>
    </div>

    {{-- MODAL PERINGATAN (JIKA MINUS/SURPLUS) --}}
    <div id="surplusModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border-t-4 border-red-500">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-bold text-gray-900">Periksa Kembali Data!</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Jumlah (Rusak + Hilang) melebihi stok yang tersedia. Cek buku berikut:
                                </p>
                                <ul id="surplusList" class="mt-3 text-sm bg-red-50 border border-red-200 rounded-md p-2 max-h-40 overflow-y-auto list-disc pl-5 text-red-700 font-bold"></ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                    <button type="button" onclick="closeSurplusModal()" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:w-auto sm:text-sm">
                        Kembali & Perbaiki
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL KONFIRMASI STANDARD --}}
    <div id="confirmModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeConfirmModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Simpan Laporan?</h3>
                            <p class="text-sm text-gray-500 mt-2">Pastikan input Rusak & Hilang sudah benar. Sisa buku akan otomatis dianggap Bagus.</p>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse gap-2">
                    <button type="button" onclick="submitForm()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:w-auto sm:text-sm">Ya, Simpan</button>
                    <button type="button" onclick="closeConfirmModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">Batal</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // 1. FILTER SEARCH (Tetap Sama)
        const searchInput = document.getElementById('bookSearch');
        const tableRows = document.querySelectorAll('.book-row');
        const noResult = document.getElementById('noResult');
        searchInput.addEventListener('keyup', function() {
            const keyword = this.value.toLowerCase();
            let visibleCount = 0;
            tableRows.forEach(row => {
                const text = row.getAttribute('data-search');
                if(text.includes(keyword)) { row.style.display = ''; visibleCount++; }
                else { row.style.display = 'none'; }
            });
            if(visibleCount === 0) noResult.classList.remove('hidden'); else noResult.classList.add('hidden');
        });

        // 2. LOGIKA UTAMA: HITUNG STOCK & BATASI INPUT (FIXED BUG)
        function calculateStock(row, changedInput = null) {
            // Ambil elemen input
            const inputRusak  = row.querySelector('.input-rusak');
            const inputHilang = row.querySelector('.input-hilang');
            const inputBagus  = row.querySelector('.input-bagus');
            const msgError    = row.querySelector('.msg-error');

            // Ambil TD untuk background styling
            const tdRusak  = row.querySelector('.td-rusak');
            const tdHilang = row.querySelector('.td-hilang');
            const tdBagus  = row.querySelector('.td-bagus');

            // Ambil Data Referensi (Stok Sistem & Dipinjam)
            // Menggunakan index kolom yang pasti sesuai tabel terakhir
            const stokSistemText = row.children[1].innerText.trim();
            const dipinjamText   = row.children[2].innerText.trim();
            const stokSistem = parseInt(stokSistemText) || 0;
            const dipinjam   = parseInt(dipinjamText) || 0;

            // Hitung Stok Tersedia di Rak (Max Limit)
            const maxAvailable = Math.max(0, stokSistem - dipinjam);

            // Ambil nilai input saat ini (Cegah minus dengan Math.max 0)
            let valRusak   = Math.max(0, parseInt(inputRusak.value) || 0);
            let valHilang  = Math.max(0, parseInt(inputHilang.value) || 0);

            // --- LOGIKA PEMBATASAN CERDAS (SMART CLIPPING) ---

            if (changedInput) {
                // KASUS 1: User sedang mengetik (Interaktif)
                if (changedInput.classList.contains('input-rusak')) {
                    // User mengetik RUSAK.
                    // Hitung sisa ruang yang tersedia setelah dikurangi HILANG yang sudah ada.
                    let limitForRusak = Math.max(0, maxAvailable - valHilang);

                    // Jika ketikan user melebihi sisa ruang, potong paksa!
                    if (valRusak > limitForRusak) {
                        valRusak = limitForRusak;
                        inputRusak.value = valRusak; // Kembalikan angka ke batas maksimal
                    }
                }
                else if (changedInput.classList.contains('input-hilang')) {
                    // User mengetik HILANG.
                    // Hitung sisa ruang yang tersedia setelah dikurangi RUSAK yang sudah ada.
                    let limitForHilang = Math.max(0, maxAvailable - valRusak);

                    // Jika ketikan user melebihi sisa ruang, potong paksa!
                    if (valHilang > limitForHilang) {
                        valHilang = limitForHilang;
                        inputHilang.value = valHilang; // Kembalikan angka ke batas maksimal
                    }
                }
            } else {
                // KASUS 2: Initial Load (Saat halaman baru dibuka)
                // Prioritaskan validasi sederhana, potong jika total melebihi.
                // Kita prioritaskan RUSAK diamankan dulu.
                if (valRusak > maxAvailable) {
                    valRusak = maxAvailable;
                    inputRusak.value = valRusak;
                }
                // Lalu Hilang menyesuaikan sisanya
                let limitForHilang = Math.max(0, maxAvailable - valRusak);
                if (valHilang > limitForHilang) {
                    valHilang = limitForHilang;
                    inputHilang.value = valHilang;
                }
            }

            // Set Atribut 'Max' pada HTML agar tombol spinner (panah) macet otomatis
            inputRusak.max  = Math.max(0, maxAvailable - valHilang);
            inputHilang.max = Math.max(0, maxAvailable - valRusak);

            // Hitung Ulang Sisa Bagus setelah koreksi
            let sisaBagus = maxAvailable - valRusak - valHilang;

            // --- UPDATE UI WARNA (Sama seperti sebelumnya) ---

            // A. Update Warna Rusak
            if (valRusak > 0) {
                tdRusak.classList.add('bg-yellow-50', 'dark:bg-yellow-900/20');
                inputRusak.classList.remove('border-gray-200', 'text-gray-300');
                inputRusak.classList.add('border-yellow-200', 'text-yellow-700');
            } else {
                tdRusak.classList.remove('bg-yellow-50', 'dark:bg-yellow-900/20');
                inputRusak.classList.remove('border-yellow-200', 'text-yellow-700');
                inputRusak.classList.add('border-gray-200', 'text-gray-300');
            }

            // B. Update Warna Hilang
            if (valHilang > 0) {
                tdHilang.classList.add('bg-red-50', 'dark:bg-red-900/20');
                inputHilang.classList.remove('border-gray-200', 'text-gray-300');
                inputHilang.classList.add('border-red-300', 'text-red-700');
            } else {
                tdHilang.classList.remove('bg-red-50', 'dark:bg-red-900/20');
                inputHilang.classList.remove('border-red-300', 'text-red-700');
                inputHilang.classList.add('border-gray-200', 'text-gray-300');
            }

            // C. Update Sisa Bagus
            inputBagus.value = sisaBagus;

            tdBagus.classList.remove('bg-green-50', 'dark:bg-green-900/20');
            inputBagus.classList.remove('text-green-700', 'font-extrabold', 'text-gray-300');

            if (sisaBagus > 0) {
                tdBagus.classList.add('bg-green-50', 'dark:bg-green-900/20');
                inputBagus.classList.add('text-green-700', 'font-extrabold');
            } else {
                inputBagus.classList.add('text-gray-300'); // Habis (0)
            }
        }

        // Pasang Event Listener saat Load
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll('.book-row').forEach(row => calculateStock(row));
        });

        // Event Listener Interaktif
        document.querySelectorAll('input[type="number"]').forEach(input => {
            // Deteksi saat user mengetik
            input.addEventListener('input', function(e) {
                const row = e.target.closest('tr');
                calculateStock(row, e.target); // PENTING: Kirim e.target agar tahu siapa yg diketik
            });
            // Deteksi saat user klik panah spinner (kadang trigger change)
            input.addEventListener('change', function(e) {
                const row = e.target.closest('tr');
                calculateStock(row, e.target);
            });
        });

        // 3. VALIDASI SIMPAN (Security Check Terakhir)
        function validateAndOpenModal() {
            let errorBooks = [];
            document.querySelectorAll('.book-row').forEach(row => {
                const inputBagus = row.querySelector('.input-bagus');
                const title = row.querySelector('.book-title').innerText;
                // Double check: kalau masih ada minus (harusnya mustahil dgn script di atas), blokir.
                if (parseInt(inputBagus.value) < 0) {
                    errorBooks.push(title);
                }
            });

            if (errorBooks.length > 0) {
                const listElem = document.getElementById('surplusList');
                listElem.innerHTML = '';
                errorBooks.forEach(item => {
                    let li = document.createElement('li');
                    li.innerText = item;
                    listElem.appendChild(li);
                });
                document.getElementById('surplusModal').classList.remove('hidden');
            } else {
                openConfirmModal();
            }
        }

        // Modal Helpers
        function closeSurplusModal() { document.getElementById('surplusModal').classList.add('hidden'); }
        function openConfirmModal() { document.getElementById('confirmModal').classList.remove('hidden'); }
        function closeConfirmModal() { document.getElementById('confirmModal').classList.add('hidden'); }
        function submitForm() { document.getElementById('opnameForm').submit(); }

        // Scanner
        let html5QrCode = null;
        function startScanner() {
            document.getElementById('scannerModal').classList.remove('hidden');
            html5QrCode = new Html5Qrcode("reader");
            Html5Qrcode.getCameras().then(devices => {
                if(devices && devices.length) {
                    html5QrCode.start(devices[0].id, { fps: 10, qrbox: {width: 250, height: 150} }, (decodedText) => {
                        stopScanner();
                        searchInput.value = decodedText;
                        searchInput.dispatchEvent(new Event('keyup'));
                        alert('Buku Ditemukan!');
                    }, () => {});
                }
            });
        }
        function stopScanner() {
            if(html5QrCode) html5QrCode.stop();
            document.getElementById('scannerModal').classList.add('hidden');
        }
    </script>
</x-app-layout>
