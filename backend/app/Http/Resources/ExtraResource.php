<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExtraResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price_type' => $this->price_type,
            'unit_price' => $this->unit_price,
            'is_mandatory' => $this->is_mandatory,
            'max_quantity' => $this->max_quantity,
            'is_active' => $this->is_active,
        ];
    }
}
