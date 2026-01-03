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

        /* State Visual Buku */
        .book-unverified { opacity: 0.5; filter: grayscale(0.8); border-left: 8px solid #cbd5e1; } /* Abu-abu redup */
        .book-verified { opacity: 1; filter: grayscale(0); border-left: 8px solid #22c55e; background-color: #f0fdf4; } /* Hijau Terang (Ada) */
        .book-lost { opacity: 1; filter: grayscale(0); border-left: 8px solid #ef4444; background-color: #fef2f2; } /* Merah Terang (Hilang) */

        @keyframes popIn { 0% { transform: scale(0.9); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }
        .animate-pop-in { animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
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

        {{-- KOLOM KIRI: LIST BUKU --}}
        <div class="w-full lg:w-5/12 bg-white p-8 border-r-4 border-green-50 flex flex-col justify-between">

            {{-- Instruksi Awal --}}
            <div id="instruction-panel">
                <div class="mb-8 text-center">
                    <div class="w-32 h-32 bg-green-100 rounded-full flex items-center justify-center text-6xl mx-auto mb-6 shadow-inner border-4 border-white">🤝</div>
                    <h2 class="text-3xl font-extrabold text-green-900 mb-2">Terima Kasih</h2>
                    <p class="text-green-600 text-lg font-medium">Sudah selesai baca?<br>Yuk kembalikan bukunya.</p>
                </div>
                <div class="bg-green-50 p-6 rounded-[2rem] border-2 border-green-100">
                    <h3 class="font-bold text-green-800 mb-2">Instruksi:</h3>
                    <ul class="text-left text-green-700 space-y-2 font-medium list-disc pl-5 text-sm">
                        <li>Scan Kartu Anggota.</li>
                        <li>Daftar buku akan muncul (Redup).</li>
                        <li><b>Scan Barcode Buku</b> satu per satu untuk verifikasi (Jadi Terang).</li>
                        <li>Jika buku hilang, ubah status di dropdown.</li>
                    </ul>
                </div>
            </div>

            {{-- View List (Hidden Awal) --}}
            <div id="view-list" class="hidden flex-1 flex flex-col h-full w-full">
                <div class="flex justify-between items-center border-b-4 border-green-50 pb-4 mb-4">
                    <div>
                        <p class="text-gray-400 text-xs font-bold uppercase tracking-wider">Halo,</p>
                        <h2 class="text-2xl font-extrabold text-gray-800 line-clamp-1" id="memberName">-</h2>
                    </div>
                    <button onclick="location.reload()" class="bg-red-50 text-red-500 px-4 py-2 rounded-xl font-bold hover:bg-red-100 btn-press">Batal</button>
                </div>

                {{-- Scrollable List --}}
                <div class="flex-1 overflow-y-auto pr-2 space-y-3" id="loans-container">
                    {{-- Item akan di-render di sini --}}
                </div>

                {{-- Info Denda & Tombol --}}
                <div id="fine-box" class="mt-4 bg-red-50 p-4 rounded-2xl border-2 border-red-100 flex justify-between items-center hidden animate-pop-in">
                    <div>
                        <p class="text-xs font-bold text-red-500 uppercase">Estimasi Tagihan</p>
                        <p class="text-xs text-red-400">Denda + Ganti Rugi</p>
                    </div>
                    <p class="text-2xl font-extrabold text-red-600" id="totalFineDisplay">Rp 0</p>
                </div>

                <div class="mt-4 pt-2">
                    <button onclick="openConfirmModal()" id="btn-process" class="w-full bg-gray-300 text-white font-bold py-4 rounded-2xl shadow-none text-xl border-b-8 border-gray-400 cursor-not-allowed transition flex items-center justify-center gap-2" disabled>
                        ⏳ SCAN BUKU DULU...
                    </button>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: SCANNER --}}
        <div class="w-full lg:w-7/12 p-6 flex flex-col relative bg-white">
            <div id="view-scanner" class="flex-1 bg-black rounded-[2.5rem] overflow-hidden shadow-inner border-8 border-green-100 relative min-h-[400px]">
                <div id="reader" class="w-full h-full object-cover"></div>
                <div class="absolute inset-0 border-4 border-green-500/30 rounded-[2.5rem] pointer-events-none"></div>

                {{-- Status Container (Posisi Tengah) --}}
                <div id="status-container" class="absolute inset-0 flex items-center justify-center z-20 pointer-events-none">
                    <div class="bg-white/90 text-green-700 px-8 py-4 rounded-3xl font-bold text-2xl shadow-2xl border-4 border-white animate-pulse" id="cam-status">
                        Scan Kartu Anggota... 💳
                    </div>
                </div>
            </div>

            <button id="btn-manual" onclick="openNumpad()" class="mt-4 w-full bg-green-50 text-green-700 font-bold py-4 rounded-2xl shadow-sm border-b-4 border-green-200 btn-press flex items-center justify-center gap-2 text-xl hover:bg-green-100 transition">
                ⌨️ Ketik ID Anggota
            </button>
        </div>
    </div>

    {{-- MODAL KONFIRMASI --}}
    <div id="confirmModal" class="hidden fixed inset-0 bg-black/90 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white rounded-[2.5rem] max-w-sm w-full p-8 text-center shadow-2xl animate-pop-in">
            <h3 class="text-2xl font-bold mb-4 text-gray-800">Konfirmasi</h3>
            <p class="text-gray-500 mb-2">Anda akan mengembalikan <span id="confirm-qty" class="text-green-600 font-bold text-xl">0</span> buku.</p>
            <p class="text-red-400 text-xs mb-8 bg-red-50 p-2 rounded-lg">*Buku yang redup (tidak discan) tidak akan diproses.</p>

            <button onclick="processReturn()" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-4 rounded-2xl text-xl shadow-lg btn-press border-b-8 border-green-800 transition">YA, PROSES</button>
            <button onclick="closeConfirmModal()" class="mt-6 text-gray-400 font-bold text-lg hover:text-red-500">Cek Lagi</button>
        </div>
    </div>

    {{-- MODAL SUKSES --}}
    <div id="success-overlay" class="hidden absolute inset-0 bg-green-500 rounded-[3rem] flex flex-col items-center justify-center text-white z-[90] m-1 animate-pop-in">
        <div class="text-9xl mb-6 animate-bounce">🎉</div>
        <h2 class="text-5xl font-extrabold mb-4">BERHASIL!</h2>
        <a href="/" class="mt-12 bg-white text-green-600 px-12 py-5 rounded-3xl font-bold shadow-2xl text-2xl btn-press border-b-8 border-green-800 transition transform hover:scale-105">Selesai</a>
    </div>

    {{-- MODAL ERROR --}}
    <div id="errorModal" class="hidden fixed inset-0 bg-black/90 z-[90] flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white rounded-[2.5rem] w-full max-w-md p-8 text-center shadow-2xl animate-pop-in relative overflow-hidden">
            <div class="w-24 h-24 bg-red-100 text-red-500 rounded-full flex items-center justify-center text-5xl mx-auto mb-6 border-4 border-white shadow-lg">⛔</div>
            <h3 class="text-3xl font-extrabold text-gray-800 mb-2">Gagal</h3>
            <p class="text-red-700 font-bold text-lg leading-relaxed mb-4" id="errorMessage">Error...</p>
            <button onclick="closeErrorModal()" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-4 rounded-2xl text-xl btn-press border-b-4 border-gray-300 transition">Tutup</button>
        </div>
    </div>

    {{-- MODAL NUMPAD --}}
    <div id="numpadModal" class="hidden fixed inset-0 bg-black/80 z-[60] flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-gray-100 rounded-[2.5rem] w-full max-w-md shadow-2xl overflow-hidden animate-pop-in">
            <div class="bg-white p-6 border-b-2 border-gray-200 flex justify-between items-center">
                <h3 class="font-bold text-xl text-gray-500" id="numpadTitle">Input ID</h3>
                <button onclick="closeNumpad()" class="text-red-500 font-bold text-lg bg-red-50 px-4 py-2 rounded-xl">Tutup</button>
            </div>
            <div class="p-6">
                <div class="bg-white border-4 border-green-200 rounded-2xl p-4 mb-6 relative">
                    <input type="text" id="virtualInput" class="w-full text-center text-4xl font-mono font-bold text-gray-800 tracking-widest focus:outline-none" placeholder="..." readonly>
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
        let html5QrCode = null;
        let activeMemberId = null;
        let loanData = [];
        let scannedBookIds = new Set();
        let currentMode = 'SCAN_MEMBER';

        const dendaPerHari = {{ $dendaPerHari ?? 500 }};
        const dendaRusak   = {{ $dendaRusak ?? 10000 }};

        document.addEventListener("DOMContentLoaded", function() { startCamera(); });

        function startCamera() {
            html5QrCode = new Html5Qrcode("reader");
            Html5Qrcode.getCameras().then(devices => { if(devices.length) html5QrCode.start(devices[0].id, { fps: 10, qrbox: { width: 300, height: 300 } }, handleScan, ()=>{}); });
        }

        function handleScan(code) {
            if (currentMode === 'SCAN_MEMBER') {
                html5QrCode.pause();
                fetchLoans(code);
            } else if (currentMode === 'SCAN_BOOK') {
                verifyBook(code);
            }
        }

        function fetchLoans(code) {
            fetch('{{ route("public.check-member-loans") }}', {
                method: 'POST', headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}, body: JSON.stringify({member_code:code})
            }).then(r=>r.json()).then(d=>{
                if(d.status=='success') {
                    showList(d.member, d.loans);
                    html5QrCode.resume();
                } else {
                    showError(d.message);
                    setTimeout(() => { html5QrCode.resume(); }, 2000);
                }
            }).catch(() => { showError("Gagal koneksi server."); html5QrCode.resume(); });
        }

        function showList(m, loans) {
            document.getElementById('instruction-panel').classList.add('hidden');
            document.getElementById('view-list').classList.remove('hidden');
            document.getElementById('memberName').innerText = m.nama_lengkap;

            currentMode = 'SCAN_BOOK';
            activeMemberId = m.id;
            loanData = loans;
            scannedBookIds.clear();

            const badge = document.getElementById('cam-status');
            badge.innerText = "Scan Barcode Buku 📖";
            badge.className = "bg-indigo-600 text-white px-8 py-4 rounded-3xl font-bold text-2xl shadow-2xl border-4 border-white animate-pulse";
            document.getElementById('btn-manual').innerText = "⌨️ Ketik Kode Buku";

            renderList();
        }

        // --- RENDER LIST (REVISI: Tampilkan Semua Status) ---
        function renderList() {
            const container = document.getElementById('loans-container');
            container.innerHTML = '';

            const today = new Date(); today.setHours(0,0,0,0);

            loanData.forEach((ln) => {
                const tempo = new Date(ln.tgl_wajib_kembali); tempo.setHours(0,0,0,0);
                const diffTime = today - tempo;
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                const dendaTelat = (diffDays > 0) ? diffDays * dendaPerHari : 0;

                ln.details.forEach(dt => {
                    const isScanned = scannedBookIds.has(String(dt.id));

                    // Cek apakah buku ini SUDAH DIKEMBALIKAN sebelumnya (dari DB)
                    const isReturnedDB = (dt.status_item !== 'dipinjam');

                    // Style Dasar
                    let rowClass = 'bg-white border-gray-200';
                    let statusBadge = '';
                    let isDisabled = '';

                    // LOGIKA TAMPILAN
                    if (isReturnedDB) {
                        // KASUS: SUDAH KEMBALI DI DB (Tampilkan tapi disable)
                        rowClass = 'bg-gray-100 border-gray-200 opacity-60';
                        statusBadge = '<span class="text-xs font-bold bg-gray-200 text-gray-500 px-2 py-1 rounded">Sudah Kembali</span>';
                        isDisabled = 'disabled';
                    }
                    else if (isScanned) {
                        // KASUS: BARU SAJA DISCAN (HIJAU)
                        rowClass = 'bg-green-50 border-green-500 ring-2 ring-green-200';
                        statusBadge = '<span class="text-xs font-bold bg-green-200 text-green-700 px-2 py-1 rounded">Terverifikasi ✅</span>';
                    }
                    else {
                        // KASUS: BELUM DISCAN (ABU / DEFAULT)
                        rowClass = 'bg-white border-gray-300';
                        statusBadge = '<span class="text-xs font-bold bg-yellow-100 text-yellow-700 px-2 py-1 rounded">Belum Scan ⏳</span>';
                    }

                    // Cek dropdown value manual (jika ada refresh)
                    let currentVal = 'kembali';
                    let existingSelect = document.getElementById(`status-${dt.id}`);
                    if (existingSelect) currentVal = existingSelect.value;
                    if(currentVal === 'hilang' && !isReturnedDB) {
                        rowClass = 'bg-red-50 border-red-500';
                        statusBadge = '<span class="text-xs font-bold bg-red-200 text-red-700 px-2 py-1 rounded">Hilang ❌</span>';
                    }

                    const html = `
                        <div id="card-${dt.id}" class="flex justify-between items-center p-4 rounded-2xl mb-2 border-l-8 transition-all duration-300 shadow-sm ${rowClass}">
                            <div class="w-2/3">
                                <div class="font-bold text-gray-800 text-lg leading-tight line-clamp-1 ${isReturnedDB ? 'line-through text-gray-400' : ''}">${dt.book.judul}</div>
                                <div class="flex gap-2 mt-1">
                                    <span class="text-xs text-gray-500 font-mono bg-gray-100 px-1 rounded">Kode: ${dt.book.kode_buku ?? '-'}</span>
                                    ${dendaTelat > 0 && !isReturnedDB ? `<span class="text-xs text-red-500 font-bold">Telat: Rp ${new Intl.NumberFormat('id-ID').format(dendaTelat)}</span>` : ''}
                                </div>
                            </div>
                            <div class="text-right flex flex-col items-end gap-1">
                                ${statusBadge}
                                ${!isReturnedDB ? `
                                <select id="status-${dt.id}"
                                        data-loan-id="${ln.id}"
                                        onchange="updateRowStatus('${dt.id}')"
                                        data-price="${dt.book.harga}"
                                        data-denda-telat="${dendaTelat}"
                                        class="text-xs border-gray-300 rounded focus:ring-green-500 focus:border-green-500 py-1 pl-2 pr-6 bg-white shadow-sm" ${isDisabled}>
                                    <option value="kembali" ${currentVal=='kembali'?'selected':''}>Ada</option>
                                    <option value="rusak" ${currentVal=='rusak'?'selected':''}>Rusak</option>
                                    <option value="hilang" ${currentVal=='hilang'?'selected':''}>Hilang</option>
                                </select>
                                ` : ''}
                            </div>
                        </div>
                    `;
                    container.insertAdjacentHTML('beforeend', html);
                });
            });
            updateTotalEstimate();
        }

        // --- VERIFIKASI (1 Scan = 1 Item) ---
        function verifyBook(code) {
            let found = false;

            // Loop LABEL agar bisa break total
            loopPencarian:
            for (const ln of loanData) {
                for (const dt of ln.details) {

                    // SKIP jika buku ini sudah kembali di DB (History)
                    if (dt.status_item !== 'dipinjam') continue;

                    const matchCode = (String(dt.book.kode_buku) === String(code)) || (String(dt.book_id) === String(code));
                    const detailId = String(dt.id);

                    // JIKA: Kodenya cocok DAN belum pernah discan di sesi ini
                    if (matchCode && !scannedBookIds.has(detailId)) {
                        scannedBookIds.add(detailId);
                        found = true;

                        // Update UI
                        updateRowStatus(dt.id);

                        // Efek Visual
                        const card = document.getElementById(`card-${dt.id}`);
                        if(card) {
                            card.classList.add('scale-105', 'ring-4', 'ring-green-300');
                            setTimeout(() => card.classList.remove('scale-105', 'ring-4', 'ring-green-300'), 300);
                        }

                        // STOP LOOPING (Agar tidak ambil 2 buku sekaligus)
                        break loopPencarian;
                    }
                }
            }

            if (!found) {
                // Cek apakah duplikat atau salah buku
                let isDuplicate = false;
                let isAlreadyReturned = false;

                loanData.forEach(ln => ln.details.forEach(dt => {
                    const match = (String(dt.book.kode_buku) === String(code) || String(dt.book_id) === String(code));
                    if (match) {
                        if (scannedBookIds.has(String(dt.id))) isDuplicate = true;
                        if (dt.status_item !== 'dipinjam') isAlreadyReturned = true;
                    }
                }));

                if (isAlreadyReturned) {
                    showError("⚠️ Buku ini statusnya SUDAH KEMBALI di sistem.");
                } else if (isDuplicate) {
                    showError("⚠️ Buku ini sudah anda scan barusan.");
                } else {
                    showError("⛔ Buku ini BUKAN bagian dari peminjaman anda!");
                }
            }
        }

        // --- UPDATE STATUS BARIS ---
        function updateRowStatus(detailId) {
            const card = document.getElementById(`card-${detailId}`);
            const select = document.getElementById(`status-${detailId}`);
            if(!card || !select) return; // Guard clause

            const val = select.value;
            const isScanned = scannedBookIds.has(String(detailId));

            // Reset Class ke Default (Unverified)
            card.className = "flex justify-between items-center p-4 rounded-2xl mb-2 border-l-8 transition-all duration-300 shadow-sm bg-white border-gray-300";

            // Apply Class Baru
            if (val === 'hilang') {
                card.classList.remove('bg-white', 'border-gray-300');
                card.classList.add('bg-red-50', 'border-red-500');
            } else if (isScanned) {
                card.classList.remove('bg-white', 'border-gray-300');
                card.classList.add('bg-green-50', 'border-green-500', 'ring-2', 'ring-green-200');
            }

            updateTotalEstimate();
        }

        // --- HITUNG ESTIMASI & VALIDASI TOMBOL ---
        function updateTotalEstimate() {
            let totalTagihan = 0;
            let validItemCount = 0;

            const selects = document.querySelectorAll('select[id^="status-"]');
            selects.forEach(sel => {
                const detailId = sel.id.replace('status-', '');
                const val = sel.value;
                const isScanned = scannedBookIds.has(detailId);
                const price = parseFloat(sel.getAttribute('data-price') || 0);
                const dendaTelat = parseFloat(sel.getAttribute('data-denda-telat') || 0);

                // SYARAT VALID:
                // 1. Buku Hilang (Langsung Valid)
                // 2. Buku Ada/Rusak DAN Sudah Scan (Valid)
                let isValid = (val === 'hilang') || isScanned;

                if (isValid) {
                    validItemCount++;
                    if (val === 'hilang') totalTagihan += (dendaTelat + price);
                    else if (val === 'rusak') totalTagihan += (dendaTelat + dendaRusak);
                    else totalTagihan += dendaTelat;
                }
            });

            // Update Teks Harga
            const display = document.getElementById('totalFineDisplay');
            const box = document.getElementById('fine-box');
            display.innerText = "Rp " + new Intl.NumberFormat('id-ID').format(totalTagihan);

            if (totalTagihan > 0) box.classList.remove('hidden');
            else box.classList.add('hidden');

            // Update Tombol Proses
            const btn = document.getElementById('btn-process');
            if (validItemCount > 0) {
                btn.disabled = false;
                btn.innerHTML = `✅ PROSES PENGEMBALIAN (${validItemCount} BUKU)`;
                btn.className = "w-full bg-green-500 hover:bg-green-600 text-white font-bold py-4 rounded-2xl shadow-xl text-xl border-b-8 border-green-700 active:scale-95 transition flex items-center justify-center gap-2";
            } else {
                btn.disabled = true;
                btn.innerHTML = "⏳ SCAN BUKU UNTUK MEMPROSES...";
                btn.className = "w-full bg-gray-300 text-white font-bold py-4 rounded-2xl shadow-none text-xl border-b-8 border-gray-400 cursor-not-allowed transition flex items-center justify-center gap-2";
            }

            document.getElementById('confirm-qty').innerText = validItemCount;
        }

        // --- KIRIM DATA KE SERVER ---
        function processReturn() {
            let itemsToReturn = [];
            const selects = document.querySelectorAll('select[id^="status-"]');

            selects.forEach(sel => {
                const detailId = sel.id.replace('status-', '');
                const val = sel.value;
                const isScanned = scannedBookIds.has(detailId);
                const loanId = sel.getAttribute('data-loan-id');

                // HANYA KIRIM YANG VALID
                if ((val === 'hilang' || isScanned) && loanId) {
                    itemsToReturn.push({
                        loan_id: loanId,
                        detail_id: detailId,
                        status: val
                    });
                }
            });

            if(itemsToReturn.length === 0) {
                showError("Tidak ada buku yang siap diproses.");
                closeConfirmModal();
                return;
            }

            fetch('{{ route("public.process-return") }}', {
                method: 'POST', headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
                body: JSON.stringify({ member_id: activeMemberId, items: itemsToReturn })
            }).then(r=>r.json()).then(d=>{
                if(d.status=='success'){
                    document.getElementById('confirmModal').classList.add('hidden');
                    document.getElementById('success-overlay').classList.remove('hidden');
                } else { showError(d.message); }
            }).catch(err => { showError("Terjadi kesalahan koneksi."); });
        }

        // --- Helper ---
        function openConfirmModal() { document.getElementById('confirmModal').classList.remove('hidden'); }
        function closeConfirmModal() { document.getElementById('confirmModal').classList.add('hidden'); }
        function showError(msg) { document.getElementById('errorMessage').innerText = msg; document.getElementById('errorModal').classList.remove('hidden'); setTimeout(() => document.getElementById('errorModal').classList.add('hidden'), 3000); }
        function closeErrorModal() { document.getElementById('errorModal').classList.add('hidden'); }
        function openNumpad() { document.getElementById('numpadModal').classList.remove('hidden'); document.getElementById('virtualInput').value=""; document.getElementById('numpadTitle').innerText = (currentMode === 'SCAN_MEMBER') ? "Input ID Anggota" : "Input Kode Buku"; }
        function closeNumpad() { document.getElementById('numpadModal').classList.add('hidden'); }
        function typeNum(n) { document.getElementById('virtualInput').value += n; }
        function bkspNum() { let i=document.getElementById('virtualInput'); i.value=i.value.slice(0,-1); }
        function submitNumpad() { let v=document.getElementById('virtualInput').value; if(v.length>=1) { closeNumpad(); handleScan(v); } }
    </script>
</body>
</html>
