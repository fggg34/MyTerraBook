<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutOfHoursFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'time_from',
        'time_to',
        'applies_to',
        'cost_cents',
        'pickup_cost_cents',
        'dropoff_cost_cents',
        'max_combined_charge_cents',
        'tax_rate_id',
        'vehicle_ids',
        'location_ids',
        'weekday_filter',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'cost_cents' => 'integer',
            'pickup_cost_cents' => 'integer',
            'dropoff_cost_cents' => 'integer',
            'max_combined_charge_cents' => 'integer',
            'tax_rate_id' => 'integer',
            'vehicle_ids' => 'array',
            'location_ids' => 'array',
            'weekday_filter' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class);
    }
}
