<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HourlyFare extends Model
{
    protected $fillable = [
        'car_id',
        'price_type_id',
        'min_minutes',
        'max_minutes',
        'total_price_cents',
    ];

    protected function casts(): array
    {
        return [
            'min_minutes' => 'integer',
            'max_minutes' => 'integer',
            'total_price_cents' => 'integer',
        ];
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    public function priceType(): BelongsTo
    {
        return $this->belongsTo(PriceType::class);
    }
}
