<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'main_category_id' => $this->main_category_id,
            'main_category_slug' => $this->whenLoaded('mainCategory', fn () => $this->mainCategory?->slug),
            'main_category_name' => $this->whenLoaded('mainCategory', fn () => $this->mainCategory?->name),
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'sort_order' => $this->sort_order,
            'is_search_filter' => $this->is_search_filter,
            // Backward-compatible aliases for storefront filters.
            'category_id' => $this->id,
            'category_name' => $this->name,
        ];
    }
}
