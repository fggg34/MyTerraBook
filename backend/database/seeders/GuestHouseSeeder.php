<?php

namespace Database\Seeders;

use App\Enums\GuestHouseBookingStatus;
use App\Enums\GuestHouseCancellationPolicy;
use App\Enums\GuestHouseStatus;
use App\Enums\GuestHouseType;
use App\Models\GuestHouse;
use App\Models\GuestHouseAmenity;
use App\Models\GuestHouseBooking;
use App\Models\GuestHouseImage;
use App\Models\GuestHouseSeasonalPrice;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class GuestHouseSeeder extends Seeder
{
    public function run(): void
    {
        Setting::putValue('shop.currency', ['code' => 'EUR']);
        Setting::putValue('shop.default_tax', ['basis_points' => 2400]);

        $amenityIds = GuestHouseAmenity::query()->pluck('id')->all();

        $houses = [
            [
                'name' => 'Northern Lights Villa',
                'slug' => 'northern-lights-villa',
                'type' => GuestHouseType::Villa,
                'city' => 'Reykjavik',
                'country' => 'Iceland',
                'base' => 18500,
                'bedrooms' => 4,
                'max_guests' => 8,
            ],
            [
                'name' => 'Harbour View Apartment',
                'slug' => 'harbour-view-apartment',
                'type' => GuestHouseType::Apartment,
                'city' => 'Akureyri',
                'country' => 'Iceland',
                'base' => 12000,
                'bedrooms' => 2,
                'max_guests' => 4,
            ],
            [
                'name' => 'Moss Cottage',
                'slug' => 'moss-cottage',
                'type' => GuestHouseType::Cottage,
                'city' => 'Vik',
                'country' => 'Iceland',
                'base' => 9500,
                'bedrooms' => 2,
                'max_guests' => 4,
            ],
            [
                'name' => 'Downtown Studio',
                'slug' => 'downtown-studio',
                'type' => GuestHouseType::Studio,
                'city' => 'Reykjavik',
                'country' => 'Iceland',
                'base' => 7500,
                'bedrooms' => 1,
                'max_guests' => 2,
            ],
            [
                'name' => 'Glacier Room',
                'slug' => 'glacier-room',
                'type' => GuestHouseType::Room,
                'city' => 'Höfn',
                'country' => 'Iceland',
                'base' => 5500,
                'bedrooms' => 1,
                'max_guests' => 2,
            ],
        ];

        $customer = User::query()->where('email', 'customer@terrabook.test')->first();

        foreach ($houses as $index => $data) {
            $house = GuestHouse::query()->updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'short_description' => 'A comfortable stay in '.$data['city'].', Iceland.',
                    'description' => 'Welcome to '.$data['name'].'. Perfect for exploring Iceland with modern amenities and a warm atmosphere.',
                    'type' => $data['type'],
                    'status' => GuestHouseStatus::Active,
                    'address' => '12 Example Street',
                    'city' => $data['city'],
                    'country' => $data['country'],
                    'max_guests' => $data['max_guests'],
                    'bedrooms' => $data['bedrooms'],
                    'bathrooms' => 1,
                    'beds' => $data['bedrooms'],
                    'min_nights' => 2,
                    'max_nights' => 14,
                    'base_price_per_night' => $data['base'],
                    'cleaning_fee' => 4500,
                    'security_deposit' => 15000,
                    'check_in_time' => '15:00:00',
                    'check_out_time' => '11:00:00',
                    'cancellation_policy' => GuestHouseCancellationPolicy::Moderate,
                    'thumbnail' => 'https://placehold.co/800x600/1e3a5f/fff?text='.urlencode($data['name']),
                ],
            );

            $house->amenities()->sync(array_slice($amenityIds, 0, 8 + $index));

            for ($i = 1; $i <= 4; $i++) {
                GuestHouseImage::query()->firstOrCreate(
                    [
                        'guest_house_id' => $house->id,
                        'path' => 'https://placehold.co/800x600/334155/fff?text='.urlencode($data['name'].'+'.$i),
                    ],
                    ['sort_order' => $i, 'caption' => 'Photo '.$i],
                );
            }

            GuestHouseSeasonalPrice::query()->updateOrCreate(
                [
                    'guest_house_id' => $house->id,
                    'name' => 'Summer peak',
                ],
                [
                    'date_from' => Carbon::parse('2026-06-01'),
                    'date_to' => Carbon::parse('2026-08-31'),
                    'price_per_night' => (int) ($data['base'] * 1.35),
                    'minimum_nights' => 3,
                ],
            );

            GuestHouseSeasonalPrice::query()->updateOrCreate(
                [
                    'guest_house_id' => $house->id,
                    'name' => 'Winter',
                ],
                [
                    'date_from' => Carbon::parse('2026-11-01'),
                    'date_to' => Carbon::parse('2027-03-31'),
                    'price_per_night' => (int) ($data['base'] * 0.85),
                    'minimum_nights' => null,
                ],
            );

            $statuses = [
                GuestHouseBookingStatus::Pending,
                GuestHouseBookingStatus::Confirmed,
                GuestHouseBookingStatus::Completed,
            ];

            foreach ($statuses as $offset => $status) {
                $checkIn = now()->addDays(10 + ($index * 7) + ($offset * 5));
                $checkOut = $checkIn->copy()->addDays(3);
                $nights = 3;
                $baseTotal = $data['base'] * $nights;

                GuestHouseBooking::query()->firstOrCreate(
                    [
                        'guest_house_id' => $house->id,
                        'guest_email' => 'guest'.$index.$offset.'@example.com',
                        'check_in' => $checkIn->toDateString(),
                    ],
                    [
                        'user_id' => $offset === 0 ? $customer?->id : null,
                        'status' => $status,
                        'guest_name' => 'Guest '.($index + 1),
                        'guest_phone' => '+354555'.str_pad((string) ($index * 10 + $offset), 4, '0', STR_PAD_LEFT),
                        'check_out' => $checkOut->toDateString(),
                        'nights' => $nights,
                        'guests_count' => 2,
                        'base_total' => $baseTotal,
                        'cleaning_fee' => 4500,
                        'security_deposit' => 15000,
                        'discount_amount' => 0,
                        'tax_amount' => (int) floor(($baseTotal + 4500) * 0.24),
                        'total_amount' => $baseTotal + 4500 + (int) floor(($baseTotal + 4500) * 0.24),
                        'confirmed_at' => $status !== GuestHouseBookingStatus::Pending ? now() : null,
                    ],
                );
            }
        }
    }
}
