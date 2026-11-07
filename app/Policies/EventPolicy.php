<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function create(User $user): bool
    {
        return $user->canAccessOrganizerPanel()
            && $user->activeOrganization()?->isApproved();
    }

    public function update(User $user, Event $event): bool
    {
        return $this->manages($user, $event);
    }

    public function delete(User $user, Event $event): bool
    {
        return $this->manages($user, $event);
    }

    public function scan(User $user, Event $event): bool
    {
        return $this->manages($user, $event);
    }

    private function manages(User $user, Event $event): bool
    {
        if (! $user->canAccessOrganizerPanel()) {
            return false;
        }

        if ($event->organization_id) {
            return $user->belongsToApprovedOrganization($event->organization_id);
        }

        return (int) $event->organizer_id === (int) $user->id;
    }
}
