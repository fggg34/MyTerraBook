<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Car;
use App\Models\Location;
use App\Models\MainCategory;
use App\Models\Order;
use App\Models\PriceType;
use App\Models\Setting;
use App\Services\Partners\GreenlightSettings;
use App\Services\Partners\GreenlightVehicleSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GreenlightPartnerFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_saving_an_api_key_connects_greenlight_without_the_checkbox(): void
    {
        $settings = app(GreenlightSettings::class);

        $this->assertFalse($settings->enabled());

        $settings->saveFromAdmin([
            'greenlight_enabled' => false,
            'greenlight_base_url' => GreenlightSettings::DEFAULT_BASE_URL,
            'greenlight_api_key' => 'glpk_from_form',
        ]);

        $this->assertTrue($settings->enabled());
        $this->assertSame('glpk_from_form', $settings->apiKey());
    }

    public function test_sync_creates_listings_and_checkout_creates_greenlight_reservation(): void
    {
        Setting::putValue('shop.currency', ['code' => 'ISK']);
        Setting::putValue('shop.default_tax', ['basis_points' => 0]);
        Setting::putValue('partners.greenlight', [
            'enabled' => true,
            'base_url' => 'https://greenlight.test/api/partner/v1',
            'api_key' => 'glpk_test',
            'insurance_plan_ids' => [],
        ]);

        MainCategory::ensureBySlug('car', ['name' => 'Car']);
        $priceType = PriceType::query()->create(['name' => 'Basic', 'slug' => 'basic', 'is_active' => true]);

        Http::fake([
            'https://greenlight.test/api/partner/v1/locations' => Http::response([
                'data' => [[
                    'id' => 'loc_1',
                    'name' => 'Keflavik Airport',
                    'address' => 'Airport road',
                    'city' => 'Keflavik',
                    'postalCode' => '235',
                ]],
            ]),
            'https://greenlight.test/api/partner/v1/categories' => Http::response([
                'data' => [['id' => 'cat_1', 'name' => '4x4', 'slug' => '4x4']],
            ]),
            'https://greenlight.test/api/partner/v1/insurance-plans' => Http::response([
                'data' => [['id' => 'ins_1', 'name' => 'Basic', 'dailyPriceIsk' => 3000]],
            ]),
            'https://greenlight.test/api/partner/v1/vehicles' => Http::response([
                'data' => [[
                    'id' => 'veh_1',
                    'name' => 'Toyota RAV4',
                    'make' => 'Toyota',
                    'model' => 'RAV4',
                    'year' => 2024,
                    'categoryId' => 'cat_1',
                    'categoryName' => '4x4',
                    'categorySlug' => '4x4',
                    'seatCount' => 5,
                    'sleepingSpots' => 0,
                    'luggageCapacity' => 3,
                    'transmission' => 'AUTOMATIC',
                    'drivetrain' => 'FOUR_WD',
                    'fuelType' => 'PETROL',
                    'baseDailyRateIsk' => 15000,
                    'totalUnits' => 3,
                    'description' => 'Ready for the highlands.',
                    'photos' => ['https://greenlight.test/uploads/rav4.jpg'],
                ]],
            ]),
            'https://greenlight.test/api/partner/v1/availability*' => Http::response([
                'data' => ['vehicleId' => 'veh_1', 'available' => true, 'freeCount' => 2],
            ]),
            'https://greenlight.test/api/partner/v1/quote' => Http::response([
                'data' => [
                    'currency' => 'ISK',
                    'days' => 3,
                    'baseRateIsk' => 45000,
                    'extrasTotalIsk' => 9000,
                    'addonsTotalIsk' => 0,
                    'oneWayFeeIsk' => 0,
                    'outOfHoursFeeIsk' => 0,
                    'locationFeeIsk' => 2000,
                    'discountIsk' => 0,
                    'totalIsk' => 56000,
                    'lines' => [
                        ['key' => 'location', 'label' => 'Airport fee', 'amountIsk' => 2000],
                    ],
                ],
            ]),
            'https://greenlight.test/api/partner/v1/reservations' => Http::response([
                'data' => [
                    'id' => 'res_1',
                    'reference' => 'GL-1002',
                    'status' => 'CONFIRMED',
                ],
            ], 201),
        ]);

        $result = app(GreenlightVehicleSyncService::class)->sync();

        $this->assertSame(1, $result['vehicles']);
        $car = Car::query()->where('external_vehicle_id', 'veh_1')->first();
        $this->assertNotNull($car);
        $this->assertTrue($car->is_active);
        $this->assertSame('https://greenlight.test/uploads/rav4.jpg', $car->main_image_path);
        $this->assertSame(3, $car->units_available);

        $pickup = Location::query()->where('external_id', 'loc_1')->first();
        $this->assertNotNull($pickup);

        $quote = $this->postJson('/api/orders/quote', [
            'car_id' => $car->id,
            'price_type_id' => $priceType->id,
            'pickup_location_id' => $pickup->id,
            'dropoff_location_id' => $pickup->id,
            'pickup_at' => now()->addDays(2)->toDateTimeString(),
            'dropoff_at' => now()->addDays(5)->toDateTimeString(),
        ]);

        $quote->assertOk()->assertJsonPath('total_cents', 5600000);

        $create = $this->postJson('/api/orders', [
            'car_id' => $car->id,
            'price_type_id' => $priceType->id,
            'pickup_location_id' => $pickup->id,
            'dropoff_location_id' => $pickup->id,
            'pickup_at' => now()->addDays(2)->toDateTimeString(),
            'dropoff_at' => now()->addDays(5)->toDateTimeString(),
            'customer_name' => 'Anna Jonsdottir',
            'customer_email' => 'anna@example.com',
            'customer_phone' => '+3545551234',
            'customer_date_of_birth' => '1990-05-01',
        ]);

        $create->assertCreated()
            ->assertJsonPath('data.external_provider', 'greenlight')
            ->assertJsonPath('data.external_reference', 'GL-1002');

        $order = Order::query()->first();
        $this->assertSame(OrderStatus::Confirmed, $order->order_status);
        $this->assertSame('GL-1002', $order->external_reference);

        $this->assertSame(
            ['ins_1'],
            app(GreenlightSettings::class)->insurancePlanIds(),
        );
    }

    public function test_native_cars_do_not_call_greenlight(): void
    {
        Setting::putValue('shop.currency', ['code' => 'EUR']);
        Setting::putValue('shop.default_tax', ['basis_points' => 0]);

        $main = MainCategory::ensureBySlug('car', ['name' => 'Car']);
        $category = \App\Models\SubCategory::query()->create([
            'main_category_id' => $main->id,
            'name' => 'Eco',
            'is_active' => true,
            'is_search_filter' => true,
        ]);
        $car = Car::query()->create([
            'sub_category_id' => $category->id,
            'name' => 'Local Vehicle',
            'units_available' => 1,
            'is_active' => true,
        ]);
        $pickup = Location::query()->create(['name' => 'P1', 'is_active' => true]);
        $car->locations()->attach($pickup->id, ['allows_pickup' => true, 'allows_dropoff' => true]);
        $priceType = PriceType::query()->create(['name' => 'Basic', 'is_active' => true]);
        \App\Models\DailyFare::query()->create([
            'car_id' => $car->id,
            'price_type_id' => $priceType->id,
            'from_days' => 1,
            'to_days' => 30,
            'price_per_day_cents' => 5000,
        ]);

        Http::fake();

        $this->postJson('/api/orders/quote', [
            'car_id' => $car->id,
            'price_type_id' => $priceType->id,
            'pickup_location_id' => $pickup->id,
            'dropoff_location_id' => $pickup->id,
            'pickup_at' => now()->addDay()->toDateTimeString(),
            'dropoff_at' => now()->addDays(4)->toDateTimeString(),
        ])->assertOk();

        Http::assertNothingSent();
    }

    public function test_availability_calendar_uses_live_greenlight_blocked_days(): void
    {
        Setting::putValue('partners.greenlight', [
            'enabled' => true,
            'base_url' => 'https://greenlight.test/api/partner/v1',
            'api_key' => 'glpk_test',
            'insurance_plan_ids' => [],
        ]);

        $main = MainCategory::ensureBySlug('car', ['name' => 'Car']);
        $category = \App\Models\SubCategory::query()->create([
            'main_category_id' => $main->id,
            'name' => '4x4',
            'is_active' => true,
            'is_search_filter' => true,
        ]);
        $car = Car::query()->create([
            'sub_category_id' => $category->id,
            'name' => 'Toyota RAV4',
            'units_available' => 1,
            'is_active' => true,
            'external_provider' => 'greenlight',
            'external_vehicle_id' => 'veh_1',
        ]);

        Http::fake([
            'https://greenlight.test/api/partner/v1/blocked-days*' => Http::response([
                'data' => [
                    'vehicleId' => 'veh_1',
                    'totalUnits' => 3,
                    'bookableUnits' => 3,
                    'blockedDays' => ['2026-09-20', '2026-09-21'],
                ],
            ]),
        ]);

        $this->getJson("/api/cars/{$car->id}/availability-calendar")
            ->assertOk()
            ->assertJsonPath('blocked_dates.0', '2026-09-20')
            ->assertJsonPath('blocked_dates.1', '2026-09-21')
            ->assertJsonPath('blocked.0.source', 'greenlight')
            ->assertJsonPath('units_available', 3);

        $this->assertSame(3, $car->fresh()->units_available);
    }

    public function test_card_checkout_creates_a_greenlight_hold_until_payment(): void
    {
        Setting::putValue('shop.currency', ['code' => 'ISK']);
        Setting::putValue('shop.default_tax', ['basis_points' => 0]);
        Setting::putValue('partners.greenlight', [
            'enabled' => true,
            'base_url' => 'https://greenlight.test/api/partner/v1',
            'api_key' => 'glpk_test',
            'insurance_plan_ids' => ['ins_1'],
        ]);

        $main = MainCategory::ensureBySlug('car', ['name' => 'Car']);
        $category = \App\Models\SubCategory::query()->create([
            'main_category_id' => $main->id,
            'name' => '4x4',
            'is_active' => true,
            'is_search_filter' => true,
        ]);
        $car = Car::query()->create([
            'sub_category_id' => $category->id,
            'name' => 'Toyota RAV4',
            'units_available' => 2,
            'is_active' => true,
            'external_provider' => 'greenlight',
            'external_vehicle_id' => 'veh_1',
        ]);
        $pickup = Location::query()->create([
            'name' => 'Keflavik',
            'is_active' => true,
            'external_provider' => 'greenlight',
            'external_id' => 'loc_1',
        ]);
        $car->locations()->attach($pickup->id, ['allows_pickup' => true, 'allows_dropoff' => true]);
        $priceType = PriceType::query()->create(['name' => 'Basic', 'is_active' => true]);

        Http::fake([
            'https://greenlight.test/api/partner/v1/availability*' => Http::response([
                'data' => ['vehicleId' => 'veh_1', 'available' => true, 'freeCount' => 2],
            ]),
            'https://greenlight.test/api/partner/v1/quote' => Http::response([
                'data' => [
                    'currency' => 'ISK',
                    'days' => 3,
                    'baseRateIsk' => 45000,
                    'extrasTotalIsk' => 0,
                    'addonsTotalIsk' => 0,
                    'oneWayFeeIsk' => 0,
                    'outOfHoursFeeIsk' => 0,
                    'locationFeeIsk' => 0,
                    'discountIsk' => 0,
                    'totalIsk' => 45000,
                    'lines' => [],
                ],
            ]),
            'https://greenlight.test/api/partner/v1/reservations' => Http::response([
                'data' => [
                    'id' => 'res_hold',
                    'reference' => 'GL-1003',
                    'status' => 'QUOTE',
                ],
            ], 201),
            'https://greenlight.test/api/partner/v1/reservations/GL-1003/confirm' => Http::response([
                'data' => ['reference' => 'GL-1003', 'status' => 'CONFIRMED'],
            ]),
        ]);

        $this->postJson('/api/orders', [
            'car_id' => $car->id,
            'price_type_id' => $priceType->id,
            'pickup_location_id' => $pickup->id,
            'dropoff_location_id' => $pickup->id,
            'pickup_at' => now()->addDays(2)->toDateTimeString(),
            'dropoff_at' => now()->addDays(5)->toDateTimeString(),
            'customer_name' => 'Anna Jonsdottir',
            'customer_email' => 'anna@example.com',
            'customer_phone' => '+3545551234',
            'customer_date_of_birth' => '1990-05-01',
            'payment_method' => 'card',
        ])->assertCreated()
            ->assertJsonPath('data.external_reference', 'GL-1003');

        $order = Order::query()->first();
        $this->assertSame(OrderStatus::Pending, $order->order_status);

        Http::assertSent(function ($request) {
            if (! str_ends_with($request->url(), '/reservations')) {
                return false;
            }

            $body = $request->data();

            return ($body['holdUntilPaid'] ?? false) === true
                && ! empty($body['holdExpiresAt']);
        });

        $order->transitionOrderStatus(OrderStatus::Confirmed);
        app(\App\Services\Partners\GreenlightBookingService::class)->confirmIfNeeded($order);

        Http::assertSent(fn ($request) => str_contains($request->url(), '/reservations/GL-1003/confirm'));
    }

    public function test_expire_pending_cancels_unpaid_greenlight_holds_immediately(): void
    {
        Setting::putValue('partners.greenlight', [
            'enabled' => true,
            'base_url' => 'https://greenlight.test/api/partner/v1',
            'api_key' => 'glpk_test',
            'insurance_plan_ids' => ['ins_1'],
        ]);

        $main = MainCategory::ensureBySlug('car', ['name' => 'Car']);
        $category = \App\Models\SubCategory::query()->create([
            'main_category_id' => $main->id,
            'name' => '4x4',
            'is_active' => true,
            'is_search_filter' => true,
        ]);
        $car = Car::query()->create([
            'sub_category_id' => $category->id,
            'name' => 'Toyota RAV4',
            'units_available' => 1,
            'is_active' => true,
            'external_provider' => 'greenlight',
            'external_vehicle_id' => 'veh_1',
        ]);
        $priceType = PriceType::query()->create(['name' => 'Basic', 'is_active' => true]);
        $pickup = Location::query()->create(['name' => 'P1', 'is_active' => true]);

        $order = Order::query()->create([
            'car_id' => $car->id,
            'price_type_id' => $priceType->id,
            'pickup_location_id' => $pickup->id,
            'dropoff_location_id' => $pickup->id,
            'pickup_at' => now()->addDays(2),
            'dropoff_at' => now()->addDays(5),
            'order_status' => OrderStatus::Pending,
            'customer_name' => 'Anna',
            'customer_email' => 'anna@example.com',
            'external_provider' => 'greenlight',
            'external_reference' => 'GL-UNPAID',
            'base_rental_cents' => 1000,
            'total_cents' => 1000,
            'currency' => 'ISK',
            'payment_lock_expires_at' => now()->addMinutes(15),
        ]);

        Http::fake([
            'https://greenlight.test/api/partner/v1/reservations/GL-UNPAID/cancel' => Http::response([
                'data' => ['reference' => 'GL-UNPAID', 'status' => 'CANCELLED'],
            ]),
        ]);

        $this->artisan('greenlight:expire-pending', ['--immediate' => true])
            ->assertSuccessful();

        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->order_status);
        Http::assertSent(fn ($request) => str_contains($request->url(), '/reservations/GL-UNPAID/cancel'));
    }
}
