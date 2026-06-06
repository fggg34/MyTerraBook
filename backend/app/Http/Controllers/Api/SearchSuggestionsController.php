<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GuestHouse;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchSuggestionsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $scope = $request->string('scope')->toString();
        $limit = min(20, max(1, (int) $request->query('limit', 8)));
        $q = trim($request->string('q')->toString());

        return match ($scope) {
            'location' => response()->json([
                'data' => $this->locationSuggestions($request, $q, $limit),
            ]),
            'guesthouse' => response()->json([
                'data' => $this->guesthouseSuggestions($q, $limit),
            ]),
            default => response()->json(['message' => 'Invalid scope. Use location or guesthouse.'], 422),
        };
    }

    /**
     * @return list<array{id: string, label: string, subtitle: string|null, type: string, value: string}>
     */
    private function locationSuggestions(Request $request, string $q, int $limit): array
    {
        $role = $request->string('role', 'pickup')->toString();
        $pickupLocationId = $request->query('pickup_location_id');

        $query = Location::query()
            ->where('is_active', true)
            ->orderBy('name');

        if ($role === 'pickup') {
            $query->whereHas('cars', fn ($carQuery) => $carQuery->where('car_location.allows_pickup', true));
        } else {
            $query->whereHas('cars', fn ($carQuery) => $carQuery->where('car_location.allows_dropoff', true));

            if ($pickupLocationId) {
                $pickup = Location::query()->find($pickupLocationId);
                if ($pickup !== null) {
                    $allowedDropoffs = $pickup->dropoffCombinations()->pluck('id');
                    if ($allowedDropoffs->isNotEmpty()) {
                        $query->whereIn('id', $allowedDropoffs);
                    }
                }
            }
        }

        if ($q !== '') {
            $needle = '%'.$q.'%';
            $query->where(function ($builder) use ($needle) {
                $builder->where('name', 'like', $needle)
                    ->orWhere('address', 'like', $needle);
            });
        }

        return $query
            ->limit($limit)
            ->get()
            ->map(fn (Location $location) => [
                'id' => (string) $location->id,
                'label' => $location->name,
                'subtitle' => $location->address,
                'type' => 'location',
                'value' => (string) $location->id,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{id: string, label: string, subtitle: string|null, type: string, value: string}>
     */
    private function guesthouseSuggestions(string $q, int $limit): array
    {
        $results = [];
        $seen = [];

        $cityQuery = GuestHouse::query()
            ->active()
            ->whereNotNull('city')
            ->where('city', '!=', '');

        if ($q !== '') {
            $cityQuery->where('city', 'like', '%'.$q.'%');
        }

        $cities = $cityQuery
            ->select('city', DB::raw('COUNT(*) as stays_count'))
            ->groupBy('city')
            ->orderBy('city')
            ->limit($limit)
            ->get();

        foreach ($cities as $row) {
            $city = (string) $row->city;
            $key = 'city:'.$city;
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $count = (int) $row->stays_count;
            $results[] = [
                'id' => $key,
                'label' => $city,
                'subtitle' => $count === 1 ? '1 stay' : "{$count} stays",
                'type' => 'city',
                'value' => $city,
            ];
            if (count($results) >= $limit) {
                return $results;
            }
        }

        $houseQuery = GuestHouse::query()
            ->active()
            ->orderBy('name');

        if ($q !== '') {
            $needle = '%'.$q.'%';
            $houseQuery->where(function ($builder) use ($needle) {
                $builder->where('name', 'like', $needle)
                    ->orWhere('city', 'like', $needle)
                    ->orWhere('address', 'like', $needle);
            });
        }

        $houses = $houseQuery->limit($limit)->get();

        foreach ($houses as $house) {
            $key = 'house:'.$house->slug;
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $results[] = [
                'id' => $key,
                'label' => $house->name,
                'subtitle' => trim(collect([$house->city, $house->type?->value])->filter()->join(' · ')) ?: null,
                'type' => 'guesthouse',
                'value' => (string) ($house->city ?? $house->name),
            ];
            if (count($results) >= $limit) {
                break;
            }
        }

        return $results;
    }
}
