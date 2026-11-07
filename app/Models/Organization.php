<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'owner_id',
        'name',
        'slug',
        'description',
        'logo_path',
        'email',
        'phone',
        'address',
        'bio',
        'status',
        'approved_at',
        'verified_at',
        'approved_by',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function publishedReviews()
    {
        return $this->reviews()->where('status', Review::STATUS_PUBLISHED);
    }

    public function getAverageRatingAttribute(): float
    {
        return round((float) $this->publishedReviews()->avg('rating'), 1);
    }

    public function getReviewCountAttribute(): int
    {
        return $this->publishedReviews()->count();
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }
}
