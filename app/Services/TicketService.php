<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\Transaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TicketService
{
    public function issueFor(Transaction $transaction): Collection
    {
        $transaction->loadMissing('tickets');
        $required = max(1, (int) $transaction->quantity);
        $existing = $transaction->tickets->count();

        for ($sequence = $existing + 1; $sequence <= $required; $sequence++) {
            Ticket::create([
                'transaction_id' => $transaction->id,
                'event_id' => $transaction->event_id,
                'organization_id' => $transaction->organization_id,
                'user_id' => $transaction->user_id,
                'code' => 'TKT-'.strtoupper(Str::random(16)),
                'qr_token' => hash('sha256', Str::uuid()->toString().Str::random(40).$transaction->id.$sequence),
                'holder_name' => $transaction->customer_name,
                'holder_email' => $transaction->customer_email,
                'status' => Ticket::STATUS_ISSUED,
            ]);
        }

        return $transaction->fresh('tickets')->tickets;
    }
}
