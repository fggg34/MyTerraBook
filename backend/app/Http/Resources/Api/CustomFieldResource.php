<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomFieldResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'field_key' => $this->field_key,
            'label' => $this->label,
            'type' => $this->type,
            'is_required' => $this->is_required,
            'is_email' => $this->is_email,
            'popup_link_url' => $this->popup_link_url,
            'select_options' => $this->select_options ?? [],
        ];
    }
}
