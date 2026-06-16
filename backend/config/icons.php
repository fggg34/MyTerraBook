<?php

/*
|--------------------------------------------------------------------------
| Curated Lucide icon catalog (single source of truth)
|--------------------------------------------------------------------------
|
| Keys are Lucide icon names (kebab-case). They must exist in BOTH:
|   - PHP / Filament: mallardduck/blade-lucide-icons  (rendered as `lucide-<key>`)
|   - Frontend:        lucide-react                    (PascalCase component)
|
| Keep this list in sync with frontend/src/utils/iconCatalog.js.
| Each entry: 'key' => ['label' => string, 'keywords' => string].
|
*/

return [
    'groups' => [

        'Drivetrain & Performance' => [
            'cog' => ['label' => 'Gearbox / Manual', 'keywords' => 'transmission manual gear cog'],
            'settings' => ['label' => 'Automatic', 'keywords' => 'transmission automatic gear'],
            'gauge' => ['label' => 'Performance', 'keywords' => 'speed performance cruise gauge'],
            'zap' => ['label' => 'Turbo / Electric', 'keywords' => 'turbo electric power zap eco'],
            'power' => ['label' => 'Start / Stop', 'keywords' => 'start stop power button'],
            'leaf' => ['label' => 'Eco Mode', 'keywords' => 'eco green economy leaf'],
            'mountain' => ['label' => '4WD / Off-road', 'keywords' => '4wd awd offroad mountain terrain'],
        ],

        'Comfort & Convenience' => [
            'snowflake' => ['label' => 'Air Conditioning', 'keywords' => 'ac air conditioning cold climate'],
            'thermometer' => ['label' => 'Climate Control', 'keywords' => 'climate temperature thermostat'],
            'flame' => ['label' => 'Heating / Heated Seats', 'keywords' => 'heat heated seats warm fire'],
            'armchair' => ['label' => 'Seats', 'keywords' => 'seat leather chair comfort'],
            'key' => ['label' => 'Keyless Entry', 'keywords' => 'key keyless entry start'],
            'sun' => ['label' => 'Sunroof', 'keywords' => 'sunroof panoramic roof sun'],
            'wind' => ['label' => 'Ventilation', 'keywords' => 'air wind ventilation fan'],
        ],

        'Safety & Driver Assistance' => [
            'shield' => ['label' => 'Safety / ABS', 'keywords' => 'safety abs brakes shield protection'],
            'shield-check' => ['label' => 'Insurance / Assist', 'keywords' => 'insurance protection assist airbag safety'],
            'camera' => ['label' => 'Camera', 'keywords' => 'backup reverse 360 camera'],
            'radar' => ['label' => 'Parking Sensors', 'keywords' => 'parking sensors radar detection'],
            'eye' => ['label' => 'Lane / Blind Spot', 'keywords' => 'lane departure blind spot vision eye'],
            'baby' => ['label' => 'Child Seat / ISOFIX', 'keywords' => 'child baby isofix seat infant'],
        ],

        'Technology & Connectivity' => [
            'bluetooth' => ['label' => 'Bluetooth', 'keywords' => 'bluetooth wireless connect'],
            'smartphone' => ['label' => 'CarPlay / Phone', 'keywords' => 'carplay android auto phone smartphone holder'],
            'navigation' => ['label' => 'GPS Navigation', 'keywords' => 'gps navigation sat nav direction'],
            'monitor' => ['label' => 'Touchscreen', 'keywords' => 'screen display touchscreen monitor'],
            'usb' => ['label' => 'USB Port', 'keywords' => 'usb charging port'],
            'battery-charging' => ['label' => 'Wireless Charging', 'keywords' => 'charge wireless battery power'],
            'music' => ['label' => 'Sound System', 'keywords' => 'music audio sound speakers premium'],
            'plug' => ['label' => 'Power Socket', 'keywords' => 'power socket 12v plug outlet'],
            'wifi' => ['label' => 'Wi-Fi', 'keywords' => 'wifi internet hotspot 4g wireless'],
        ],

        'Winter & Iceland' => [
            'cloud-snow' => ['label' => 'Winter Tyres', 'keywords' => 'winter snow tyres studded'],
            'satellite' => ['label' => 'Satellite Beacon', 'keywords' => 'satellite sos beacon emergency gps'],
            'route' => ['label' => 'F-road / Highland', 'keywords' => 'route highland f-road track path'],
            'mountain-snow' => ['label' => 'Ski / Snow Gear', 'keywords' => 'ski snowboard snow mountain rack'],
        ],

        'Capacity & Practicality' => [
            'users' => ['label' => 'Passengers', 'keywords' => 'seats passengers people capacity users'],
            'briefcase' => ['label' => 'Luggage / Bags', 'keywords' => 'bags luggage suitcase briefcase'],
            'luggage' => ['label' => 'Cargo', 'keywords' => 'luggage cargo storage trunk'],
            'package' => ['label' => 'Roof Box / Storage', 'keywords' => 'roof box package storage cargo'],
            'bike' => ['label' => 'Bike Rack', 'keywords' => 'bike bicycle rack carrier'],
            'caravan' => ['label' => 'Camper / Tow', 'keywords' => 'caravan camper tow trailer'],
            'truck' => ['label' => 'Pickup / Truck', 'keywords' => 'truck pickup cargo'],
            'dog' => ['label' => 'Pet Friendly', 'keywords' => 'pet dog animal friendly'],
            'baby' => ['label' => 'Child Friendly', 'keywords' => 'child baby kids family'],
        ],

        'Extras & Add-ons' => [
            'umbrella' => ['label' => 'Protection / Insurance', 'keywords' => 'protection insurance cover umbrella cdw'],
            'user-plus' => ['label' => 'Additional Driver', 'keywords' => 'driver additional extra person'],
            'fuel' => ['label' => 'Fuel / Prepaid Tank', 'keywords' => 'fuel gas petrol tank prepaid'],
            'infinity' => ['label' => 'Unlimited Mileage', 'keywords' => 'unlimited mileage km infinity'],
            'phone-call' => ['label' => 'Roadside Assistance', 'keywords' => 'roadside assistance help support call'],
            'tent' => ['label' => 'Camping Kit', 'keywords' => 'camping tent gear kit outdoor'],
            'refrigerator' => ['label' => 'Cooler Box', 'keywords' => 'cooler fridge refrigerator cold box'],
            'map' => ['label' => 'Permit / Map', 'keywords' => 'map permit highland route'],
        ],

        'Stay & Amenities' => [
            'bed' => ['label' => 'Bed / Bedroom', 'keywords' => 'bed bedroom sleep berth'],
            'bath' => ['label' => 'Bathroom', 'keywords' => 'bath bathroom tub'],
            'shower-head' => ['label' => 'Shower', 'keywords' => 'shower bathroom water'],
            'tv' => ['label' => 'TV', 'keywords' => 'tv television screen entertainment'],
            'utensils' => ['label' => 'Kitchen', 'keywords' => 'kitchen cooking utensils dining'],
            'coffee' => ['label' => 'Coffee Maker', 'keywords' => 'coffee tea kitchen drink'],
            'microwave' => ['label' => 'Microwave', 'keywords' => 'microwave oven kitchen'],
            'washing-machine' => ['label' => 'Washing Machine', 'keywords' => 'laundry washer washing machine'],
            'wine' => ['label' => 'Bar / Drinks', 'keywords' => 'wine bar drinks alcohol'],
            'waves' => ['label' => 'Pool / Hot Tub', 'keywords' => 'pool hot tub jacuzzi water waves'],
            'dumbbell' => ['label' => 'Gym', 'keywords' => 'gym fitness exercise dumbbell'],
            'trees' => ['label' => 'Garden / Outdoor', 'keywords' => 'garden outdoor trees nature'],
            'door-open' => ['label' => 'Private Entrance', 'keywords' => 'door entrance access private'],
            'lock' => ['label' => 'Safe / Security', 'keywords' => 'safe lock security secure'],
            'car' => ['label' => 'Parking', 'keywords' => 'parking car garage'],
        ],

        'General' => [
            'check' => ['label' => 'Check', 'keywords' => 'check tick yes included'],
            'star' => ['label' => 'Featured', 'keywords' => 'star featured highlight premium'],
            'sparkles' => ['label' => 'Special', 'keywords' => 'special sparkle new premium'],
            'tag' => ['label' => 'Tag / Label', 'keywords' => 'tag label price'],
            'info' => ['label' => 'Info', 'keywords' => 'info information detail'],
            'clock' => ['label' => 'Time / Hours', 'keywords' => 'time clock hours schedule'],
        ],

    ],
];
