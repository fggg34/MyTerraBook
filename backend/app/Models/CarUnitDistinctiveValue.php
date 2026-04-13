<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarUnitDistinctiveValue extends Model
{
    protected $fillable = [
        'car_unit_id',
        'car_distinctive_feature_definition_id',
        'value',
    ];

    public function carUnit(): BelongsTo
    {
        return $this->belongsTo(CarUnit::class, 'car_unit_id');
    }

    public function definition(): BelongsTo
    {
        return $this->belongsTo(CarDistinctiveFeatureDefinition::class, 'car_distinctive_feature_definition_id');
    }
}
