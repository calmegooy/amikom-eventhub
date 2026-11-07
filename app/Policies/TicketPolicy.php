<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function view(User $user, Ticket $ticket): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isCustomer() && (int) $ticket->user_id === (int) $user->id) {
            return true;
        }

        return $user->canAccessOrganizerPanel()
            && $user->belongsToApprovedOrganization($ticket->organization_id);
    }

    public function checkIn(User $user, Ticket $ticket): bool
    {
        return $user->isAdmin()
            || (
                $user->canAccessOrganizerPanel()
                && $user->belongsToApprovedOrganization($ticket->organization_id)
            );
    }
}
