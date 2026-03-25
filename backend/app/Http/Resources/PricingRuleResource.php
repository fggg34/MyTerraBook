<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PricingRuleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'rule_kind' => $this->rule_kind,
            'car_id' => $this->car_id,
            'location_id' => $this->location_id,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
            'time_unit' => $this->time_unit,
            'amount' => $this->amount,
            'adjustment' => $this->adjustment,
            'priority' => $this->priority,
            'is_active' => $this->is_active,
        ];
    }
}
