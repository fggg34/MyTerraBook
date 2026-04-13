<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingRestriction extends Model
{
    protected $fillable = [
        'name',
        'date_from',
        'date_to',
        'min_rental_days',
        'max_rental_days',
        'cta_weekdays',
        'ctd_weekdays',
        'forced_pickup_weekdays',
        'min_length_multiplier',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'date_from' => 'date',
            'date_to' => 'date',
            'min_rental_days' => 'integer',
            'max_rental_days' => 'integer',
            'cta_weekdays' => 'array',
            'ctd_weekdays' => 'array',
            'forced_pickup_weekdays' => 'array',
            'min_length_multiplier' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
