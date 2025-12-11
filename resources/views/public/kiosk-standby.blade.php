<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pinjam Mandiri</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
    <style>
        .step-active { border-color: #4f46e5; background-color: #eef2ff; transform: scale(1.02); }
        .step-inactive { opacity: 0.6; filter: grayscale(1); }
        .btn-press:active { transform: scale(0.95); }
        body { font-family: 'Comic Neue', sans-serif; user-select: none; }
        #reader video { object-fit: cover; width: 100% !important; height: 100% !important; border-radius: 2rem; }
        .num-key { @apply bg-white shadow-md rounded-2xl font-bold text-3xl text-gray-700 flex items-center justify-center active:bg-indigo-100 active:scale-95 transition cursor-pointer select-none border-b-4 border-gray-200; height: 5rem; }
    </style>
</head>
<body class="bg-indigo-50 min-h-screen flex flex-col p-4 md:p-8">

    {{-- HEADER --}}
    <div class="max-w-7xl mx-auto w-full flex justify-between items-center mb-6">
        <a href="/" class="bg-white text-indigo-600 px-6 py-3 rounded-2xl font-bold shadow-lg border-b-4 border-indigo-200 btn-press flex items-center gap-2">
            &larr; <span class="hidden sm:inline">Batal</span>
        </a>
        <h1 class="text-3xl md:text-4xl font-extrabold text-indigo-900">Pinjam Mandiri 🤖</h1>
        <div class="w-20"></div>
    </div>

    <div class="flex-1 flex flex-col lg:flex-row max-w-7xl mx-auto w-full gap-8 h-full">

        {{-- KOLOM KIRI --}}
        <div class="w-full lg:w-5/12 flex flex-col gap-4">
            {{-- Step 1 --}}
            <div id="card-book" class="bg-white p-6 rounded-3xl shadow-lg border-4 border-indigo-500 transition-all">
                <div class="flex items-center gap-4 mb-2">
                    <div class="w-12 h-12 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-2xl shadow">1</div>
                    <h2 class="text-2xl font-bold text-gray-800">Scan Buku</h2>
                </div>
                <div id="info-book" class="hidden mt-4 animate-fade-in-up">
                    <div class="p-4 bg-indigo-100 rounded-2xl border-2 border-indigo-200">
                        <h3 class="font-bold text-xl text-indigo-900" id="bookTitle">Judul</h3>
                        <p class="text-indigo-600 font-medium" id="bookAuthor">Penulis</p>
                    </div>
                    <button onclick="resetProcess()" class="mt-2 text-sm text-red-500 font-bold bg-red-50 px-3 py-1 rounded-lg">Reset</button>
                </div>
                <p id="hint-book" class="text-gray-500 mt-2 text-lg">Scan barcode di belakang buku 👉</p>
            </div>

            {{-- Step 2 --}}
            <div id="card-member" class="bg-white p-6 rounded-3xl shadow border-4 border-transparent step-inactive transition-all">
                <div class="flex items-center gap-4 mb-2">
                    <div class="w-12 h-12 rounded-full bg-gray-300 text-white flex items-center justify-center font-bold text-2xl shadow">2</div>
                    <h2 class="text-2xl font-bold text-gray-800">Scan Kartu Kamu</h2>
                </div>
                <div id="info-member" class="hidden mt-4 p-4 bg-green-100 rounded-2xl border-2 border-green-200">
                    <p class="text-green-800 font-bold text-lg" id="memberIdText">ID: -</p>
                </div>
            </div>

            {{-- Step 3 --}}
            <div id="card-finish" class="hidden bg-white p-6 rounded-3xl shadow-xl border-4 border-indigo-600 animate-fade-in-up">
                <label class="block text-lg font-bold text-gray-700 mb-3">Durasi Pinjam</label>
                <div class="flex gap-4 mb-6">
                    <button onclick="setDuration(3)" id="btn-3" class="flex-1 py-3 rounded-xl border-2 border-gray-200 font-bold text-gray-500">3 Hari</button>
                    <button onclick="setDuration(7)" id="btn-7" class="flex-1 py-3 rounded-xl border-2 border-indigo-600 bg-indigo-50 text-indigo-700 font-bold">7 Hari</button>
                </div>
                <input type="hidden" id="durasi" value="7">
                <button onclick="submitLoan()" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-4 rounded-2xl shadow-lg text-xl border-b-8 border-green-700 btn-press">
                    ✅ OKE, PINJAM!
                </button>
            </div>
        </div>

        {{-- KOLOM KANAN: SCANNER --}}
        <div class="w-full lg:w-7/12 flex flex-col h-full relative p-4 bg-white rounded-[3rem] border-4 border-gray-100 shadow-xl">
            <div class="flex-1 bg-black rounded-[2.5rem] overflow-hidden shadow-inner border-8 border-gray-800 relative min-h-[400px]">
                <div id="reader" class="w-full h-full object-cover"></div>
                <div class="absolute inset-0 border-4 border-indigo-500/30 rounded-[2.5rem]"></div>
                <div class="absolute bottom-8 left-0 right-0 text-center pointer-events-none">
                    <div class="inline-block bg-black/60 text-white px-6 py-2 rounded-full backdrop-blur-md font-bold animate-pulse" id="cam-status">
                        Siap Scan...
                    </div>
                </div>
            </div>

            <button onclick="openNumpad()" class="mt-4 w-full bg-indigo-50 text-indigo-700 font-bold py-4 rounded-2xl shadow-sm border-b-4 border-indigo-100 btn-press flex items-center justify-center gap-2 text-xl">
                ⌨️ Ketik Kode Manual
            </button>
        </div>
    </div>

    {{-- MODAL NUMPAD --}}
    <div id="numpadModal" class="hidden fixed inset-0 bg-black/80 z-[60] flex items-center justify-center p-4">
        <div class="bg-gray-100 rounded-[2.5rem] w-full max-w-md shadow-2xl overflow-hidden animate-slide-up">
            <div class="bg-white p-6 border-b-2 border-gray-200 flex justify-between items-center">
                <h3 class="font-bold text-xl text-gray-500" id="numpadTitle">Input Kode</h3>
                <button onclick="closeNumpad()" class="text-red-500 font-bold text-lg bg-red-50 px-4 py-2 rounded-xl">Tutup</button>
            </div>

            <div class="p-6">
                <div class="bg-white border-4 border-indigo-200 rounded-2xl p-4 mb-6 relative">
                    <input type="text" id="virtualInput" class="w-full text-center text-5xl font-mono font-bold text-gray-800 tracking-widest focus:outline-none" placeholder="------" readonly>
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

    {{-- Loading --}}
    <div id="loading" class="hidden fixed inset-0 bg-indigo-900/90 z-[70] flex items-center justify-center">
        <div class="text-white text-center"><div class="animate-spin rounded-full h-16 w-16 border-b-4 border-white mx-auto mb-4"></div><p class="text-2xl font-bold">Memproses...</p></div>
    </div>

    <script>
        let currentStep = 1; let bookData = null; let memberCode = null; let html5QrCode = null; let isProcessing = false;
        let targetLength = 6; // Default Book ID length

        document.addEventListener("DOMContentLoaded", function() { startCamera(); });

        function startCamera() {
            html5QrCode = new Html5Qrcode("reader");
            Html5Qrcode.getCameras().then(devices => { if(devices.length) html5QrCode.start(devices[0].id, { fps: 10, qrbox: { width: 300, height: 300 } }, handleScan, ()=>{}); });
        }

        function handleScan(code) {
            if (isProcessing) return; isProcessing = true;
            if (currentStep === 1) checkBookServer(code);
            else if (currentStep === 2) {
                memberCode = code;
                updateUIMember(code);
                setTimeout(() => { isProcessing = false; }, 2000);
            }
        }

        function checkBookServer(code) {
            document.getElementById('cam-status').innerText = "Cek Buku...";
            fetch('{{ route("public.check-book") }}', { method: 'POST', headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').getAttribute('content')}, body: JSON.stringify({book_code:code}) })
            .then(r=>r.json()).then(d=>{
                if(d.status=='success'){
                    bookData=d.data;
                    document.getElementById('bookTitle').innerText=bookData.judul; document.getElementById('bookAuthor').innerText=bookData.pengarang;
                    document.getElementById('info-book').classList.remove('hidden'); document.getElementById('hint-book').classList.add('hidden');
                    document.getElementById('card-book').classList.add('border-green-500','bg-green-50');
                    document.getElementById('card-member').classList.remove('step-inactive','border-transparent'); document.getElementById('card-member').classList.add('step-active','border-indigo-500');
                    currentStep=2; targetLength=8; // Switch to Member ID length
                    document.getElementById('cam-status').innerText="SCAN KARTU...";
                } else { alert("Gagal: "+d.message); }
                isProcessing=false;
            }).catch(()=>{isProcessing=false});
        }

        function updateUIMember(c) {
            document.getElementById('memberIdText').innerText="ID: "+c;
            document.getElementById('info-member').classList.remove('hidden');
            document.getElementById('card-finish').classList.remove('hidden');
            document.getElementById('cam-status').innerText="KONFIRMASI!";
        }

        function setDuration(d) {
            document.getElementById('durasi').value=d;
            document.getElementById('btn-3').className="flex-1 py-3 rounded-xl border-2 border-gray-200 font-bold text-gray-500";
            document.getElementById('btn-7').className="flex-1 py-3 rounded-xl border-2 border-gray-200 font-bold text-gray-500";
            document.getElementById('btn-'+d).className="flex-1 py-3 rounded-xl border-2 border-indigo-600 bg-indigo-50 text-indigo-700 font-bold";
        }

        function submitLoan() {
            document.getElementById('loading').classList.remove('hidden');
            fetch('{{ route("public.kiosk.process") }}', {
                method: 'POST', headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').getAttribute('content')},
                body: JSON.stringify({kode_anggota:memberCode, book_id:bookData.id, durasi:document.getElementById('durasi').value})
            }).then(r=>r.json()).then(d=>{
                document.getElementById('loading').classList.add('hidden');
                if(d.status=='success'){ alert('🎉 BERHASIL!'); location.reload(); } else { alert('❌ GAGAL: '+d.message); }
            });
        }

        function resetProcess() { location.reload(); }

        // NUMPAD DYNAMIC LOGIC
        function openNumpad() {
            document.getElementById('numpadModal').classList.remove('hidden');
            document.getElementById('virtualInput').value="";
            document.getElementById('numpadTitle').innerText = (currentStep === 1) ? "Input Kode Buku (6 Digit)" : "Input ID Member (8 Digit)";
            checkInput();
        }
        function closeNumpad() { document.getElementById('numpadModal').classList.add('hidden'); }
        function typeNum(n) { let i=document.getElementById('virtualInput'); if(i.value.length < targetLength) { i.value+=n; checkInput(); } }
        function bkspNum() { let i=document.getElementById('virtualInput'); i.value=i.value.slice(0,-1); checkInput(); }
        function checkInput() {
            let v=document.getElementById('virtualInput').value;
            if(v.length >= targetLength) document.getElementById('checkIcon').classList.remove('hidden');
            else document.getElementById('checkIcon').classList.add('hidden');
        }
        function submitNumpad() {
            let v=document.getElementById('virtualInput').value;
            if(v.length >= 1) { closeNumpad(); handleScan(v); }
        }
    </script>
</body>
</html> 
