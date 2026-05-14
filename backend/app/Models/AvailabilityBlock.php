<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvailabilityBlock extends Model
{
    protected $fillable = [
        'car_id',
        'source',
        'starts_at',
        'ends_at',
        'units_blocked',
        'external_uid',
        'external_calendar',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'units_blocked' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }
}
