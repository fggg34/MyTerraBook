<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CarUnit extends Model
{
    protected $fillable = [
        'car_id',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    public function distinctiveValues(): HasMany
    {
        return $this->hasMany(CarUnitDistinctiveValue::class, 'car_unit_id');
    }

    public function damageMarkers(): HasMany
    {
        return $this->hasMany(CarDamageMarker::class, 'car_unit_id');
    }
}
