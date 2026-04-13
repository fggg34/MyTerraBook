<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderRentalOption extends Model
{
    protected $fillable = [
        'order_id',
        'rental_option_id',
        'quantity',
        'unit_price_cents',
        'total_cents',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price_cents' => 'integer',
            'total_cents' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function rentalOption(): BelongsTo
    {
        return $this->belongsTo(RentalOption::class);
    }
}
