<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class CertificateController extends Controller
{
    public function index(string $order_id)
    {
        $transaction = $this->eligibleTransaction($order_id);

        return view('certificates.index', compact('transaction'));
    }

    public function preview(string $order_id)
    {
        return $this->certificatePdf($this->eligibleTransaction($order_id))->stream(
            'E-Certificate-'.$order_id.'.pdf'
        );
    }

    public function download(string $order_id)
    {
        return $this->certificatePdf($this->eligibleTransaction($order_id))->download(
            'E-Certificate-'.$order_id.'.pdf'
        );
    }

    private function eligibleTransaction(string $orderId): Transaction
    {
        $transaction = Transaction::with(['event', 'tickets'])
            ->where('order_id', $orderId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        abort_unless($transaction->isPaid(), 403, 'Tiket ini belum lunas.');

        $hasCheckedIn = $transaction->tickets->contains(
            fn ($ticket) => $ticket->isUsed()
        ) || $transaction->ticket_status === 'used';

        abort_unless(
            $hasCheckedIn,
            403,
            'Sertifikat hanya tersedia setelah check-in di lokasi acara.'
        );

        return $transaction;
    }

    private function certificatePdf(Transaction $transaction)
    {
        return Pdf::loadView('certificates.pdf', [
            'transaction' => $transaction,
            'event' => $transaction->event,
            'date' => $transaction->event->date->translatedFormat('d F Y'),
        ])->setPaper('a4', 'landscape');
    }
}
