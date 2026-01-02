<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $book->judul }} - Detail Buku</title>

    {{-- CDN (Sama seperti halaman lain) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@400;700&display=swap" rel="stylesheet">

    <style>
        .btn-press:active { transform: scale(0.95); }
        body { font-family: 'Comic Neue', sans-serif; }
    </style>
</head>
<body class="bg-indigo-50 text-gray-800 antialiased min-h-screen flex flex-col">

    {{-- NAVBAR SIMPLE --}}
    <nav class="bg-white shadow-lg sticky top-0 z-50 py-4">
        <div class="max-w-6xl mx-auto px-4 flex items-center gap-4">
            <a href="/" class="bg-indigo-600 text-white rounded-2xl w-12 h-12 md:w-14 md:h-14 flex items-center justify-center shadow-lg btn-press hover:bg-indigo-700 transition">
                <svg class="w-6 h-6 md:w-8 md:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            </a>
            <h1 class="text-xl md:text-2xl font-bold text-indigo-900 truncate">Detail Buku</h1>
        </div>
    </nav>

    {{-- KONTEN UTAMA --}}
    <div class="max-w-6xl mx-auto px-4 py-6 md:py-10 w-full flex-1">

        <div class="bg-white rounded-[2.5rem] shadow-2xl overflow-hidden flex flex-col md:flex-row border-4 border-indigo-100">

            {{-- KOLOM KIRI: COVER JUMBO + EFEK BACKGROUND --}}
            <div class="md:w-5/12 bg-gray-100 flex items-center justify-center p-10 relative overflow-hidden min-h-[400px]">

                {{-- 1. BACKGROUND LAYER (GAMBAR BURAM) --}}
                @if($book->cover_image)
                    <div class="absolute inset-0 z-0">
                        {{-- Gambar diperbesar (scale-150) biar blur-nya gak bocor putih di pinggir --}}
                        <img src="{{ asset('storage/' . $book->cover_image) }}"
                             class="w-full h-full object-cover blur-2xl opacity-40 scale-150 filter grayscale-0">
                    </div>
                    {{-- Overlay gradasi biar teks/konten di atasnya tetap kontras --}}
                    <div class="absolute inset-0 bg-indigo-900/10 z-0"></div>
                @else
                    {{-- Fallback Pattern jika tidak ada gambar --}}
                    <div class="absolute inset-0 bg-indigo-50 opacity-50 z-0"
                         style="background-image: radial-gradient(#6366f1 1px, transparent 1px); background-size: 20px 20px;">
                    </div>
                @endif

                {{-- 2. MAIN IMAGE (Wadah Cover Asli) --}}
                {{-- z-10 agar muncul di atas background buram --}}
                <div class="relative z-10 w-64 md:w-72 h-auto bg-white shadow-2xl rotate-2 transform transition hover:rotate-0 duration-500 border-4 border-white rounded-2xl overflow-hidden group">
                    @if($book->cover_image)
                        <img src="{{ asset('storage/' . $book->cover_image) }}"
                             alt="{{ $book->judul }}"
                             class="w-full h-full object-cover">

                        {{-- Efek Kilau (Opsional) --}}
                        <div class="absolute inset-0 bg-gradient-to-tr from-white/20 to-transparent opacity-0 group-hover:opacity-100 transition duration-500"></div>
                    @else
                        <div class="h-80 flex flex-col items-center justify-center text-center p-6 bg-indigo-50">
                            <span class="text-8xl mb-2">📚</span>
                            <span class="text-indigo-300 font-bold text-lg">Tidak ada sampul</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- KOLOM KANAN: INFO & TOMBOL --}}
            <div class="md:w-7/12 p-8 md:p-12 flex flex-col justify-between bg-white relative">
                <div>
                    {{-- Badge Kategori --}}
                    <div class="flex items-center gap-2 mb-4">
                        <span class="inline-block bg-pink-100 text-pink-700 text-sm px-4 py-1.5 rounded-full font-bold border-2 border-pink-200 shadow-sm">
                            {{ $book->category->nama ?? 'Umum' }}
                        </span>
                        @if($book->stok_tersedia > 0)
                            <span class="inline-block bg-green-100 text-green-700 text-sm px-3 py-1.5 rounded-full font-bold border border-green-200">
                                Stok: {{ $book->stok_tersedia }}
                            </span>
                        @endif
                    </div>

                    <h1 class="text-3xl md:text-5xl font-extrabold text-gray-900 leading-tight mb-4">
                        {{ $book->judul }}
                    </h1>

                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center text-xl">✍️</div>
                        <div>
                            <p class="text-xs text-gray-500 font-bold uppercase tracking-wider">Penulis</p>
                            <p class="text-lg font-bold text-indigo-700">{{ $book->pengarang }}</p>
                        </div>
                    </div>

                    {{-- INFO RAK --}}
                    <div class="bg-yellow-50 border-l-8 border-yellow-400 rounded-r-2xl p-5 mb-8 flex items-center gap-4 shadow-sm">
                        <div class="bg-yellow-400 w-12 h-12 rounded-full flex items-center justify-center text-2xl text-white shadow-md">
                            📍
                        </div>
                        <div>
                            <p class="text-yellow-800 text-xs font-bold uppercase tracking-wider">Lokasi Rak:</p>
                            <p class="text-2xl font-extrabold text-gray-800">
                                {{ $book->shelf->nama_rak ?? '-' }}
                            </p>
                            <p class="text-sm text-gray-600 font-medium">{{ $book->shelf->lokasi ?? '' }}</p>
                        </div>
                    </div>
                </div>

                {{-- TOMBOL AKSI --}}
                <div class="flex flex-col sm:flex-row gap-4 mt-auto">
                    @if($book->stok_tersedia > 0)
                        {{-- Tombol Pinjam (Theme Indigo) --}}
                        <a href="{{ route('public.kiosk', $book->id) }}" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-center font-bold text-xl py-5 rounded-2xl shadow-lg border-b-8 border-indigo-800 btn-press flex items-center justify-center gap-3 transition group">
                            <span>📖</span> Pinjam Sekarang
                        </a>
                    @else
                        <button disabled class="flex-1 bg-gray-100 text-gray-400 font-bold text-xl py-5 rounded-2xl cursor-not-allowed border-b-8 border-gray-200">
                            Stok Habis ❌
                        </button>
                    @endif

                    <a href="/" class="px-8 py-5 border-4 border-gray-200 text-gray-500 font-bold text-xl rounded-2xl hover:bg-gray-50 hover:text-gray-800 transition btn-press text-center">
                        Batal
                    </a>
                </div>

            </div>
        </div>

        {{-- REKOMENDASI --}}
        @if($relatedBooks->count() > 0)
        <div class="mt-16">
            <div class="flex items-center gap-3 mb-6 ml-2">
                <span class="text-3xl">✨</span>
                <h2 class="text-2xl md:text-3xl font-bold text-gray-800">Buku Seru Lainnya</h2>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                @foreach($relatedBooks as $related)
                <a href="{{ route('public.book.show', $related->id) }}" class="group bg-white p-4 rounded-[2rem] shadow-lg border-b-8 border-indigo-50 hover:border-indigo-200 transition btn-press flex flex-col h-full">

                    {{-- Mini Cover --}}
                    <div class="aspect-[3/4] bg-gray-100 rounded-2xl mb-4 overflow-hidden relative">
                        @if($related->cover_image)
                            <img src="{{ asset('storage/' . $related->cover_image) }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-300 font-bold text-4xl bg-indigo-50/50">
                                {{ substr($related->judul, 0, 1) }}
                            </div>
                        @endif
                    </div>

                    <h3 class="font-bold text-gray-800 text-lg leading-tight line-clamp-2 mb-1 group-hover:text-indigo-600 transition">
                        {{ $related->judul }}
                    </h3>
                    <p class="text-xs text-gray-400 mt-auto">{{ $related->pengarang }}</p>
                </a>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</body>
</html>
