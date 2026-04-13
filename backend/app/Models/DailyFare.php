<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyFare extends Model
{
    protected $fillable = [
        'car_id',
        'price_type_id',
        'from_days',
        'to_days',
        'price_per_day_cents',
    ];

    protected function casts(): array
    {
        return [
            'from_days' => 'integer',
            'to_days' => 'integer',
            'price_per_day_cents' => 'integer',
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
