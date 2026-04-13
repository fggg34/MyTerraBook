<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocationFee extends Model
{
    protected $fillable = [
        'pickup_location_id',
        'dropoff_location_id',
        'cost_cents',
        'multiply_by_days',
        'tax_rate_id',
        'apply_inverted',
        'day_overrides',
        'is_one_way_fee',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'cost_cents' => 'integer',
            'multiply_by_days' => 'boolean',
            'apply_inverted' => 'boolean',
            'day_overrides' => 'array',
            'is_one_way_fee' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function pickupLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'pickup_location_id');
    }

    public function dropoffLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'dropoff_location_id');
    }

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class);
    }
}
