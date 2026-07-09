<?php

namespace App\Services\Admin;

use Illuminate\Http\Request;

class AdminCalendarFilters
{
    /**
     * @param  list<string>  $resourceIds  FullCalendar resource ids like "car:12" / "guesthouse:3"
     */
    public function __construct(
        public readonly string $listingType = 'all',
        public readonly ?int $hostId = null,
        public readonly ?string $city = null,
        public readonly ?string $status = null,
        public readonly ?string $search = null,
        public readonly array $resourceIds = [],
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
            resourceIds: self::parseResourceIds($request),
        );
    }

    /**
     * @return list<string>
     */
    private static function parseResourceIds(Request $request): array
    {
        $raw = $request->query('resource_ids', $request->query('resource_id'));
        if ($raw === null || $raw === '') {
            return [];
        }

        $parts = is_array($raw) ? $raw : explode(',', (string) $raw);

        return collect($parts)
            ->map(fn ($id) => trim((string) $id))
            ->filter(fn (string $id) => (bool) preg_match('/^(car|guesthouse):\d+$/', $id))
            ->unique()
            ->values()
            ->all();
    }

    public function includesVehicles(): bool
    {
        if ($this->resourceIds !== []) {
            return collect($this->resourceIds)->contains(fn (string $id) => str_starts_with($id, 'car:'));
        }

        return $this->listingType === 'all' || $this->listingType === 'vehicle';
    }

    public function includesGuesthouses(): bool
    {
        if ($this->resourceIds !== []) {
            return collect($this->resourceIds)->contains(fn (string $id) => str_starts_with($id, 'guesthouse:'));
        }

        return $this->listingType === 'all' || $this->listingType === 'guesthouse';
    }

    /**
     * @return list<int>
     */
    public function carIds(): array
    {
        return collect($this->resourceIds)
            ->filter(fn (string $id) => str_starts_with($id, 'car:'))
            ->map(fn (string $id) => (int) substr($id, 4))
            ->filter(fn (int $id) => $id > 0)
            ->values()
            ->all();
    }

    /**
     * @return list<int>
     */
    public function guestHouseIds(): array
    {
        return collect($this->resourceIds)
            ->filter(fn (string $id) => str_starts_with($id, 'guesthouse:'))
            ->map(fn (string $id) => (int) substr($id, strlen('guesthouse:')))
            ->filter(fn (int $id) => $id > 0)
            ->values()
            ->all();
    }
}
