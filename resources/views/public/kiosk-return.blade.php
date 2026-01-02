<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kembali Mandiri</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Comic Neue', sans-serif; user-select: none; }
        .btn-press:active { transform: scale(0.95); }
        #reader video { object-fit: cover; width: 100% !important; height: 100% !important; border-radius: 2rem; }
        .num-key { @apply bg-white shadow-md rounded-2xl font-bold text-3xl text-gray-700 flex items-center justify-center active:bg-green-100 active:scale-95 transition cursor-pointer select-none border-b-4 border-gray-200; height: 5rem; }
        .animate-pop-in { animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        @keyframes popIn { 0% { transform: scale(0.8); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }
    </style>
</head>
<body class="bg-green-50 min-h-screen flex flex-col items-center justify-center p-4 md:p-8">

    {{-- HEADER --}}
    <div class="max-w-6xl w-full flex justify-between items-center mb-6 px-4">
        <a href="/" class="bg-white text-green-600 px-6 py-3 rounded-2xl font-bold shadow-lg border-b-4 border-green-200 btn-press flex items-center gap-2">
            &larr; <span class="hidden sm:inline">Menu Utama</span>
        </a>
        <h1 class="text-3xl md:text-4xl font-extrabold text-green-900">Kembalikan 🔄</h1>
        <div class="w-20"></div>
    </div>

    {{-- MAIN CONTAINER --}}
    <div class="bg-white rounded-[3rem] shadow-2xl overflow-hidden max-w-6xl w-full flex flex-col lg:flex-row min-h-[600px] border-4 border-green-200">

        {{-- KOLOM KIRI: INFO & HASIL LIST --}}
        <div class="w-full lg:w-5/12 bg-white p-8 border-r-4 border-green-50 flex flex-col justify-between">
            <div id="instruction-panel">
                <div class="mb-8 text-center">
                    <div class="w-32 h-32 bg-green-100 rounded-full flex items-center justify-center text-6xl mx-auto mb-6 shadow-inner border-4 border-white">🤝</div>
                    <h2 class="text-3xl font-extrabold text-green-900 mb-2">Terima Kasih</h2>
                    <p class="text-green-600 text-lg font-medium">Sudah selesai baca?<br>Yuk kembalikan bukunya.</p>
                </div>
                <div class="bg-green-50 p-6 rounded-[2rem] border-2 border-green-100">
                    <h3 class="font-bold text-green-800 mb-2">Cara Kembali:</h3>
                    <ol class="text-left text-green-700 space-y-2 font-medium list-decimal pl-5">
                        <li>Scan Kartu Anggota kamu.</li>
                        <li>Cek daftar buku di layar.</li>
                        <li>Taruh buku di keranjang.</li>
                    </ol>
                </div>
            </div>

            <div id="view-list" class="hidden flex-1 flex flex-col h-full w-full">
                <div class="flex justify-between items-center border-b-4 border-green-50 pb-4 mb-4">
                    <div>
                        <p class="text-gray-400 text-xs font-bold uppercase tracking-wider">Halo,</p>
                        <h2 class="text-2xl font-extrabold text-gray-800 line-clamp-1" id="memberName">-</h2>
                    </div>
                    <button onclick="location.reload()" class="bg-red-50 text-red-500 px-4 py-2 rounded-xl font-bold hover:bg-red-100 btn-press">Batal</button>
                </div>
                <div class="flex-1 overflow-y-auto pr-2 space-y-3" id="loans-container"></div>

                {{-- Estimasi Denda --}}
                <div id="fine-box" class="hidden mt-4 bg-red-50 p-4 rounded-2xl border-2 border-red-100 animate-pop-in">
                    <p id="fineLabel" class="text-red-500 font-bold uppercase text-xs tracking-wider mb-1">Tagihan</p>
                    <p class="text-3xl font-extrabold text-red-600" id="totalFineDisplay">Rp 0</p>
                </div>

                <div class="mt-4 pt-2">
                    <button onclick="openConfirmModal()" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-4 rounded-2xl shadow-xl text-xl border-b-8 border-green-700 active:scale-95 transition flex items-center justify-center gap-2">
                        ✅ PROSES PENGEMBALIAN
                    </button>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: SCANNER --}}
        <div class="w-full lg:w-7/12 p-6 flex flex-col relative bg-white">
            <div id="view-scanner" class="flex-1 bg-black rounded-[2.5rem] overflow-hidden shadow-inner border-8 border-green-100 relative min-h-[400px]">
                <div id="reader" class="w-full h-full object-cover"></div>
                <div class="absolute inset-0 border-4 border-green-500/30 rounded-[2.5rem] pointer-events-none"></div>

                {{-- ID: STATUS-CONTAINER (Agar bisa dipindah posisinya via JS) --}}
                <div id="status-container" class="absolute bottom-10 left-0 right-0 text-center pointer-events-none transition-all duration-500">
                    <div class="inline-block bg-white/90 text-green-700 px-6 py-2 rounded-full backdrop-blur-md font-bold animate-pulse text-lg shadow-lg border border-green-100" id="cam-status">
                        Scan Kartu Anggota...
                    </div>
                </div>
            </div>
            <button onclick="openNumpad()" class="mt-4 w-full bg-green-50 text-green-700 font-bold py-4 rounded-2xl shadow-sm border-b-4 border-green-200 btn-press flex items-center justify-center gap-2 text-xl hover:bg-green-100 transition">
                ⌨️ Ketik ID Anggota
            </button>
        </div>
    </div>

    {{-- MODAL KONFIRMASI --}}
    <div id="confirmModal" class="hidden fixed inset-0 bg-black/90 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white rounded-[2.5rem] max-w-sm w-full p-8 text-center shadow-2xl animate-pop-in">
            <h3 class="text-2xl font-bold mb-4 text-gray-800">Sudah Benar?</h3>
            <p class="text-gray-500 mb-8 font-medium">Pastikan buku fisik yang <b>dikembalikan</b> sudah diletakkan di keranjang.</p>
            <button onclick="processReturn()" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-4 rounded-2xl text-xl shadow-lg btn-press border-b-8 border-green-800 transition">YA, PROSES</button>
            <button onclick="closeConfirmModal()" class="mt-6 text-gray-400 font-bold text-lg hover:text-red-500">Cek Lagi</button>
        </div>
    </div>

    {{-- ✅ MODAL SUKSES --}}
    <div id="success-overlay" class="hidden absolute inset-0 bg-green-500 rounded-[3rem] flex flex-col items-center justify-center text-white z-[90] m-1 animate-pop-in">
        <div class="text-9xl mb-6 animate-bounce">🎉</div>
        <h2 class="text-5xl font-extrabold mb-4">BERHASIL!</h2>
        <p class="text-2xl opacity-90 font-medium">Terima kasih sudah mengembalikan buku.</p>
        <a href="/" class="mt-12 bg-white text-green-600 px-12 py-5 rounded-3xl font-bold shadow-2xl text-2xl btn-press border-b-8 border-green-800 transition transform hover:scale-105">
            Selesai
        </a>
    </div>

    {{-- 🛑 MODAL ERROR --}}
    <div id="errorModal" class="hidden fixed inset-0 bg-black/90 z-[90] flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white rounded-[3rem] w-full max-w-md p-8 text-center shadow-2xl animate-pop-in relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-32 bg-red-50 rounded-b-[50%] -mt-16 z-0"></div>
            <div class="relative z-10">
                <div class="w-24 h-24 bg-red-100 text-red-500 rounded-full flex items-center justify-center text-5xl mx-auto mb-6 border-4 border-white shadow-lg">⛔</div>
                <h3 class="text-3xl font-extrabold text-gray-800 mb-2">Gagal</h3>
                <p class="text-red-700 font-bold text-lg leading-relaxed mb-4" id="errorMessage">Error...</p>
                <button onclick="closeErrorModal()" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-4 rounded-2xl text-xl btn-press border-b-4 border-gray-300 transition">Tutup</button>
            </div>
        </div>
    </div>

    {{-- MODAL NUMPAD --}}
    <div id="numpadModal" class="hidden fixed inset-0 bg-black/80 z-[60] flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-gray-100 rounded-[2.5rem] w-full max-w-md shadow-2xl overflow-hidden animate-pop-in">
            <div class="bg-white p-6 border-b-2 border-gray-200 flex justify-between items-center">
                <h3 class="font-bold text-xl text-gray-500">Input ID Anggota</h3>
                <button onclick="closeNumpad()" class="text-red-500 font-bold text-lg bg-red-50 px-4 py-2 rounded-xl">Tutup</button>
            </div>
            <div class="p-6">
                <div class="bg-white border-4 border-green-200 rounded-2xl p-4 mb-6 relative">
                    <input type="text" id="virtualInput" class="w-full text-center text-4xl font-mono font-bold text-gray-800 tracking-widest focus:outline-none" placeholder="ID..." readonly>
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
        let html5QrCode = null; let activeMemberId = null; let loanData = [];
        const dendaPerHari = {{ $dendaPerHari }};
        const dendaRusak   = {{ $dendaRusak }};

        document.addEventListener("DOMContentLoaded", function() { startCamera(); });

        function startCamera() {
            html5QrCode = new Html5Qrcode("reader");
            Html5Qrcode.getCameras().then(devices => { if(devices.length) html5QrCode.start(devices[0].id, { fps: 10, qrbox: { width: 300, height: 300 } }, handleMemberScan, ()=>{}); });
        }

        function handleMemberScan(code) { html5QrCode.stop().then(() => fetchLoans(code)).catch(() => fetchLoans(code)); }

        function fetchLoans(code) {
            fetch('{{ route("public.check-member-loans") }}', {
                method: 'POST', headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}, body: JSON.stringify({member_code:code})
            }).then(r=>r.json()).then(d=>{
                if(d.status=='success') showList(d.member, d.loans);
                else {
                    showError(d.message);
                    setTimeout(() => location.reload(), 2000);
                }
            }).catch(() => showError("Gagal koneksi ke server."));
        }

        function showList(m, loans) {
            document.getElementById('instruction-panel').classList.add('hidden');
            document.getElementById('view-list').classList.remove('hidden');
            document.getElementById('view-scanner').classList.add('opacity-50', 'pointer-events-none');
            document.getElementById('memberName').innerText = m.nama_lengkap;

            // --- MODIFIKASI POSISI STATUS ---
            const container = document.getElementById('status-container');
            container.classList.remove('bottom-10'); // Hapus posisi bawah
            container.classList.add('inset-0', 'flex', 'items-center', 'justify-center', 'z-20'); // Pindah ke tengah & timpa

            const badge = document.getElementById('cam-status');
            badge.innerText = "Member Terbaca ✅";
            badge.className = "bg-green-500 text-white px-8 py-4 rounded-3xl font-bold text-2xl shadow-2xl transform scale-110 border-4 border-white";
            // ---------------------------------

            activeMemberId = m.id;
            loanData = loans;
            renderList();
        }

        function renderList() {
            const container = document.getElementById('loans-container');
            container.innerHTML = '';
            const today = new Date(); today.setHours(0,0,0,0);

            loanData.forEach((ln, idx) => {
                const tempo = new Date(ln.tgl_wajib_kembali); tempo.setHours(0,0,0,0);
                const diffTime = today - tempo;
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                const dendaTelat = (diffDays > 0) ? diffDays * dendaPerHari : 0;
                loanData[idx].dendaTelat = dendaTelat;

                let bukuHtml = '';
                ln.details.forEach(dt => {
                    const hargaBuku = dt.book.harga || 0;
                    bukuHtml += `
                        <div class="flex justify-between items-center bg-gray-50 p-3 rounded-2xl mb-2 border border-gray-100">
                            <div>
                                <div class="font-bold text-gray-800 text-lg leading-tight line-clamp-1">${dt.book.judul}</div>
                                <div class="text-xs text-gray-500">Harga: Rp ${new Intl.NumberFormat('id-ID').format(hargaBuku)}</div>
                            </div>
                            <div class="relative">
                                <select onchange="updateTotalEstimate()" id="status-${dt.id}" data-loan-id="${ln.id}" data-price="${hargaBuku}" class="bg-white border-2 border-gray-200 text-gray-700 py-2 pl-3 pr-8 rounded-xl font-bold focus:border-green-500 focus:outline-none appearance-none">
                                    <option value="kembali">✅ Ada</option>
                                    <option value="rusak">⚠️ Rusak</option>
                                    <option value="hilang">❌ Hilang</option>
                                </select>
                            </div>
                        </div>
                    `;
                });

                let dendaHtml = '';
                if (dendaTelat > 0) dendaHtml = `<div class="mt-2 text-red-500 text-xs font-bold bg-red-50 p-2 rounded-lg inline-block">⚠️ Telat ${diffDays} Hari (Rp ${new Intl.NumberFormat('id-ID').format(dendaTelat)})</div>`;

                container.innerHTML += `<div class="bg-white border-2 border-gray-200 p-4 rounded-[1.5rem] shadow-sm mb-2">${bukuHtml}${dendaHtml}</div>`;
            });
            updateTotalEstimate();
        }

        function updateTotalEstimate() {
            let totalDendaWaktu = 0; let totalGantiRugi = 0;
            loanData.forEach(ln => { totalDendaWaktu += ln.dendaTelat || 0; });
            const selects = document.querySelectorAll('select[id^="status-"]');
            selects.forEach(sel => {
                if (sel.value === 'hilang') {
                    totalGantiRugi += parseFloat(sel.getAttribute('data-price') || 0);
                } else if (sel.value === 'rusak') {
                    totalGantiRugi += dendaRusak;
                }
            });

            let grandTotal = totalDendaWaktu + totalGantiRugi;
            document.getElementById('totalFineDisplay').innerText = "Rp " + new Intl.NumberFormat('id-ID').format(grandTotal);

            const box = document.getElementById('fine-box');
            const label = document.getElementById('fineLabel');

            if (grandTotal > 0) {
                box.classList.remove('hidden');
                if(totalDendaWaktu > 0 && totalGantiRugi > 0) label.innerText = "Denda Telat + Ganti Rugi";
                else if(totalDendaWaktu > 0) label.innerText = "Denda Keterlambatan";
                else label.innerText = "Ganti Rugi Buku";
            } else {
                box.classList.add('hidden');
            }
        }

        function openConfirmModal() { document.getElementById('confirmModal').classList.remove('hidden'); }
        function closeConfirmModal() { document.getElementById('confirmModal').classList.add('hidden'); }

        function processReturn() {
            let itemsToReturn = [];
            document.querySelectorAll('select[id^="status-"]').forEach(sel => {
                itemsToReturn.push({ loan_id: sel.getAttribute('data-loan-id'), detail_id: sel.id.replace('status-', ''), status: sel.value });
            });

            fetch('{{ route("public.process-return") }}', {
                method: 'POST', headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
                body: JSON.stringify({ member_id: activeMemberId, items: itemsToReturn })
            }).then(r=>r.json()).then(d=>{
                if(d.status=='success'){
                    document.getElementById('success-overlay').classList.remove('hidden');
                }
                else { showError(d.message); }
            });
        }

        function showError(msg) {
            document.getElementById('errorMessage').innerText = msg;
            document.getElementById('errorModal').classList.remove('hidden');
        }
        function closeErrorModal() {
            document.getElementById('errorModal').classList.add('hidden');
        }

        function openNumpad() { document.getElementById('numpadModal').classList.remove('hidden'); document.getElementById('virtualInput').value=""; }
        function closeNumpad() { document.getElementById('numpadModal').classList.add('hidden'); }
        function typeNum(n) { document.getElementById('virtualInput').value += n; }
        function bkspNum() { let i=document.getElementById('virtualInput'); i.value=i.value.slice(0,-1); }
        function submitNumpad() { let v=document.getElementById('virtualInput').value; if(v.length>=1) { closeNumpad(); handleMemberScan(v); } }
    </script>
</body>
</html>
