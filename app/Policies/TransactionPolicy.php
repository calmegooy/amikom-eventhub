<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    public function view(User $user, Transaction $transaction): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isCustomer() && (int) $transaction->user_id === (int) $user->id) {
            return true;
        }

        return $user->canAccessOrganizerPanel()
            && $user->belongsToApprovedOrganization($transaction->organization_id);
    }
}
