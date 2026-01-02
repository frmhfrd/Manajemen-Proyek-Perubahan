<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Konfirmasi Pinjam</title>
    {{-- CDN Tailwind & Font --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@400;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Comic Neue', sans-serif; user-select: none; }
        .btn-press:active { transform: scale(0.95); }
        #reader video { object-fit: cover; width: 100% !important; height: 100% !important; border-radius: 2rem; }
        .num-key { @apply bg-white shadow-md rounded-2xl font-bold text-3xl text-gray-700 flex items-center justify-center active:bg-indigo-100 active:scale-95 transition cursor-pointer select-none border-b-4 border-gray-200; height: 5rem; }

        /* Animasi Pop Up */
        @keyframes popIn {
            0% { transform: scale(0.8); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
        .animate-pop-in { animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
    </style>
</head>
<body class="bg-indigo-50 min-h-screen flex flex-col items-center justify-center p-4 md:p-8">

    {{-- HEADER --}}
    <div class="max-w-6xl w-full flex justify-between items-center mb-6 px-4">
        <a href="/" class="bg-white text-indigo-600 px-6 py-3 rounded-2xl font-bold shadow-lg border-b-4 border-indigo-200 btn-press flex items-center gap-2">
            &larr; <span class="hidden sm:inline">Batal</span>
        </a>
        <h1 class="text-3xl md:text-4xl font-extrabold text-indigo-900">Konfirmasi Pinjam 📖</h1>
        <div class="w-20"></div>
    </div>

    {{-- MAIN CONTAINER --}}
    <div class="bg-white rounded-[3rem] shadow-2xl overflow-hidden max-w-6xl w-full flex flex-col lg:flex-row min-h-[650px] border-4 border-indigo-200">

        {{-- KOLOM KIRI --}}
        <div class="w-full lg:w-5/12 bg-white p-8 flex flex-col gap-6 border-r-4 border-indigo-50">
            {{-- Card Info Buku --}}
            <div class="bg-indigo-50 p-6 rounded-[2.5rem] border-4 border-indigo-100 relative overflow-hidden shadow-inner">
                <div class="relative z-10">
                    <p class="text-indigo-400 font-bold mb-1 uppercase tracking-wider text-xs">Buku yang akan dipinjam:</p>
                    <h2 class="text-2xl font-extrabold text-indigo-900 leading-tight mb-1">{{ $book->judul }}</h2>
                    <p class="text-indigo-600 font-medium mb-4">{{ $book->pengarang }}</p>
                    <div class="inline-block bg-white text-indigo-800 px-4 py-2 rounded-xl font-bold text-sm border-2 border-indigo-200 shadow-sm">
                        📍 Rak {{ $book->shelf->nama_rak ?? '-' }}
                    </div>
                </div>
                <div class="absolute -bottom-4 -right-4 text-9xl opacity-10">📚</div>
            </div>

            {{-- Card Durasi --}}
            <div id="step-confirm" class="hidden bg-white p-6 rounded-[2.5rem] shadow-lg border-4 border-indigo-500 animate-fade-in-up flex-1 flex flex-col justify-center">
                <label class="block text-xl font-bold text-gray-700 mb-4 text-center">Pinjam Berapa Lama?</label>
                <div class="flex gap-3 mb-6">
                    <button onclick="setDuration(3)" id="btn-3" class="flex-1 py-4 rounded-2xl border-2 border-gray-200 font-bold text-gray-500 bg-gray-50 text-lg transition">3 Hari</button>
                    <button onclick="setDuration(7)" id="btn-7" class="flex-1 py-4 rounded-2xl border-2 border-indigo-500 bg-indigo-600 text-white font-bold shadow-lg text-lg transition">7 Hari</button>
                </div>
                <input type="hidden" id="durasi" value="7">

                <button onclick="submitLoan()" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-5 rounded-2xl shadow-xl text-2xl border-b-8 border-green-700 btn-press flex items-center justify-center gap-2">
                    ✅ OKE, PINJAM!
                </button>
            </div>

            <div id="instruction-text" class="flex-1 flex items-center justify-center text-center p-4">
                <p class="text-gray-400 text-lg font-medium">Silakan scan kartu anggota Anda di sebelah kanan 👉</p>
            </div>
        </div>

        {{-- KOLOM KANAN --}}
        <div class="w-full lg:w-7/12 p-6 flex flex-col relative bg-white">
            <div id="view-scanner" class="flex-1 bg-black rounded-[2.5rem] overflow-hidden shadow-inner border-8 border-gray-800 relative min-h-[400px]">
                <div id="reader" class="w-full h-full object-cover"></div>
                <div class="absolute inset-0 border-4 border-indigo-500/30 rounded-[2.5rem] pointer-events-none"></div>
                <div class="absolute bottom-10 left-0 right-0 text-center pointer-events-none">
                    <div class="inline-block bg-black/60 text-white px-6 py-2 rounded-full backdrop-blur-md font-bold animate-pulse text-lg" id="cam-status">
                        Scan Kartu Anggota...
                    </div>
                </div>
            </div>

            <button onclick="openNumpad()" class="mt-4 w-full bg-indigo-50 text-indigo-700 font-bold py-4 rounded-2xl shadow-sm border-b-4 border-indigo-100 btn-press flex items-center justify-center gap-2 text-xl hover:bg-indigo-100">
                ⌨️ Ketik ID Manual
            </button>
        </div>
    </div>

    {{-- 🛑 MODAL ERROR (BLOKIR) --}}
    <div id="errorModal" class="hidden fixed inset-0 bg-black/90 z-[80] flex items-center justify-center p-4">
        <div class="bg-white rounded-[3rem] w-full max-w-md p-8 text-center shadow-2xl animate-pop-in relative overflow-hidden">
            {{-- Background Merah Tipis --}}
            <div class="absolute top-0 left-0 w-full h-32 bg-red-50 rounded-b-[50%] -mt-16 z-0"></div>

            <div class="relative z-10">
                <div class="w-24 h-24 bg-red-100 text-red-500 rounded-full flex items-center justify-center text-5xl mx-auto mb-6 shadow-inner border-4 border-white">
                    ⛔
                </div>
                <h3 class="text-3xl font-extrabold text-gray-800 mb-2">Ups, Gagal!</h3>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-xl mb-8 text-left">
                    <p class="text-red-700 font-medium text-lg leading-relaxed" id="errorMessage">
                        Pesan error akan muncul di sini...
                    </p>
                </div>
                <button onclick="closeErrorModal()" class="w-full bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-4 rounded-2xl text-xl btn-press border-b-4 border-gray-300 transition">
                    Tutup & Coba Lagi
                </button>
            </div>
        </div>
    </div>

    {{-- ✅ MODAL SUKSES --}}
    <div id="success-overlay" class="hidden absolute inset-0 bg-green-500 rounded-[3rem] flex flex-col items-center justify-center text-white z-20 m-1 animate-pop-in">
        <div class="text-9xl mb-6 animate-bounce">🎉</div>
        <h2 class="text-5xl font-extrabold mb-4">BERHASIL!</h2>
        <p class="text-2xl opacity-90">Selamat membaca!</p>
        <a href="/" class="mt-12 bg-white text-green-600 px-12 py-5 rounded-3xl font-bold shadow-2xl text-2xl btn-press border-b-8 border-green-800">
            Selesai
        </a>
    </div>

    {{-- MODAL NUMPAD --}}
    <div id="numpadModal" class="hidden fixed inset-0 bg-black/80 z-[60] flex items-center justify-center p-4">
        <div class="bg-gray-100 rounded-[2.5rem] w-full max-w-md shadow-2xl overflow-hidden animate-slide-up">
            <div class="bg-white p-6 border-b-2 border-gray-200 flex justify-between items-center">
                <h3 class="font-bold text-xl text-gray-500">Input ID Member</h3>
                <button onclick="closeNumpad()" class="text-red-500 font-bold text-lg bg-red-50 px-4 py-2 rounded-xl">Tutup</button>
            </div>
            <div class="p-6">
                <div class="bg-white border-4 border-indigo-200 rounded-2xl p-4 mb-6 relative">
                    <input type="text" id="virtualInput" class="w-full text-center text-5xl font-mono font-bold text-gray-800 tracking-widest focus:outline-none" placeholder="--------" readonly>
                    <div id="checkIcon" class="hidden absolute right-4 top-1/2 transform -translate-y-1/2 text-green-500"><svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg></div>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div class="num-key" onclick="typeNum('1')">1</div><div class="num-key" onclick="typeNum('2')">2</div><div class="num-key" onclick="typeNum('3')">3</div>
                    <div class="num-key" onclick="typeNum('4')">4</div><div class="num-key" onclick="typeNum('5')">5</div><div class="num-key" onclick="typeNum('6')">6</div>
                    <div class="num-key" onclick="typeNum('7')">7</div><div class="num-key" onclick="typeNum('8')">8</div><div class="num-key" onclick="typeNum('9')">9</div>
                    <div class="num-key bg-red-100 text-red-500" onclick="bkspNum()">⌫</div><div class="num-key" onclick="typeNum('0')">0</div><div class="num-key bg-green-500 text-white border-green-700" onclick="submitNumpad()">OK</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let scannedCode = null; const bookId = {{ $book->id }}; let html5QrCode = null;
        let targetLength = 8; // ID Member

        document.addEventListener("DOMContentLoaded", function() { startCamera(); });

        function startCamera() {
            if (typeof Html5Qrcode === 'undefined') return;
            html5QrCode = new Html5Qrcode("reader");
            Html5Qrcode.getCameras().then(devices => {
                if (devices.length) {
                    html5QrCode.start(devices[0].id, { fps: 10, qrbox: { width: 300, height: 300 } },
                        (decodedText) => onScanSuccess(decodedText), () => {}
                    );
                }
            });
        }

        function onScanSuccess(decodedText) {
            html5QrCode.stop().then(() => {
                scannedCode = decodedText;
                document.getElementById('view-scanner').classList.add('opacity-50', 'pointer-events-none');
                document.getElementById('instruction-text').classList.add('hidden');
                document.getElementById('step-confirm').classList.remove('hidden');
                document.getElementById('cam-status').innerText = "Kartu Terbaca ✅";
                document.getElementById('cam-status').classList.replace('bg-black/60', 'bg-green-500');
            });
        }

        function setDuration(d) {
            document.getElementById('durasi').value = d;
            document.getElementById('btn-3').className = "flex-1 py-4 rounded-2xl border-2 border-gray-200 font-bold text-gray-500 bg-gray-50 text-lg transition";
            document.getElementById('btn-7').className = "flex-1 py-4 rounded-2xl border-2 border-gray-200 font-bold text-gray-500 bg-gray-50 text-lg transition";
            document.getElementById('btn-'+d).className = "flex-1 py-4 rounded-2xl border-2 border-indigo-500 bg-indigo-600 text-white font-bold shadow-lg text-lg transition";
        }

        function submitLoan() {
            const d = document.getElementById('durasi').value;
            const t = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch('{{ route("public.kiosk.process") }}', {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':t},
                body: JSON.stringify({kode_anggota:scannedCode, book_id:bookId, durasi:d})
            })
            .then(r => r.json())
            .then(d => {
                if(d.status == 'success') {
                    document.getElementById('success-overlay').classList.remove('hidden');
                } else {
                    // PANGGIL MODAL ERROR BARU
                    showError(d.message);
                }
            })
            .catch(err => {
                showError("Terjadi kesalahan koneksi server.");
            });
        }

        // --- FUNGSI MODAL ERROR ---
        function showError(msg) {
            document.getElementById('errorMessage').innerText = msg;
            document.getElementById('errorModal').classList.remove('hidden');
        }

        function closeErrorModal() {
            document.getElementById('errorModal').classList.add('hidden');
            location.reload(); // Refresh halaman agar user bisa scan ulang
        }

        // Numpad Functions
        function openNumpad() { document.getElementById('numpadModal').classList.remove('hidden'); document.getElementById('virtualInput').value=""; checkInput(); }
        function closeNumpad() { document.getElementById('numpadModal').classList.add('hidden'); }
        function typeNum(n) { let i=document.getElementById('virtualInput'); if(i.value.length < targetLength) { i.value+=n; checkInput(); } }
        function bkspNum() { let i=document.getElementById('virtualInput'); i.value=i.value.slice(0,-1); checkInput(); }
        function checkInput() { let v=document.getElementById('virtualInput').value; if(v.length>=targetLength) document.getElementById('checkIcon').classList.remove('hidden'); else document.getElementById('checkIcon').classList.add('hidden'); }
        function submitNumpad() { let v=document.getElementById('virtualInput').value; if(v.length>=1) { closeNumpad(); onScanSuccess(v); } }
    </script>
</body>
</html>
