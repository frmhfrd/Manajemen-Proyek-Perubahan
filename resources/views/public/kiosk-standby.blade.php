<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pinjam Mandiri</title>
    {{-- CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@400;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Comic Neue', sans-serif; user-select: none; }
        .btn-press:active { transform: scale(0.95); }
        #reader video { object-fit: cover; width: 100% !important; height: 100% !important; border-radius: 2rem; }
        .num-key { @apply bg-white shadow-md rounded-2xl font-bold text-3xl text-gray-700 flex items-center justify-center active:bg-indigo-100 active:scale-95 transition cursor-pointer select-none border-b-4 border-gray-200; height: 5rem; }

        /* Animasi Modal */
        .animate-pop-in { animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        @keyframes popIn { 0% { transform: scale(0.8); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }
    </style>
</head>
<body class="bg-indigo-50 min-h-screen flex flex-col items-center justify-center p-4 md:p-8">

    {{-- HEADER --}}
    <div class="max-w-6xl w-full flex justify-between items-center mb-6 px-4">
        <a href="/" class="bg-white text-indigo-600 px-6 py-3 rounded-2xl font-bold shadow-lg border-b-4 border-indigo-200 btn-press flex items-center gap-2">
            &larr; <span class="hidden sm:inline">Menu Utama</span>
        </a>
        <h1 class="text-3xl md:text-4xl font-extrabold text-indigo-900">Mulai Pinjam 🚀</h1>
        <div class="w-20"></div>
    </div>

    {{-- MAIN CONTAINER --}}
    <div class="bg-white rounded-[3rem] shadow-2xl overflow-hidden max-w-6xl w-full flex flex-col lg:flex-row min-h-[600px] border-4 border-indigo-200">

        {{-- KOLOM KIRI: INSTRUKSI SCAN BUKU --}}
        <div class="w-full lg:w-5/12 bg-white p-8 border-r-4 border-indigo-50 flex flex-col justify-center text-center">

            <div class="mb-8">
                <div class="w-32 h-32 bg-indigo-100 rounded-full flex items-center justify-center text-6xl mx-auto mb-6 shadow-inner border-4 border-white">
                    📖
                </div>
                <h2 class="text-3xl font-extrabold text-indigo-900 mb-2">Scan Buku Dulu</h2>
                <p class="text-indigo-600 text-lg font-medium">Ambil buku yang mau dipinjam,<br>lalu scan barcode di belakangnya.</p>
            </div>

            <div class="bg-indigo-50 p-6 rounded-[2rem] border-2 border-indigo-100">
                <h3 class="font-bold text-indigo-800 mb-2">Langkah Mudah:</h3>
                <ol class="text-left text-indigo-700 space-y-2 font-medium list-decimal pl-5">
                    <li>Cari barcode di sampul belakang.</li>
                    <li>Arahkan ke kamera di samping.</li>
                    <li>Setelah itu, scan kartu anggota kamu.</li>
                </ol>
            </div>
        </div>

        {{-- KOLOM KANAN: SCANNER --}}
        <div class="w-full lg:w-7/12 p-6 flex flex-col relative bg-white">
            <div class="flex-1 bg-black rounded-[2.5rem] overflow-hidden shadow-inner border-8 border-gray-100 relative min-h-[400px]">
                <div id="reader" class="w-full h-full object-cover"></div>
                <div class="absolute inset-0 border-4 border-indigo-500/30 rounded-[2.5rem] pointer-events-none"></div>

                {{-- Status Badge --}}
                <div class="absolute bottom-10 left-0 right-0 text-center pointer-events-none">
                    <div class="inline-block bg-white/90 text-indigo-700 px-8 py-3 rounded-full backdrop-blur-md font-bold animate-pulse text-xl shadow-lg border-2 border-indigo-100">
                        Kamera Siap... 📷
                    </div>
                </div>
            </div>

            <button onclick="openNumpad()" class="mt-4 w-full bg-indigo-50 text-indigo-700 font-bold py-4 rounded-2xl shadow-sm border-b-4 border-indigo-200 btn-press flex items-center justify-center gap-2 text-xl hover:bg-indigo-100 transition">
                ⌨️ Ketik Kode Buku Manual
            </button>
        </div>
    </div>

    {{-- MODAL ERROR --}}
    <div id="errorModal" class="hidden fixed inset-0 bg-black/90 z-[80] flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white rounded-[3rem] w-full max-w-md p-8 text-center shadow-2xl animate-pop-in relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-32 bg-red-50 rounded-b-[50%] -mt-16 z-0"></div>
            <div class="relative z-10">
                <div class="w-24 h-24 bg-red-100 text-red-500 rounded-full flex items-center justify-center text-5xl mx-auto mb-6 border-4 border-white shadow-lg">⛔</div>
                <h3 class="text-3xl font-extrabold text-gray-800 mb-2">Ups, Gagal!</h3>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-xl mb-8 text-left">
                    <p class="text-red-700 font-bold text-lg leading-relaxed" id="errorMessage">Buku tidak ditemukan...</p>
                </div>
                <button onclick="closeErrorModal()" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-4 rounded-2xl text-xl btn-press border-b-4 border-gray-300 transition">
                    Tutup & Scan Lagi
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL NUMPAD --}}
    <div id="numpadModal" class="hidden fixed inset-0 bg-black/80 z-[60] flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-gray-100 rounded-[2.5rem] w-full max-w-md shadow-2xl overflow-hidden animate-pop-in">
            <div class="bg-white p-6 border-b-2 border-gray-200 flex justify-between items-center">
                <h3 class="font-bold text-xl text-gray-500">Input Kode Buku</h3>
                <button onclick="closeNumpad()" class="text-red-500 font-bold text-lg bg-red-50 px-4 py-2 rounded-xl">Tutup</button>
            </div>
            <div class="p-6">
                <div class="bg-white border-4 border-indigo-200 rounded-2xl p-4 mb-6 relative">
                    <input type="text" id="virtualInput" class="w-full text-center text-4xl font-mono font-bold text-gray-800 tracking-widest focus:outline-none" placeholder="Kode..." readonly>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div class="num-key" onclick="typeNum('1')">1</div><div class="num-key" onclick="typeNum('2')">2</div><div class="num-key" onclick="typeNum('3')">3</div>
                    <div class="num-key" onclick="typeNum('4')">4</div><div class="num-key" onclick="typeNum('5')">5</div><div class="num-key" onclick="typeNum('6')">6</div>
                    <div class="num-key" onclick="typeNum('7')">7</div><div class="num-key" onclick="typeNum('8')">8</div><div class="num-key" onclick="typeNum('9')">9</div>
                    <div class="num-key bg-red-100 text-red-500" onclick="bkspNum()">⌫</div><div class="num-key" onclick="typeNum('0')">0</div><div class="num-key bg-indigo-500 text-white border-indigo-700" onclick="submitNumpad()">OK</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let html5QrCode = null;
        let isProcessing = false;

        document.addEventListener("DOMContentLoaded", function() { startCamera(); });

        function startCamera() {
            if (typeof Html5Qrcode === 'undefined') return;
            html5QrCode = new Html5Qrcode("reader");
            Html5Qrcode.getCameras().then(devices => { if(devices.length) html5QrCode.start(devices[0].id, { fps: 10, qrbox: { width: 300, height: 300 } }, handleScan, ()=>{}); });
        }

        function handleScan(code) {
            if (isProcessing) return; isProcessing = true;
            checkBookServer(code);
        }

        function checkBookServer(code) {
            // Panggil API Cek Buku
            fetch('{{ route("public.check-book") }}', {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').getAttribute('content')},
                body: JSON.stringify({book_code:code})
            })
            .then(r=>r.json()).then(d=>{
                if(d.status=='success'){
                    // JIKA SUKSES, Redirect ke Halaman Konfirmasi (Step 2)
                    // URL ini akan membawa variable $book di Controller 'kiosk'
                    window.location.href = "/pinjam-mandiri/" + d.data.id;
                } else {
                    showError(d.message);
                    isProcessing=false;
                }
            }).catch(()=>{
                showError("Terjadi kesalahan sistem.");
                isProcessing=false;
            });
        }

        function showError(msg) {
            document.getElementById('errorMessage').innerText = msg;
            document.getElementById('errorModal').classList.remove('hidden');
        }

        function closeErrorModal() {
            document.getElementById('errorModal').classList.add('hidden');
        }

        // Numpad Functions
        function openNumpad() { document.getElementById('numpadModal').classList.remove('hidden'); document.getElementById('virtualInput').value=""; }
        function closeNumpad() { document.getElementById('numpadModal').classList.add('hidden'); }
        function typeNum(n) { document.getElementById('virtualInput').value += n; }
        function bkspNum() { let i=document.getElementById('virtualInput'); i.value=i.value.slice(0,-1); }
        function submitNumpad() {
            let v=document.getElementById('virtualInput').value;
            if(v.length >= 1) { closeNumpad(); handleScan(v); }
        }
    </script>
</body>
</html>
