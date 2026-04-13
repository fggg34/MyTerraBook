<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CarDistinctiveFeatureDefinition extends Model
{
    protected $fillable = [
        'car_id',
        'name',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    public function unitValues(): HasMany
    {
        return $this->hasMany(CarUnitDistinctiveValue::class, 'car_distinctive_feature_definition_id');
    }
}
