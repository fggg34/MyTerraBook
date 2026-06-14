<?php

namespace Database\Seeders;

use App\Enums\GuestHouseCancellationPolicy;
use App\Enums\GuestHouseStatus;
use App\Enums\GuestHouseType;
use App\Models\Car;
use App\Models\CarDistinctiveFeatureDefinition;
use App\Models\CarUnit;
use App\Models\CarUnitDistinctiveValue;
use App\Models\SubCategory;
use App\Models\Characteristic;
use App\Models\DailyFare;
use App\Models\ExtraHourFare;
use App\Models\GuestHouse;
use App\Models\GuestHouseAmenity;
use App\Models\GuestHouseImage;
use App\Models\GuestHouseSeasonalPrice;
use App\Models\ListingReview;
use App\Models\Location;
use App\Models\PriceType;
use App\Models\RentalOption;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoShowcaseSeeder extends Seeder
{
    public function run(): void
    {
        $cars = $this->seedDemoCars();
        $campervans = $this->seedDemoCampervans();
        $guesthouses = $this->seedDemoGuesthouses();

        $this->command?->info('Demo listings ready for review:');
        $this->command?->info('Cars:');
        foreach ($cars as $car) {
            $this->command?->info("  /cars/{$car->id}, {$car->name}");
        }
        $this->command?->info('Campervans:');
        foreach ($campervans as $van) {
            $this->command?->info("  /campervans/{$van->id}, {$van->name}");
        }
        $this->command?->info('Guesthouses:');
        foreach ($guesthouses as $house) {
            $this->command?->info("  /guesthouses/{$house->slug}, {$house->name}");
        }
    }

    /** @return list<Car> */
    private function seedDemoCars(): array
    {
        $economy = SubCategory::query()->where('name', 'Economy')->firstOrFail();
        $compact = SubCategory::query()->where('name', 'Compact')->firstOrFail();
        $luxury = SubCategory::query()->where('name', 'Luxury')->firstOrFail();

        $specs = [
            [
                'slug' => 'toyota-yaris-2024',
                'name' => 'Toyota Yaris 2024',
                'sub_category_id' => $economy->id,
                'description' => 'Compact hybrid ideal for city trips and short tours. Automatic, Bluetooth, unlimited mileage.',
                'transmission' => 'automatic',
                'fuel_type' => 'hybrid',
                'rate' => 3200,
                'image' => 'https://placehold.co/1200x800/0f766e/fff?text=Toyota+Yaris',
                'chars' => ['GPS Navigation', 'Bluetooth', 'USB Port', 'Air Conditioning'],
            ],
            [
                'slug' => 'vw-golf-2023',
                'name' => 'Volkswagen Golf 2023',
                'sub_category_id' => $compact->id,
                'description' => 'Comfortable compact with great fuel economy. Perfect for couples exploring the south coast.',
                'transmission' => 'manual',
                'fuel_type' => 'petrol',
                'rate' => 3800,
                'image' => 'https://placehold.co/1200x800/1e40af/fff?text=VW+Golf',
                'chars' => ['GPS Navigation', 'Bluetooth', 'Air Conditioning', 'Cruise Control'],
            ],
            [
                'slug' => 'bmw-3-series-2024',
                'name' => 'BMW 3 Series 2024',
                'sub_category_id' => $luxury->id,
                'description' => 'Premium sedan with leather interior and advanced safety features for a refined road trip.',
                'transmission' => 'automatic',
                'fuel_type' => 'diesel',
                'rate' => 8900,
                'image' => 'https://placehold.co/1200x800/1f2937/fff?text=BMW+3+Series',
                'chars' => ['GPS Navigation', 'Bluetooth', 'Parking Sensors', 'Backup Camera'],
            ],
        ];

        return array_map(fn (array $data) => $this->seedVehicle($data, 'car'), $specs);
    }

    /** @return list<Car> */
    private function seedDemoCampervans(): array
    {
        $van = SubCategory::query()->where('name', 'Van')->firstOrFail();

        $specs = [
            [
                'slug' => 'vw-california-2023',
                'name' => 'VW California 2023',
                'sub_category_id' => $van->id,
                'description' => 'Pop-top campervan sleeping four with kitchenette and camping essentials. Ideal for the Ring Road.',
                'transmission' => 'automatic',
                'fuel_type' => 'diesel',
                'rate' => 12500,
                'units' => 1,
                'image' => 'https://placehold.co/1200x800/7c2d12/fff?text=VW+California',
                'chars' => ['GPS Navigation', '4WD', 'Air Conditioning', 'Cruise Control', 'Backup Camera'],
            ],
            [
                'slug' => 'mercedes-marco-polo-2022',
                'name' => 'Mercedes Marco Polo 2022',
                'sub_category_id' => $van->id,
                'description' => 'Luxury campervan with standing room, diesel heater, and full camping kit. Sleeps 4 comfortably.',
                'transmission' => 'automatic',
                'fuel_type' => 'diesel',
                'rate' => 14200,
                'units' => 2,
                'image' => 'https://placehold.co/1200x800/374151/fff?text=Marco+Polo',
                'chars' => ['GPS Navigation', '4WD', 'Air Conditioning', 'Cruise Control'],
            ],
            [
                'slug' => 'ford-transit-custom-2024',
                'name' => 'Ford Transit Custom 2024',
                'sub_category_id' => $van->id,
                'description' => 'Spacious high-roof camper with shower option and room for five. Built for long Icelandic adventures.',
                'transmission' => 'automatic',
                'fuel_type' => 'diesel',
                'rate' => 11800,
                'units' => 1,
                'image' => 'https://placehold.co/1200x800/92400e/fff?text=Transit+Custom',
                'chars' => ['GPS Navigation', '4WD', 'Backup Camera', 'Cruise Control'],
            ],
        ];

        return array_map(fn (array $data) => $this->seedVehicle($data, 'campervan'), $specs);
    }

    /** @return list<GuestHouse> */
    private function seedDemoGuesthouses(): array
    {
        $amenityIds = GuestHouseAmenity::query()->pluck('id')->all();

        $specs = [
            [
                'slug' => 'reykjavik-retreat',
                'name' => 'Reykjavík Retreat',
                'type' => GuestHouseType::Apartment,
                'city' => 'Reykjavik',
                'base' => 9800,
                'bedrooms' => 2,
                'max_guests' => 4,
                'short' => 'Bright apartment on Laugavegur with modern amenities.',
                'thumb' => 'https://placehold.co/800x600/1e3a8a/fff?text=Reykjavik+Retreat',
            ],
            [
                'slug' => 'harbour-view-apartment',
                'name' => 'Harbour View Apartment',
                'type' => GuestHouseType::Apartment,
                'city' => 'Akureyri',
                'base' => 12000,
                'bedrooms' => 2,
                'max_guests' => 4,
                'short' => 'Waterfront apartment with mountain views in north Iceland.',
                'thumb' => 'https://placehold.co/800x600/0e7490/fff?text=Harbour+View',
            ],
            [
                'slug' => 'moss-cottage',
                'name' => 'Moss Cottage',
                'type' => GuestHouseType::Cottage,
                'city' => 'Vik',
                'base' => 9500,
                'bedrooms' => 2,
                'max_guests' => 4,
                'short' => 'Cozy cottage near black sand beaches and glacier views.',
                'thumb' => 'https://placehold.co/800x600/166534/fff?text=Moss+Cottage',
            ],
            [
                'slug' => 'northern-lights-villa',
                'name' => 'Northern Lights Villa',
                'type' => GuestHouseType::Villa,
                'city' => 'Reykjavik',
                'base' => 18500,
                'bedrooms' => 4,
                'max_guests' => 8,
                'short' => 'Spacious villa with hot tub, perfect for aurora watching.',
                'thumb' => 'https://placehold.co/800x600/312e81/fff?text=Northern+Lights+Villa',
            ],
        ];

        $houses = [];
        foreach ($specs as $index => $data) {
            $houses[] = $this->seedGuesthouse($data, $amenityIds, $index);
        }

        return $houses;
    }

    private function seedVehicle(array $data, string $kind): Car
    {
        $car = Car::query()->updateOrCreate(
            ['slug' => $data['slug']],
            [
                'name' => $data['name'],
                'sub_category_id' => $data['sub_category_id'],
                'description' => $data['description'],
                'transmission' => $data['transmission'],
                'fuel_type' => $data['fuel_type'],
                'units_available' => $data['units'] ?? 2,
                'is_active' => true,
                'main_image_path' => $data['image'],
                'details_image_paths' => [
                    str_replace('text=', 'text=Interior+', $data['image']),
                    str_replace('text=', 'text=Detail+', $data['image']),
                ],
            ],
        );

        $this->wireVehicle($car, $data['rate'], $data['chars']);

        $this->seedCarReviews($car, [
            ['guest_name' => 'Anna K.', 'rating' => 5, 'body' => "Excellent {$kind}, smooth booking and clear pricing."],
            ['guest_name' => 'Marco R.', 'rating' => 5, 'body' => 'Great vehicle, easy pickup and friendly handover.'],
        ]);

        return $car;
    }

    /** @param  list<int>  $amenityIds */
    private function seedGuesthouse(array $data, array $amenityIds, int $index): GuestHouse
    {
        $house = GuestHouse::query()->updateOrCreate(
            ['slug' => $data['slug']],
            [
                'name' => $data['name'],
                'short_description' => $data['short'],
                'description' => "Welcome to {$data['name']}. {$data['short']} Book your stay and explore Iceland with confidence.",
                'type' => $data['type'],
                'status' => GuestHouseStatus::Active,
                'address' => '12 Example Street',
                'city' => $data['city'],
                'country' => 'Iceland',
                'max_guests' => $data['max_guests'],
                'bedrooms' => $data['bedrooms'],
                'bathrooms' => 1,
                'beds' => $data['bedrooms'],
                'min_nights' => 1,
                'max_nights' => 14,
                'base_price_per_night' => $data['base'],
                'cleaning_fee' => 4500,
                'security_deposit' => 15000,
                'check_in_time' => '15:00:00',
                'check_out_time' => '11:00:00',
                'cancellation_policy' => GuestHouseCancellationPolicy::Moderate,
                'thumbnail' => $data['thumb'],
            ],
        );

        $house->amenities()->sync(array_slice($amenityIds, 0, 8 + $index));

        for ($i = 1; $i <= 3; $i++) {
            GuestHouseImage::query()->firstOrCreate(
                [
                    'guest_house_id' => $house->id,
                    'path' => str_replace('.co/800', ".co/800/{$i}", $data['thumb']),
                ],
                ['sort_order' => $i, 'caption' => "Photo {$i}"],
            );
        }

        GuestHouseSeasonalPrice::query()->updateOrCreate(
            ['guest_house_id' => $house->id, 'name' => 'Summer peak'],
            [
                'date_from' => Carbon::parse('2026-06-01'),
                'date_to' => Carbon::parse('2026-08-31'),
                'price_per_night' => (int) ($data['base'] * 1.35),
                'minimum_nights' => 2,
            ],
        );

        ListingReview::query()->firstOrCreate(
            [
                'reviewable_type' => GuestHouse::class,
                'reviewable_id' => $house->id,
                'guest_name' => 'Guest '.($index + 1),
            ],
            [
                'rating' => 5,
                'body' => 'Wonderful stay, exactly as described. Would book again.',
                'is_approved' => true,
            ],
        );

        return $house;
    }

    private function wireVehicle(Car $car, int $dailyRateCents, array $characteristicNames): void
    {
        $locations = Location::query()->where('is_active', true)->get();
        $characteristics = Characteristic::query()
            ->whereIn('name', $characteristicNames)
            ->pluck('id');
        $rentalOptions = RentalOption::query()->where('is_active', true)->take(5)->pluck('id');
        $basic = PriceType::query()->where('slug', 'basic')->firstOrFail();
        $plus = PriceType::query()->where('slug', 'plus')->firstOrFail();
        $max = PriceType::query()->where('slug', 'max')->firstOrFail();

        $locationPivot = $locations->mapWithKeys(fn (Location $loc) => [
            $loc->id => ['allows_pickup' => true, 'allows_dropoff' => true],
        ])->all();
        $car->locations()->sync($locationPivot);
        $car->characteristics()->sync($characteristics);
        $car->rentalOptions()->sync($rentalOptions);

        foreach ([
            [$basic->id, 1, 6, $dailyRateCents],
            [$basic->id, 7, 30, (int) round($dailyRateCents * 0.85)],
            [$plus->id, 1, 30, (int) round($dailyRateCents * 1.2)],
            [$max->id, 1, 30, (int) round($dailyRateCents * 1.45)],
        ] as [$ptId, $from, $to, $cents]) {
            DailyFare::query()->updateOrCreate(
                [
                    'car_id' => $car->id,
                    'price_type_id' => $ptId,
                    'from_days' => $from,
                    'to_days' => $to,
                ],
                ['price_per_day_cents' => $cents],
            );
        }

        ExtraHourFare::query()->updateOrCreate(
            ['car_id' => $car->id, 'price_type_id' => $basic->id],
            ['charge_per_extra_hour_cents' => (int) round($dailyRateCents / 8)],
        );

        $plateDef = CarDistinctiveFeatureDefinition::query()->firstOrCreate(
            ['car_id' => $car->id, 'name' => 'License Plate'],
            ['sort_order' => 0],
        );
        $colorDef = CarDistinctiveFeatureDefinition::query()->firstOrCreate(
            ['car_id' => $car->id, 'name' => 'Color'],
            ['sort_order' => 1],
        );

        for ($i = 0; $i < $car->units_available; $i++) {
            $unit = CarUnit::query()->firstOrCreate(
                ['car_id' => $car->id, 'sort_order' => $i],
                ['is_active' => true],
            );
            CarUnitDistinctiveValue::query()->firstOrCreate(
                ['car_unit_id' => $unit->id, 'car_distinctive_feature_definition_id' => $plateDef->id],
                ['value' => 'IS-'.str_pad((string) ($car->id * 10 + $i), 3, '0', STR_PAD_LEFT)],
            );
            CarUnitDistinctiveValue::query()->firstOrCreate(
                ['car_unit_id' => $unit->id, 'car_distinctive_feature_definition_id' => $colorDef->id],
                ['value' => $i === 0 ? 'White' : 'Silver'],
            );
        }
    }

    /** @param  list<array{guest_name: string, rating: int, body: string}>  $reviews */
    private function seedCarReviews(Car $car, array $reviews): void
    {
        foreach ($reviews as $review) {
            ListingReview::query()->firstOrCreate(
                [
                    'reviewable_type' => Car::class,
                    'reviewable_id' => $car->id,
                    'guest_name' => $review['guest_name'],
                ],
                [
                    'rating' => $review['rating'],
                    'body' => $review['body'],
                    'is_approved' => true,
                ],
            );
        }
    }
}
