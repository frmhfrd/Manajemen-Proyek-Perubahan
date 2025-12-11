<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kembali Mandiri</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
    <style>
        body { font-family: 'Comic Neue', sans-serif; user-select: none; }
        .btn-press:active { transform: scale(0.95); }
        #reader video { object-fit: cover; width: 100% !important; height: 100% !important; border-radius: 2rem; }
        .num-key { @apply bg-white shadow-md rounded-2xl font-bold text-3xl text-gray-700 flex items-center justify-center active:bg-green-100 active:scale-95 transition cursor-pointer select-none border-b-4 border-gray-200; height: 5rem; }
    </style>
</head>
<body class="bg-green-50 min-h-screen flex flex-col items-center justify-center p-4 md:p-8">

    {{-- HEADER --}}
    <div class="max-w-6xl w-full flex justify-between items-center mb-6 px-4">
        <a href="/" class="bg-white text-green-600 px-6 py-3 rounded-2xl font-bold shadow-lg border-b-4 border-green-200 btn-press flex items-center gap-2">
            &larr; <span class="hidden sm:inline">Menu Utama</span>
        </a>
        <h1 class="text-3xl md:text-4xl font-extrabold text-green-900">Kembali Mandiri 🔄</h1>
        <div class="w-20"></div>
    </div>

    {{-- MAIN CONTAINER --}}
    <div class="bg-white rounded-[3rem] shadow-2xl overflow-hidden max-w-6xl w-full flex flex-col lg:flex-row min-h-[650px] border-4 border-green-200">

        {{-- KOLOM KIRI: INFO --}}
        <div class="w-full lg:w-5/12 bg-white p-8 border-r-4 border-green-50 flex flex-col justify-between">
            <div>
                <div class="bg-green-50 p-6 rounded-[2.5rem] border-2 border-green-100 mb-8">
                    <h2 class="text-xl font-bold text-green-800 mb-2">Instruksi:</h2>
                    <ul class="text-green-700 text-sm space-y-2 list-disc pl-4 font-medium">
                        <li>Scan kartu anggota kamu.</li>
                        <li>Cek daftar buku yang muncul.</li>
                        <li>Letakkan buku di keranjang pengembalian.</li>
                        <li>Klik "Selesai".</li>
                    </ul>
                </div>

                <div class="space-y-6">
                    <div id="step-1-ind" class="flex items-center gap-6 p-6 bg-green-500 text-white rounded-[2rem] shadow-lg transform scale-105 transition-all duration-300">
                        <div class="w-14 h-14 rounded-full bg-white text-green-600 flex items-center justify-center font-bold text-2xl shadow">1</div>
                        <div>
                            <span class="font-extrabold text-2xl block">Scan Kartu</span>
                            <span class="text-green-100 text-sm">Login Anggota</span>
                        </div>
                    </div>

                    <div id="step-2-ind" class="flex items-center gap-6 p-6 bg-white text-gray-300 border-4 border-gray-100 rounded-[2rem] transition-all duration-300">
                        <div class="w-14 h-14 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center font-bold text-2xl">2</div>
                        <div>
                            <span class="font-bold text-2xl block">Kembalikan</span>
                            <span class="text-sm">Konfirmasi Akhir</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: SCANNER / LIST --}}
        <div class="w-full lg:w-7/12 p-6 flex flex-col relative bg-white h-full min-h-[500px]">

            {{-- VIEW SCANNER --}}
            <div id="view-scanner" class="flex-1 flex flex-col items-center justify-center">
                <div class="w-full max-w-lg aspect-square bg-black rounded-[2.5rem] overflow-hidden shadow-inner border-8 border-gray-100 relative">
                    <div id="reader" class="w-full h-full"></div>
                    <div class="absolute inset-0 border-4 border-green-500/30 rounded-[2.5rem] pointer-events-none"></div>
                    <div class="absolute bottom-8 left-0 right-0 text-center pointer-events-none">
                        <div class="inline-block bg-black/60 text-white px-6 py-2 rounded-full backdrop-blur-md font-bold animate-pulse text-lg">
                            Scan Kartu Anggota...
                        </div>
                    </div>
                </div>

                <button onclick="openNumpad()" class="mt-6 w-full max-w-lg bg-green-50 hover:bg-green-100 text-green-700 font-bold py-4 rounded-2xl shadow-sm border-b-4 border-green-200 btn-press flex items-center justify-center gap-3 text-xl transition">
                    ⌨️ Ketik ID Manual
                </button>
            </div>

            {{-- VIEW LIST (Hidden Awal) --}}
            <div id="view-list" class="hidden h-full flex flex-col w-full">
                {{-- Header List --}}
                <div class="flex justify-between items-center border-b-4 border-green-50 pb-4 mb-4">
                    <div>
                        <p class="text-gray-400 text-xs font-bold uppercase tracking-wider">Halo,</p>
                        <h2 class="text-2xl font-extrabold text-gray-800" id="memberName">-</h2>
                        <p class="text-green-600 font-bold font-mono text-lg" id="memberId">ID: -</p>
                    </div>
                    <button onclick="location.reload()" class="bg-red-50 text-red-500 px-5 py-3 rounded-2xl font-bold hover:bg-red-100 btn-press border-2 border-red-100">
                        Batal
                    </button>
                </div>

                {{-- List Container --}}
                <div class="flex-1 overflow-y-auto pr-2 space-y-4" id="loans-container">
                    {{-- Item buku akan masuk sini --}}
                </div>

                {{-- Footer Action --}}
                <div class="mt-4 pt-4 border-t-4 border-green-50">
                    <button onclick="openConfirmModal()" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-5 rounded-2xl shadow-xl text-2xl border-b-8 border-green-700 active:scale-95 transition flex items-center justify-center gap-3">
                        <span class="bg-white text-green-600 rounded-full w-8 h-8 flex items-center justify-center text-sm">✓</span>
                        KEMBALIKAN SEMUA
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL NUMPAD (Untuk Scan Awal Saja) --}}
    <div id="numpadModal" class="hidden fixed inset-0 bg-black/80 z-[60] flex items-center justify-center p-4">
        <div class="bg-gray-100 rounded-[2.5rem] w-full max-w-md shadow-2xl overflow-hidden animate-slide-up">
            <div class="bg-white p-6 border-b-2 border-gray-200 flex justify-between items-center">
                <h3 class="font-bold text-xl text-gray-500">Input ID Member</h3>
                <button onclick="closeNumpad()" class="text-red-500 font-bold text-lg bg-red-50 px-4 py-2 rounded-xl">Tutup</button>
            </div>
            <div class="p-6">
                <div class="bg-white border-4 border-green-200 rounded-2xl p-4 mb-6 relative">
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

    {{-- MODAL KONFIRMASI SIMPLE (Tanpa Input) --}}
    <div id="confirmModal" class="hidden fixed inset-0 bg-black/90 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-[2.5rem] max-w-sm w-full p-8 text-center shadow-2xl animate-fade-in-up">
            <div class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-4xl mx-auto mb-6">
                📚
            </div>
            <h3 class="text-2xl font-bold mb-4 text-gray-800">Sudah diletakkan?</h3>
            <p class="text-gray-500 mb-8 font-medium">Pastikan buku fisik sudah kamu taruh di meja pengembalian ya.</p>

            <button onclick="processReturn()" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-4 rounded-2xl text-xl shadow-lg btn-press border-b-8 border-green-800 transition">
                YA, SUDAH
            </button>
            <button onclick="closeConfirmModal()" class="mt-6 text-gray-400 font-bold text-lg hover:text-red-500">Belum, Kembali</button>
        </div>
    </div>

    <script>
        let html5QrCode = null; let activeMemberId = null; let activeLoanIds = [];
        let targetLength = 8;

        document.addEventListener("DOMContentLoaded", function() { startCamera(); });
        function startCamera() {
            if (typeof Html5Qrcode === 'undefined') return;
            html5QrCode = new Html5Qrcode("reader");
            Html5Qrcode.getCameras().then(devices => { if(devices.length) html5QrCode.start(devices[0].id, { fps: 10, qrbox: { width: 300, height: 300 } }, handleMemberScan, ()=>{}); });
        }
        function handleMemberScan(code) { html5QrCode.stop().then(() => fetchLoans(code)).catch(() => fetchLoans(code)); }

        function fetchLoans(code) {
            fetch('{{ route("public.check-member-loans") }}', { method: 'POST', headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}, body: JSON.stringify({member_code:code}) })
            .then(r=>r.json()).then(d=>{ if(d.status=='success') showList(d.member, d.loans); else { alert(d.message); location.reload(); } });
        }

        function showList(m, l) {
            document.getElementById('view-scanner').classList.add('hidden'); document.getElementById('view-list').classList.remove('hidden');
            // Update Indicators
            document.getElementById('step-1-ind').className="flex items-center gap-6 p-6 bg-white text-gray-300 border-4 border-gray-100 rounded-[2rem] transition-all duration-300 grayscale opacity-70";
            document.getElementById('step-2-ind').className="flex items-center gap-6 p-6 bg-green-500 text-white rounded-[2rem] shadow-lg transform scale-105 transition-all duration-300";

            document.getElementById('memberName').innerText=m.nama_lengkap; document.getElementById('memberId').innerText="ID: "+m.kode_anggota;
            activeMemberId=m.id; activeLoanIds=[]; const c=document.getElementById('loans-container'); c.innerHTML='';

            l.forEach(ln=>{ activeLoanIds.push(ln.id); let b=''; ln.details.forEach(dt=>{b+=`<div class="font-extrabold text-xl text-gray-800 mb-1 leading-tight">${dt.book.judul}</div>`});
                c.innerHTML+=`<div class="bg-white border-2 border-gray-200 p-5 rounded-[1.5rem] shadow-sm flex justify-between items-center"><div><div class="text-xs font-bold text-gray-400 uppercase mb-1">Dipinjam: ${new Date(ln.tgl_pinjam).toLocaleDateString()}</div>${b}</div><div class="text-4xl text-green-200">✅</div></div>`;
            });
        }

        function openConfirmModal() { document.getElementById('confirmModal').classList.remove('hidden'); }
        function closeConfirmModal() { document.getElementById('confirmModal').classList.add('hidden'); }

        // PROSES TANPA KONFIRMASI INPUT LAGI
        function processReturn() {
            fetch('{{ route("public.process-return") }}', {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
                body: JSON.stringify({
                    member_id: activeMemberId,
                    loan_ids: activeLoanIds // Tidak kirim confirm_code lagi
                })
            })
            .then(r=>r.json()).then(d=>{
                if(d.status=='success'){ alert('🎉 BERHASIL!'); location.href='/'; }
                else { alert('❌ GAGAL: '+d.message); }
            });
        }

        // Numpad Logic (Hanya untuk Step 1)
        function openNumpad() { document.getElementById('numpadModal').classList.remove('hidden'); document.getElementById('virtualInput').value=""; checkInput(); }
        function closeNumpad() { document.getElementById('numpadModal').classList.add('hidden'); }
        function typeNum(n) { let i=document.getElementById('virtualInput'); if(i.value.length < targetLength) { i.value+=n; checkInput(); } }
        function bkspNum() { let i=document.getElementById('virtualInput'); i.value=i.value.slice(0,-1); checkInput(); }
        function checkInput() { let v=document.getElementById('virtualInput').value; if(v.length>=targetLength) document.getElementById('checkIcon').classList.remove('hidden'); else document.getElementById('checkIcon').classList.add('hidden'); }
        function submitNumpad() { let v=document.getElementById('virtualInput').value; if(v.length>=1) { closeNumpad(); handleMemberScan(v); } }
    </script>
</body>
</html>
