<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestHouseReview extends Model
{
    protected $fillable = [
        'guest_house_id',
        'guest_house_booking_id',
        'user_id',
        'rating',
        'title',
        'body',
        'is_approved',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_approved' => 'boolean',
        ];
    }

    public function guestHouse(): BelongsTo
    {
        return $this->belongsTo(GuestHouse::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(GuestHouseBooking::class, 'guest_house_booking_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
