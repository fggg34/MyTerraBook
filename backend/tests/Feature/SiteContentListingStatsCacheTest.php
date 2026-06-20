<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\DailyFare;
use App\Models\GuestHouse;
use App\Models\PriceType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SiteContentListingStatsCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_saving_car_clears_site_content_cache(): void
    {
        $priceType = PriceType::factory()->create();
        $car = Car::factory()->create();

        DailyFare::query()->create([
            'car_id' => $car->id,
            'price_type_id' => $priceType->id,
            'from_days' => 1,
            'to_days' => 30,
            'price_per_day_cents' => 5000,
        ]);

        Cache::put('site_content.homepage', ['cached' => true], 3600);
        Cache::put('site_content.all', ['cached' => true], 3600);

        $car->update(['name' => 'Updated name']);

        $this->assertFalse(Cache::has('site_content.homepage'));
        $this->assertFalse(Cache::has('site_content.all'));
    }

    public function test_saving_guest_house_clears_site_content_cache(): void
    {
        $guestHouse = GuestHouse::query()->create([
            'name' => 'Harbour Stay',
            'slug' => 'harbour-stay-cache',
            'type' => \App\Enums\GuestHouseType::Apartment,
            'status' => \App\Enums\GuestHouseStatus::Active,
            'city' => 'Reykjavik',
            'max_guests' => 2,
            'base_price_per_night' => 11000,
        ]);

        Cache::put('site_content.homepage', ['cached' => true], 3600);

        $guestHouse->update(['name' => 'Updated guest house']);

        $this->assertFalse(Cache::has('site_content.homepage'));
    }
}
