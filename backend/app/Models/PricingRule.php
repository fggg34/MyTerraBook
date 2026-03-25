<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricingRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'rule_kind',
        'car_id',
        'location_id',
        'date_from',
        'date_to',
        'time_unit',
        'amount',
        'adjustment',
        'priority',
        'min_duration_days',
        'min_duration_hours',
        'is_active',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
        'amount' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
