<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    public const STATUS_ISSUED = 'issued';

    public const STATUS_USED = 'used';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'transaction_id',
        'event_id',
        'organization_id',
        'user_id',
        'code',
        'qr_token',
        'holder_name',
        'holder_email',
        'status',
        'checked_in_at',
        'checked_in_by',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
    ];

    protected $hidden = [
        'qr_token',
    ];

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

    public function checkedInBy()
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    public function isUsed(): bool
    {
        return $this->status === self::STATUS_USED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
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
