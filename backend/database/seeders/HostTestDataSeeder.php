<?php

namespace Database\Seeders;

use App\Enums\GuestHouseAvailabilityBlockReason;
use App\Enums\GuestHouseAvailabilityBlockSource;
use App\Enums\GuestHouseCancellationPolicy;
use App\Enums\GuestHouseStatus;
use App\Enums\GuestHouseType;
use App\Enums\ListingApprovalStatus;
use App\Enums\UserRole;
use App\Models\AvailabilityBlock;
use App\Models\Car;
use App\Models\CarDistinctiveFeatureDefinition;
use App\Models\CarUnit;
use App\Models\CarUnitDistinctiveValue;
use App\Models\Characteristic;
use App\Models\DailyFare;
use App\Models\ExtraHourFare;
use App\Models\GuestHouse;
use App\Models\GuestHouseAmenity;
use App\Models\GuestHouseAvailabilityBlock;
use App\Models\GuestHouseImage;
use App\Models\GuestHouseSeasonalPrice;
use App\Models\HourlyFare;
use App\Models\Location;
use App\Models\LocationFee;
use App\Models\OutOfHoursFee;
use App\Models\PriceType;
use App\Models\RentalOption;
use App\Models\SubCategory;
use App\Models\TaxRate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class HostTestDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->ensurePrerequisites();

        $admin = User::query()->where('email', 'admin@terrabook.test')->first();
        $standardTax = TaxRate::query()->firstOrCreate(
            ['name' => 'Standard VAT (10%)'],
            ['basis_points' => 1000],
        );
        $locations = Location::query()->where('is_active', true)->orderBy('id')->get();
        $allCharacteristicIds = Characteristic::query()->pluck('id')->all();
        $allRentalOptionIds = RentalOption::query()->where('is_active', true)->pluck('id')->all();
        $allAmenityIds = GuestHouseAmenity::query()->pluck('id')->all();
        $basic = $this->requirePriceType('basic');
        $plus = $this->requirePriceType('plus');
        $max = $this->requirePriceType('max');

        $luxuryCar = $this->requireSubCategory('Luxury');
        $motorhome = $this->requireSubCategory('Motorhome');

        $hosts = [
            [
                'email' => 'host.approved@terrabook.test',
                'name' => 'Einar Jónsson',
                'phone' => '+3545551001',
                'prefix' => 'einar',
                'listing_status' => ListingApprovalStatus::Approved,
                'guesthouse_status' => GuestHouseStatus::Active,
                'is_active' => true,
                'guesthouse_type' => GuestHouseType::Villa,
                'cancellation_policy' => GuestHouseCancellationPolicy::Flexible,
            ],
            [
                'email' => 'host.draft@terrabook.test',
                'name' => 'Sigríður Árnadóttir',
                'phone' => '+3545551002',
                'prefix' => 'sigridur',
                'listing_status' => ListingApprovalStatus::Draft,
                'guesthouse_status' => GuestHouseStatus::Draft,
                'is_active' => false,
                'guesthouse_type' => GuestHouseType::Cottage,
                'cancellation_policy' => GuestHouseCancellationPolicy::Moderate,
            ],
            [
                'email' => 'host.pending@terrabook.test',
                'name' => 'Björn Magnússon',
                'phone' => '+3545551003',
                'prefix' => 'bjorn',
                'listing_status' => ListingApprovalStatus::PendingReview,
                'guesthouse_status' => GuestHouseStatus::PendingReview,
                'is_active' => false,
                'guesthouse_type' => GuestHouseType::Chalet,
                'cancellation_policy' => GuestHouseCancellationPolicy::Strict,
            ],
        ];

        foreach ($hosts as $profile) {
            $host = User::query()->updateOrCreate(
                ['email' => $profile['email']],
                [
                    'name' => $profile['name'],
                    'phone' => $profile['phone'],
                    'password' => Hash::make('password'),
                    'role' => UserRole::Host,
                    'locale' => 'en',
                    'email_verified_at' => now(),
                ],
            );

            $car = $this->seedHostCar(
                host: $host,
                prefix: $profile['prefix'],
                kind: 'car',
                subCategory: $luxuryCar,
                listingStatus: $profile['listing_status'],
                isActive: $profile['is_active'],
                admin: $admin,
                locations: $locations,
                characteristicIds: $allCharacteristicIds,
                rentalOptionIds: $allRentalOptionIds,
                basic: $basic,
                plus: $plus,
                max: $max,
                standardTax: $standardTax,
                dailyRateCents: 8900,
                seats: 5,
                sleeps: null,
                bags: 3,
                transmission: 'automatic',
                fuelType: 'hybrid',
            );

            $campervan = $this->seedHostCar(
                host: $host,
                prefix: $profile['prefix'],
                kind: 'campervan',
                subCategory: $motorhome,
                listingStatus: $profile['listing_status'],
                isActive: $profile['is_active'],
                admin: $admin,
                locations: $locations,
                characteristicIds: $allCharacteristicIds,
                rentalOptionIds: $allRentalOptionIds,
                basic: $basic,
                plus: $plus,
                max: $max,
                standardTax: $standardTax,
                dailyRateCents: 14200,
                seats: 5,
                sleeps: 4,
                bags: 6,
                transmission: 'automatic',
                fuelType: 'diesel',
            );

            $guesthouse = $this->seedHostGuesthouse(
                host: $host,
                prefix: $profile['prefix'],
                status: $profile['guesthouse_status'],
                type: $profile['guesthouse_type'],
                cancellationPolicy: $profile['cancellation_policy'],
                admin: $admin,
                amenityIds: $allAmenityIds,
                standardTax: $standardTax,
            );

            $this->command?->info("Host {$host->email} (password: password)");
            $this->command?->info("  Car:       /cars/{$car->id}, {$car->name} [{$profile['listing_status']->value}]");
            $this->command?->info("  Campervan: /campervans/{$campervan->id}, {$campervan->name} [{$profile['listing_status']->value}]");
            $this->command?->info("  Stay:      /guesthouses/{$guesthouse->slug}, {$guesthouse->name} [{$profile['guesthouse_status']->value}]");
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Location>  $locations
     * @param  list<int>  $characteristicIds
     * @param  list<int>  $rentalOptionIds
     */
    private function seedHostCar(
        User $host,
        string $prefix,
        string $kind,
        SubCategory $subCategory,
        ListingApprovalStatus $listingStatus,
        bool $isActive,
        ?User $admin,
        $locations,
        array $characteristicIds,
        array $rentalOptionIds,
        PriceType $basic,
        PriceType $plus,
        PriceType $max,
        TaxRate $standardTax,
        int $dailyRateCents,
        ?int $seats,
        ?int $sleeps,
        ?int $bags,
        string $transmission,
        string $fuelType,
    ): Car {
        $slug = "{$prefix}-host-{$kind}";
        $name = ucfirst($kind).', '.ucfirst(str_replace('.', ' ', explode('@', $host->email)[0]));
        $imageBase = 'https://placehold.co/1200x800/'.($kind === 'car' ? '1e40af' : '7c2d12').'/fff?text='.urlencode($name);

        $submittedAt = in_array($listingStatus, [ListingApprovalStatus::PendingReview, ListingApprovalStatus::Approved], true)
            ? now()->subDays(2)
            : null;
        $reviewedAt = $listingStatus === ListingApprovalStatus::Approved ? now()->subDay() : null;

        $car = Car::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'user_id' => $host->id,
                'sub_category_id' => $subCategory->id,
                'name' => $name,
                'meta_title' => "{$name} | MyTerraBook",
                'meta_description' => "Fully equipped {$kind} listing with every host option enabled for QA testing.",
                'og_image' => $imageBase,
                'description' => "This is a comprehensive host-owned {$kind} seeded with all catalog relations, pricing tiers, units, fees, and availability blocks.",
                'transmission' => $transmission,
                'fuel_type' => $fuelType,
                'seats' => $seats,
                'sleeps' => $sleeps,
                'bags' => $bags,
                'main_image_path' => $imageBase,
                'details_image_paths' => [
                    str_replace('text=', 'text=Interior+', $imageBase),
                    str_replace('text=', 'text=Dashboard+', $imageBase),
                    str_replace('text=', 'text=Exterior+', $imageBase),
                ],
                'units_available' => 2,
                'ical_import_url' => 'https://calendar.example.test/feeds/'.$slug.'.ics',
                'pickup_time_from' => '08:00:00',
                'pickup_time_to' => '18:00:00',
                'dropoff_time_from' => '08:00:00',
                'dropoff_time_to' => '18:00:00',
                'is_active' => $isActive,
                'listing_status' => $listingStatus,
                'submitted_at' => $submittedAt,
                'reviewed_at' => $reviewedAt,
                'reviewed_by' => $reviewedAt ? $admin?->id : null,
                'rejection_reason' => null,
            ],
        );

        $locationPivot = [];
        foreach ($locations->values() as $index => $location) {
            $locationPivot[$location->id] = [
                'allows_pickup' => $index !== 2,
                'allows_dropoff' => $index !== 1,
            ];
        }
        $car->locations()->sync($locationPivot);
        $car->characteristics()->sync($characteristicIds);
        $car->rentalOptions()->sync($rentalOptionIds);

        foreach ([
            [$basic->id, 1, 6, $dailyRateCents],
            [$basic->id, 7, 30, (int) round($dailyRateCents * 0.85)],
            [$plus->id, 1, 6, (int) round($dailyRateCents * 1.2)],
            [$plus->id, 7, 30, (int) round($dailyRateCents * 1.1)],
            [$max->id, 1, 30, (int) round($dailyRateCents * 1.45)],
        ] as [$priceTypeId, $fromDays, $toDays, $cents]) {
            DailyFare::query()->updateOrCreate(
                [
                    'car_id' => $car->id,
                    'price_type_id' => $priceTypeId,
                    'from_days' => $fromDays,
                    'to_days' => $toDays,
                ],
                ['price_per_day_cents' => $cents],
            );
        }

        foreach ([$basic, $plus, $max] as $priceType) {
            ExtraHourFare::query()->updateOrCreate(
                ['car_id' => $car->id, 'price_type_id' => $priceType->id],
                ['charge_per_extra_hour_cents' => (int) round($dailyRateCents / 8)],
            );

            HourlyFare::query()->updateOrCreate(
                [
                    'car_id' => $car->id,
                    'price_type_id' => $priceType->id,
                    'min_minutes' => 60,
                    'max_minutes' => 480,
                ],
                ['total_price_cents' => (int) round($dailyRateCents * 0.6)],
            );
        }

        $featureDefs = [
            ['name' => 'License Plate', 'sort_order' => 0],
            ['name' => 'Color', 'sort_order' => 1],
            ['name' => 'VIN', 'sort_order' => 2],
        ];
        $definitions = [];
        foreach ($featureDefs as $def) {
            $definitions[] = CarDistinctiveFeatureDefinition::query()->updateOrCreate(
                ['car_id' => $car->id, 'name' => $def['name']],
                ['sort_order' => $def['sort_order']],
            );
        }

        $unitColors = ['White', 'Silver'];
        $unitPlates = ['IS-'.strtoupper(substr($prefix, 0, 3)).'01', 'IS-'.strtoupper(substr($prefix, 0, 3)).'02'];
        $unitVins = ['VIN'.strtoupper($prefix).'001', 'VIN'.strtoupper($prefix).'002'];

        for ($i = 0; $i < $car->units_available; $i++) {
            $unit = CarUnit::query()->updateOrCreate(
                ['car_id' => $car->id, 'sort_order' => $i],
                ['is_active' => true],
            );

            CarUnitDistinctiveValue::query()->updateOrCreate(
                ['car_unit_id' => $unit->id, 'car_distinctive_feature_definition_id' => $definitions[0]->id],
                ['value' => $unitPlates[$i]],
            );
            CarUnitDistinctiveValue::query()->updateOrCreate(
                ['car_unit_id' => $unit->id, 'car_distinctive_feature_definition_id' => $definitions[1]->id],
                ['value' => $unitColors[$i]],
            );
            CarUnitDistinctiveValue::query()->updateOrCreate(
                ['car_unit_id' => $unit->id, 'car_distinctive_feature_definition_id' => $definitions[2]->id],
                ['value' => $unitVins[$i]],
            );
        }

        AvailabilityBlock::query()->updateOrCreate(
            [
                'car_id' => $car->id,
                'source' => 'maintenance',
                'starts_at' => now()->addDays(30)->startOfDay(),
                'ends_at' => now()->addDays(32)->endOfDay(),
            ],
            [
                'units_blocked' => 1,
                'notes' => 'Scheduled service block for QA',
                'is_active' => true,
            ],
        );

        if ($locations->count() >= 2) {
            $pickup = $locations->first();
            $dropoff = $locations->skip(1)->first();

            LocationFee::query()->updateOrCreate(
                [
                    'car_id' => $car->id,
                    'pickup_location_id' => $pickup->id,
                    'dropoff_location_id' => $dropoff->id,
                ],
                [
                    'cost_cents' => 4900,
                    'multiply_by_days' => false,
                    'tax_rate_id' => $standardTax->id,
                    'apply_inverted' => false,
                    'day_overrides' => ['1' => 3900, '7' => 2900],
                    'is_one_way_fee' => true,
                    'is_active' => true,
                ],
            );
        }

        OutOfHoursFee::query()->updateOrCreate(
            ['name' => "Late hours, {$slug}"],
            [
                'time_from' => '20:00:00',
                'time_to' => '08:00:00',
                'applies_to' => 'both',
                'cost_cents' => 3500,
                'pickup_cost_cents' => 3500,
                'dropoff_cost_cents' => 2500,
                'max_combined_charge_cents' => 5000,
                'tax_rate_id' => $standardTax->id,
                'vehicle_ids' => [$car->id],
                'location_ids' => $locations->take(2)->pluck('id')->all(),
                'weekday_filter' => [1, 2, 3, 4, 5, 6, 7],
                'is_active' => true,
            ],
        );

        return $car;
    }

    /**
     * @param  list<int>  $amenityIds
     */
    private function seedHostGuesthouse(
        User $host,
        string $prefix,
        GuestHouseStatus $status,
        GuestHouseType $type,
        GuestHouseCancellationPolicy $cancellationPolicy,
        ?User $admin,
        array $amenityIds,
        TaxRate $standardTax,
    ): GuestHouse {
        $slug = "{$prefix}-host-guesthouse";
        $name = ucfirst(str_replace('.', ' ', explode('@', $host->email)[0])).' Guesthouse';
        $thumb = 'https://placehold.co/800x600/1e3a8a/fff?text='.urlencode($name);
        $basePrice = 16500;

        $submittedAt = in_array($status, [GuestHouseStatus::PendingReview, GuestHouseStatus::Active], true)
            ? now()->subDays(2)
            : null;
        $reviewedAt = $status === GuestHouseStatus::Active ? now()->subDay() : null;

        $house = GuestHouse::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'user_id' => $host->id,
                'name' => $name,
                'meta_title' => "{$name} | MyTerraBook Stays",
                'meta_description' => 'Fully featured guesthouse with every amenity, seasonal price, and availability block for testing.',
                'og_image' => $thumb,
                'short_description' => 'A complete test stay with all backend options enabled.',
                'description' => 'Welcome to '.$name.'. This seeded listing includes every guesthouse field, all amenities, seasonal pricing, blocked dates, and host review workflow states.',
                'type' => $type,
                'status' => $status,
                'address' => 'Laugavegur '.(10 + crc32($prefix) % 80),
                'city' => 'Reykjavik',
                'country' => 'Iceland',
                'latitude' => 64.1466,
                'longitude' => -21.9426,
                'max_guests' => 6,
                'bedrooms' => 3,
                'bathrooms' => 2,
                'beds' => 4,
                'min_nights' => 2,
                'max_nights' => 21,
                'base_price_per_night' => $basePrice,
                'cleaning_fee' => 5500,
                'security_deposit' => 20000,
                'check_in_time' => '15:00:00',
                'check_out_time' => '11:00:00',
                'cancellation_policy' => $cancellationPolicy,
                'thumbnail' => $thumb,
                'tax_rate_id' => $standardTax->id,
                'submitted_at' => $submittedAt,
                'reviewed_at' => $reviewedAt,
                'reviewed_by' => $reviewedAt ? $admin?->id : null,
                'rejection_reason' => null,
            ],
        );

        $house->amenities()->sync($amenityIds);

        for ($i = 1; $i <= 4; $i++) {
            GuestHouseImage::query()->updateOrCreate(
                [
                    'guest_house_id' => $house->id,
                    'path' => 'https://placehold.co/800x600/334155/fff?text='.urlencode($name.'+'.$i),
                ],
                ['sort_order' => $i, 'caption' => "Room view {$i}"],
            );
        }

        GuestHouseSeasonalPrice::query()->updateOrCreate(
            ['guest_house_id' => $house->id, 'name' => 'Summer peak'],
            [
                'date_from' => Carbon::parse('2026-06-01'),
                'date_to' => Carbon::parse('2026-08-31'),
                'price_per_night' => (int) round($basePrice * 1.4),
                'minimum_nights' => 3,
            ],
        );

        GuestHouseSeasonalPrice::query()->updateOrCreate(
            ['guest_house_id' => $house->id, 'name' => 'Winter off-season'],
            [
                'date_from' => Carbon::parse('2026-11-01'),
                'date_to' => Carbon::parse('2027-03-31'),
                'price_per_night' => (int) round($basePrice * 0.8),
                'minimum_nights' => null,
            ],
        );

        GuestHouseAvailabilityBlock::query()->updateOrCreate(
            [
                'guest_house_id' => $house->id,
                'blocked_from' => now()->addDays(45)->toDateString(),
                'blocked_to' => now()->addDays(48)->toDateString(),
            ],
            [
                'reason' => GuestHouseAvailabilityBlockReason::Maintenance,
                'note' => 'Planned maintenance window',
                'source' => GuestHouseAvailabilityBlockSource::Manual,
            ],
        );

        GuestHouseAvailabilityBlock::query()->updateOrCreate(
            [
                'guest_house_id' => $house->id,
                'blocked_from' => now()->addDays(60)->toDateString(),
                'blocked_to' => now()->addDays(62)->toDateString(),
            ],
            [
                'reason' => GuestHouseAvailabilityBlockReason::OwnerUse,
                'note' => 'Host personal stay',
                'source' => GuestHouseAvailabilityBlockSource::Manual,
            ],
        );

        return $house;
    }

    private function ensurePrerequisites(): void
    {
        $this->call(TaxRateSeeder::class);

        if (PriceType::query()->where('slug', 'basic')->doesntExist()) {
            $this->command?->info('Catalog missing, running CatalogSeeder…');
            $this->call(CatalogSeeder::class);
        }

        if (GuestHouseAmenity::query()->doesntExist()) {
            $this->command?->info('Guesthouse amenities missing, running GuestHouseAmenitySeeder…');
            $this->call(GuestHouseAmenitySeeder::class);
        }
    }

    private function requirePriceType(string $slug): PriceType
    {
        $priceType = PriceType::query()->where('slug', $slug)->first();

        if ($priceType === null) {
            throw new \RuntimeException(
                "Price type [{$slug}] not found. Run: php artisan db:seed --class=CatalogSeeder"
            );
        }

        return $priceType;
    }

    private function requireSubCategory(string $name): SubCategory
    {
        $subCategory = SubCategory::query()->where('name', $name)->first();

        if ($subCategory === null) {
            throw new \RuntimeException(
                "Sub-category [{$name}] not found. Run: php artisan db:seed --class=CatalogSeeder"
            );
        }

        return $subCategory;
    }
}
