<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Event extends Model
{
    protected $fillable = [
        'category_id',
        'organizer_id',
        'organization_id',
        'title',
        'description',
        'date',
        'location',
        'price',
        'stock',
        'poster_path',
        // Multi-tier pricing (UAS)
        'early_bird_price',
        'early_bird_until',
        'presale_price',
        'presale_until',
    ];

    protected $casts = [
        'date' => 'datetime',
        'early_bird_until' => 'datetime',
        'presale_until' => 'datetime',
    ];

    // --- Relationships ---

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /** Pemilik event (organizer / admin) */
    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /** Semua transaksi untuk event ini */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    /** Semua ulasan untuk event ini */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /** Semua kupon yang berlaku spesifik untuk event ini */
    public function coupons()
    {
        return $this->hasMany(Coupon::class);
    }

    /**
     * Stok yang dapat dijual setelah mengurangi reservasi aktif.
     * Stok fisik baru berkurang ketika pembayaran berhasil.
     */
    public function getAvailableStockAttribute()
    {
        return max(0, $this->stock - $this->reserved_count);
    }

    /** Jumlah tiket yang sedang ditahan (transaksi Pending, belum expired) */
    public function getReservedCountAttribute()
    {
        return $this->transactions()
            ->where('payment_status', Transaction::PAYMENT_PENDING)
            ->where('reserved_until', '>', now())
            ->sum('quantity');
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

    /**
     * Event yang aman ditampilkan pada halaman publik.
     *
     * Event tenant hanya tampil jika organisasinya approved. Event lama yang
     * belum memiliki organization tetap dipertahankan bila dibuat platform
     * (tanpa organizer) atau organizer legacy yang telah disetujui.
     */
    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query
            ->where('date', '>=', now())
            ->where(function (Builder $visibility): void {
                $visibility
                    ->whereHas('organization', function (Builder $organization): void {
                        $organization->where('status', Organization::STATUS_APPROVED);
                    })
                    ->orWhere(function (Builder $legacy): void {
                        $legacy
                            ->whereNull('organization_id')
                            ->where(function (Builder $owner): void {
                                $owner
                                    ->whereNull('organizer_id')
                                    ->orWhereHas('organizer', function (Builder $organizer): void {
                                        $organizer
                                            ->where('role', User::ROLE_ADMIN)
                                            ->orWhere('is_approved', true);
                                    });
                            });
                    });
            });
    }

    // --- Accessors (Dynamic Pricing) ---

    /**
     * Harga yang aktif saat ini berdasarkan tanggal & tier (Early Bird > Presale > Regular).
     */
    public function getActivePriceAttribute()
    {
        if ($this->early_bird_price !== null && $this->early_bird_until && now()->lt($this->early_bird_until)) {
            return $this->early_bird_price;
        }

        if ($this->presale_price !== null && $this->presale_until && now()->lt($this->presale_until)) {
            return $this->presale_price;
        }

        return $this->price;
    }

    /** Label tier harga yang sedang aktif, untuk ditampilkan di UI */
    public function getActivePriceLabelAttribute()
    {
        if ($this->early_bird_price !== null && $this->early_bird_until && now()->lt($this->early_bird_until)) {
            return 'Early Bird';
        }

        if ($this->presale_price !== null && $this->presale_until && now()->lt($this->presale_until)) {
            return 'Presale';
        }

        return 'Regular';
    }

    /**
     * URL poster yang dipakai seluruh tampilan aplikasi.
     *
     * Poster unggahan penyelenggara/superadmin selalu diprioritaskan. Jika
     * event belum memiliki unggahan, gunakan aset lokal yang sesuai kategori
     * agar katalog tidak menampilkan gambar generik yang sama untuk semua
     * event atau bergantung pada layanan gambar pihak ketiga.
     */
    public function getPosterUrlAttribute(): string
    {
        if (filled($this->poster_path)) {
            return Str::startsWith($this->poster_path, ['http://', 'https://'])
                ? $this->poster_path
                : asset('storage/'.$this->poster_path);
        }

        return asset($this->defaultPosterPath());
    }

    private function defaultPosterPath(): string
    {
        return match ($this->category?->slug) {
            'entertainment' => 'assets/concert.png',
            'workshop' => 'assets/workshop.png',
            'seminar-it', 'business-startup' => 'assets/hackathon.png',
            default => 'assets/workshop.png',
        };
    }
}
