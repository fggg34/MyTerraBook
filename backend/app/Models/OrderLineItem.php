<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderLineItem extends Model
{
    protected $fillable = [
        'order_id',
        'kind',
        'label',
        'amount_cents',
        'quantity',
        'meta',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'amount_cents' => 'integer',
            'quantity' => 'integer',
            'meta' => 'array',
            'sort_order' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
