<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $book->judul }} - Detail Buku</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .btn-press:active { transform: scale(0.95); }
        body { font-family: 'Comic Neue', 'Verdana', sans-serif; }
    </style>
</head>
<body class="bg-blue-50 text-gray-800 font-sans antialiased min-h-screen flex flex-col">

    {{-- NAVBAR SIMPLE (Hanya Tombol Home) --}}
    <nav class="bg-white shadow-lg sticky top-0 z-50 py-4">
        <div class="max-w-7xl mx-auto px-4 flex items-center gap-4">
            <a href="/" class="bg-indigo-600 text-white rounded-2xl w-14 h-14 flex items-center justify-center shadow-lg btn-press">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            </a>
            <h1 class="text-2xl font-bold text-indigo-900">Detail Buku</h1>
        </div>
    </nav>

    {{-- KONTEN UTAMA --}}
    <div class="max-w-6xl mx-auto px-4 py-8 w-full flex-1">

        <div class="bg-white rounded-[2rem] shadow-xl overflow-hidden flex flex-col md:flex-row border-4 border-indigo-100">

            {{-- KOLOM KIRI: COVER JUMBO --}}
            <div class="md:w-1/3 bg-gray-100 flex items-center justify-center p-10 relative overflow-hidden">
                <div class="absolute inset-0 bg-indigo-50 opacity-50 pattern-dots"></div> {{-- Pattern Background --}}
                <div class="w-64 h-80 bg-white shadow-2xl rotate-3 transform transition hover:rotate-0 duration-500 flex items-center justify-center border-2 border-gray-200 rounded-3xl z-10">
                    <div class="text-center">
                        <span class="block text-8xl text-indigo-200 font-bold mb-2">{{ substr($book->judul, 0, 1) }}</span>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: INFO & TOMBOL --}}
            <div class="md:w-2/3 p-8 md:p-12 flex flex-col justify-between bg-white">
                <div>
                    {{-- Badge Kategori --}}
                    <span class="inline-block bg-pink-100 text-pink-700 text-sm px-4 py-2 rounded-full font-bold mb-4 border-2 border-pink-200">
                        {{ $book->category->nama ?? 'Umum' }}
                    </span>

                    <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 leading-tight mb-4">
                        {{ $book->judul }}
                    </h1>
                    <p class="text-xl text-gray-500 mb-8 font-medium">
                        Oleh: <span class="text-indigo-600 font-bold">{{ $book->pengarang }}</span>
                    </p>

                    {{-- INFO RAK (Seperti Peta) --}}
                    <div class="bg-yellow-50 border-4 border-yellow-200 rounded-3xl p-6 mb-8 flex items-center gap-4 shadow-sm">
                        <div class="bg-yellow-400 w-16 h-16 rounded-full flex items-center justify-center text-3xl shadow-inner border-2 border-yellow-500 text-white">
                            📍
                        </div>
                        <div>
                            <p class="text-yellow-800 text-sm font-bold uppercase tracking-wider">Lokasi Buku:</p>
                            <p class="text-3xl font-extrabold text-gray-800">
                                Rak {{ $book->shelf->nama_rak ?? '?' }}
                            </p>
                            <p class="text-sm text-gray-500">{{ $book->shelf->lokasi ?? '' }}</p>
                        </div>
                    </div>
                </div>

                {{-- TOMBOL AKSI RAKSASA --}}
                <div class="flex flex-col sm:flex-row gap-4">
                    @if($book->stok_tersedia > 0)
                        <a href="{{ route('public.kiosk', $book->id) }}" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-center font-bold text-2xl py-6 rounded-3xl shadow-xl border-b-8 border-indigo-800 btn-press flex items-center justify-center gap-3 transition">
                            <span>📖</span> Pinjam Sekarang
                        </a>
                    @else
                        <button disabled class="flex-1 bg-gray-200 text-gray-400 font-bold text-2xl py-6 rounded-3xl cursor-not-allowed border-b-8 border-gray-300">
                            Stok Habis 😔
                        </button>
                    @endif

                    <a href="/" class="px-8 py-6 border-4 border-gray-200 text-gray-500 font-bold text-xl rounded-3xl hover:bg-gray-100 hover:text-gray-800 transition btn-press text-center">
                        Batal
                    </a>
                </div>

            </div>
        </div>

        {{-- REKOMENDASI --}}
        @if($relatedBooks->count() > 0)
        <div class="mt-12">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 ml-2">📚 Buku Seru Lainnya</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                @foreach($relatedBooks as $related)
                <a href="{{ route('public.book.show', $related->id) }}" class="bg-white p-4 rounded-3xl shadow-lg border-b-8 border-indigo-100 hover:border-indigo-300 transition group btn-press">
                    <div class="h-40 bg-gray-100 rounded-2xl mb-4 flex items-center justify-center text-gray-300 font-bold text-4xl group-hover:bg-indigo-50 group-hover:text-indigo-300 transition">
                        {{ substr($related->judul, 0, 1) }}
                    </div>
                    <h3 class="font-bold text-gray-900 text-lg leading-tight line-clamp-2">{{ $related->judul }}</h3>
                </a>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</body>
</html>
