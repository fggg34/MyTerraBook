<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value,
            'expires_at' => $this->expires_at,
            'usage_limit' => $this->usage_limit,
            'times_used' => $this->times_used,
            'min_order_amount' => $this->min_order_amount,
            'is_active' => $this->is_active,
        ];
    }
}
