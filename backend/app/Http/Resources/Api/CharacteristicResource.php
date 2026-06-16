<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CharacteristicResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'icon' => $this->icon,
            'icon_url' => $this->icon_path ? Storage::disk('public')->url($this->icon_path) : null,
            'display_text' => $this->display_text ?? $this->name,
            'is_search_filter' => $this->is_search_filter,
        ];
    }
}
