<?php

namespace Tests\Feature;

use App\Enums\ListingApprovalStatus;
use App\Models\Car;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CarenAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_calendar_blocks_unavailable_class_days_and_selected_location_closures(): void
    {
        [$car, $airport, $hotel] = $this->fleet();
        $soldOut = now()->addDays(15)->toDateString();
        $open = now()->addDays(16)->toDateString();
        $closedStart = now()->addDays(40)->toDateString();
        $closedEnd = now()->addDays(41)->toDateString();
        $otherClosed = now()->addDays(50)->toDateString();

        Http::preventStrayRequests();
        Http::fake(function (Request $request) use ($soldOut, $open, $closedStart, $closedEnd, $otherClosed) {
            $url = $request->url();
            if (str_ends_with($url, '/user/login')) {
                return Http::response(['Success' => true, 'Session' => 'session']);
            }
            if (str_ends_with($url, '/class/availabilitycalendar')) {
                return Http::response(['Availability' => [[
                    'ClassId' => 2180,
                    'DatesAvailability' => [
                        ['Date' => $soldOut.'T00:00:00', 'AvailableUnits' => 0, 'IsAvailable' => false],
                        ['Date' => $open.'T00:00:00', 'AvailableUnits' => 2, 'IsAvailable' => true],
                    ],
                ]]]);
            }
            if (str_ends_with($url, '/location/getpickuplist')) {
                return Http::response(['Locations' => [[
                    'Id' => 666,
                    'ClosedDates' => [['From' => $closedStart.'T01:00:00', 'To' => $closedEnd.'T01:00:00']],
                ]]]);
            }
            if (str_ends_with($url, '/location/getdropofflist')) {
                return Http::response(['Locations' => [
                    ['Id' => 666, 'ClosedDates' => []],
                    ['Id' => 745, 'ClosedDates' => [['From' => $otherClosed.'T00:00:00', 'To' => $otherClosed.'T00:00:00']]],
                ]]);
            }

            return Http::response(['Success' => false, 'Code' => 10000], 500);
        });

        $response = $this->getJson('/api/cars/'.$car->id.'/availability-calendar?'.http_build_query([
            'pickup_location_id' => $airport->id,
            'dropoff_location_id' => $airport->id,
        ]))->assertOk();

        $this->assertEqualsCanonicalizing(
            [$soldOut, $closedStart, $closedEnd],
            $response->json('blocked_dates'),
        );
        $this->assertNotContains($open, $response->json('blocked_dates'));
        $this->assertNotContains($otherClosed, $response->json('blocked_dates'));
        $this->assertSame('caren', $response->json('blocked.0.source'));
        $this->assertSame(2, $response->json('units_available'));
        $this->assertSame(0, $car->fresh()->units_available);

        Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '/class/availabilitycalendar')
            && $request['RentalId'] === 120
            && $request['ClassIds'] === [2180]
            && $request['DateFrom'] === now()->startOfDay()->format('Y-m-d').' 00:00');
        Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '/location/getpickuplist')
            && $request['ShowClosedDates'] === true);
        Http::assertSentCount(4);

        $this->getJson('/api/cars/'.$car->id.'/availability-calendar?'.http_build_query([
            'pickup_location_id' => $hotel->id,
            'dropoff_location_id' => $hotel->id,
        ]))->assertOk()->assertJsonFragment(['blocked_dates' => [$soldOut, $otherClosed]]);
        Http::assertSentCount(4);
    }

    public function test_provider_failure_is_not_cached_as_an_open_calendar(): void
    {
        [$car] = $this->fleet();
        Http::preventStrayRequests();
        Http::fake([
            '*/user/login' => Http::response(['Success' => true, 'Session' => 'session']),
            '*/class/availabilitycalendar' => Http::response(['Success' => false, 'Code' => 10000]),
        ]);

        $this->getJson('/api/cars/'.$car->id.'/availability-calendar')
            ->assertStatus(422)
            ->assertJsonPath('blocked_dates', [])
            ->assertJsonPath('message', 'Caren availability could not be checked.');
    }

    /**
     * @return array{0: Car, 1: Location, 2: Location}
     */
    private function fleet(): array
    {
        $host = User::factory()->host()->create([
            'partner_provider' => 'caren', 'partner_api_enabled' => true,
            'partner_api_key' => 'key', 'partner_api_username' => 'api@example.test',
            'partner_api_password' => 'password', 'partner_rental_id' => 120,
        ]);
        $airport = Location::create([
            'name' => 'Airport', 'is_active' => true,
            'external_provider' => 'caren', 'external_id' => '120:666', 'host_user_id' => $host->id,
        ]);
        $hotel = Location::create([
            'name' => 'Hotel', 'is_active' => true,
            'external_provider' => 'caren', 'external_id' => '120:745', 'host_user_id' => $host->id,
        ]);
        $car = Car::factory()->create([
            'user_id' => $host->id, 'external_provider' => 'caren',
            'external_vehicle_id' => '120:2180', 'is_active' => true,
            'listing_status' => ListingApprovalStatus::Approved, 'units_available' => 0,
        ]);
        $car->locations()->attach($airport->id, ['allows_pickup' => true, 'allows_dropoff' => true]);
        $car->locations()->attach($hotel->id, ['allows_pickup' => true, 'allows_dropoff' => true]);

        return [$car, $airport, $hotel];
    }
}
