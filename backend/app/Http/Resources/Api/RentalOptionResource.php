<?php

namespace App\Http\Resources\Api;

use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class RentalOptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $pivotCost = $this->resource->pivot?->cost_cents;
        $costCents = $pivotCost !== null ? (int) $pivotCost : (int) $this->cost_cents;
        $isDailyCost = $this->resource->pivot?->is_daily_cost ?? $this->is_daily_cost;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'icon' => $this->icon,
            'icon_url' => $this->image_path ? Storage::disk('public')->url($this->image_path) : null,
            'description' => $this->description,
            'cost' => Money::formatDecimalFromCents($costCents),
            'cost_cents' => $costCents,
            'is_daily_cost' => (bool) $isDailyCost,
            'has_quantity' => $this->has_quantity,
            'is_mandatory' => $this->is_mandatory,
        ];
    }
}
