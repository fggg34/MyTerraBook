<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecialPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'date_from',
        'date_to',
        'weekdays',
        'type',
        'value_mode',
        'value_fixed_cents',
        'value_percent_bips',
        'day_overrides',
        'vehicle_ids',
        'price_type_ids',
        'pickup_location_ids',
        'dropoff_location_ids',
        'apply_after_season_start',
        'lock_first_day_rate',
        'round_to_integer',
        'year',
        'is_promotion',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'date_from' => 'date',
            'date_to' => 'date',
            'weekdays' => 'array',
            'day_overrides' => 'array',
            'vehicle_ids' => 'array',
            'price_type_ids' => 'array',
            'pickup_location_ids' => 'array',
            'dropoff_location_ids' => 'array',
            'apply_after_season_start' => 'boolean',
            'lock_first_day_rate' => 'boolean',
            'round_to_integer' => 'boolean',
            'year' => 'integer',
            'is_promotion' => 'boolean',
            'value_fixed_cents' => 'integer',
            'value_percent_bips' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
