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
use App\Models\RentalCondition;
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
        // Format: group => [ [name, useAsSearchFilter, lucideIcon], ... ]
        $characteristicGroups = [
            'Drivetrain & Performance' => [
                ['4WD / AWD', true, 'mountain'],
                ['Front-wheel Drive', false, 'cog'],
                ['Manual Transmission', false, 'cog'],
                ['Automatic Transmission', true, 'settings'],
                ['Turbocharged Engine', false, 'zap'],
                ['Start/Stop System', false, 'power'],
                ['Eco Driving Mode', false, 'leaf'],
            ],
            'Comfort & Convenience' => [
                ['Air Conditioning', true, 'snowflake'],
                ['Dual-zone Climate Control', false, 'thermometer'],
                ['Heated Seats', true, 'flame'],
                ['Heated Steering Wheel', false, 'flame'],
                ['Leather Seats', false, 'armchair'],
                ['Keyless Entry & Start', false, 'key'],
                ['Cruise Control', false, 'gauge'],
                ['Adaptive Cruise Control', false, 'gauge'],
                ['Power Windows', false, 'wind'],
                ['Panoramic / Sunroof', false, 'sun'],
            ],
            'Safety & Driver Assistance' => [
                ['ABS Brakes', false, 'shield'],
                ['Multiple Airbags', false, 'shield-check'],
                ['Backup Camera', true, 'camera'],
                ['360° Camera', false, 'camera'],
                ['Parking Sensors', false, 'radar'],
                ['Lane Departure Warning', false, 'eye'],
                ['Blind Spot Monitoring', false, 'eye'],
                ['Emergency Braking Assist', false, 'shield-check'],
                ['Tyre Pressure Monitoring', false, 'gauge'],
                ['ISOFIX Child-seat Anchors', false, 'baby'],
            ],
            'Technology & Connectivity' => [
                ['Bluetooth', true, 'bluetooth'],
                ['Apple CarPlay', true, 'smartphone'],
                ['Android Auto', true, 'smartphone'],
                ['GPS Navigation', true, 'navigation'],
                ['Touchscreen Display', false, 'monitor'],
                ['USB Port', false, 'usb'],
                ['USB-C Charging', false, 'usb'],
                ['Wireless Phone Charging', false, 'battery-charging'],
                ['Premium Sound System', false, 'music'],
                ['12V Power Socket', false, 'plug'],
            ],
            'Winter & Iceland' => [
                ['Studded Winter Tyres', true, 'cloud-snow'],
                ['All-season Tyres', false, 'cloud-snow'],
                ['Engine Block Heater', false, 'flame'],
                ['Heated Windscreen', false, 'flame'],
                ['Snow Chains', false, 'snowflake'],
                ['Underbody / Skid Protection', false, 'shield'],
            ],
            'Capacity & Practicality' => [
                ['Roof Rack', false, 'package'],
                ['Roof Box', false, 'package'],
                ['Tow Bar', false, 'caravan'],
                ['Bike Rack', false, 'bike'],
                ['Large Cargo Space', false, 'luggage'],
                ['Folding Rear Seats', false, 'armchair'],
                ['Child Seat Available', true, 'baby'],
                ['Pet Friendly', true, 'dog'],
                ['Non-smoking Vehicle', false, 'wind'],
            ],
        ];

        $characteristicSort = 0;
        $characteristics = collect();
        foreach ($characteristicGroups as $group => $items) {
            foreach ($items as [$name, $isSearchFilter, $icon]) {
                $characteristicSort += 10;
                $characteristics->push(Characteristic::query()->updateOrCreate(
                    ['name' => $name],
                    [
                        'icon' => $icon,
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
            ['name' => 'Gravel Protection (GP)', 'icon' => 'shield-check', 'description' => 'Covers windscreen, lights & bodywork damage from gravel roads', 'cost_cents' => 250000, 'is_daily_cost' => true],
            ['name' => 'Sand & Ash Protection (SAAP)', 'icon' => 'umbrella', 'description' => 'Covers paint/bodywork damage from sandstorms and volcanic ash', 'cost_cents' => 280000, 'is_daily_cost' => true],
            ['name' => 'Super CDW / Premium Insurance', 'icon' => 'shield-check', 'description' => 'Reduces the collision damage excess to a minimum', 'cost_cents' => 350000, 'is_daily_cost' => true],
            ['name' => 'Theft Protection (TP)', 'icon' => 'lock', 'description' => 'Reduces liability in case of theft', 'cost_cents' => 150000, 'is_daily_cost' => true],
            ['name' => 'Additional Driver', 'icon' => 'user-plus', 'description' => 'Register a second approved driver', 'cost_cents' => 120000, 'is_daily_cost' => false],
            ['name' => '4G Wi-Fi Hotspot', 'icon' => 'wifi', 'description' => 'Unlimited mobile data across Iceland', 'cost_cents' => 150000, 'is_daily_cost' => true],
            ['name' => 'GPS Device', 'icon' => 'navigation', 'description' => 'Pre-loaded Iceland navigation', 'cost_cents' => 130000, 'is_daily_cost' => true],
            ['name' => 'Child / Booster Seat', 'icon' => 'baby', 'description' => 'Approved seat for infants and children', 'cost_cents' => 90000, 'is_daily_cost' => true],
            ['name' => 'Snow Chains', 'icon' => 'snowflake', 'description' => 'Winter traction for mountain and F-roads', 'cost_cents' => 180000, 'is_daily_cost' => false],
            ['name' => 'Camping Equipment Kit', 'icon' => 'tent', 'description' => 'Sleeping bags, stove and cookware for two', 'cost_cents' => 450000, 'is_daily_cost' => false],
            ['name' => 'Cooler Box', 'icon' => 'refrigerator', 'description' => '40L electric cooler for road trips', 'cost_cents' => 110000, 'is_daily_cost' => false],
            ['name' => 'Tyre & Windscreen Protection (TWP)', 'icon' => 'shield', 'description' => 'Covers tyres and windscreen damage with zero excess', 'cost_cents' => 200000, 'is_daily_cost' => true],
            ['name' => 'Unlimited Mileage', 'icon' => 'infinity', 'description' => 'Remove the daily kilometre cap', 'cost_cents' => 150000, 'is_daily_cost' => true],
            ['name' => 'Roadside Assistance Plus', 'icon' => 'phone-call', 'description' => '24/7 priority breakdown and recovery service', 'cost_cents' => 90000, 'is_daily_cost' => true],
            ['name' => 'Prepaid Full Tank of Fuel', 'icon' => 'fuel', 'description' => 'Return the vehicle empty, no refuelling stop needed', 'cost_cents' => 1200000, 'is_daily_cost' => false],
            ['name' => 'Highland (F-road) Permit', 'icon' => 'map', 'description' => 'Authorisation to drive marked F-roads and highland routes', 'cost_cents' => 350000, 'is_daily_cost' => false],
            ['name' => 'Ski / Snowboard Rack', 'icon' => 'mountain-snow', 'description' => 'Roof-mounted carrier for winter gear', 'cost_cents' => 100000, 'is_daily_cost' => false],
            ['name' => 'Phone Holder & Charger', 'icon' => 'smartphone', 'description' => 'Dashboard mount with fast charging cable', 'cost_cents' => 50000, 'is_daily_cost' => false],
            ['name' => 'Emergency Satellite Beacon', 'icon' => 'satellite', 'description' => 'GPS SOS device for remote highland travel', 'cost_cents' => 250000, 'is_daily_cost' => false],
        ])->map(function (array $data) use ($standardTax) {
            $option = RentalOption::query()->firstOrCreate(
                ['name' => $data['name']],
                [
                    'icon' => $data['icon'] ?? null,
                    'description' => $data['description'] ?? "Optional add-on: {$data['name']}.",
                    'cost_cents' => $data['cost_cents'],
                    'is_daily_cost' => $data['is_daily_cost'],
                    'tax_rate_id' => $standardTax->id,
                    'sort_order' => 0,
                    'is_active' => true,
                ]
            );

            // Backfill the icon on pre-existing rows without touching other fields.
            if (empty($option->icon) && ! empty($data['icon'])) {
                $option->update(['icon' => $data['icon']]);
            }

            return $option;
        });

        $rentalConditionSort = 0;
        collect([
            ['name' => 'Driver age 25+', 'title' => 'Driver age 25+', 'icon' => 'users', 'description' => 'All drivers must be at least 25 years old.'],
            ['name' => 'Licence held 2+ years', 'title' => 'Licence held 2+ years', 'icon' => 'key', 'description' => 'Full driving licence valid for the trip.'],
            ['name' => 'Security deposit', 'title' => '€1,500 deposit', 'icon' => 'lock', 'description' => 'Refundable hold, reducible with Excess Insurance.'],
            ['name' => 'Unlimited mileage', 'title' => 'Unlimited mileage', 'icon' => 'infinity', 'description' => 'Drive the whole Ring Road, no extra per-km fees.'],
            ['name' => 'Return with full tank', 'title' => 'Return with full tank', 'icon' => 'fuel', 'description' => 'Same fuel level as pick-up, or pre-pay fuel.'],
            ['name' => 'CDW included', 'title' => 'CDW insurance included', 'icon' => 'shield-check', 'description' => 'Collision damage waiver comes with every booking.'],
            ['name' => 'Free cancellation', 'title' => 'Free cancellation', 'icon' => 'check', 'description' => 'Full refund up to 48 hours before pick-up.'],
            ['name' => 'Pets welcome', 'title' => 'Pets welcome', 'icon' => 'dog', 'description' => 'Bring the dog, no extra cleaning charge.'],
            ['name' => 'Minimum rental 3 days', 'title' => 'Minimum 3-day rental', 'icon' => 'clock', 'description' => 'Applies during peak season unless otherwise stated.'],
            ['name' => 'Cross-border travel', 'title' => 'Cross-border travel not allowed', 'icon' => 'route', 'description' => 'Vehicle must remain in Iceland for the full rental period.'],
            ['name' => 'F-road permit required', 'title' => 'Highland (F-road) permit required', 'icon' => 'map', 'description' => 'Book the Highland permit add-on before driving marked F-roads.'],
            ['name' => 'Credit card required', 'title' => 'Credit card required at pick-up', 'icon' => 'tag', 'description' => 'A valid credit card in the main driver\'s name is required for the deposit.'],
        ])->each(function (array $data) use (&$rentalConditionSort): void {
            $rentalConditionSort += 10;
            RentalCondition::query()->updateOrCreate(
                ['name' => $data['name']],
                [
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'icon' => $data['icon'],
                    'sort_order' => $rentalConditionSort,
                    'is_active' => true,
                ]
            );
        });

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
