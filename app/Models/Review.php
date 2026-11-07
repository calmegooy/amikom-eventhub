<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    public const STATUS_PUBLISHED = 'published';

    public const STATUS_LEGACY_HIDDEN = 'legacy_hidden';

    protected $fillable = [
        'transaction_id', 'event_id', 'organization_id', 'user_id', 'rating', 'comment', 'status',
    ];

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
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

        return $query->where(function (Builder $scope) use ($organizationIds, $mayManageLegacyRecords, $user): void {
            if ($organizationIds->isNotEmpty()) {
                $scope->whereIn('organization_id', $organizationIds);
            }

            if ($mayManageLegacyRecords) {
                $method = $organizationIds->isNotEmpty() ? 'orWhere' : 'where';
                $scope->{$method}(function (Builder $legacy) use ($user): void {
                    $legacy
                        ->whereNull('organization_id')
                        ->whereHas('event', function (Builder $event) use ($user): void {
                            $event
                                ->whereNull('organization_id')
                                ->where('organizer_id', $user->id);
                        });
                });
            }
        });
    }
}
