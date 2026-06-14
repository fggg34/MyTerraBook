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
        $standardTax = TaxRate::query()->updateOrCreate(
            ['name' => 'Standard VAT (24%)'],
            ['basis_points' => 2400],
        );

        \App\Models\Setting::putValue('shop.currency', ['code' => 'ISK', 'symbol' => 'kr']);
        \App\Models\Setting::putValue('shop.default_tax', ['basis_points' => 2400]);

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
            ['name' => 'Economy', 'sort_order' => 1],
            ['name' => 'Compact', 'sort_order' => 2],
            ['name' => 'Hatchback', 'sort_order' => 3],
            ['name' => 'Sedan', 'sort_order' => 4],
            ['name' => 'Mid-size', 'sort_order' => 5],
            ['name' => 'Estate', 'sort_order' => 6],
            ['name' => 'Crossover', 'sort_order' => 7],
            ['name' => 'SUV', 'sort_order' => 8],
            ['name' => '4x4 / Off-road', 'sort_order' => 9],
            ['name' => 'Pickup Truck', 'sort_order' => 10],
            ['name' => 'Minivan / MPV', 'sort_order' => 11],
            ['name' => '7-Seater', 'sort_order' => 12],
            ['name' => '9-Seater Minibus', 'sort_order' => 13],
            ['name' => 'Luxury', 'sort_order' => 14],
            ['name' => 'Sports / Coupe', 'sort_order' => 15],
            ['name' => 'Convertible', 'sort_order' => 16],
            ['name' => 'Electric', 'sort_order' => 17],
            ['name' => 'Hybrid', 'sort_order' => 18],
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
            ['name' => 'Mini Camper', 'sort_order' => 1],
            ['name' => '2-Berth Campervan', 'sort_order' => 2],
            ['name' => '3-Berth Campervan', 'sort_order' => 3],
            ['name' => '4-Berth Campervan', 'sort_order' => 4],
            ['name' => '5+ Berth Motorhome', 'sort_order' => 5],
            ['name' => 'Van', 'sort_order' => 6],
            ['name' => 'Motorhome', 'sort_order' => 7],
            ['name' => 'Camper', 'sort_order' => 8],
            ['name' => '4x4 Camper', 'sort_order' => 9],
            ['name' => 'Rooftop Tent 4x4', 'sort_order' => 10],
        ];

        foreach ($campervanSubCategories as $data) {
            SubCategory::ensureBySlug(Str::slug($data['name']), $campervanMain->id, [
                'name' => $data['name'],
                'description' => "{$data['name']} rentals for camping and extended self-drive tours.",
                'sort_order' => $data['sort_order'],
                'is_search_filter' => true,
            ]);
        }

        // Comprehensive, grouped characteristic catalogue a car/campervan host needs.
        // Format: group => [ [name, useAsSearchFilter], ... ]
        $characteristicGroups = [
            'Drivetrain & Performance' => [
                ['4WD / AWD', true],
                ['Front-wheel Drive', false],
                ['Manual Transmission', false],
                ['Automatic Transmission', true],
                ['Turbocharged Engine', false],
                ['Start/Stop System', false],
                ['Eco Driving Mode', false],
            ],
            'Comfort & Convenience' => [
                ['Air Conditioning', true],
                ['Dual-zone Climate Control', false],
                ['Heated Seats', true],
                ['Heated Steering Wheel', false],
                ['Leather Seats', false],
                ['Keyless Entry & Start', false],
                ['Cruise Control', false],
                ['Adaptive Cruise Control', false],
                ['Power Windows', false],
                ['Panoramic / Sunroof', false],
            ],
            'Safety & Driver Assistance' => [
                ['ABS Brakes', false],
                ['Multiple Airbags', false],
                ['Backup Camera', true],
                ['360° Camera', false],
                ['Parking Sensors', false],
                ['Lane Departure Warning', false],
                ['Blind Spot Monitoring', false],
                ['Emergency Braking Assist', false],
                ['Tyre Pressure Monitoring', false],
                ['ISOFIX Child-seat Anchors', false],
            ],
            'Technology & Connectivity' => [
                ['Bluetooth', true],
                ['Apple CarPlay', true],
                ['Android Auto', true],
                ['GPS Navigation', true],
                ['Touchscreen Display', false],
                ['USB Port', false],
                ['USB-C Charging', false],
                ['Wireless Phone Charging', false],
                ['Premium Sound System', false],
                ['12V Power Socket', false],
            ],
            'Winter & Iceland' => [
                ['Studded Winter Tyres', true],
                ['All-season Tyres', false],
                ['Engine Block Heater', false],
                ['Heated Windscreen', false],
                ['Snow Chains', false],
                ['Underbody / Skid Protection', false],
            ],
            'Capacity & Practicality' => [
                ['Roof Rack', false],
                ['Roof Box', false],
                ['Tow Bar', false],
                ['Bike Rack', false],
                ['Large Cargo Space', false],
                ['Folding Rear Seats', false],
                ['Child Seat Available', true],
                ['Pet Friendly', true],
                ['Non-smoking Vehicle', false],
            ],
        ];

        $characteristicSort = 0;
        $characteristics = collect();
        foreach ($characteristicGroups as $group => $items) {
            foreach ($items as [$name, $isSearchFilter]) {
                $characteristicSort += 10;
                $characteristics->push(Characteristic::query()->updateOrCreate(
                    ['name' => $name],
                    [
                        'display_text' => $name,
                        'group' => $group,
                        'sort_order' => $characteristicSort,
                        'is_search_filter' => $isSearchFilter,
                    ]
                ));
            }
        }

        $locations = collect([
            ['name' => 'Keflavík International Airport (KEF)', 'address' => 'Keflavíkurflugvöllur, Reykjanesbær', 'lat' => 63.985, 'lng' => -22.6056],
            ['name' => 'Reykjavík City Center (BSÍ Terminal)', 'address' => 'Vatnsmýrarvegur 10, Reykjavík', 'lat' => 64.143, 'lng' => -21.94],
            ['name' => 'Reykjavík Domestic Airport (RKV)', 'address' => 'Reykjavíkurflugvöllur, Reykjavík', 'lat' => 64.13, 'lng' => -21.9406],
            ['name' => 'Akureyri Airport (AEY)', 'address' => 'Akureyrarflugvöllur, Akureyri', 'lat' => 65.66, 'lng' => -18.0727],
            ['name' => 'Egilsstaðir Airport (EGS)', 'address' => 'Egilsstaðaflugvöllur, Egilsstaðir', 'lat' => 65.283, 'lng' => -14.401],
            ['name' => 'Höfn í Hornafirði', 'address' => 'Hafnarbraut, Höfn', 'lat' => 64.254, 'lng' => -15.208],
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

        $airport = $locations->firstWhere('name', 'Keflavík International Airport (KEF)');
        $city = $locations->firstWhere('name', 'Reykjavík City Center (BSÍ Terminal)');
        $akureyri = $locations->firstWhere('name', 'Akureyri Airport (AEY)');
        $egilsstadir = $locations->firstWhere('name', 'Egilsstaðir Airport (EGS)');
        if ($airport && $city) {
            LocationFee::query()->firstOrCreate(
                ['pickup_location_id' => $airport->id, 'dropoff_location_id' => $city->id],
                ['cost_cents' => 750000, 'is_one_way_fee' => false, 'tax_rate_id' => $standardTax->id, 'is_active' => true]
            );
            LocationFee::query()->firstOrCreate(
                ['pickup_location_id' => $city->id, 'dropoff_location_id' => $airport->id],
                ['cost_cents' => 750000, 'is_one_way_fee' => false, 'tax_rate_id' => $standardTax->id, 'is_active' => true]
            );
        }
        if ($airport && $akureyri) {
            LocationFee::query()->firstOrCreate(
                ['pickup_location_id' => $airport->id, 'dropoff_location_id' => $akureyri->id],
                ['cost_cents' => 3500000, 'is_one_way_fee' => true, 'tax_rate_id' => $standardTax->id, 'is_active' => true]
            );
        }
        if ($airport && $egilsstadir) {
            LocationFee::query()->firstOrCreate(
                ['pickup_location_id' => $airport->id, 'dropoff_location_id' => $egilsstadir->id],
                ['cost_cents' => 5500000, 'is_one_way_fee' => true, 'tax_rate_id' => $standardTax->id, 'is_active' => true]
            );
        }

        collect([
            ['slug' => 'basic', 'name' => 'Basic', 'attribute_value_per_day' => '250.000 kr deposit'],
            ['slug' => 'plus', 'name' => 'Plus', 'attribute_value_per_day' => '100.000 kr deposit'],
            ['slug' => 'max', 'name' => 'Max', 'attribute_value_per_day' => '0 kr deposit'],
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
            ['name' => 'Gravel Protection (GP)', 'description' => 'Covers windscreen, lights & bodywork damage from gravel roads', 'cost_cents' => 250000, 'is_daily_cost' => true],
            ['name' => 'Sand & Ash Protection (SAAP)', 'description' => 'Covers paint/bodywork damage from sandstorms and volcanic ash', 'cost_cents' => 280000, 'is_daily_cost' => true],
            ['name' => 'Super CDW / Premium Insurance', 'description' => 'Reduces the collision damage excess to a minimum', 'cost_cents' => 350000, 'is_daily_cost' => true],
            ['name' => 'Theft Protection (TP)', 'description' => 'Reduces liability in case of theft', 'cost_cents' => 150000, 'is_daily_cost' => true],
            ['name' => 'Additional Driver', 'description' => 'Register a second approved driver', 'cost_cents' => 120000, 'is_daily_cost' => false],
            ['name' => '4G Wi-Fi Hotspot', 'description' => 'Unlimited mobile data across Iceland', 'cost_cents' => 150000, 'is_daily_cost' => true],
            ['name' => 'GPS Device', 'description' => 'Pre-loaded Iceland navigation', 'cost_cents' => 130000, 'is_daily_cost' => true],
            ['name' => 'Child / Booster Seat', 'description' => 'Approved seat for infants and children', 'cost_cents' => 90000, 'is_daily_cost' => true],
            ['name' => 'Snow Chains', 'description' => 'Winter traction for mountain and F-roads', 'cost_cents' => 180000, 'is_daily_cost' => false],
            ['name' => 'Camping Equipment Kit', 'description' => 'Sleeping bags, stove and cookware for two', 'cost_cents' => 450000, 'is_daily_cost' => false],
            ['name' => 'Cooler Box', 'description' => '40L electric cooler for road trips', 'cost_cents' => 110000, 'is_daily_cost' => false],
            ['name' => 'Tyre & Windscreen Protection (TWP)', 'description' => 'Covers tyres and windscreen damage with zero excess', 'cost_cents' => 200000, 'is_daily_cost' => true],
            ['name' => 'Unlimited Mileage', 'description' => 'Remove the daily kilometre cap', 'cost_cents' => 150000, 'is_daily_cost' => true],
            ['name' => 'Roadside Assistance Plus', 'description' => '24/7 priority breakdown and recovery service', 'cost_cents' => 90000, 'is_daily_cost' => true],
            ['name' => 'Prepaid Full Tank of Fuel', 'description' => 'Return the vehicle empty — no refuelling stop needed', 'cost_cents' => 1200000, 'is_daily_cost' => false],
            ['name' => 'Highland (F-road) Permit', 'description' => 'Authorisation to drive marked F-roads and highland routes', 'cost_cents' => 350000, 'is_daily_cost' => false],
            ['name' => 'Ski / Snowboard Rack', 'description' => 'Roof-mounted carrier for winter gear', 'cost_cents' => 100000, 'is_daily_cost' => false],
            ['name' => 'Phone Holder & Charger', 'description' => 'Dashboard mount with fast charging cable', 'cost_cents' => 50000, 'is_daily_cost' => false],
            ['name' => 'Emergency Satellite Beacon', 'description' => 'GPS SOS device for remote highland travel', 'cost_cents' => 250000, 'is_daily_cost' => false],
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
            ['code' => 'cash', 'name' => 'Pay at Pickup', 'auto_confirm_order' => true],
            ['code' => 'bank_transfer', 'name' => 'Bank Transfer', 'auto_confirm_order' => true],
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
