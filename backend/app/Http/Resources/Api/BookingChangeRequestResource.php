<?php

namespace App\Http\Resources\Api;

use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingChangeRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'status' => $this->status->value,
            'customer_message' => $this->customer_message,
            'requested_changes' => $this->requested_changes,
            'admin_response' => $this->admin_response,
            'pricing_before' => $this->pricing_before,
            'pricing_after' => $this->pricing_after,
            'price_delta_cents' => $this->price_delta_cents,
            'price_delta_formatted' => $this->price_delta_cents !== null
                ? Money::formatDecimalFromCents(abs($this->price_delta_cents))
                : null,
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'applied_at' => $this->applied_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
