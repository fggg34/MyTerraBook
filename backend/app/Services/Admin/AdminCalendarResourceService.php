<?php

namespace App\Services\Admin;

use App\Models\Car;
use App\Models\GuestHouse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class AdminCalendarResourceService
{
    /**
     * @return array{data: list<array<string, mixed>>, meta: array<string, mixed>}
     */
    public function list(AdminCalendarFilters $filters, Request $request): array
    {
        $perPage = min(100, max(10, (int) $request->query('per_page', 50)));
        $page = max(1, (int) $request->query('page', 1));

        $resources = collect();

        if ($filters->includesVehicles()) {
            $carQuery = Car::query()
                ->select(['id', 'name', 'user_id', 'units_available', 'listing_status', 'is_active'])
                ->with(['host:id,name,email', 'locations:id,name'])
                ->when($filters->hostId, fn ($q) => $q->where('user_id', $filters->hostId))
                ->when($filters->city, function ($q) use ($filters): void {
                    $q->whereHas('locations', fn ($loc) => $loc
                        ->where('name', 'like', '%'.$filters->city.'%')
                        ->orWhere('address', 'like', '%'.$filters->city.'%'));
                })
                ->when($filters->search, function ($q) use ($filters): void {
                    $term = '%'.$filters->search.'%';
                    $q->where(function ($inner) use ($term): void {
                        $inner->where('name', 'like', $term)
                            ->orWhereHas('host', fn ($h) => $h->where('name', 'like', $term));
                    });
                })
                ->orderBy('name');

            foreach ($carQuery->get() as $car) {
                $resources->push($this->mapCarResource($car));
            }
        }

        if ($filters->includesGuesthouses()) {
            $stayQuery = GuestHouse::query()
                ->select(['id', 'name', 'user_id', 'type', 'status', 'city'])
                ->with(['host:id,name,email'])
                ->when($filters->hostId, fn ($q) => $q->where('user_id', $filters->hostId))
                ->when($filters->city, fn ($q) => $q->where('city', 'like', '%'.$filters->city.'%'))
                ->when($filters->search, function ($q) use ($filters): void {
                    $term = '%'.$filters->search.'%';
                    $q->where(function ($inner) use ($term): void {
                        $inner->where('name', 'like', $term)
                            ->orWhere('city', 'like', $term)
                            ->orWhereHas('host', fn ($h) => $h->where('name', 'like', $term));
                    });
                })
                ->orderBy('name');

            foreach ($stayQuery->get() as $house) {
                $resources->push($this->mapGuestHouseResource($house));
            }
        }

        $sorted = $resources->sortBy('title')->values();
        $total = $sorted->count();
        $offset = ($page - 1) * $perPage;
        $slice = $sorted->slice($offset, $perPage)->values();

        return [
            'data' => $slice->all(),
            'meta' => [
                'current_page' => $page,
                'last_page' => max(1, (int) ceil($total / $perPage)),
                'per_page' => $perPage,
                'total' => $total,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapCarResource(Car $car): array
    {
        $location = $car->locations->first();

        return [
            'id' => 'car:'.$car->id,
            'title' => $car->name,
            'type' => 'vehicle',
            'hostId' => $car->user_id,
            'hostName' => $car->host?->name,
            'city' => $location?->name,
            'capacity' => max(1, (int) $car->units_available),
            'extendedProps' => [
                'listingStatus' => $car->listing_status?->value,
                'isActive' => $car->is_active,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapGuestHouseResource(GuestHouse $house): array
    {
        return [
            'id' => 'guesthouse:'.$house->id,
            'title' => $house->name,
            'type' => 'guesthouse',
            'hostId' => $house->user_id,
            'hostName' => $house->host?->name,
            'city' => $house->city,
            'capacity' => 1,
            'extendedProps' => [
                'listingType' => $house->type?->value,
                'listingStatus' => $house->status?->value,
            ],
        ];
    }
}
