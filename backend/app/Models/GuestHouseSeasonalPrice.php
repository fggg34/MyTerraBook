<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestHouseSeasonalPrice extends Model
{
    protected $fillable = [
        'guest_house_id',
        'name',
        'date_from',
        'date_to',
        'price_per_night',
        'minimum_nights',
    ];

    protected function casts(): array
    {
        return [
            'date_from' => 'date',
            'date_to' => 'date',
            'price_per_night' => 'integer',
            'minimum_nights' => 'integer',
        ];
    }

    public function guestHouse(): BelongsTo
    {
        return $this->belongsTo(GuestHouse::class);
    }
}
