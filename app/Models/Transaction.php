<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    public const PAYMENT_PENDING = 'pending';

    public const PAYMENT_PAID = 'paid';

    public const PAYMENT_CHALLENGED = 'challenged';

    public const PAYMENT_FAILED = 'failed';

    public const PAYMENT_EXPIRED = 'expired';

    public const PAYMENT_CANCELLED = 'cancelled';

    public const PAYMENT_REFUNDED = 'refunded';

    public const PAYMENT_STOCK_ISSUE = 'paid_stock_issue';

    protected $fillable = [
        'event_id',
        'organization_id',
        'user_id',
        'order_id',
        'checkout_token',
        'customer_name',
        'customer_email',
        'customer_phone',
        'quantity',
        'total_price',
        'status',
        'payment_status',
        'snap_token',
        'midtrans_transaction_id',
        'coupon_id',
        'ticket_status',
        'certificate_sent',
        'expires_at',
        'reserved_until',
        'paid_at',
        'expired_at',
        'cancelled_at',
        'stock_decremented_at',
        'coupon_released_at',
        'ticket_email_sent_at',
        'stock_released',
        'whatsapp_recovery_sent',
        'whatsapp_ticket_sent',
    ];

    protected $casts = [
        'certificate_sent' => 'boolean',
        'expires_at' => 'datetime',
        'reserved_until' => 'datetime',
        'paid_at' => 'datetime',
        'expired_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'stock_decremented_at' => 'datetime',
        'coupon_released_at' => 'datetime',
        'ticket_email_sent_at' => 'datetime',
        'stock_released' => 'boolean',
        'whatsapp_recovery_sent' => 'boolean',
        'whatsapp_ticket_sent' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Transaction $transaction) {
            if ($transaction->payment_status) {
                return;
            }

            $transaction->payment_status = match (strtolower((string) $transaction->status)) {
                'success', 'settlement', 'paid' => self::PAYMENT_PAID,
                'expire', 'expired' => self::PAYMENT_EXPIRED,
                'cancel', 'cancelled' => self::PAYMENT_CANCELLED,
                'deny', 'failed' => self::PAYMENT_FAILED,
                'challenge' => self::PAYMENT_CHALLENGED,
                default => self::PAYMENT_PENDING,
            };
        });
    }

    // --- Relationships ---

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /** Pembeli yang terdaftar (nullable untuk guest checkout) */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Kupon yang dipakai pada transaksi ini */
    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    /** Ulasan yang diberikan pembeli setelah acara */
    public function review()
    {
        return $this->hasOne(Review::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function paymentNotifications()
    {
        return $this->hasMany(PaymentNotification::class);
    }

    // --- Helper Methods ---

    /** Cek apakah transaksi ini sudah lunas */
    public function isPaid(): bool
    {
        if ($this->payment_status) {
            return $this->payment_status === self::PAYMENT_PAID;
        }

        return in_array(strtolower((string) $this->status), ['success', 'settlement', 'paid'], true);
    }

    public function isPending(): bool
    {
        return ($this->payment_status ?: strtolower((string) $this->status)) === self::PAYMENT_PENDING;
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', self::PAYMENT_PAID);
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
