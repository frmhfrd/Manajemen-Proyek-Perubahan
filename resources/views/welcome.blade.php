<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Perpus Ceria - SDN 6 Singaparna</title>

    {{-- CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@400;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Comic Neue', sans-serif; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .btn-press:active { transform: scale(0.95); }

        /* Animasi Modal */
        @keyframes popIn { 0% { transform: scale(0.8); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }
        .animate-pop-in { animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }

        #search-reader video { object-fit: cover; width: 100% !important; height: 100% !important; border-radius: 1.5rem; }
    </style>
</head>
<body class="bg-indigo-50 text-gray-800 antialiased min-h-screen flex flex-col">

    {{-- NAVBAR --}}
    <nav class="bg-white shadow-lg sticky top-0 z-50 py-3 md:py-4">
        <div class="max-w-7xl mx-auto px-3 md:px-4">
            <div class="flex gap-2 md:gap-4 items-center">

                {{-- Logo --}}
                <a href="/" class="flex-shrink-0 bg-indigo-600 text-white rounded-2xl w-12 h-12 md:w-14 md:h-14 flex items-center justify-center shadow-lg btn-press">
                    <svg class="w-6 h-6 md:w-8 md:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                </a>

                {{-- SEARCH BAR --}}
                <form action="{{ route('home') }}" class="flex-1 flex gap-2">
                    <div class="relative flex-1">
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari judul buku..."
                            class="w-full h-12 md:h-14 pl-4 md:pl-6 pr-14 md:pr-16 rounded-2xl border-2 border-indigo-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200 text-lg md:text-xl font-bold shadow-inner placeholder-gray-400">

                        <button type="button" onclick="openSearchScanner()" class="absolute right-1 top-1 bottom-1 md:right-2 md:top-2 md:bottom-2 bg-yellow-400 hover:bg-yellow-500 text-yellow-900 rounded-xl px-3 flex items-center justify-center transition btn-press border-b-4 border-yellow-600" title="Scan Buku">
                            <svg class="w-6 h-6 md:w-8 md:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                        </button>
                    </div>

                    <button type="submit" class="hidden sm:block bg-indigo-600 text-white h-12 md:h-14 px-6 md:px-8 rounded-2xl font-bold text-lg shadow-lg border-b-4 border-indigo-800 btn-press">
                        CARI
                    </button>

                    @if(request('q') || request('cat'))
                    <a href="/" class="bg-red-500 text-white h-12 md:h-14 w-12 md:w-14 rounded-2xl flex items-center justify-center font-bold text-lg shadow-lg border-b-4 border-red-700 btn-press">X</a>
                    @endif
                </form>
            </div>
        </div>
    </nav>

    {{-- KONTEN UTAMA --}}
    <div class="flex-1 max-w-7xl mx-auto w-full px-3 md:px-4 py-4 md:py-6 space-y-6 md:space-y-8">

        {{-- 1. MENU AKSES CEPAT (Responsive Grid) --}}
        @if(!request('q') && !request('cat'))
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-6">

            {{-- Tombol Pinjam (INDIGO) --}}
            <a href="{{ route('public.kiosk-standby') }}" class="group relative bg-white overflow-hidden rounded-[2rem] shadow-xl border-b-8 border-indigo-200 btn-press p-6 flex flex-row sm:flex-col items-center justify-center text-center hover:bg-indigo-50 transition gap-4 sm:gap-0">
                <div class="w-16 h-16 md:w-24 md:h-24 bg-indigo-100 rounded-full flex items-center justify-center md:mb-4 text-4xl md:text-6xl group-hover:scale-110 transition shadow-inner">📖</div>
                <div class="text-left sm:text-center">
                    <h2 class="text-2xl md:text-3xl font-extrabold text-indigo-700">Pinjam Buku</h2>
                    <p class="text-gray-500 text-sm md:text-lg">Ambil buku, lalu scan di sini.</p>
                </div>
            </a>

            {{-- Tombol Kembali (HIJAU) --}}
            <a href="{{ route('public.kiosk-return') }}" class="group relative bg-white overflow-hidden rounded-[2rem] shadow-xl border-b-8 border-green-200 btn-press p-6 flex flex-row sm:flex-col items-center justify-center text-center hover:bg-green-50 transition gap-4 sm:gap-0">
                <div class="w-16 h-16 md:w-24 md:h-24 bg-green-100 rounded-full flex items-center justify-center md:mb-4 text-4xl md:text-6xl group-hover:scale-110 transition shadow-inner">🔄</div>
                <div class="text-left sm:text-center">
                    <h2 class="text-2xl md:text-3xl font-extrabold text-green-700">Kembalikan</h2>
                    <p class="text-gray-500 text-sm md:text-lg">Sudah selesai baca? Balikin yuk.</p>
                </div>
            </a>
        </div>
        @endif

        {{-- 2. KATEGORI --}}
        <div>
            <div class="flex justify-between items-end mb-3 md:mb-4">
                <h3 class="text-xl md:text-2xl font-bold text-gray-800">📚 Pilih Kategori</h3>
                <span class="text-xs md:text-sm text-gray-400">Geser &rarr;</span>
            </div>

            <div class="flex gap-3 md:gap-4 overflow-x-auto no-scrollbar pb-2 md:pb-4">
                <a href="{{ route('home') }}" class="flex-shrink-0 px-4 py-3 md:px-6 md:py-4 rounded-2xl font-bold text-base md:text-lg shadow-md transition border-b-4 btn-press {{ !request('cat') ? 'bg-indigo-600 text-white border-indigo-800' : 'bg-white text-gray-600 border-gray-200' }}">Semua</a>

                @foreach($categories as $index => $cat)
                @php
                    $colors = [['bg-pink-100', 'text-pink-700', 'border-pink-300'], ['bg-yellow-100', 'text-yellow-700', 'border-yellow-300'], ['bg-cyan-100', 'text-cyan-700', 'border-cyan-300'], ['bg-lime-100', 'text-lime-700', 'border-lime-300']];
                    $c = $colors[$index % 4];
                    $active = request('cat') == $cat->id;
                @endphp

                <a href="{{ route('home', ['cat' => $cat->id]) }}" class="flex-shrink-0 px-4 py-3 md:px-6 md:py-4 rounded-2xl font-bold text-base md:text-lg shadow-md transition border-b-4 btn-press flex items-center gap-2 {{ $active ? 'bg-gray-800 text-white border-black' : $c[0].' '.$c[1].' '.$c[2] }}">
                    <span>{{ ['🚀','🐉','⚽','🌿','🎨'][$index % 5] }}</span>
                    <span>{{ $cat->nama }}</span>
                </a>
                @endforeach
            </div>
        </div>

        {{-- 3. GRID BUKU --}}
        <div>
            <h3 class="text-xl md:text-2xl font-bold text-gray-800 mb-4 md:mb-6">
                {{ request('q') ? '🔍 Hasil: "'.request('q').'"' : '✨ Buku Seru Hari Ini' }}
            </h3>

            @if($books->count() > 0)
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
                @foreach($books as $book)
                <a href="{{ route('public.book.show', $book->id) }}" class="group bg-white rounded-2xl md:rounded-3xl shadow-lg border-2 border-transparent hover:border-indigo-400 overflow-hidden transition-all duration-300 hover:shadow-2xl btn-press flex flex-col h-full">
                    <div class="h-40 md:h-64 bg-gray-100 relative flex items-center justify-center overflow-hidden">
                        @if($book->cover_image)
                            <img src="{{ asset('storage/' . $book->cover_image) }}"
                                alt="{{ $book->judul }}"
                                class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                        @else
                            {{-- Fallback --}}
                            <div class="text-6xl md:text-8xl text-gray-300 font-bold select-none group-hover:scale-110 transition duration-500">
                                {{ substr($book->judul, 0, 1) }}
                            </div>
                        @endif
                        <div class="absolute top-2 right-2 md:top-3 md:right-3 bg-black/70 text-white px-2 py-1 md:px-3 md:py-1 rounded-full text-[10px] md:text-xs font-bold backdrop-blur">Rak {{ $book->shelf->nama_rak ?? '?' }}</div>
                    </div>

                    <div class="p-3 md:p-5 flex-1 flex flex-col justify-between">
                        <div>
                            <h4 class="font-bold text-base md:text-lg text-gray-900 leading-tight line-clamp-2 mb-1">{{ $book->judul }}</h4>
                            <p class="text-xs md:text-sm text-gray-500 mb-3 truncate">✍️ {{ $book->pengarang }}</p>
                        </div>
                        <div class="flex items-center justify-between mt-auto">
                            @if($book->stok_tersedia > 0)
                                <div class="bg-green-100 text-green-700 px-2 py-1 md:px-3 rounded-lg text-xs md:text-sm font-bold">Ada {{ $book->stok_tersedia }}</div>
                                <span class="w-8 h-8 md:w-10 md:h-10 bg-indigo-600 rounded-full flex items-center justify-center text-white shadow-lg text-sm md:text-base transition transform group-hover:rotate-12">➜</span>
                            @else
                                <div class="bg-red-100 text-red-700 px-2 py-1 rounded-lg text-xs font-bold w-full text-center">Habis</div>
                            @endif
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
            <div class="mt-8">{{ $books->withQueryString()->links() }}</div>
            @else
            <div class="text-center py-10 md:py-20 bg-white rounded-3xl border-4 border-indigo-50">
                <div class="text-4xl md:text-6xl mb-4">🙈</div>
                <h3 class="text-xl md:text-2xl font-bold text-gray-600">Yah, bukunya gak ketemu...</h3>
                <a href="/" class="inline-block mt-6 bg-indigo-600 text-white px-6 py-3 rounded-xl font-bold shadow-lg btn-press">Coba Reset</a>
            </div>
            @endif
        </div>
    </div>

    {{-- FOOTER --}}
    <footer class="bg-white border-t mt-auto py-6 md:py-8">
        <div class="max-w-7xl mx-auto px-4 flex justify-between items-center text-gray-400 text-xs md:text-sm">
            <div>&copy; {{ date('Y') }} SDN 6 Singaparna.</div>
            <a href="{{ route('login') }}" class="flex items-center gap-1 hover:text-indigo-500 opacity-50 hover:opacity-100 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                <span class="hidden sm:inline">Admin Area</span>
            </a>
        </div>
    </footer>

    {{-- MODAL SCANNER PENCARIAN --}}
    <div id="searchScannerModal" class="hidden fixed inset-0 bg-black/90 z-[60] flex items-center justify-center p-4">
        <div class="bg-white rounded-[2.5rem] w-full max-w-lg overflow-hidden relative shadow-2xl animate-pop-in">
            <div class="bg-gray-100 p-4 flex justify-between items-center border-b">
                <h3 class="font-bold text-xl text-gray-800 pl-2">🔍 Scan Buku</h3>
                <button onclick="closeSearchScanner()" class="bg-red-100 text-red-600 w-10 h-10 rounded-full flex items-center justify-center font-bold text-xl btn-press">&times;</button>
            </div>

            <div class="p-6">
                <div class="relative w-full h-64 md:h-80 bg-black rounded-3xl overflow-hidden border-4 border-indigo-100 shadow-inner">
                    <div id="search-reader" class="w-full h-full object-cover"></div>
                    <div class="absolute inset-0 border-4 border-indigo-500/30 rounded-3xl pointer-events-none"></div>
                </div>
                <div class="mt-6 text-center">
                    <p class="text-indigo-600 font-medium">Arahkan kamera ke barcode buku.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- 🛑 MODAL ERROR (BARU: PENGGANTI ALERT) --}}
    <div id="errorModal" class="hidden fixed inset-0 bg-black/90 z-[90] flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white rounded-[2.5rem] w-full max-w-sm p-6 text-center shadow-2xl animate-pop-in relative">
            <div class="text-6xl mb-4">🙈</div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Tidak Ditemukan</h3>
            <p class="text-gray-500 mb-6">Maaf, buku dengan kode tersebut tidak ada di data kami.</p>
            <button onclick="closeErrorModal()" class="w-full bg-indigo-100 text-indigo-700 font-bold py-3 rounded-xl hover:bg-indigo-200 transition">Tutup</button>
        </div>
    </div>

    <script>
        let searchHtml5Qrcode = null;

        function openSearchScanner() {
            document.getElementById('searchScannerModal').classList.remove('hidden');
            searchHtml5Qrcode = new Html5Qrcode("search-reader");
            Html5Qrcode.getCameras().then(devices => {
                if(devices && devices.length) {
                    searchHtml5Qrcode.start(devices[0].id, { fps: 10, qrbox: {width: 250, height: 250} }, (decodedText) => { checkBookAndRedirect(decodedText); }, () => {});
                }
            });
        }

        function closeSearchScanner() {
            if(searchHtml5Qrcode) {
                searchHtml5Qrcode.stop().then(() => {
                    document.getElementById('searchScannerModal').classList.add('hidden');
                }).catch(err => {
                    document.getElementById('searchScannerModal').classList.add('hidden');
                });
            } else {
                document.getElementById('searchScannerModal').classList.add('hidden');
            }
        }

        function checkBookAndRedirect(code) {
            fetch('{{ route("public.check-book") }}', {
                method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify({ book_code: code })
            }).then(res => res.json()).then(data => {
                if(data.status === 'success') {
                    // Redirect jika sukses
                    window.location.href = "/buku/" + data.data.id;
                } else {
                    // Tampilkan Modal Error (Bukan Alert)
                    document.getElementById('errorModal').classList.remove('hidden');
                }
                closeSearchScanner();
            });
        }

        // Fungsi Tutup Modal Error
        function closeErrorModal() {
            document.getElementById('errorModal').classList.add('hidden');
        }
    </script>
</body>
</html>
