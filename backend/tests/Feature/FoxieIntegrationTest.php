<?php

namespace Tests\Feature;

use App\Enums\DriveType;
use App\Enums\ListingApprovalStatus;
use App\Filament\Resources\RegisteredHosts\Pages\EditRegisteredHost;
use App\Enums\OrderStatus;
use App\Models\Car;
use App\Models\Location;
use App\Models\Order;
use App\Models\PriceType;
use App\Models\User;
use App\Services\Partners\FoxiePartnerClient;
use App\Services\Partners\FoxiePricingService;
use App\Services\Partners\FoxieVehicleSyncService;
use Carbon\Carbon;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use RuntimeException;
use Tests\TestCase;

class FoxieIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    protected function tearDown(): void
    {
        putenv('FOXIE_API_KEY');
        unset($_ENV['FOXIE_API_KEY'], $_SERVER['FOXIE_API_KEY']);
        parent::tearDown();
    }

    public function test_catalogue_imports_campers_locations_and_retires_missing_vehicles(): void
    {
        $host = $this->host();
        $sync = app(FoxieVehicleSyncService::class);
        $result = $sync->importCatalogue($host, $this->catalogue());

        $this->assertSame(['vehicles' => 2, 'locations' => 1, 'skipped' => 1, 'deactivated' => 0], $result);
        $camper = $host->cars()->where('external_vehicle_id', '20')->firstOrFail();
        $this->assertSame('Campervan 2 Persons Premium', $camper->name);
        $this->assertSame('Sleeps 2', $camper->description);
        $this->assertSame('automatic', $camper->transmission);
        $this->assertSame('diesel', $camper->fuel_type);
        $this->assertSame(DriveType::Fwd, $camper->drive_type);
        $this->assertSame(2, $camper->sleeps);
        $this->assertSame(3, $camper->units_available);
        $this->assertSame(ListingApprovalStatus::Approved, $camper->listing_status);
        $this->assertSame('campervan', $camper->subCategory->mainCategory->slug);
        $this->assertSame('10:00:00', substr((string) $camper->pickup_time_from, 0, 8));
        $this->assertSame([
            'https://foxiecampers.is/photo.jpg',
            'https://foxiecampers.is/campers/side.jpg',
        ], $camper->details_image_paths);
        $location = $camper->locations()->first();
        $this->assertSame('2', $location->external_id);
        $this->assertSame('IS', $location->country_code);
        $this->assertTrue((bool) $location->pivot->allows_pickup);
        $this->assertTrue((bool) $location->pivot->allows_dropoff);
        $minibus = $host->cars()->where('external_vehicle_id', '22')->firstOrFail();
        $this->assertSame('car', $minibus->subCategory->mainCategory->slug);
        $this->assertSame(0, $host->cars()->where('name', 'Test Camper')->count());

        $again = $this->catalogue();
        $again['vehicles'] = array_values(array_filter(
            $again['vehicles'],
            fn (array $row) => (int) $row['id'] !== 22,
        ));
        $second = $sync->importCatalogue($host, $again);
        $this->assertSame(1, $second['deactivated']);
        $this->assertFalse($minibus->fresh()->is_active);
        $this->assertTrue($camper->fresh()->is_active);
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('daily_fares', 0);
    }

    public function test_client_uses_the_foxie_key_and_hides_it_when_rejected(): void
    {
        $host = $this->host();
        Http::preventStrayRequests();
        $mode = 'ok';
        Http::fake(function (Request $request) use (&$mode) {
            if ($mode === 'reject') {
                return Http::response(['error' => 'forbidden', 'message' => 'Invalid or revoked API key test-foxie-key'], 403);
            }

            return $this->catalogueResponse($request);
        });
        $catalogue = app(FoxiePartnerClient::class)->catalogue($host);
        $this->assertSame('Sleeps 2', $catalogue['vehicles'][0]['description']);
        Http::assertSent(fn (Request $request) => str_contains($request->url(), '/locations')
            && $request->hasHeader('X-API-Key', 'test-foxie-key'));
        Http::assertNotSent(fn (Request $request) => str_contains($request->url(), '/bookings'));

        $mode = 'reject';
        try {
            app(FoxiePartnerClient::class)->catalogue($host);
            $this->fail('An invalid Foxie key should fail.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Foxie request failed (invalid_api_key).', $exception->getMessage());
        }

        $host->partner_api_base_url = 'https://evil.example/api';
        Http::fake();
        try {
            app(FoxiePartnerClient::class)->catalogue($host);
            $this->fail('A non-Foxie URL should be refused.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Foxie only accepts its published API URL.', $exception->getMessage());
        }
        Http::assertNothingSent();
    }

    public function test_edit_page_saves_the_key_and_imports_the_fleet(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $host = $this->host();
        Http::preventStrayRequests();
        Http::fake(fn (Request $request) => $this->catalogueResponse($request, 'updated-key'));
        Livewire::test(EditRegisteredHost::class, ['record' => $host->id])
            ->fillForm([
                'partner_provider' => 'foxie',
                'partner_api_enabled' => true,
                'partner_api_base_url' => FoxiePartnerClient::BASE_URL,
                'partner_api_key' => 'updated-key',
            ])
            ->callAction(TestAction::make('saveAndImportFoxieVehicles')->schemaComponent('form-actions', 'content'))
            ->assertHasNoFormErrors()
            ->assertNotified('Foxie fleet imported');
        $this->assertSame('updated-key', $host->fresh()->partner_api_key);
        $this->assertSame(1, $host->cars()->count());
        Http::assertNotSent(fn (Request $request) => str_contains($request->url(), '/bookings'));
    }

    public function test_import_turns_the_connection_on_and_keeps_a_saved_key(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $host = $this->host();
        $host->update([
            'partner_api_enabled' => false,
            'partner_api_base_url' => 'https://foxiecampers.is/api/v1',
        ]);
        Http::preventStrayRequests();
        Http::fake(fn (Request $request) => $this->catalogueResponse($request));
        Livewire::test(EditRegisteredHost::class, ['record' => $host->id])
            ->fillForm([
                'partner_provider' => 'foxie',
                'partner_api_enabled' => false,
                'partner_api_base_url' => 'https://foxiecampers.is/api/v1',
                'partner_api_key' => '',
            ])
            ->callAction(TestAction::make('saveAndImportFoxieVehicles')->schemaComponent('form-actions', 'content'))
            ->assertHasNoFormErrors()
            ->assertNotified('Foxie fleet imported');
        $fresh = $host->fresh();
        $this->assertTrue($fresh->partner_api_enabled);
        $this->assertSame('foxie', $fresh->partner_provider);
        $this->assertSame(FoxiePartnerClient::BASE_URL, $fresh->partner_api_base_url);
        $this->assertSame('test-foxie-key', $fresh->partner_api_key);
        $this->assertSame(1, $host->cars()->count());
    }

    public function test_import_asks_for_a_key_when_none_is_saved(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $host = $this->host();
        $host->update(['partner_api_key' => null, 'partner_api_enabled' => false]);
        Http::preventStrayRequests();
        Http::fake();
        Livewire::test(EditRegisteredHost::class, ['record' => $host->id])
            ->fillForm([
                'partner_provider' => 'foxie',
                'partner_api_enabled' => false,
                'partner_api_key' => '',
            ])
            ->callAction(TestAction::make('saveAndImportFoxieVehicles')->schemaComponent('form-actions', 'content'))
            ->assertNotified('Host saved, but Foxie import failed');
        Http::assertNothingSent();
        $this->assertSame(0, $host->cars()->count());
        $this->assertTrue($host->fresh()->partner_api_enabled);
        $this->assertNull($host->fresh()->partner_api_key);
    }

    public function test_connect_command_imports_only_after_foxie_accepts_the_key(): void
    {
        Http::preventStrayRequests();
        $mode = 'ok';
        Http::fake(function (Request $request) use (&$mode) {
            if ($mode === 'reject') {
                return Http::response(['error' => 'forbidden', 'message' => 'Invalid or revoked API key test-foxie-key'], 403);
            }

            return $this->catalogueResponse($request);
        });
        putenv('FOXIE_API_KEY=test-foxie-key');
        $_ENV['FOXIE_API_KEY'] = 'test-foxie-key';
        $_SERVER['FOXIE_API_KEY'] = 'test-foxie-key';
        $this->artisan('foxie:connect', ['email' => 'foxie@partners.myterrabook.local'])
            ->expectsOutputToContain('Foxie Camper')
            ->doesntExpectOutputToContain('test-foxie-key')
            ->assertSuccessful();
        $host = User::query()->where('email', 'foxie@partners.myterrabook.local')->firstOrFail();
        $this->assertSame('Foxie Camper', $host->name);
        $this->assertSame('+3548504441', $host->phone);
        $this->assertSame('foxie', $host->partner_provider);
        $this->assertSame(FoxiePartnerClient::BASE_URL, $host->partner_api_base_url);
        $this->assertSame('test-foxie-key', $host->partner_api_key);
        $this->assertNotSame('test-foxie-key', DB::table('users')->where('id', $host->id)->value('partner_api_key'));
        $this->assertSame(1, $host->cars()->count());

        $mode = 'reject';
        $this->artisan('foxie:connect', ['email' => 'rejected@partners.myterrabook.local'])
            ->expectsOutputToContain('Foxie request failed (invalid_api_key).')
            ->doesntExpectOutputToContain('test-foxie-key')
            ->assertFailed();
        $this->assertDatabaseMissing('users', ['email' => 'rejected@partners.myterrabook.local']);
    }

    public function test_prices_come_from_foxie_and_failures_are_not_cached(): void
    {
        [$car, $trip] = $this->fleet();
        Http::preventStrayRequests();
        Http::fake(fn (Request $request) => $this->quoteResponse($request));
        $response = $this->getJson('/api/cars?'.http_build_query($trip))->assertOk();
        $row = collect($response->json('data'))->firstWhere('id', $car->id);
        $this->assertSame('priced', $row['pricing_status']);
        $this->assertSame('18900.00', $row['search_pricing']['rental_subtotal']);
        $this->assertSame('2700.00', $row['search_pricing']['daily_price']);
        $this->assertSame('ISK', $row['search_pricing']['currency']);
        $this->assertTrue($row['search_pricing']['estimate_only']);
        $this->assertSame('foxie', $row['search_pricing']['source']);
        Http::assertSentCount(2);
        Http::assertSent(function (Request $request) {
            if (! str_contains($request->url(), '/availability')) {
                return false;
            }
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            return ($query['vehicle_id'] ?? null) === '20'
                && ($query['pickup_location'] ?? null) === '2'
                && ($query['pickup_time'] ?? null) === '10:00'
                && ($query['pickup_date'] ?? null) === now()->addDays(10)->format('Y-m-d');
        });
        $this->getJson('/api/cars/'.$car->id.'/foxie-price?'.http_build_query($trip))
            ->assertOk()->assertJsonPath('pricing.rental_subtotal', '18900.00');
        Http::assertSentCount(2);
        $this->assertDatabaseCount('daily_fares', 0);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_unavailable_and_failed_quotes_do_not_invent_a_price(): void
    {
        [$car, $trip] = $this->fleet();
        Http::preventStrayRequests();
        Http::fake(fn (Request $request) => str_contains($request->url(), '/pricing')
            ? Http::response(['code' => 'no_price', 'message' => 'No price.'], 404)
            : Http::response(['available' => true]));
        $this->getJson('/api/cars/'.$car->id.'/foxie-price?'.http_build_query($trip))
            ->assertOk()->assertJsonPath('status', 'unavailable')->assertJsonPath('pricing', null);

        Cache::flush();
        $priced = false;
        Http::fake(function (Request $request) use (&$priced) {
            if (str_contains($request->url(), '/pricing')) {
                $priced = true;
            }

            return Http::response(['available' => false]);
        });
        $this->getJson('/api/cars/'.$car->id.'/foxie-price?'.http_build_query($trip))
            ->assertOk()->assertJsonPath('status', 'unavailable');
        $this->assertFalse($priced);

        Cache::flush();
        Http::fake(fn (Request $request) => str_contains($request->url(), '/pricing')
            ? Http::response(['total' => 0, 'currency' => 'ISK', 'days' => 7])
            : Http::response(['available' => true]));
        $this->getJson('/api/cars/'.$car->id.'/foxie-price?'.http_build_query($trip))
            ->assertOk()->assertJsonPath('status', 'unavailable')->assertJsonPath('pricing', null);
    }

    public function test_provider_failure_is_not_cached_as_a_price(): void
    {
        [$car, $trip] = $this->fleet();
        Http::preventStrayRequests();
        Http::fake(['*' => Http::response(['code' => 'server_error', 'message' => 'test-foxie-key'], 500)]);
        $from = Carbon::parse($trip['pickup_at']);
        $to = Carbon::parse($trip['dropoff_at']);
        $first = app(FoxiePricingService::class)->quote($car, $trip['pickup_location_id'], $trip['dropoff_location_id'], $from, $to);
        $this->assertSame('error', $first['status']);
        $this->assertNull($first['pricing']);
        Http::assertSentCount(1);
        $second = app(FoxiePricingService::class)->quote($car, $trip['pickup_location_id'], $trip['dropoff_location_id'], $from, $to);
        $this->assertSame('error', $second['status']);
        Http::assertSentCount(2);
    }

    public function test_missing_dates_show_the_foxie_starting_price(): void
    {
        [$car, $trip] = $this->fleet();
        Http::preventStrayRequests();
        Http::fake([
            '*/vehicles/20' => Http::response([
                'id' => 20,
                'price_from' => 10000,
                'price_from_per_days' => 1,
                'currency' => 'ISK',
            ]),
        ]);
        $this->getJson('/api/cars/'.$car->id.'/foxie-price')->assertOk()
            ->assertJsonPath('status', 'base')
            ->assertJsonPath('pricing.daily_price', '10000.00')
            ->assertJsonPath('pricing.currency', 'ISK')
            ->assertJsonPath('pricing.base_only', true);
        $row = collect($this->getJson('/api/cars')->assertOk()->json('data'))->firstWhere('id', $car->id);
        $this->assertSame('10000.00', $row['search_pricing']['daily_price']);
        $this->assertSame('base', $row['pricing_status']);
        $trip['pickup_location_id'] = 999999;
        $this->getJson('/api/cars/'.$car->id.'/foxie-price?'.http_build_query($trip))->assertOk()
            ->assertJsonPath('status', 'unavailable');
        Http::assertSentCount(1);

        $car->update(['is_active' => false]);
        $this->getJson('/api/cars/'.$car->id.'/foxie-price')->assertNotFound();
    }

    public function test_a_zero_foxie_starting_price_is_not_shown(): void
    {
        [$car] = $this->fleet();
        Http::preventStrayRequests();
        Http::fake([
            '*/vehicles/20' => Http::response([
                'id' => 20,
                'price_from' => 0,
                'price_from_per_days' => 1,
                'currency' => 'ISK',
            ]),
        ]);
        $this->getJson('/api/cars/'.$car->id.'/foxie-price')->assertOk()
            ->assertJsonPath('status', 'needs_dates')
            ->assertJsonPath('pricing', null);
    }

    public function test_calendar_blocks_foxie_dates_and_does_not_cache_a_failure(): void
    {
        [$car] = $this->fleet();
        Http::preventStrayRequests();
        Http::fake([
            '*/vehicles/20' => Http::response([
                'id' => 20,
                'units' => 3,
                'blocked_dates' => [
                    ['from' => now()->addDays(20)->toDateString(), 'to' => now()->addDays(22)->toDateString()],
                    ['date' => now()->addDays(30)->toDateString(), 'available' => true],
                    ['date' => now()->addDays(31)->toDateString(), 'available' => false],
                    now()->addDays(400)->toDateString(),
                ],
            ]),
        ]);
        $expected = [
            now()->addDays(20)->toDateString(),
            now()->addDays(21)->toDateString(),
            now()->addDays(22)->toDateString(),
            now()->addDays(31)->toDateString(),
        ];
        $this->getJson('/api/cars/'.$car->id.'/availability-calendar')->assertOk()
            ->assertJsonPath('blocked_dates', $expected)
            ->assertJsonPath('blocked.0.source', 'foxie')
            ->assertJsonPath('units_available', 3);
        $this->assertSame(0, $car->fresh()->units_available);
        $this->getJson('/api/cars/'.$car->id.'/availability-calendar')->assertOk();
        Http::assertSentCount(1);
    }

    public function test_calendar_failure_is_not_cached_as_an_open_calendar(): void
    {
        [$car] = $this->fleet();
        Http::preventStrayRequests();
        Http::fake(['*' => Http::response(['code' => 'server_error'], 500)]);
        $this->getJson('/api/cars/'.$car->id.'/availability-calendar')->assertStatus(422)
            ->assertJsonPath('blocked_dates', [])
            ->assertJsonPath('message', 'Foxie availability could not be checked.');
        $this->getJson('/api/cars/'.$car->id.'/availability-calendar')->assertStatus(422);
        Http::assertSentCount(2);
    }

    public function test_checkout_creates_a_foxie_reservation_from_a_live_quote(): void
    {
        [$car, $trip] = $this->fleet();
        $wrong = PriceType::factory()->create();
        $rate = PriceType::query()->firstOrCreate(
            ['slug' => 'foxie-standard'],
            ['name' => 'Foxie rate', 'is_active' => true],
        );
        Http::preventStrayRequests();
        Http::fake(function (Request $request) {
            if ($request->method() === 'POST' && str_contains($request->url(), '/bookings')) {
                return Http::response([
                    'success' => true,
                    'booking_id' => 4401,
                    'status' => 'confirmed',
                    'total' => 18900,
                    'currency' => 'ISK',
                    'paid_online' => true,
                    'paid_amount' => 1890,
                    'balance_at_pickup' => 17010,
                ], 201);
            }

            return $this->quoteResponse($request);
        });
        $payload = $trip + [
            'car_id' => $car->id,
            'price_type_id' => $wrong->id,
            'customer_name' => 'Test Driver',
            'customer_email' => 'traveler@example.test',
            'customer_phone' => '+3545550000',
        ];
        $this->postJson('/api/orders/quote', $payload)->assertStatus(422)
            ->assertJsonPath('message', 'Choose the Foxie rate.');
        $this->assertDatabaseCount('orders', 0);

        $payload['price_type_id'] = $rate->id;
        $this->postJson('/api/orders/quote', $payload)->assertOk()
            ->assertJsonPath('total', '18900.00')
            ->assertJsonPath('currency', 'ISK')
            ->assertJsonPath('rental_days', 7);
        $this->postJson('/api/orders', $payload)->assertCreated()
            ->assertJsonPath('data.external_reference', '4401')
            ->assertJsonPath('data.order_status', 'confirmed')
            ->assertJsonPath('data.currency', 'ISK');
        $this->assertDatabaseHas('orders', [
            'car_id' => $car->id,
            'external_provider' => 'foxie',
            'external_reference' => '4401',
            'total_cents' => 1890000,
            'currency' => 'ISK',
            'order_status' => 'confirmed',
            'partner_sync_state' => 'confirmed',
        ]);
        Http::assertSent(function (Request $request) {
            if ($request->method() !== 'POST' || ! str_contains($request->url(), '/bookings')) {
                return false;
            }
            $body = $request->data();

            return ($body['vehicle_id'] ?? null) === 20
                && ($body['pickup_location'] ?? null) === 2
                && ($body['dropoff_location'] ?? null) === 2
                && ($body['customer']['first_name'] ?? null) === 'Test'
                && ($body['customer']['last_name'] ?? null) === 'Driver'
                && ($body['customer']['email'] ?? null) === 'traveler@example.test';
        });

        Http::fake([
            '*/bookings/4401' => Http::response([
                'success' => true,
                'booking_id' => 4401,
                'status' => 'cancelled',
            ]),
        ]);
        Order::query()->firstOrFail()->update(['order_status' => OrderStatus::Cancelled]);
        $this->assertSame('cancelled', Order::query()->firstOrFail()->partner_sync_state);
        Http::assertSent(fn (Request $request) => $request->method() === 'DELETE'
            && str_contains($request->url(), '/bookings/4401'));
    }

    public function test_a_rejected_foxie_booking_does_not_create_an_order(): void
    {
        [$car, $trip] = $this->fleet();
        $rate = PriceType::query()->firstOrCreate(
            ['slug' => 'foxie-standard'],
            ['name' => 'Foxie rate', 'is_active' => true],
        );
        Http::preventStrayRequests();
        Http::fake(function (Request $request) {
            if ($request->method() === 'POST') {
                return Http::response(['code' => 'missing_field', 'message' => 'Customer first_name, last_name, and email are required.'], 400);
            }

            return $this->quoteResponse($request);
        });
        $this->postJson('/api/orders', $trip + [
            'car_id' => $car->id,
            'price_type_id' => $rate->id,
            'customer_name' => 'Test Driver',
            'customer_email' => 'traveler@example.test',
        ])->assertStatus(422)
            ->assertJsonPath('message', 'Foxie could not complete this reservation. Please try again.');
        $this->assertDatabaseCount('orders', 0);
        Http::assertNotSent(fn (Request $request) => $request->method() === 'DELETE');
    }

    public function test_foxie_insurance_is_quoted_and_sent_with_the_booking(): void
    {
        [$car, $trip] = $this->fleet();
        $rate = PriceType::query()->firstOrCreate(
            ['slug' => 'foxie-standard'],
            ['name' => 'Foxie rate', 'is_active' => true],
        );
        Http::preventStrayRequests();
        Http::fake(function (Request $request) {
            if ($request->method() === 'POST' && str_contains($request->url(), '/bookings')) {
                return Http::response([
                    'success' => true,
                    'booking_id' => 4402,
                    'status' => 'confirmed',
                    'total' => 49000,
                    'insurance' => 30100,
                    'currency' => 'ISK',
                ], 201);
            }

            return $this->quoteResponse($request);
        });

        $this->getJson('/api/cars/'.$car->id)->assertOk()
            ->assertJsonPath('data.insurance_plans.0.name', 'Basic')
            ->assertJsonPath('data.insurance_plans.0.cost', '0.00')
            ->assertJsonPath('data.insurance_plans.1.id', 14)
            ->assertJsonPath('data.insurance_plans.1.name', 'Gold')
            ->assertJsonPath('data.insurance_plans.1.cost', '4300.00')
            ->assertJsonPath('data.insurance_plans.1.per_day', true)
            ->assertJsonPath('data.partner_characteristics.0.name', 'Kitchen')
            ->assertJsonPath('data.partner_characteristics.1.name', 'Awning')
            ->assertJsonPath('data.partner_characteristics.2.name', '4 doors')
            ->assertJsonPath('data.partner_extras.0.id', 5)
            ->assertJsonPath('data.partner_extras.0.name', 'Extra Gas Can')
            ->assertJsonPath('data.partner_extras.0.cost', '1300.00');

        $payload = $trip + [
            'car_id' => $car->id,
            'price_type_id' => $rate->id,
            'customer_name' => 'Test Driver',
            'customer_email' => 'traveler@example.test',
            'foxie_insurance_id' => 99,
        ];
        $this->postJson('/api/orders/quote', $payload)->assertStatus(422)
            ->assertJsonPath('message', 'Choose a Foxie insurance plan from this vehicle.');

        $payload['foxie_insurance_id'] = 14;
        $this->postJson('/api/orders/quote', $payload)->assertOk()
            ->assertJsonPath('total', '49000.00')
            ->assertJsonPath('extras_subtotal', '30100.00')
            ->assertJsonPath('extras_lines.0.name', 'Gold');
        $this->postJson('/api/orders', $payload)->assertCreated()
            ->assertJsonPath('data.total', '49000.00');
        $this->assertDatabaseHas('orders', [
            'car_id' => $car->id,
            'external_reference' => '4402',
            'extras_cents' => 3010000,
            'total_cents' => 4900000,
        ]);
        Http::assertSent(function (Request $request) {
            if ($request->method() !== 'POST' || ! str_contains($request->url(), '/bookings')) {
                return false;
            }

            return ($request->data()['insurance'] ?? null) === 14;
        });
    }

    public function test_foxie_extras_are_quoted_and_sent_with_the_booking(): void
    {
        [$car, $trip] = $this->fleet();
        $rate = PriceType::query()->firstOrCreate(
            ['slug' => 'foxie-standard'],
            ['name' => 'Foxie rate', 'is_active' => true],
        );
        Http::preventStrayRequests();
        Http::fake(function (Request $request) {
            if ($request->method() === 'POST' && str_contains($request->url(), '/bookings')) {
                return Http::response([
                    'success' => true,
                    'booking_id' => 4403,
                    'status' => 'confirmed',
                    'total' => 20200,
                    'currency' => 'ISK',
                ], 201);
            }

            return $this->quoteResponse($request);
        });

        $payload = $trip + [
            'car_id' => $car->id,
            'price_type_id' => $rate->id,
            'customer_name' => 'Test Driver',
            'customer_email' => 'traveler@example.test',
            'partner_extra_ids' => [99],
        ];
        $this->postJson('/api/orders/quote', $payload)->assertStatus(422)
            ->assertJsonPath('message', 'Choose extras from this vehicle.');

        $payload['partner_extra_ids'] = ['5'];
        $this->postJson('/api/orders/quote', $payload)->assertOk()
            ->assertJsonPath('total', '20200.00')
            ->assertJsonPath('extras_subtotal', '1300.00')
            ->assertJsonPath('extras_lines.0.name', 'Extra Gas Can');
        $this->postJson('/api/orders', $payload)->assertCreated();
        Http::assertSent(function (Request $request) {
            if ($request->method() !== 'POST' || ! str_contains($request->url(), '/bookings')) {
                return false;
            }

            return ($request->data()['extras'] ?? null) === '5:1';
        });
    }

    private function host(): User
    {
        return User::factory()->host()->create([
            'partner_provider' => 'foxie',
            'partner_api_enabled' => true,
            'partner_api_base_url' => FoxiePartnerClient::BASE_URL,
            'partner_api_key' => 'test-foxie-key',
            'country_code' => 'IS',
        ]);
    }

    /**
     * @return array{0: Car, 1: array<string, mixed>}
     */
    private function fleet(): array
    {
        $host = $this->host();
        $location = Location::create([
            'name' => 'Keflavik Airport',
            'is_active' => true,
            'external_provider' => 'foxie',
            'external_id' => '2',
            'host_user_id' => $host->id,
            'country_code' => 'IS',
        ]);
        $car = Car::factory()->create([
            'user_id' => $host->id,
            'external_provider' => 'foxie',
            'external_vehicle_id' => '20',
            'is_active' => true,
            'listing_status' => ListingApprovalStatus::Approved,
            'units_available' => 0,
        ]);
        $car->locations()->attach($location->id, ['allows_pickup' => true, 'allows_dropoff' => true]);

        return [$car, [
            'pickup_location_id' => $location->id,
            'dropoff_location_id' => $location->id,
            'pickup_at' => now()->addDays(10)->format('Y-m-d').'T10:00:00',
            'dropoff_at' => now()->addDays(17)->format('Y-m-d').'T10:00:00',
        ]];
    }

    /**
     * @return array{locations: list<array<string, mixed>>, vehicles: list<array<string, mixed>>}
     */
    private function catalogue(): array
    {
        return [
            'locations' => [[
                'id' => 2,
                'name' => 'Keflavik Airport',
                'address' => 'Smidjuvellir 80',
                'latitude' => 63.99,
                'longitude' => -22.6,
            ]],
            'vehicles' => [
                [
                    'id' => 20,
                    'name' => 'Campervan 2 Persons Premium',
                    'category' => 'Medium Camper',
                    'units' => 3,
                    'description' => 'Sleeps 2',
                    'seats' => 2,
                    'sleeps' => 2,
                    'transmission' => 'Automatic',
                    'fuel' => 'Diesel',
                    'drive' => 'FWD',
                    'images' => ['https://foxiecampers.is/photo.jpg', '/campers/side.jpg', 'http://insecure.example/a.jpg'],
                    'pickup_locations' => [2],
                    'return_locations' => [2],
                ],
                ['id' => 21, 'name' => 'Test Camper'],
                [
                    'id' => 22,
                    'name' => 'Renault Trafic 9 Seater Minibus',
                    'seats' => 9,
                    'transmission' => 'Automatic',
                ],
            ],
        ];
    }

    private function catalogueResponse(Request $request, string $key = 'test-foxie-key')
    {
        if (! $request->hasHeader('X-API-Key', $key)) {
            return Http::response(['code' => 'invalid_api_key', 'message' => 'Invalid API key.'], 403);
        }
        $path = parse_url($request->url(), PHP_URL_PATH);
        if (str_ends_with((string) $path, '/locations')) {
            return Http::response([['id' => 2, 'name' => 'Keflavik Airport', 'address' => 'Smidjuvellir 80']]);
        }
        if (str_ends_with((string) $path, '/vehicles')) {
            return Http::response([
                ['id' => 20, 'name' => 'Campervan 2 Persons Premium', 'units' => 3],
                ['id' => 21, 'name' => 'Test Camper'],
            ]);
        }
        if (str_ends_with((string) $path, '/vehicles/20')) {
            return Http::response([
                'id' => 20,
                'name' => 'Campervan 2 Persons Premium',
                'description' => 'Sleeps 2',
                'sleeps' => 2,
                'transmission' => 'Automatic',
                'fuel' => 'Diesel',
                'drive' => 'FWD',
                'units' => 3,
                'images' => ['https://foxiecampers.is/photo.jpg'],
                'pickup_locations' => [2],
                'return_locations' => [2],
            ]);
        }
        if (str_ends_with((string) $path, '/vehicles/21')) {
            return Http::response(['id' => 21, 'name' => 'Test Camper']);
        }

        return Http::response(['code' => 'request_failed'], 500);
    }

    private function quoteResponse(Request $request)
    {
        if (str_contains($request->url(), '/pricing')) {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);
            $insurance = (int) ($query['insurance'] ?? 0);
            $insuranceTotal = $insurance === 14 ? 30100 : 0;
            $extraIds = array_values(array_filter(explode(';', (string) ($query['extras'] ?? ''))));
            $extrasTotal = count($extraIds) * 1300;

            return Http::response([
                'total' => 18900 + $insuranceTotal + $extrasTotal,
                'insurance_total' => $insuranceTotal,
                'extras_total' => $extrasTotal,
                'insurance' => $insurance ? [['id' => $insurance, 'name' => 'Gold']] : [],
                'currency' => 'ISK',
                'days' => 7,
            ]);
        }
        if (str_contains($request->url(), '/vehicles/20')) {
            return Http::response([
                'id' => 20,
                'price_from' => 10000,
                'price_from_per_days' => 1,
                'currency' => 'ISK',
                'doors' => 4,
                'characteristics' => ['Kitchen', ['name' => 'Awning']],
                'insurance' => [
                    ['id' => 12, 'name' => 'Basic', 'cost' => 0, 'per_day' => false, 'is_insurance' => true, 'description' => ''],
                    ['id' => 14, 'name' => 'Gold', 'cost' => 4300, 'per_day' => true, 'is_insurance' => true, 'description' => 'Lower excess'],
                ],
                'extras' => [
                    ['id' => 5, 'name' => 'Extra Gas Can', 'cost' => 1300, 'per_day' => false, 'is_insurance' => false, 'description' => 'Spare fuel', 'max_quantity' => 1],
                ],
            ]);
        }

        return Http::response(['available' => true, 'vehicle_id' => 20]);
    }
}
