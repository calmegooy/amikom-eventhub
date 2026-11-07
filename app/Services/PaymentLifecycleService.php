<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class PaymentLifecycleService
{
    public function __construct(private readonly TicketService $ticketService) {}

    /**
     * @return array{transaction: Transaction, newly_paid: bool}
     */
    public function markPaid(Transaction $transaction, ?string $providerTransactionId = null): array
    {
        if ($transaction->isPending() && $transaction->reserved_until?->isPast()) {
            $expiredTransaction = $this->markStatus($transaction, Transaction::PAYMENT_EXPIRED);

            return [
                'transaction' => $expiredTransaction->fresh(['event', 'tickets']),
                'newly_paid' => false,
            ];
        }

        return DB::transaction(function () use ($transaction, $providerTransactionId) {
            $lockedTransaction = Transaction::query()
                ->whereKey($transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedTransaction->isPaid() && $lockedTransaction->stock_decremented_at) {
                $this->ticketService->issueFor($lockedTransaction);

                return [
                    'transaction' => $lockedTransaction->fresh(['event', 'tickets']),
                    'newly_paid' => false,
                ];
            }

            if (! $lockedTransaction->isPending()) {
                return [
                    'transaction' => $lockedTransaction->fresh(['event', 'tickets']),
                    'newly_paid' => false,
                ];
            }

            $event = $lockedTransaction->event()
                ->lockForUpdate()
                ->firstOrFail();

            $quantity = max(1, (int) $lockedTransaction->quantity);

            if (! $lockedTransaction->stock_decremented_at) {
                if ($event->stock < $quantity) {
                    $lockedTransaction->update([
                        'payment_status' => Transaction::PAYMENT_STOCK_ISSUE,
                        'status' => Transaction::PAYMENT_STOCK_ISSUE,
                        'paid_at' => now(),
                        'midtrans_transaction_id' => $providerTransactionId,
                    ]);

                    return [
                        'transaction' => $lockedTransaction->fresh(['event', 'tickets']),
                        'newly_paid' => false,
                    ];
                }

                $event->decrement('stock', $quantity);
            }

            $lockedTransaction->update([
                'payment_status' => Transaction::PAYMENT_PAID,
                'status' => 'success',
                'paid_at' => $lockedTransaction->paid_at ?: now(),
                'stock_decremented_at' => $lockedTransaction->stock_decremented_at ?: now(),
                'midtrans_transaction_id' => $providerTransactionId
                    ?: $lockedTransaction->midtrans_transaction_id,
                'stock_released' => false,
            ]);

            $this->ticketService->issueFor($lockedTransaction);

            DB::afterCommit(function () use ($lockedTransaction) {
                \App\Jobs\SendTicketWhatsAppJob::dispatch($lockedTransaction);
            });

            return [
                'transaction' => $lockedTransaction->fresh(['event', 'tickets']),
                'newly_paid' => true,
            ];
        }, 3);
    }

    public function markStatus(Transaction $transaction, string $paymentStatus): Transaction
    {
        return DB::transaction(function () use ($transaction, $paymentStatus) {
            $lockedTransaction = Transaction::query()
                ->whereKey($transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedTransaction->isPaid() || ! $lockedTransaction->isPending()) {
                return $lockedTransaction;
            }

            $legacyStatus = match ($paymentStatus) {
                Transaction::PAYMENT_EXPIRED => 'Expired',
                Transaction::PAYMENT_CANCELLED => 'cancelled',
                default => $paymentStatus,
            };

            $changes = [
                'payment_status' => $paymentStatus,
                'status' => $legacyStatus,
            ];

            if ($paymentStatus === Transaction::PAYMENT_EXPIRED) {
                $changes['expired_at'] = $lockedTransaction->expired_at ?: now();
                $changes['stock_released'] = true;
            }

            if ($paymentStatus === Transaction::PAYMENT_CANCELLED) {
                $changes['cancelled_at'] = $lockedTransaction->cancelled_at ?: now();
                $changes['stock_released'] = true;
            }

            if (
                in_array($paymentStatus, [
                    Transaction::PAYMENT_EXPIRED,
                    Transaction::PAYMENT_CANCELLED,
                    Transaction::PAYMENT_FAILED,
                ], true)
                && $lockedTransaction->coupon_id
                && ! $lockedTransaction->coupon_released_at
            ) {
                $lockedTransaction->coupon()
                    ->where('used_count', '>', 0)
                    ->decrement('used_count');
                $changes['coupon_released_at'] = now();
            }

            $lockedTransaction->update($changes);

            return $lockedTransaction->fresh();
        }, 3);
    }
}
