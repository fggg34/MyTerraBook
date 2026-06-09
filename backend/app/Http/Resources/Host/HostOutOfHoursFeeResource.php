<?php

namespace App\Http\Resources\Host;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HostOutOfHoursFeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'time_from' => $this->formatTime($this->time_from),
            'time_to' => $this->formatTime($this->time_to),
            'applies_to' => $this->applies_to,
            'pickup_cost_cents' => (int) ($this->pickup_cost_cents ?? $this->cost_cents ?? 0),
            'dropoff_cost_cents' => (int) ($this->dropoff_cost_cents ?? $this->cost_cents ?? 0),
            'pickup_cost_euros' => round((int) ($this->pickup_cost_cents ?? $this->cost_cents ?? 0) / 100, 2),
            'dropoff_cost_euros' => round((int) ($this->dropoff_cost_cents ?? $this->cost_cents ?? 0) / 100, 2),
            'location_ids' => $this->location_ids ?? [],
            'vehicle_ids' => $this->vehicle_ids ?? [],
            'is_active' => $this->is_active,
        ];
    }

    private function formatTime(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $str = (string) $value;
        if (preg_match('/^(\d{1,2}):(\d{2})/', $str, $m)) {
            return sprintf('%02d:%02d', (int) $m[1], (int) $m[2]);
        }

        return $str;
    }
}
