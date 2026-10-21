<?php

namespace Tests\Feature;

use App\Enums\DriveType;
use App\Enums\ListingApprovalStatus;
use App\Filament\Resources\RegisteredHosts\Pages\EditRegisteredHost;
use App\Models\Car;
use App\Models\Location;
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

    public function test_missing_dates_and_bad_locations_do_not_call_foxie(): void
    {
        [$car, $trip] = $this->fleet();
        Http::fake();
        $this->getJson('/api/cars/'.$car->id.'/foxie-price')->assertOk()
            ->assertJsonPath('status', 'needs_dates')->assertJsonPath('pricing', null);
        $trip['pickup_location_id'] = 999999;
        $this->getJson('/api/cars/'.$car->id.'/foxie-price?'.http_build_query($trip))->assertOk()
            ->assertJsonPath('status', 'unavailable');
        $trip['total'] = 0;
        Http::assertNothingSent();
        $car->update(['is_active' => false]);
        $this->getJson('/api/cars/'.$car->id.'/foxie-price')->assertNotFound();
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

    public function test_checkout_cannot_create_a_foxie_reservation(): void
    {
        [$car, $trip] = $this->fleet();
        $price = PriceType::factory()->create();
        $payload = $trip + [
            'car_id' => $car->id,
            'price_type_id' => $price->id,
            'customer_name' => 'Traveler',
            'customer_email' => 'traveler@example.test',
        ];
        $this->postJson('/api/orders/quote', $payload)->assertStatus(422)
            ->assertJsonPath('message', 'Online booking is not yet available for this vehicle.');
        $this->postJson('/api/orders', $payload)->assertStatus(422)
            ->assertJsonPath('message', 'Online booking is not yet available for this vehicle.');
        $this->assertDatabaseCount('orders', 0);
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
            return Http::response(['total' => 18900, 'currency' => 'ISK', 'days' => 7]);
        }

        return Http::response(['available' => true, 'vehicle_id' => 20]);
    }
}
