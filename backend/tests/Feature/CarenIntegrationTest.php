<?php

namespace Tests\Feature;

use App\Enums\ListingApprovalStatus;
use App\Filament\Resources\RegisteredHosts\Pages\EditRegisteredHost;
use App\Models\Car;
use App\Models\User;
use App\Services\Partners\CarenPartnerClient;
use App\Services\Partners\CarenVehicleSyncService;
use App\Services\RentalQuoteService;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Livewire\Livewire;
use RuntimeException;
use Tests\TestCase;

class CarenIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private function host(): User
    {
        return User::factory()->host()->create([
            'partner_provider' => 'caren', 'partner_api_enabled' => true,
            'partner_api_base_url' => CarenPartnerClient::BASE_URL,
            'partner_api_key' => 'test-api-key', 'partner_api_username' => 'api@example.test',
            'partner_api_password' => 'test-api-password', 'partner_rental_id' => 120,
            'country_code' => 'IS',
        ]);
    }

    private function fakeCatalogue(?array $classes = null, bool $failClasses = false): void
    {
        Http::preventStrayRequests();
        Http::fake([
            '*/user/login' => Http::response(['Success' => true, 'Session' => 'test-session']),
            '*/user/logout' => Http::response(['Success' => true]),
            '*/rental/list' => Http::response(['Rentals' => [['Id' => 120, 'Name' => 'Konvin']]]),
            '*/class/getlist' => $failClasses ? Http::response(['Success' => false, 'Error' => 'secret-provider-detail'], 401) : Http::response(['Classes' => $classes ?? [
                ['Id' => 2181, 'Name' => 'Jeep Rooftop', 'Model' => '2024', 'Seats' => 5,
                    'TransmissionName' => 'Automatic', 'FuelName' => 'Gasoline / Electric Hybrid',
                    'DriveName' => '4WD', 'Image' => '/images/120/jeep.png',
                    'Images' => [['Url' => '/images/120/jeep.png'], ['Url' => '/images/120/side.png']]],
                ['Id' => 2727, 'Name' => 'Test'],
            ]]),
            '*/location/getpickuplist' => Http::response(['Locations' => [['Id' => 10, 'Name' => 'Airport']]]),
            '*/location/getdropofflist' => Http::response(['Locations' => [['Id' => 11, 'Name' => 'Hotel']]]),
        ]);
    }

    public function test_credentials_are_encrypted_at_rest_and_excluded_from_serialization(): void
    {
        $host = $this->host();
        $raw = DB::table('users')->find($host->id);
        foreach (['partner_api_key', 'partner_api_username', 'partner_api_password'] as $field) {
            $this->assertNotSame($host->$field, $raw->$field);
            $this->assertArrayNotHasKey($field, $host->toArray());
        }
        $this->assertSame('test-api-password', $host->fresh()->partner_api_password);
    }

    public function test_edit_page_saves_credentials_and_imports_without_rental_discovery(): void
    {
        $this->fakeCatalogue();
        $this->actingAs(User::factory()->admin()->create());
        $host = $this->host();
        $host->update(['partner_provider' => null, 'partner_api_enabled' => false]);
        $page = Livewire::test(EditRegisteredHost::class, ['record' => $host->id])
            ->fillForm([
                'partner_provider' => 'caren', 'partner_api_enabled' => true,
                'partner_rental_id' => 120, 'partner_api_password' => 'updated-password',
            ])
            ->callAction(TestAction::make('saveAndImportCarenVehicles')->schemaComponent('form-actions', 'content'))
            ->assertHasNoFormErrors()
            ->assertNotified('Caren fleet imported');
        $this->assertSame('updated-password', $host->fresh()->partner_api_password);
        $this->assertSame('caren', $host->fresh()->partner_provider);
        $this->assertSame(1, $host->cars()->count());
        Http::assertNotSent(fn (Request $request) => str_ends_with($request->url(), '/rental/list'));
        $page->callAction('syncCarenVehicles')->assertNotified('Caren fleet imported');
        $this->assertSame(1, $host->cars()->count());
    }

    public function test_edit_page_keeps_saved_credentials_when_api_import_fails(): void
    {
        $this->fakeCatalogue(failClasses: true);
        $this->actingAs(User::factory()->admin()->create());
        $host = $this->host();
        Livewire::test(EditRegisteredHost::class, ['record' => $host->id])
            ->fillForm(['partner_api_password' => 'updated-password'])
            ->callAction('syncCarenVehicles')
            ->assertNotified('Host saved, but Caren import failed');
        $this->assertSame('updated-password', $host->fresh()->partner_api_password);
        $this->assertSame(0, $host->cars()->count());
    }

    public function test_edit_page_validates_before_saving_or_calling_caren(): void
    {
        Http::fake();
        $this->actingAs(User::factory()->admin()->create());
        $host = $this->host();
        Livewire::test(EditRegisteredHost::class, ['record' => $host->id])
            ->fillForm(['name' => ''])
            ->callAction('syncCarenVehicles')->assertHasFormErrors(['name' => 'required'], 'form');
        Http::assertNothingSent();
    }

    public function test_connect_command_fetches_catalogue_only_once(): void
    {
        $this->fakeCatalogue();
        $this->artisan('caren:connect', ['email' => 'konvin@example.test', '--rental' => 120])
            ->expectsQuestion('Caren API username', 'api@example.test')
            ->expectsQuestion('Caren Vehicle API key', 'test-api-key')
            ->expectsQuestion('Caren API password', 'test-api-password')
            ->assertSuccessful();
        $this->assertSame(1, User::where('email', 'konvin@example.test')->sole()->cars()->count());
        $this->assertSame('Caren rental 120', User::where('email', 'konvin@example.test')->sole()->name);
        Http::assertSentCount(4);
    }

    public function test_rental_discovery_still_works_when_no_rental_id_is_saved(): void
    {
        $this->fakeCatalogue();
        $host = $this->host();
        $host->update(['partner_rental_id' => null]);
        app(CarenVehicleSyncService::class)->sync($host);
        $this->assertSame(120, $host->fresh()->partner_rental_id);
        Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '/rental/list'));
    }

    public function test_import_is_idempotent_scoped_and_automatically_publishes(): void
    {
        $this->fakeCatalogue();
        $host = $this->host();
        $otherHost = $this->host();
        $otherCar = Car::factory()->create(['user_id' => $otherHost->id, 'external_provider' => 'caren', 'external_vehicle_id' => '120:2181']);
        $result = app(CarenVehicleSyncService::class)->sync($host);
        $this->assertSame(['vehicles' => 1, 'locations' => 2, 'skipped' => 1, 'deactivated' => 0], $result);
        $car = $host->cars()->sole();
        $this->assertTrue($car->is_active);
        $this->assertSame(ListingApprovalStatus::Approved, $car->listing_status);
        $this->assertTrue(Car::publiclyVisible()->whereKey($car->id)->exists());
        $this->assertSame('hybrid', $car->fuel_type);
        $this->assertSame('automatic', $car->transmission);
        $this->assertSame('campervan', $car->subCategory->mainCategory->slug);
        $this->assertSame('https://booking.caren.is/images/120/jeep.png', $car->main_image_path);
        $this->assertCount(2, $car->details_image_paths);
        $this->assertSame(0, $car->units_available);
        $this->assertSame(0, $car->dailyFares()->count());
        $pickup = $car->locations()->where('external_id', '120:10')->sole();
        $this->assertTrue((bool) $pickup->pivot->allows_pickup);
        $this->assertFalse((bool) $pickup->pivot->allows_dropoff);
        $car->update(['is_active' => false, 'listing_status' => ListingApprovalStatus::Draft]);
        app(CarenVehicleSyncService::class)->sync($host);
        $this->assertSame(1, $host->cars()->count());
        $this->assertSame(ListingApprovalStatus::Approved, $car->fresh()->listing_status);
        $this->assertTrue($car->fresh()->is_active);
        $this->assertTrue(Car::publiclyVisible()->whereKey($car->id)->exists());
        $this->assertSame($otherHost->id, $otherCar->fresh()->user_id);
        Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '/user/login') && $request['ApiKey'] === 'test-api-key');
        Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '/class/getlist') && $request['Session'] === 'test-session' && $request['SkipDateCheck'] === true);
        Http::assertNotSent(fn (Request $request) => str_ends_with($request->url(), '/user/logout'));
    }

    public function test_empty_catalogue_does_not_deactivate_existing_cars(): void
    {
        $this->fakeCatalogue([]);
        $host = $this->host();
        $car = Car::factory()->create(['user_id' => $host->id, 'external_provider' => 'caren', 'external_vehicle_id' => '120:1']);
        app(CarenVehicleSyncService::class)->sync($host);
        $this->assertTrue($car->fresh()->is_active);
    }

    public function test_failed_catalogue_preserves_session_without_changing_listings(): void
    {
        $this->fakeCatalogue(failClasses: true);
        $host = $this->host();
        try {
            app(CarenVehicleSyncService::class)->sync($host);
            $this->fail('Expected a failed API request.');
        } catch (RuntimeException $exception) {
            $this->assertStringNotContainsString('secret-provider-detail', $exception->getMessage());
        }
        $this->assertSame(0, $host->cars()->count());
        Http::assertNotSent(fn (Request $request) => str_ends_with($request->url(), '/user/logout'));
    }

    public function test_repeated_catalogues_work_when_login_reuses_a_session_invalidated_by_logout(): void
    {
        Http::preventStrayRequests();
        $loggedOut = false;
        Http::fake(function (Request $request) use (&$loggedOut) {
            $path = parse_url($request->url(), PHP_URL_PATH);
            if (str_ends_with($path, '/user/login')) {
                return Http::response(['Success' => true, 'Session' => 'reused-session']);
            }
            if (str_ends_with($path, '/user/logout')) {
                $loggedOut = true;
                return Http::response(['Success' => true]);
            }
            if ($loggedOut) {
                return Http::response(['Success' => false, 'Code' => 10000]);
            }
            $this->assertSame('reused-session', $request['Session']);

            return Http::response(str_ends_with($path, '/class/getlist')
                ? ['Classes' => [['Id' => 2181, 'Name' => 'Jeep']]]
                : ['Locations' => [['Id' => 10, 'Name' => 'Airport']]]);
        });

        $host = $this->host();
        for ($attempt = 0; $attempt < 2; $attempt++) {
            $catalogue = app(CarenPartnerClient::class)->catalogue($host);
            $this->assertCount(1, $catalogue['vehicles']);
            $this->assertCount(1, $catalogue['pickups']);
            $this->assertCount(1, $catalogue['dropoffs']);
        }
        $this->assertFalse($loggedOut);
        Http::assertSentCount(8);
    }

    public function test_unapproved_api_origin_cannot_receive_credentials(): void
    {
        Http::fake();
        $host = $this->host();
        $host->partner_api_base_url = 'https://other.example';
        try {
            app(CarenPartnerClient::class)->catalogue($host);
            $this->fail('Expected invalid origin to be rejected.');
        } catch (RuntimeException) {
            Http::assertNothingSent();
        }
    }

    public function test_imported_caren_cars_cannot_use_local_booking_prices(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not available for online booking yet');
        app(RentalQuoteService::class)->quote(new Car(['external_provider' => 'caren']), 1, now(), now()->addDays(3), 1, 1, [], null);
    }
}
