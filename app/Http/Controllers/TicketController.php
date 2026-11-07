<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use App\Services\TicketQrCodeService;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function show(string $order_id, TicketQrCodeService $ticketQrCode)
    {
        $transaction = Transaction::with(['event.organizer', 'tickets'])
            ->where('order_id', $order_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        abort_unless($transaction->isPaid(), 403, 'Tiket belum tersedia karena pembayaran belum lunas.');

        $qrCodes = $ticketQrCode->forTickets($transaction->tickets);

        return view('user.ticket-detail', compact('transaction', 'qrCodes'));
    }

    public function myTickets()
    {
        $categories = Category::all();
        $tickets = Transaction::with(['event', 'tickets'])
            ->where('user_id', Auth::id())
            ->paid()
            ->latest()
            ->get();
        $pendingOrders = Transaction::with('event')
            ->where('user_id', Auth::id())
            ->where('payment_status', Transaction::PAYMENT_PENDING)
            ->latest()
            ->get();

        return view('user.tickets', compact('tickets', 'pendingOrders', 'categories'));
    }
}
