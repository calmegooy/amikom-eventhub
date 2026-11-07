<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scanner Check-in - AmikomEventHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        #reader video { border-radius: 1rem; object-fit: cover; }
        #reader { border: none !important; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col items-center p-6 relative overflow-hidden">

    <!-- Efek Pendaran Latar Belakang -->
    <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-emerald-500/20 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-96 h-96 bg-indigo-500/20 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10">
        <!-- Header -->
        <div class="text-center mb-8 pt-4">
            <div class="inline-block px-3 py-1 bg-emerald-50 border border-emerald-500/20 text-emerald-600 font-bold uppercase tracking-widest text-xs rounded-full mb-3 shadow-[0_0_15px_rgba(16,185,129,0.2)]">
                Modul Panitia
            </div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Access Control</h1>
            <p class="text-gray-500 text-sm mt-2 font-medium">Arahkan kamera ke QR code tiket peserta</p>
        </div>

        <!-- Box Scanner -->
        <div class="relative bg-white backdrop-blur-xl border border-gray-200/50 p-2 rounded-3xl shadow-2xl mb-6">
            <div class="absolute inset-0 border-2 border-emerald-500/30 rounded-3xl pointer-events-none"></div>
            <!-- Sudut Target Kamera -->
            <div class="absolute top-4 left-4 w-8 h-8 border-t-4 border-l-4 border-emerald-500 rounded-tl-xl pointer-events-none z-10"></div>
            <div class="absolute top-4 right-4 w-8 h-8 border-t-4 border-r-4 border-emerald-500 rounded-tr-xl pointer-events-none z-10"></div>
            <div class="absolute bottom-4 left-4 w-8 h-8 border-b-4 border-l-4 border-emerald-500 rounded-bl-xl pointer-events-none z-10"></div>
            <div class="absolute bottom-4 right-4 w-8 h-8 border-b-4 border-r-4 border-emerald-500 rounded-br-xl pointer-events-none z-10"></div>
            
            <div id="reader" class="rounded-2xl overflow-hidden bg-gray-200 aspect-square"></div>
        </div>

        <!-- Input Manual -->
        <div class="bg-white backdrop-blur-md border border-gray-200/50 rounded-2xl p-5 shadow-xl transition-all duration-300">
            <label class="block font-bold text-xs text-gray-500 mb-3 tracking-widest uppercase">Input Manual (Opsional)</label>
            <form id="manual-form" class="flex gap-2">
                <input type="text" id="ticket-code" placeholder="Misal: TRX-..." 
                    class="flex-1 bg-gray-50/50 border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-900 placeholder-slate-600 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all font-mono" required>
                <button type="submit" id="submit-manual" class="px-6 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl text-sm transition-all shadow-[0_0_20px_rgba(16,185,129,0.3)] hover:shadow-[0_0_25px_rgba(16,185,129,0.5)] active:scale-95">
                    Validasi
                </button>
            </form>
        </div>

        <!-- Kotak Hasil (Hidden by default) -->
        <div id="result" class="mt-6 p-6 rounded-2xl hidden transform transition-all duration-300 backdrop-blur-md border shadow-2xl">
            <div class="flex flex-col items-center justify-center">
                <div id="result-icon" class="text-5xl mb-3 drop-shadow-lg"></div>
                <p id="result-message" class="text-xl font-black text-white text-center tracking-tight"></p>
                <div class="w-12 h-1 bg-white/20 rounded-full my-3"></div>
                <p id="result-detail" class="text-sm font-medium text-center text-gray-800"></p>
            </div>
        </div>

        <button id="scan-again" class="hidden w-full mt-4 py-4 bg-gray-100 hover:bg-slate-700 border border-slate-600 text-gray-900 rounded-2xl font-bold transition-all shadow-lg active:scale-95 flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
            Scan Tiket Berikutnya
        </button>
    </div>

    <script>
        const resultBox = document.getElementById('result');
        const resultIcon = document.getElementById('result-icon');
        const resultMessage = document.getElementById('result-message');
        const resultDetail = document.getElementById('result-detail');
        const scanAgainBtn = document.getElementById('scan-again');
        const manualForm = document.getElementById('manual-form');
        const ticketCodeInput = document.getElementById('ticket-code');
        const submitManualBtn = document.getElementById('submit-manual');
        let isProcessing = false;
        const scanner = new Html5Qrcode("reader");

        // Audio Feedback Function
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        function playBeep(isValid) {
            if (audioCtx.state === 'suspended') audioCtx.resume();
            const oscillator = audioCtx.createOscillator();
            const gainNode = audioCtx.createGain();
            
            oscillator.type = isValid ? 'sine' : 'sawtooth';
            oscillator.frequency.setValueAtTime(isValid ? 800 : 150, audioCtx.currentTime);
            
            gainNode.gain.setValueAtTime(0.5, audioCtx.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + (isValid ? 0.2 : 0.4));
            
            oscillator.connect(gainNode);
            gainNode.connect(audioCtx.destination);
            
            oscillator.start();
            oscillator.stop(audioCtx.currentTime + (isValid ? 0.2 : 0.4));
        }

        function startScanner() {
            scanner.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                onScanSuccess
            ).catch(err => {
                console.warn("Kamera tidak bisa diakses, menggunakan fallback manual.");
            });
        }

        async function onScanSuccess(decodedText) {
            if (isProcessing) return;
            if (typeof Html5QrcodeScannerState !== 'undefined' && scanner.getState() === Html5QrcodeScannerState.SCANNING) {
                scanner.pause();
            }
            await validateCode(decodedText);
        }

        async function validateCode(code) {
            if (isProcessing) return;
            isProcessing = true;

            submitManualBtn.disabled = true;
            submitManualBtn.innerHTML = `<svg class="animate-spin h-5 w-5 text-gray-900 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>`;

            try {
                // Endpoint validasi Laravel
                const res = await fetch("{{ route('scanner.validate') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ order_id: code })
                });
                const data = await res.json();

                resultBox.classList.remove('hidden');
                scanAgainBtn.classList.remove('hidden');
                resultBox.classList.remove('translate-y-4', 'opacity-0');

                // Trigger Audio Feedback
                playBeep(data.valid);

                if (data.valid) {
                    resultBox.className = "mt-6 p-6 rounded-2xl bg-emerald-900/80 border border-emerald-500 shadow-[0_0_30px_rgba(16,185,129,0.3)] transform transition-all duration-300 backdrop-blur-xl";
                    resultIcon.textContent = "✅";
                } else {
                    resultBox.className = "mt-6 p-6 rounded-2xl bg-rose-900/80 border border-rose-500 shadow-[0_0_30px_rgba(225,29,72,0.3)] transform transition-all duration-300 backdrop-blur-xl";
                    resultIcon.textContent = "❌";
                }
                
                resultMessage.textContent = data.message;
                resultDetail.textContent = data.customer_name ? `${data.customer_name} • ${data.event_title}` : '';
                ticketCodeInput.value = '';

            } catch (e) {
                playBeep(false); // Bunyi gagal saat koneksi terputus
                resultBox.classList.remove('hidden');
                resultBox.className = "mt-6 p-6 rounded-2xl bg-rose-900/80 border border-rose-500 shadow-[0_0_30px_rgba(225,29,72,0.3)] transform transition-all duration-300 backdrop-blur-xl";
                resultIcon.textContent = "⚠️";
                resultMessage.textContent = "Koneksi Terputus";
                resultDetail.textContent = "Periksa jaringan server Anda.";
            } finally {
                isProcessing = false;
                submitManualBtn.disabled = false;
                submitManualBtn.textContent = "Validasi";
            }
        }

        manualForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (isProcessing) return;
            const code = ticketCodeInput.value.trim();
            if (!code) return;

            if (typeof Html5QrcodeScannerState !== 'undefined' && scanner.getState() === Html5QrcodeScannerState.SCANNING) {
                scanner.pause();
            }
            await validateCode(code);
        });

        scanAgainBtn.addEventListener('click', () => {
            resultBox.classList.add('hidden', 'translate-y-4', 'opacity-0');
            scanAgainBtn.classList.add('hidden');
            isProcessing = false;
            
            if (typeof Html5QrcodeScannerState !== 'undefined' && scanner.getState() === Html5QrcodeScannerState.PAUSED) {
                scanner.resume();
            } else {
                startScanner();
            }
        });

        // Initialize
        resultBox.classList.add('translate-y-4', 'opacity-0');
        startScanner();
    </script>
</body>
</html>