<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'code',
        'name',
        'is_enabled',
        'auto_confirm_order',
        'charge_or_discount',
        'charge_discount_type',
        'charge_fixed_cents',
        'charge_percent_bips',
        'config',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'auto_confirm_order' => 'boolean',
            'config' => 'array',
            'charge_fixed_cents' => 'integer',
            'charge_percent_bips' => 'integer',
            'sort_order' => 'integer',
        ];
    }
}
