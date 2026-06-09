<?php

namespace App\Http\Resources\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HostProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var User $host */
        $host = $this->resource;

        return [
            'name' => $host->name,
            'member_since' => $host->created_at?->toDateString(),
        ];
    }
}
