<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ListingReview extends Model
{
    protected $fillable = [
        'reviewable_type',
        'reviewable_id',
        'user_id',
        'guest_name',
        'rating',
        'body',
        'photo_path',
        'is_approved',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_approved' => 'boolean',
        ];
    }

    public function reviewable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('is_approved', true);
    }

    public function listingName(): string
    {
        $reviewable = $this->reviewable;

        if ($reviewable instanceof Car || $reviewable instanceof GuestHouse) {
            return $reviewable->name;
        }

        return 'Listing #'.$this->reviewable_id;
    }
}
