<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\MidtransService;
use App\Services\TicketQrCodeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class UserTicketController extends Controller
{
    // List Seluruh Tiket yang Dibeli User
    public function index(MidtransService $midtransService)
    {
        $tickets = Transaction::with(['event', 'tickets'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        // Browser callback is only a convenience. The server reconciles active
        // paid orders here so a local/tunnel redirect failure cannot leave a
        // settled Midtrans payment permanently shown as pending.
        $tickets
            ->filter(fn (Transaction $transaction) => $transaction->isPending()
                && (int) $transaction->total_price > 0
                && $transaction->snap_token)
            ->each(function (Transaction $transaction) use ($midtransService): void {
                try {
                    $midtransService->reconcile($transaction);
                } catch (Throwable $exception) {
                    Log::info('Status pembayaran pending belum dapat diselaraskan dari halaman Tiket Saya.', [
                        'order_id' => $transaction->order_id,
                        'message' => $exception->getMessage(),
                    ]);
                }
            });

        $tickets = Transaction::with(['event', 'tickets'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('user.tickets', compact('tickets'));
    }

    // Tampilan Detail E-Tiket + QR Code
    public function show($order_id, TicketQrCodeService $ticketQrCode)
    {
        $transaction = Transaction::with(['event', 'event.organizer', 'tickets'])
            ->where('order_id', $order_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        abort_unless($transaction->isPaid(), 403, 'Tiket belum tersedia karena pembayaran belum lunas.');

        $qrCodes = $ticketQrCode->forTickets($transaction->tickets);

        return view('user.ticket-detail', compact('transaction', 'qrCodes'));
    }

    // Hapus Transaksi (untuk bersih-bersih data demo)
    public function destroy($order_id)
    {
        $transaction = Transaction::where('order_id', $order_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $transaction->tickets()->delete();
        $transaction->delete();

        return redirect()->route('user.tickets')->with('success', 'Transaksi berhasil dihapus.');
    }
}
