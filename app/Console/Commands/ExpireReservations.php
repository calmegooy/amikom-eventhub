<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Services\PaymentLifecycleService;
use Illuminate\Console\Command;

class ExpireReservations extends Command
{
    protected $signature = 'tickets:expire-reservations';

    protected $description = 'Expire pending ticket reservations safely and release coupon usage';

    public function handle(PaymentLifecycleService $paymentLifecycle): int
    {
        $expired = 0;

        Transaction::query()
            ->where('payment_status', Transaction::PAYMENT_PENDING)
            ->whereNotNull('reserved_until')
            ->where('reserved_until', '<=', now())
            ->orderBy('id')
            ->chunkById(100, function ($transactions) use ($paymentLifecycle, &$expired) {
                foreach ($transactions as $transaction) {
                    $paymentLifecycle->markStatus($transaction, Transaction::PAYMENT_EXPIRED);
                    $expired++;
                }
            });

        $this->info("{$expired} reservasi kedaluwarsa telah diproses.");

        return self::SUCCESS;
    }
}
