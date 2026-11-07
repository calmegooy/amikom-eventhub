<?php

namespace App\Http\Controllers;

use App\Mail\EventCertificateMail;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ScannerController extends Controller
{
    /**
     * Tampilkan antarmuka Scanner Check-in untuk Panitia.
     */
    public function index()
    {
        return view('scanner.index');
    }

    /**
     * Endpoint AJAX untuk memvalidasi QR Code tiket dari kamera panitia.
     */
    public function validateTicket(Request $request)
    {
        $request->validate([
            'order_id' => 'required|string'
        ]);

        $transaction = Transaction::with('event')->where('order_id', $request->order_id)->first();

        // 1. Validasi Keberadaan Tiket
        if (!$transaction) {
            return response()->json([
                'valid' => false, 
                'message' => 'Tiket tidak ditemukan dalam sistem.'
            ], 404);
        }

        // 2. Validasi Status Pembayaran
        if (!$transaction->isPaid()) {
            return response()->json([
                'valid' => false, 
                'message' => 'Akses Ditolak: Tiket ini belum lunas atau tidak sah.'
            ], 400);
        }

        // 3. Validasi Double-Entry (Penyusup)
        if ($transaction->ticket_status === 'used') {
            return response()->json([
                'valid' => false,
                'message' => 'DITOLAK: Tiket ini SUDAH DIPAKAI sebelumnya!',
                'customer_name' => $transaction->customer_name,
                'event_title' => $transaction->event->title,
            ], 409);
        }

        // 4. Proses Check-in (Tandai terpakai)
        $transaction->update(['ticket_status' => 'used']);

        // 5. Trigger Pengiriman E-Certificate otomatis
        if (!$transaction->certificate_sent) {
            try {
                // Catatan Teknis: Untuk performa skala besar, idealnya menggunakan ->queue() 
                // agar request scanner tidak tertahan (delay) oleh proses respons server SMTP.
                // Namun ->send() tetap dipertahankan di sini untuk kompatibilitas environment lokal.
                Mail::to($transaction->customer_email)->send(new EventCertificateMail($transaction));
                $transaction->update(['certificate_sent' => true]);
            } catch (\Exception $e) {
                // Log error diam-diam agar panitia di lapangan tidak terganggu oleh error SMTP
                Log::error('Gagal mengirim E-Certificate (' . $transaction->order_id . '): ' . $e->getMessage());
            }
        }

        // 6. Kembalikan Respons Sukses ke UI Scanner
        return response()->json([
            'valid' => true,
            'message' => 'Check-in berhasil! Akses diberikan.',
            'customer_name' => $transaction->customer_name,
            'event_title' => $transaction->event->title,
        ]);
    }
}