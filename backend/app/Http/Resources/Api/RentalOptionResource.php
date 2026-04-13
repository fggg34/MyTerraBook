<?php

namespace App\Http\Resources\Api;

use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RentalOptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'cost' => Money::formatDecimalFromCents((int) $this->cost_cents),
            'cost_cents' => (int) $this->cost_cents,
            'is_daily_cost' => $this->is_daily_cost,
            'has_quantity' => $this->has_quantity,
            'is_mandatory' => $this->is_mandatory,
        ];
    }
}
