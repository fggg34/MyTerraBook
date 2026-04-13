<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutOfHoursFee extends Model
{
    protected $fillable = [
        'time_from',
        'time_to',
        'applies_to',
        'cost_cents',
        'max_combined_charge_cents',
        'vehicle_ids',
        'location_ids',
        'weekday_filter',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'cost_cents' => 'integer',
            'max_combined_charge_cents' => 'integer',
            'vehicle_ids' => 'array',
            'location_ids' => 'array',
            'weekday_filter' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
