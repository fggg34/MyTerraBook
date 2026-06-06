<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SearchPromotionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kicker' => $this->kicker,
            'title' => $this->title,
            'text' => $this->text,
            'cta' => $this->cta_label,
            'href' => $this->cta_href,
            'layout' => $this->layout,
            'context' => $this->context,
            'insert_after' => $this->insert_after,
            'image' => $this->image_path ? Storage::disk('public')->url($this->image_path) : null,
            'image_alt' => $this->image_alt,
            'sort_order' => $this->sort_order,
        ];
    }
}
