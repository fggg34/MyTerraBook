<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'discount_type',
        'discount_fixed_cents',
        'discount_percent_bips',
        'vehicle_ids',
        'valid_from',
        'valid_to',
        'min_order_total_cents',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'vehicle_ids' => 'array',
            'valid_from' => 'date',
            'valid_to' => 'date',
            'discount_fixed_cents' => 'integer',
            'discount_percent_bips' => 'integer',
            'min_order_total_cents' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(CouponRedemption::class);
    }
}
