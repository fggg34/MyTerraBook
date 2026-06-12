<?php

namespace Database\Seeders;

use App\Models\BookingRestriction;
use App\Models\Characteristic;
use App\Models\Coupon;
use App\Models\CustomField;
use App\Models\Location;
use App\Models\LocationClosingDay;
use App\Models\LocationFee;
use App\Models\LocationSchedule;
use App\Models\MainCategory;
use App\Models\OutOfHoursFee;
use App\Models\PaymentMethod;
use App\Models\PriceType;
use App\Models\RentalOption;
use App\Models\SpecialPrice;
use App\Models\SubCategory;
use App\Models\TaxRate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $standardTax = TaxRate::query()->firstOrCreate(
            ['name' => 'Standard VAT (10%)'],
            ['basis_points' => 1000],
        );

        $carMain = MainCategory::ensureBySlug('car', [
            'name' => 'Car',
            'description' => 'Passenger cars and 4×4s.',
            'sort_order' => 1,
        ]);

        $campervanMain = MainCategory::ensureBySlug('campervan', [
            'name' => 'Campervan',
            'description' => 'Campervans and motorhomes.',
            'sort_order' => 2,
        ]);

        $carSubCategories = [
            ['name' => 'Hatchback', 'sort_order' => 1],
            ['name' => 'Sedan', 'sort_order' => 2],
            ['name' => 'Estate', 'sort_order' => 3],
            ['name' => 'Economy', 'sort_order' => 4],
            ['name' => 'Compact', 'sort_order' => 5],
            ['name' => 'Mid-size', 'sort_order' => 6],
            ['name' => 'SUV', 'sort_order' => 7],
            ['name' => 'Luxury', 'sort_order' => 8],
            ['name' => 'Electric', 'sort_order' => 9],
            ['name' => 'Convertible', 'sort_order' => 10],
        ];

        foreach ($carSubCategories as $data) {
            SubCategory::ensureBySlug(Str::slug($data['name']), $carMain->id, [
                'name' => $data['name'],
                'description' => "{$data['name']} rental vehicles for everyday travel and road trips.",
                'sort_order' => $data['sort_order'],
                'is_search_filter' => true,
            ]);
        }

        $campervanSubCategories = [
            ['name' => 'Van', 'sort_order' => 1],
            ['name' => 'Motorhome', 'sort_order' => 2],
            ['name' => 'Camper', 'sort_order' => 3],
            ['name' => '4x4 Camper', 'sort_order' => 4],
        ];

        foreach ($campervanSubCategories as $data) {
            SubCategory::ensureBySlug(Str::slug($data['name']), $campervanMain->id, [
                'name' => $data['name'],
                'description' => "{$data['name']} rentals for camping and extended self-drive tours.",
                'sort_order' => $data['sort_order'],
                'is_search_filter' => true,
            ]);
        }

        $characteristics = collect([
            'Air Conditioning', 'GPS Navigation', 'Bluetooth', 'USB Port',
            'Child Seat Available', 'Automatic Transmission', '4WD', 'Cruise Control',
            'Parking Sensors', 'Backup Camera',
        ])->map(fn (string $name) => Characteristic::query()->firstOrCreate(
            ['name' => $name],
            ['display_text' => $name, 'sort_order' => 0, 'is_search_filter' => true]
        ));

        $locations = collect([
            ['name' => 'Tirana Airport (TIA)', 'address' => 'Rinas, Tirana', 'lat' => 41.4147, 'lng' => 19.7206],
            ['name' => 'Tirana City Center', 'address' => 'Skanderbeg Square, Tirana', 'lat' => 41.3275, 'lng' => 19.8187],
            ['name' => 'Durres Port', 'address' => 'Durres Harbor', 'lat' => 41.3236, 'lng' => 19.4547],
            ['name' => 'Vlorë Downtown', 'address' => 'Vlorë City Center', 'lat' => 40.4667, 'lng' => 19.4897],
            ['name' => 'Shkodër Station', 'address' => 'Shkodër Main Station', 'lat' => 42.0683, 'lng' => 19.5126],
        ])->map(fn (array $data) => Location::query()->firstOrCreate(
            ['name' => $data['name']],
            [
                'address' => $data['address'],
                'latitude' => $data['lat'],
                'longitude' => $data['lng'],
                'tax_rate_id' => $standardTax->id,
                'default_opening_time' => '08:00:00',
                'default_closing_time' => '20:00:00',
                'suggested_preselected_time' => '10:00:00',
                'is_active' => true,
            ]
        ));

        foreach ($locations as $location) {
            for ($weekday = 0; $weekday <= 6; $weekday++) {
                LocationSchedule::query()->firstOrCreate(
                    ['location_id' => $location->id, 'weekday' => $weekday],
                    ['opening_time' => '08:00:00', 'closing_time' => '20:00:00', 'is_closed' => false]
                );
            }

            LocationClosingDay::query()->firstOrCreate(
                ['location_id' => $location->id, 'recurring_weekday' => 0],
                ['specific_date' => null]
            );
        }

        foreach ($locations as $pickup) {
            foreach ($locations as $dropoff) {
                $pickup->dropoffCombinations()->syncWithoutDetaching([$dropoff->id]);
            }
        }

        $airport = $locations->firstWhere('name', 'Tirana Airport (TIA)');
        $city = $locations->firstWhere('name', 'Tirana City Center');
        $durres = $locations->firstWhere('name', 'Durres Port');
        if ($airport && $city) {
            LocationFee::query()->firstOrCreate(
                ['pickup_location_id' => $airport->id, 'dropoff_location_id' => $city->id],
                ['cost_cents' => 4900, 'is_one_way_fee' => false, 'tax_rate_id' => $standardTax->id, 'is_active' => true]
            );
            LocationFee::query()->firstOrCreate(
                ['pickup_location_id' => $city->id, 'dropoff_location_id' => $airport->id],
                ['cost_cents' => 4900, 'is_one_way_fee' => false, 'tax_rate_id' => $standardTax->id, 'is_active' => true]
            );
        }
        if ($airport && $durres) {
            LocationFee::query()->firstOrCreate(
                ['pickup_location_id' => $airport->id, 'dropoff_location_id' => $durres->id],
                ['cost_cents' => 2900, 'is_one_way_fee' => true, 'tax_rate_id' => $standardTax->id, 'is_active' => true]
            );
        }

        collect([
            ['slug' => 'basic', 'name' => 'Basic', 'attribute_value_per_day' => '€1,500 deposit'],
            ['slug' => 'plus', 'name' => 'Plus', 'attribute_value_per_day' => '€500 deposit'],
            ['slug' => 'max', 'name' => 'Max', 'attribute_value_per_day' => '€0 deposit'],
        ])->each(fn (array $data) => PriceType::query()->updateOrCreate(
            ['slug' => $data['slug']],
            [
                'name' => $data['name'],
                'attribute_label' => 'Refundable deposit',
                'attribute_value_per_day' => $data['attribute_value_per_day'],
                'tax_rate_id' => $standardTax->id,
                'is_active' => true,
            ]
        ));

        PriceType::query()
            ->whereIn('slug', ['standard-rate', 'premium-rate', 'long-term-rate'])
            ->update(['is_active' => false]);

        $rentalOptions = collect([
            ['name' => 'Camping chairs & table', 'description' => 'Foldable set for two', 'cost_cents' => 1500, 'is_daily_cost' => false],
            ['name' => 'Extra bedding kit', 'description' => 'Duvet, pillow & linen for guest 3', 'cost_cents' => 2500, 'is_daily_cost' => false],
            ['name' => 'Kitchen kit', 'description' => 'Pots, pans, cutlery & French press', 'cost_cents' => 2000, 'is_daily_cost' => false],
            ['name' => '4G Wi-Fi hotspot', 'description' => 'Unlimited data', 'cost_cents' => 900, 'is_daily_cost' => true],
            ['name' => 'Return-clean service', 'description' => 'Skip the cleaning, bring it back as-is', 'cost_cents' => 4500, 'is_daily_cost' => false],
            ['name' => 'GPS Device', 'cost_cents' => 800, 'is_daily_cost' => true],
            ['name' => 'Child Seat', 'cost_cents' => 500, 'is_daily_cost' => false],
        ])->map(fn (array $data) => RentalOption::query()->firstOrCreate(
            ['name' => $data['name']],
            [
                'description' => $data['description'] ?? "Optional add-on: {$data['name']}.",
                'cost_cents' => $data['cost_cents'],
                'is_daily_cost' => $data['is_daily_cost'],
                'tax_rate_id' => $standardTax->id,
                'sort_order' => 0,
                'is_active' => true,
            ]
        ));

        collect([
            ['code' => 'card', 'name' => 'Credit / Debit Card', 'auto_confirm_order' => true],
            ['code' => 'cash', 'name' => 'Pay at Pickup', 'auto_confirm_order' => false],
            ['code' => 'bank_transfer', 'name' => 'Bank Transfer', 'auto_confirm_order' => false],
        ])->each(fn (array $data) => PaymentMethod::query()->firstOrCreate(
            ['code' => $data['code']],
            [
                'name' => $data['name'],
                'is_enabled' => true,
                'auto_confirm_order' => $data['auto_confirm_order'],
                'charge_or_discount' => 'none',
                'sort_order' => 0,
            ]
        ));

        Coupon::query()->firstOrCreate(
            ['code' => 'WELCOME10'],
            [
                'type' => 'permanent',
                'discount_type' => 'percentage',
                'discount_percent_bips' => 1000,
                'valid_from' => now()->subMonth(),
                'valid_to' => now()->addYear(),
                'min_order_total_cents' => 5000,
                'is_active' => true,
            ]
        );

        Coupon::query()->firstOrCreate(
            ['code' => 'SUMMER25'],
            [
                'type' => 'gift',
                'discount_type' => 'fixed',
                'discount_fixed_cents' => 2500,
                'valid_from' => now()->startOfMonth(),
                'valid_to' => now()->addMonths(4),
                'is_active' => true,
            ]
        );

        BookingRestriction::query()->firstOrCreate(
            ['name' => 'Peak Season Minimum 3 Days'],
            [
                'date_from' => now()->startOfMonth(),
                'date_to' => now()->addMonths(3),
                'min_rental_days' => 3,
                'is_active' => true,
            ]
        );

        SpecialPrice::query()->firstOrCreate(
            ['name' => 'Summer Early Bird'],
            [
                'date_from' => now()->startOfMonth(),
                'date_to' => now()->addMonths(4),
                'type' => 'discount',
                'value_mode' => 'percentage',
                'value_percent_bips' => 1500,
                'is_promotion' => true,
                'is_active' => true,
            ]
        );

        OutOfHoursFee::query()->firstOrCreate(
            ['name' => 'After-hours Pickup/Dropoff'],
            [
                'time_from' => '20:00:00',
                'time_to' => '08:00:00',
                'applies_to' => 'both',
                'cost_cents' => 3500,
                'tax_rate_id' => $standardTax->id,
                'is_active' => true,
            ]
        );

        CustomField::query()->firstOrCreate(
            ['field_key' => 'flight_number'],
            ['label' => 'Flight Number', 'type' => 'text', 'sort_order' => 1, 'is_active' => true]
        );

        CustomField::query()->firstOrCreate(
            ['field_key' => 'hotel_name'],
            ['label' => 'Hotel Name', 'type' => 'text', 'sort_order' => 2, 'is_active' => true]
        );

        \App\Models\Setting::putValue('shop.exchange_rates', [
            'EUR' => 1,
            'USD' => 1.08,
            'GBP' => 0.86,
            'ISK' => 150,
        ]);
    }
}
