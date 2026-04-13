<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExtraHourFare extends Model
{
    protected $fillable = [
        'car_id',
        'price_type_id',
        'charge_per_extra_hour_cents',
    ];

    protected function casts(): array
    {
        return [
            'charge_per_extra_hour_cents' => 'integer',
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
