<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestHouseBookingPayment extends Model
{
    protected $fillable = [
        'guest_house_booking_id',
        'amount',
        'method',
        'status',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(GuestHouseBooking::class, 'guest_house_booking_id');
    }
}
