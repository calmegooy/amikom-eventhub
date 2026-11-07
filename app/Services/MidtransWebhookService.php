<?php

namespace App\Services;

use App\Models\PaymentNotification;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MidtransWebhookService
{
    public function __construct(private readonly PaymentLifecycleService $paymentLifecycle) {}

    public function verifySignature(array $payload): bool
    {
        $required = ['order_id', 'status_code', 'gross_amount', 'signature_key'];
        foreach ($required as $key) {
            if (! isset($payload[$key])) {
                return false;
            }
        }

        $serverKey = (string) config('midtrans.server_key');
        if ($serverKey === '') {
            return false;
        }

        $expected = hash(
            'sha512',
            $payload['order_id'].$payload['status_code'].$payload['gross_amount'].$serverKey
        );

        return hash_equals($expected, (string) $payload['signature_key']);
    }

    /**
     * @return array{transaction: Transaction, newly_paid: bool, duplicate: bool}
     */
    public function handle(array $payload): array
    {
        if (! $this->verifySignature($payload)) {
            throw new RuntimeException('Signature Midtrans tidak valid.');
        }

        $transaction = Transaction::query()
            ->where('order_id', $payload['order_id'])
            ->firstOrFail();

        if ((int) round((float) $payload['gross_amount']) !== (int) $transaction->total_price) {
            throw new RuntimeException('Nominal notifikasi tidak sama dengan transaksi lokal.');
        }

        $notification = PaymentNotification::firstOrCreate(
            ['signature_key' => $payload['signature_key']],
            [
                'transaction_id' => $transaction->id,
                'provider' => 'midtrans',
                'provider_transaction_id' => $payload['transaction_id'] ?? null,
                'order_id' => $payload['order_id'],
                'transaction_status' => $payload['transaction_status'] ?? null,
                'fraud_status' => $payload['fraud_status'] ?? null,
                'payload' => $payload,
            ]
        );

        if ($notification->processed_at) {
            return [
                'transaction' => $transaction->fresh(['event', 'tickets']),
                'newly_paid' => false,
                'duplicate' => true,
            ];
        }

        $status = (string) ($payload['transaction_status'] ?? '');
        $fraudStatus = (string) ($payload['fraud_status'] ?? '');
        $newlyPaid = false;

        if (
            $status === 'settlement'
            || ($status === 'capture' && in_array($fraudStatus, ['', 'accept'], true))
        ) {
            $result = $this->paymentLifecycle->markPaid(
                $transaction,
                $payload['transaction_id'] ?? null
            );
            $transaction = $result['transaction'];
            $newlyPaid = $result['newly_paid'];
        } else {
            $mappedStatus = match ($status) {
                'pending' => Transaction::PAYMENT_PENDING,
                'capture' => Transaction::PAYMENT_CHALLENGED,
                'deny' => Transaction::PAYMENT_FAILED,
                'expire' => Transaction::PAYMENT_EXPIRED,
                'cancel' => Transaction::PAYMENT_CANCELLED,
                'refund', 'partial_refund' => Transaction::PAYMENT_REFUNDED,
                default => throw new RuntimeException('Status Midtrans tidak dikenali.'),
            };

            $transaction = $this->paymentLifecycle->markStatus($transaction, $mappedStatus);
        }

        DB::transaction(function () use ($notification) {
            PaymentNotification::whereKey($notification->id)
                ->whereNull('processed_at')
                ->update(['processed_at' => now()]);
        });

        return [
            'transaction' => $transaction->fresh(['event', 'tickets']),
            'newly_paid' => $newlyPaid,
            'duplicate' => false,
        ];
    }
}
