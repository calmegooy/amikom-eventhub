@extends('layouts.app')

@section('title', 'Scanner Check-in')

@section('content')
<div class="min-h-[85vh] py-12 px-4 bg-slate-50 flex items-center justify-center">
    <div class="max-w-md w-full bg-white border border-slate-200 rounded-3xl p-6 shadow-xl">
        <div class="text-center mb-6">
            <p class="text-xs font-bold uppercase tracking-wider text-indigo-600">Event terpilih</p>
            <h1 class="text-xl font-bold text-slate-900">{{ $event->title }}</h1>
            <p class="text-xs text-slate-500 mt-1">QR dari event lain akan otomatis ditolak.</p>
        </div>

        <div id="reader" class="overflow-hidden rounded-2xl border-2 border-dashed border-indigo-200 bg-slate-50 mb-6"></div>
        <div id="scan-result" class="hidden p-4 rounded-2xl text-center text-xs font-medium"></div>

        <div class="mt-6 border-t border-slate-100 pt-4">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">
                Input kode tiket manual
            </label>
            <form id="manual-form" class="flex gap-2">
                <input type="text" id="manual-ticket" placeholder="TKT-00000001-001" required
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs">
                <button type="submit" class="px-4 bg-indigo-600 text-white font-bold rounded-xl text-xs">
                    Proses
                </button>
            </form>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const resultDiv = document.getElementById('scan-result');
    let isProcessing = false;

    if (!window.isSecureContext || !navigator.mediaDevices?.getUserMedia) {
        resultDiv.className = 'p-4 rounded-2xl text-center text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200 block';
        resultDiv.textContent = 'Scanner kamera memerlukan HTTPS dan izin akses kamera. Gunakan localhost saat pengujian lokal.';
        return;
    }

    async function processCheckIn(ticketToken) {
        if (isProcessing) return;
        isProcessing = true;
        resultDiv.className = 'p-4 rounded-2xl text-center text-xs font-medium bg-indigo-50 text-indigo-700 block';
        resultDiv.textContent = 'Memproses tiket...';

        try {
            const response = await fetch(@json(route('organizer.checkin', $event)), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': @json(csrf_token())
                },
                body: JSON.stringify({ ticket_token: ticketToken })
            });
            const data = await response.json();

            if (response.ok && data.success) {
                resultDiv.className = 'p-4 rounded-2xl text-center text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 block';
                resultDiv.textContent = `Check-in berhasil: ${data.customer_name} (${data.ticket_code})`;
            } else {
                resultDiv.className = 'p-4 rounded-2xl text-center text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 block';
                resultDiv.textContent = data.message || 'Tiket ditolak.';
            }
        } catch (error) {
            resultDiv.className = 'p-4 rounded-2xl text-center text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 block';
            resultDiv.textContent = 'Koneksi ke server gagal.';
        } finally {
            setTimeout(() => { isProcessing = false; }, 1500);
        }
    }

    const scanner = new Html5QrcodeScanner('reader', {
        fps: 10,
        qrbox: { width: 220, height: 220 }
    });
    scanner.render(processCheckIn, () => {});

    document.getElementById('manual-form').addEventListener('submit', function (event) {
        event.preventDefault();
        const code = document.getElementById('manual-ticket').value.trim();
        if (code) processCheckIn(code);
    });
});
</script>
@endsection
