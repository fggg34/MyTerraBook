<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarDamageMarker extends Model
{
    protected $fillable = [
        'car_unit_id',
        'diagram_key',
        'position_x',
        'position_y',
        'description',
        'icon_path',
        'marked_at',
    ];

    protected function casts(): array
    {
        return [
            'position_x' => 'decimal:2',
            'position_y' => 'decimal:2',
            'marked_at' => 'datetime',
        ];
    }

    public function carUnit(): BelongsTo
    {
        return $this->belongsTo(CarUnit::class, 'car_unit_id');
    }
}
