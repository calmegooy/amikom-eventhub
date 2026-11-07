<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_CUSTOMER = 'user';

    public const ROLE_ORGANIZER = 'organizer';

    public const ROLE_ADMIN = 'admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'role',
        // SSO Google
        'google_id',
        'avatar',
        // Organizer fields
        'organization_name',
        'organization_slug',
        'organization_bio',
        'is_approved',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_approved' => 'boolean',
        ];
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isCustomer(): bool
    {
        return $this->hasRole(self::ROLE_CUSTOMER);
    }

    public function isOrganizer(): bool
    {
        return $this->hasRole(self::ROLE_ORGANIZER);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    // --- Relationships ---

    /** Event-event yang dimiliki organizer ini */
    public function events()
    {
        return $this->hasMany(Event::class, 'organizer_id');
    }

    public function ownedOrganizations()
    {
        return $this->hasMany(Organization::class, 'owner_id');
    }

    public function organizations()
    {
        return $this->belongsToMany(Organization::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function activeOrganization()
    {
        return $this->organizations()
            ->where('organizations.status', Organization::STATUS_APPROVED)
            ->orderByPivot('id')
            ->first()
            ?: $this->ownedOrganizations()
                ->where('status', Organization::STATUS_APPROVED)
                ->oldest('id')
                ->first()
            ?: $this->organizations()->orderByPivot('id')->first()
            ?: $this->ownedOrganizations()->oldest('id')->first();
    }

    public function editableOrganization()
    {
        return $this->ownedOrganizations()
            ->where('status', Organization::STATUS_APPROVED)
            ->oldest('id')
            ->first()
            ?: $this->organizations()
                ->where('organizations.status', Organization::STATUS_APPROVED)
                ->wherePivot('role', 'owner')
                ->orderByPivot('id')
                ->first()
            ?: $this->ownedOrganizations()->oldest('id')->first()
            ?: $this->organizations()
                ->wherePivot('role', 'owner')
                ->orderByPivot('id')
                ->first();
    }

    /**
     * ID tenant approved yang boleh dikelola oleh organizer.
     *
     * Relasi owner tetap digabungkan untuk menjaga data lama ketika record pivot
     * belum tersedia. Hasil dibuat unik agar aman dipakai oleh semua query scope.
     *
     * @return Collection<int, int>
     */
    public function approvedOrganizationIds(): Collection
    {
        $memberIds = $this->organizations()
            ->where('organizations.status', Organization::STATUS_APPROVED)
            ->pluck('organizations.id');

        $ownedIds = $this->ownedOrganizations()
            ->where('status', Organization::STATUS_APPROVED)
            ->pluck('id');

        return $memberIds
            ->merge($ownedIds)
            ->map(static fn ($id): int => (int) $id)
            ->unique()
            ->values();
    }

    public function belongsToOrganization(?int $organizationId): bool
    {
        if (! $organizationId) {
            return false;
        }

        return $this->organizations()
            ->whereKey($organizationId)
            ->exists()
            || $this->ownedOrganizations()
                ->whereKey($organizationId)
                ->exists();
    }

    public function belongsToApprovedOrganization(?int $organizationId): bool
    {
        if (! $organizationId) {
            return false;
        }

        return $this->organizations()
            ->whereKey($organizationId)
            ->where('organizations.status', Organization::STATUS_APPROVED)
            ->exists()
            || $this->ownedOrganizations()
                ->whereKey($organizationId)
                ->where('status', Organization::STATUS_APPROVED)
                ->exists();
    }

    /**
     * Organizer legacy tanpa record organization tetap dapat masuk berdasarkan
     * is_approved. Begitu record tenant tersedia, status organization menjadi
     * sumber validasi tambahan agar data drift tidak membuka akses.
     */
    public function canAccessOrganizerPanel(): bool
    {
        if (! $this->isOrganizer() || ! $this->is_approved) {
            return false;
        }

        $hasOrganization = $this->organizations()->exists()
            || $this->ownedOrganizations()->exists();

        if (! $hasOrganization) {
            return true;
        }

        return $this->organizations()
            ->where('organizations.status', Organization::STATUS_APPROVED)
            ->exists()
            || $this->ownedOrganizations()
                ->where('status', Organization::STATUS_APPROVED)
                ->exists();
    }

    /** Transaksi sebagai pembeli (buyer) */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    /** Ulasan yang ditulis pengguna ini */
    public function reviews()
    {
        return $this->hasMany(Review::class, 'user_id');
    }

    /** Seluruh ulasan untuk event-event yang diselenggarakan oleh user (organizer) ini */
    public function organizerReviews()
    {
        return $this->hasManyThrough(Review::class, Event::class, 'organizer_id', 'event_id');
    }

    public function getAverageRatingAttribute()
    {
        return round($this->organizerReviews()->avg('rating'), 1) ?: 0;
    }

    public function getReviewCountAttribute()
    {
        return $this->organizerReviews()->count();
    }
}
