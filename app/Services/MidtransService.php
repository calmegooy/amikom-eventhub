<?php

namespace App\Services;

use App\Models\Transaction;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction as MidtransTransaction;
use RuntimeException;

class MidtransService
{
    public function __construct(private readonly PaymentLifecycleService $paymentLifecycle) {}

    public function createSnapToken(Transaction $transaction): string
    {
        if ($transaction->snap_token) {
            return $transaction->snap_token;
        }

        $this->configure();

        $token = Snap::getSnapToken([
            'transaction_details' => [
                'order_id' => $transaction->order_id,
                'gross_amount' => (int) $transaction->total_price,
            ],
            'customer_details' => [
                'first_name' => $transaction->customer_name,
                'email' => $transaction->customer_email,
                'phone' => $transaction->customer_phone,
            ],
            'expiry' => [
                'start_time' => now()->format('Y-m-d H:i:s O'),
                'duration' => 15,
                'unit' => 'minute',
            ],
            'callbacks' => [
                'finish' => route('checkout.finish', ['order_id' => $transaction->order_id]),
            ],
        ]);

        $transaction->update(['snap_token' => $token]);

        return $token;
    }

    /**
     * Reconcile a customer return from Snap with Midtrans' server-to-server status API.
     * Browser query parameters are intentionally never used as payment proof.
     *
     * @return array{transaction: Transaction, newly_paid: bool}
     */
    public function reconcile(Transaction $transaction): array
    {
        if ($transaction->isPaid()) {
            return [
                'transaction' => $transaction->fresh(['event', 'tickets']),
                'newly_paid' => false,
            ];
        }

        $this->configure();

        $status = MidtransTransaction::status($transaction->order_id);

        if (($status->order_id ?? null) !== $transaction->order_id) {
            throw new RuntimeException('Respons status Midtrans tidak cocok dengan pesanan.');
        }

        if (isset($status->gross_amount) && (int) round((float) $status->gross_amount) !== (int) $transaction->total_price) {
            throw new RuntimeException('Nominal status Midtrans tidak sama dengan transaksi lokal.');
        }

        $paymentStatus = (string) ($status->transaction_status ?? '');
        $fraudStatus = (string) ($status->fraud_status ?? '');

        if (
            $paymentStatus === 'settlement'
            || ($paymentStatus === 'capture' && in_array($fraudStatus, ['', 'accept'], true))
        ) {
            return $this->paymentLifecycle->markPaid(
                $transaction,
                $status->transaction_id ?? null
            );
        }

        $mappedStatus = match ($paymentStatus) {
            'pending' => Transaction::PAYMENT_PENDING,
            'capture' => Transaction::PAYMENT_CHALLENGED,
            'deny' => Transaction::PAYMENT_FAILED,
            'expire' => Transaction::PAYMENT_EXPIRED,
            'cancel' => Transaction::PAYMENT_CANCELLED,
            'refund', 'partial_refund' => Transaction::PAYMENT_REFUNDED,
            default => throw new RuntimeException('Status Midtrans tidak dikenali.'),
        };

        return [
            'transaction' => $this->paymentLifecycle->markStatus($transaction, $mappedStatus),
            'newly_paid' => false,
        ];
    }

    private function configure(): void
    {
        $serverKey = (string) config('midtrans.server_key');
        if ($serverKey === '') {
            throw new RuntimeException('MIDTRANS_SERVER_KEY belum dikonfigurasi.');
        }

        Config::$serverKey = $serverKey;
        Config::$isProduction = (bool) config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }
}
