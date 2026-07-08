<?php

namespace App\Services\Admin;

use Illuminate\Http\Request;

class AdminCalendarFilters
{
    public function __construct(
        public readonly string $listingType = 'all',
        public readonly ?int $hostId = null,
        public readonly ?string $city = null,
        public readonly ?string $status = null,
        public readonly ?string $search = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $listingType = $request->string('listing_type', 'all')->toString();
        if (! in_array($listingType, ['all', 'vehicle', 'guesthouse'], true)) {
            $listingType = 'all';
        }

        return new self(
            listingType: $listingType,
            hostId: $request->filled('host_id') ? (int) $request->query('host_id') : null,
            city: $request->filled('city') ? trim($request->string('city')->toString()) : null,
            status: $request->filled('status') ? trim($request->string('status')->toString()) : null,
            search: $request->filled('search') ? trim($request->string('search')->toString()) : null,
        );
    }

    public function includesVehicles(): bool
    {
        return $this->listingType === 'all' || $this->listingType === 'vehicle';
    }

    public function includesGuesthouses(): bool
    {
        return $this->listingType === 'all' || $this->listingType === 'guesthouse';
    }
}
