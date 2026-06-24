<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Car;
use App\Models\DailyFare;
use App\Models\Location;
use App\Models\MainCategory;
use App\Models\Order;
use App\Models\PriceType;
use App\Models\Setting;
use App\Models\SubCategory;
use App\Models\User;
use App\Support\BookingConfirmationUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingConfirmationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_confirmation_page_payload_by_token(): void
    {
        Setting::putValue('shop.currency', ['code' => 'EUR']);

        $main = MainCategory::query()->firstOrCreate(['slug' => 'car'], ['name' => 'Car', 'is_active' => true]);
        $category = SubCategory::query()->create(['main_category_id' => $main->id, 'name' => 'Eco', 'is_active' => true, 'is_search_filter' => true]);
        $host = User::factory()->create([
            'name' => 'Einar Host',
            'role' => UserRole::Host,
        ]);
        $car = Car::query()->create([
            'user_id' => $host->id,
            'sub_category_id' => $category->id,
            'name' => 'Test Vehicle',
            'units_available' => 1,
            'is_active' => true,
        ]);
        $pickup = Location::query()->create(['name' => 'P1', 'is_active' => true]);
        $dropoff = Location::query()->create(['name' => 'D1', 'is_active' => true]);
        $priceType = PriceType::query()->create(['name' => 'Basic', 'is_active' => true]);

        $token = BookingConfirmationUrl::generateToken();
        $order = Order::query()->create([
            'reference' => 'ORD-TEST-123',
            'confirmation_token' => $token,
            'confirmation_url' => BookingConfirmationUrl::forToken($token),
            'car_id' => $car->id,
            'price_type_id' => $priceType->id,
            'pickup_location_id' => $pickup->id,
            'dropoff_location_id' => $dropoff->id,
            'pickup_at' => now()->addDay(),
            'dropoff_at' => now()->addDays(4),
            'customer_name' => 'Alex Guest',
            'customer_email' => 'alex@example.com',
            'order_status' => OrderStatus::Confirmed,
            'base_rental_cents' => 15000,
            'extras_cents' => 0,
            'fees_cents' => 0,
            'discount_cents' => 0,
            'tax_cents' => 0,
            'total_cents' => 15000,
            'currency' => 'EUR',
        ]);

        $this->getJson("/api/booking-confirmation/{$token}")
            ->assertOk()
            ->assertJsonPath('data.reference', $order->reference)
            ->assertJsonPath('data.confirmation_url', $order->confirmation_url)
            ->assertJsonPath('data.customer_email', 'alex@example.com')
            ->assertJsonPath('data.bookable_kind', 'order')
            ->assertJsonPath('data.host.name', 'Einar Host')
            ->assertJsonPath('data.host.initial', 'E');

        $this->get("/api/booking-confirmation/{$token}/calendar.ics")
            ->assertOk()
            ->assertHeader('content-type', 'text/calendar; charset=utf-8');
    }
}
