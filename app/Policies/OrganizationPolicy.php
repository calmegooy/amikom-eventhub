<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy
{
    public function update(User $user, Organization $organization): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if (! $user->canAccessOrganizerPanel() || ! $organization->isApproved()) {
            return false;
        }

        if ((int) $organization->owner_id === (int) $user->id) {
            return true;
        }

        return $organization->members()
            ->whereKey($user->id)
            ->wherePivot('role', 'owner')
            ->exists();
    }
}
