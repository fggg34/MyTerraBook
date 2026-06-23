<?php

namespace App\Http\Resources\Host;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HostIntegrationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'integration_token' => $this->integration_token,
            'blocked_days_endpoint' => url('/api/integrations/blocked-days'),
            'vehicles' => $this->cars()
                ->orderBy('name')
                ->get(['id', 'name', 'units_available'])
                ->map(fn ($car) => [
                    'id' => $car->id,
                    'name' => $car->name,
                    'units_available' => max(1, (int) $car->units_available),
                ])
                ->values()
                ->all(),
        ];
    }
}
