<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'event_id', 'organizer_id', 'organization_id', 'code', 'type', 'value',
        'max_usage', 'used_count', 'expires_at', 'is_active',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function isValidFor(Event $event): bool
    {
        if (! $this->is_active) {
            return false;
        }
        if ($this->expires_at && now()->gt($this->expires_at)) {
            return false;
        }
        if ($this->max_usage && $this->used_count >= $this->max_usage) {
            return false;
        }
        if ($this->event_id && $this->event_id !== $event->id) {
            return false;
        }
        if ($this->organization_id) {
            if ((int) $this->organization_id !== (int) $event->organization_id) {
                return false;
            }
        } elseif ($this->organizer_id && $this->organizer_id !== $event->organizer_id) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(int $price): int
    {
        if ($this->type === 'percent') {
            return (int) round($price * $this->value / 100);
        }

        return min($price, $this->value);
    }

    public function scopeManagedBy(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        if (! $user->isOrganizer()) {
            return $query->whereRaw('1 = 0');
        }

        $organizationIds = $user->approvedOrganizationIds();
        $mayManageLegacyRecords = $user->canAccessOrganizerPanel();

        if ($organizationIds->isEmpty() && ! $mayManageLegacyRecords) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $scope) use ($organizationIds, $mayManageLegacyRecords, $user) {
            if ($organizationIds->isNotEmpty()) {
                $scope->whereIn('organization_id', $organizationIds);
            }

            if ($mayManageLegacyRecords) {
                $method = $organizationIds->isNotEmpty() ? 'orWhere' : 'where';
                $scope->{$method}(function (Builder $legacy) use ($user): void {
                    $legacy
                        ->whereNull('organization_id')
                        ->where('organizer_id', $user->id);
                });
            }
        });
    }
}
