<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Buat Peminjaman Baru') }}
            </h2>
            <a href="{{ route('loans.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    {{-- Load Library Scanner --}}
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Pesan Error --}}
            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm">
                    <p class="font-bold">Gagal</p>
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <form action="{{ route('loans.store') }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

                    {{-- === KOLOM KIRI: PILIH ANGGOTA (Sticky) === --}}
                    <div class="lg:col-span-1 lg:sticky lg:top-6 space-y-6">

                        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                            <label class="block font-bold text-lg text-indigo-900 dark:text-indigo-200 mb-4 flex items-center gap-2">
                                <span class="bg-indigo-100 text-indigo-700 w-8 h-8 rounded-full flex items-center justify-center text-sm">1</span>
                                Pilih Anggota
                            </label>

                            {{-- Input Pencarian Anggota --}}
                            <div class="relative flex gap-2">
                                <div class="relative flex-1">
                                    {{-- Input dengan padding kiri agar tidak menimpa icon --}}
                                    <input type="text" id="memberSearch" placeholder="Ketik Nama / Scan..."
                                        class="w-full h-11 rounded-lg border-gray-300 dark:bg-gray-900 focus:ring-indigo-500 pl-10 shadow-sm text-sm" autocomplete="off">

                                    {{-- Icon Kaca Pembesar (Centered) --}}
                                    <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                    </div>
                                </div>

                                {{-- Tombol Scan --}}
                                <button type="button" onclick="startCamera('member')" class="bg-indigo-600 text-white w-11 h-11 rounded-lg hover:bg-indigo-700 shadow-md transition flex items-center justify-center flex-shrink-0" title="Scan Kartu Anggota">
                                    <svg class="w-6 h-6 md:w-8 md:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                                </button>
                            </div>

                            {{-- Dropdown Hasil --}}
                            <div id="memberList" class="hidden absolute z-30 w-64 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-xl mt-1 max-h-60 overflow-y-auto"></div>

                            <input type="hidden" name="member_id" id="selectedMemberId" required>

                            {{-- Info Member Terpilih (Muncul Otomatis setelah Scan/Pilih) --}}
                            <div id="selectedMemberCard" class="hidden mt-4 bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border border-green-200 dark:border-green-800 relative animate-pulse">
                                <p class="text-xs text-green-600 font-bold uppercase tracking-wider mb-1">Anggota Terpilih:</p>
                                <p class="font-bold text-gray-800 dark:text-white" id="selectedMemberName">-</p>
                                <p class="text-xs text-gray-500" id="selectedMemberClass">-</p>
                                <button type="button" onclick="resetMember()" class="absolute top-2 right-2 text-red-500 hover:text-red-700 text-xs underline">Ganti</button>
                            </div>
                        </div>

                        {{-- Tombol Submit --}}
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 px-6 rounded-xl shadow-lg transition transform hover:-translate-y-1 flex items-center justify-center gap-2">
                            <span>✅</span> PROSES PEMINJAMAN
                        </button>
                    </div>

                    {{-- === KOLOM KANAN: PILIH BUKU === --}}
                    <div class="lg:col-span-2 bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">

                        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6 pb-4 border-b border-gray-100">
                            <label class="font-bold text-lg text-indigo-900 dark:text-indigo-200 flex items-center gap-2">
                                <span class="bg-indigo-100 text-indigo-700 w-8 h-8 rounded-full flex items-center justify-center text-sm">2</span>
                                Pilih Buku
                            </label>

                            <div class="flex gap-2 w-full sm:w-auto">
                                <div class="relative flex-1 sm:w-64">
                                    <input type="text" id="bookSearch" placeholder="Cari Judul / Kode..."
                                        class="w-full h-11 rounded-lg border-gray-300 dark:bg-gray-900 focus:ring-indigo-500 pl-10 pr-4 text-sm shadow-sm">

                                    <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                    </div>
                                </div>
                                <button type="button" onclick="startCamera('book')" class="bg-yellow-500 text-white w-11 h-11 rounded-lg hover:bg-yellow-600 shadow-md transition flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 md:w-8 md:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                                </button>
                            </div>
                        </div>

                        {{-- CONTAINER SCROLL BUKU (Aman, Max Height 500px) --}}
                        <div class="overflow-y-auto pr-2 custom-scrollbar max-h-[500px] bg-gray-50 dark:bg-gray-900 rounded-xl p-3 border border-gray-200 dark:border-gray-700">

                            <div id="emptyBookMsg" class="hidden py-10 flex flex-col items-center justify-center text-gray-400">
                                <svg class="w-12 h-12 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <p>Buku tidak ditemukan.</p>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="bookGrid">
                                @foreach($books as $book)
                                <label class="book-item relative flex items-center p-3 rounded-lg border border-gray-200 bg-white hover:border-indigo-400 cursor-pointer transition shadow-sm group select-none h-20"
                                       data-search="{{ strtolower($book->judul . ' ' . $book->kode_buku) }}">

                                    {{-- Checkbox --}}
                                    <div class="flex items-center justify-center h-full px-2">
                                        <input type="checkbox" name="book_ids[]" value="{{ $book->id }}" class="w-5 h-5 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500 cursor-pointer">
                                    </div>

                                    {{-- Info --}}
                                    <div class="ml-3 flex-1 overflow-hidden">
                                        <span class="text-[10px] text-gray-400 font-mono mb-0.5 block">{{ $book->kode_buku }}</span>
                                        <span class="block text-sm font-bold text-gray-800 leading-tight mb-1 truncate group-hover:text-indigo-600" title="{{ $book->judul }}">
                                            {{ $book->judul }}
                                        </span>
                                        <div class="flex justify-between items-center mt-1">
                                            <span class="text-xs text-gray-500 truncate max-w-[120px]">{{ $book->pengarang }}</span>
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded {{ $book->stok_tersedia > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                                Stok: {{ $book->stok_tersedia }}
                                            </span>
                                        </div>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- MODAL SCANNER (Sama seperti halaman lain) --}}
    <div id="scannerModal" class="hidden fixed inset-0 bg-black/90 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-md overflow-hidden relative shadow-2xl">
            <div class="bg-gray-100 p-4 flex justify-between items-center border-b">
                <h3 class="font-bold text-lg text-gray-800">Scan Barcode</h3>
                <button onclick="stopCamera()" class="text-gray-500 hover:text-red-500 font-bold text-2xl">&times;</button>
            </div>
            <div id="reader" class="w-full h-64 bg-black"></div>
            <div class="p-6 text-center bg-white">
                <p class="text-sm text-gray-500 mb-4">Arahkan kode ke kamera.</p>
                <button onclick="stopCamera()" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg font-bold hover:bg-gray-300 w-full">Batal</button>
            </div>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #15803d; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #59a574; }
    </style>

    <script>
        const members = @json($members);
        const memberInput = document.getElementById('memberSearch');
        const memberList = document.getElementById('memberList');
        const hiddenId = document.getElementById('selectedMemberId');
        const cardInfo = document.getElementById('selectedMemberCard');
        const bookInput = document.getElementById('bookSearch');
        const bookItems = document.querySelectorAll('.book-item');
        const emptyMsg = document.getElementById('emptyBookMsg');

        // MEMBER SEARCH
        memberInput.addEventListener('keyup', function() {
            const keyword = this.value.toLowerCase();
            memberList.innerHTML = '';
            if (keyword.length < 1) { memberList.classList.add('hidden'); return; }
            const filtered = members.filter(m => m.nama_lengkap.toLowerCase().includes(keyword) || m.kode_anggota.toLowerCase().includes(keyword));
            if (filtered.length > 0) {
                memberList.classList.remove('hidden');
                filtered.forEach(m => {
                    const div = document.createElement('div');
                    div.className = "p-3 hover:bg-indigo-50 cursor-pointer border-b last:border-0 text-sm";
                    div.innerHTML = `<p class='font-bold text-gray-800'>${m.nama_lengkap}</p><p class='text-xs text-gray-500'>${m.kode_anggota}</p>`;
                    div.onclick = () => selectMember(m);
                    memberList.appendChild(div);
                });
            } else { memberList.classList.add('hidden'); }
        });

        function selectMember(m) {
            hiddenId.value = m.id;
            document.getElementById('selectedMemberName').innerText = m.nama_lengkap;
            document.getElementById('selectedMemberClass').innerText = m.kode_anggota + " | " + m.kelas;
            memberInput.value = '';
            memberList.classList.add('hidden');
            memberInput.parentElement.parentElement.classList.add('hidden');
            cardInfo.classList.remove('hidden');
        }

        function resetMember() {
            hiddenId.value = '';
            cardInfo.classList.add('hidden');
            memberInput.parentElement.parentElement.classList.remove('hidden');
            memberInput.focus();
        }

        // BOOK SEARCH
        bookInput.addEventListener('keyup', function() { filterBooks(this.value); });

        function filterBooks(keyword) {
            keyword = keyword.toLowerCase();
            let visibleCount = 0;
            bookItems.forEach(item => {
                const text = item.getAttribute('data-search');
                if (text.includes(keyword)) {
                    item.classList.remove('hidden');
                    visibleCount++;
                } else { item.classList.add('hidden'); }
            });
            if (visibleCount === 0) emptyMsg.classList.remove('hidden');
            else emptyMsg.classList.add('hidden');
        }

        // CAMERA (Update Logic)
        let html5QrCode = null;
        let currentScanType = null;

        function startCamera(type) {
            currentScanType = type;
            document.getElementById('scannerModal').classList.remove('hidden');
            html5QrCode = new Html5Qrcode("reader");
            Html5Qrcode.getCameras().then(devices => {
                if(devices && devices.length) {
                    html5QrCode.start(devices[0].id, { fps: 10, qrbox: {width: 250, height: 250} }, (decodedText) => handleScanSuccess(decodedText), () => {});
                }
            });
        }

        function stopCamera() {
            if(html5QrCode) {
                html5QrCode.stop().then(() => { document.getElementById('scannerModal').classList.add('hidden'); }).catch(() => { document.getElementById('scannerModal').classList.add('hidden'); });
            } else { document.getElementById('scannerModal').classList.add('hidden'); }
        }

        function handleScanSuccess(decodedText) {
            stopCamera(); // Langsung tutup kamera (Silent)

            if (currentScanType === 'member') {
                const found = members.find(m => m.kode_anggota == decodedText);
                if (found) {
                    // Langsung Pilih, Gak perlu Alert
                    selectMember(found);
                    // (Opsional) Play sound effect 'beep' here if needed
                } else {
                    alert('❌ Anggota tidak ditemukan!'); // Alert hanya kalau error
                }
            } else if (currentScanType === 'book') {
                bookInput.value = decodedText;
                filterBooks(decodedText);
            }
        }
    </script>
</x-app-layout>
