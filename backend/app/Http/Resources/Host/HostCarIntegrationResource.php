<?php

namespace App\Http\Resources\Host;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HostCarIntegrationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'units_available' => $this->units_available,
            'listing_status' => $this->listing_status?->value,
            'integration_token' => $this->integration_token,
            'blocked_days_endpoint' => url("/api/integrations/cars/{$this->id}/blocked-days"),
        ];
    }
}
