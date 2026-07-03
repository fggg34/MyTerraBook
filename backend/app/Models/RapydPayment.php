<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RapydPayment extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'host_id',
        'checkout_id',
        'payment_id',
        'total_price',
        'platform_fee',
        'cash_due_on_arrival',
        'currency',
        'status',
        'paid_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'total_price' => 'decimal:2',
            'platform_fee' => 'decimal:2',
            'cash_due_on_arrival' => 'decimal:2',
            'paid_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(GuestHouseBooking::class, 'order_id');
    }
}
