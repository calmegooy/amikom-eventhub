<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\Transaction;
use App\Models\User;

class ReviewPolicy
{
    public function createForTransaction(User $user, Transaction $transaction): bool
    {
        if (
            ! $user->isCustomer()
            || (int) $transaction->user_id !== (int) $user->id
            || ! $transaction->isPaid()
        ) {
            return false;
        }

        return ! Review::query()
            ->where('user_id', $user->id)
            ->where('event_id', $transaction->event_id)
            ->exists();
    }
}
