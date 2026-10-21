<?php

namespace Tests\Feature;

use App\Enums\ListingApprovalStatus;
use App\Models\Car;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CarenPricingTest extends TestCase
{
    use RefreshDatabase;

    private function fleet(): array
    {
        $host = User::factory()->host()->create([
            'partner_provider' => 'caren', 'partner_api_enabled' => true,
            'partner_api_key' => 'key', 'partner_api_username' => 'api@example.test',
            'partner_api_password' => 'password', 'partner_rental_id' => 120,
        ]);
        $location = Location::create(['name' => 'Airport', 'is_active' => true,
            'external_provider' => 'caren', 'external_id' => '120:666', 'host_user_id' => $host->id]);
        $cars = collect([2180, 2181])->map(function ($id) use ($host, $location) {
            $car = Car::factory()->create(['user_id' => $host->id, 'external_provider' => 'caren',
                'external_vehicle_id' => '120:'.$id, 'is_active' => true,
                'listing_status' => ListingApprovalStatus::Approved, 'units_available' => 0]);
            $car->locations()->attach($location->id, ['allows_pickup' => true, 'allows_dropoff' => true]);

            return $car;
        });

        return [$cars, ['pickup_location_id' => $location->id, 'dropoff_location_id' => $location->id,
            'pickup_at' => now()->addDays(10)->format('Y-m-d').'T10:00:00Z',
            'dropoff_at' => now()->addDays(17)->format('Y-m-d').'T10:00:00Z']];
    }

    private function fakePrices(array $overrides = [], bool $fail = false): void
    {
        Http::preventStrayRequests();
        Http::fake([
            '*/user/login' => Http::response(['Success' => true, 'Session' => 'session']),
            '*/class/getlist' => Http::response($fail ? ['Success' => false, 'Code' => 10000] : ['Classes' => [
                array_replace(['Id' => 2180, 'Available' => true, 'Currency' => 'ISK', 'Days' => 7, 'TotalPrice' => 123456.78], $overrides),
                ['Id' => 2181, 'Available' => true, 'Currency' => 'ISK', 'Days' => 7, 'TotalPrice' => 140000],
            ]]),
        ]);
    }

    public function test_search_batches_prices_and_preserves_provider_currency_and_total(): void
    {
        [$cars, $trip] = $this->fleet();
        $this->fakePrices();
        $response = $this->getJson('/api/cars?'.http_build_query($trip))->assertOk();
        $row = collect($response->json('data'))->firstWhere('id', $cars[0]->id);
        $this->assertSame('priced', $row['pricing_status']);
        $this->assertSame('123456.78', $row['search_pricing']['rental_subtotal']);
        $this->assertSame('ISK', $row['search_pricing']['currency']);
        $this->assertTrue($row['search_pricing']['estimate_only']);
        Http::assertSentCount(2);
        Http::assertSent(fn (Request $r) => str_ends_with($r->url(), '/class/getlist')
            && $r['PickupLocationId'] === 666 && $r['DropoffLocationId'] === 666
            && $r['RentalId'] === 120 && $r['ShowPrices'] === true && $r['SkipDateCheck'] === false
            && $r['DateFrom'] === now()->addDays(10)->format('Y-m-d').' 10:00');
        $this->getJson('/api/cars/'.$cars[0]->id.'/caren-price?'.http_build_query($trip))
            ->assertOk()->assertJsonPath('pricing.rental_subtotal', '123456.78');
        Http::assertSentCount(2);
        $this->assertDatabaseCount('daily_fares', 0);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_missing_dates_never_fetch_or_display_a_zero_quote(): void
    {
        [$cars] = $this->fleet();
        Http::fake();
        $this->getJson('/api/cars/'.$cars[0]->id.'/caren-price')->assertOk()
            ->assertJsonPath('status', 'needs_dates')->assertJsonPath('pricing', null);
        Http::assertNothingSent();
    }

    public function test_zero_price_is_not_a_free_vehicle(): void
    {
        [$cars, $trip] = $this->fleet();
        $this->fakePrices(['TotalPrice' => 0]);
        $this->getJson('/api/cars/'.$cars[0]->id.'/caren-price?'.http_build_query($trip))->assertOk()
            ->assertJsonPath('status', 'unavailable')->assertJsonPath('pricing', null);
    }

    public function test_unavailable_vehicle_has_no_quote_even_with_a_price(): void
    {
        [$cars, $trip] = $this->fleet();
        $this->fakePrices(['Available' => false]);
        $this->getJson('/api/cars/'.$cars[0]->id.'/caren-price?'.http_build_query($trip))->assertOk()
            ->assertJsonPath('status', 'unavailable')->assertJsonPath('pricing', null);
    }

    public function test_provider_failure_is_safe_and_only_requested_once_for_the_fleet(): void
    {
        [$cars, $trip] = $this->fleet();
        $this->fakePrices(fail: true);
        $response = $this->getJson('/api/cars?'.http_build_query($trip))->assertOk();
        foreach ($response->json('data') as $row) {
            $this->assertSame('error', $row['pricing_status']);
            $this->assertNull($row['search_pricing']);
        }
        Http::assertSentCount(2);
    }

    public function test_wrong_host_location_and_invalid_dates_do_not_call_provider(): void
    {
        [$cars, $trip] = $this->fleet();
        Http::fake();
        $trip['pickup_location_id'] = 999999;
        $this->getJson('/api/cars/'.$cars[0]->id.'/caren-price?'.http_build_query($trip))->assertOk()
            ->assertJsonPath('status', 'unavailable');
        $trip['dropoff_at'] = 'invalid';
        $this->getJson('/api/cars/'.$cars[0]->id.'/caren-price?'.http_build_query($trip))->assertUnprocessable();
        $cars[0]->update(['is_active' => false]);
        $this->getJson('/api/cars/'.$cars[0]->id.'/caren-price')->assertNotFound();
        Http::assertNothingSent();
    }
}
