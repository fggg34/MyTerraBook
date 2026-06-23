<?php

namespace Tests\Feature;

use App\Enums\GuestHouseStatus;
use App\Enums\GuestHouseType;
use App\Enums\ListingApprovalStatus;
use App\Enums\UserRole;
use App\Models\Car;
use App\Models\MainCategory;
use App\Models\SubCategory;
use App\Models\GuestHouse;
use App\Models\GuestHouseAmenity;
use App\Models\GuestHouseImage;
use App\Models\Location;
use App\Models\LocationFee;
use App\Models\OutOfHoursFee;
use App\Models\PriceType;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class HostPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_host_requires_phone(): void
    {
        $this->postJson('/api/auth/register-host', [
            'name' => 'Host User',
            'email' => 'host@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_register_host_creates_host_user(): void
    {
        $response = $this->postJson('/api/auth/register-host', [
            'name' => 'Host User',
            'email' => 'host@example.test',
            'phone' => '+354 555 1234',
            'host_account_type' => 'individual',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJsonPath('user.role', UserRole::Host->value);

        $this->assertDatabaseHas('users', [
            'email' => 'host@example.test',
            'role' => UserRole::Host->value,
        ]);
    }

    public function test_host_cannot_view_another_hosts_guesthouse(): void
    {
        $hostA = User::factory()->host()->create();
        $hostB = User::factory()->host()->create();

        $house = GuestHouse::query()->create([
            'user_id' => $hostA->id,
            'name' => 'House A',
            'slug' => 'house-a',
            'type' => GuestHouseType::Apartment,
            'status' => GuestHouseStatus::Draft,
            'city' => 'Reykjavík',
            'max_guests' => 2,
            'bedrooms' => 1,
            'bathrooms' => 1,
            'beds' => 1,
            'min_nights' => 1,
            'base_price_per_night' => 10000,
        ]);

        Sanctum::actingAs($hostB);

        $this->getJson("/api/host/guest-houses/{$house->id}")
            ->assertForbidden();
    }

    public function test_submit_guesthouse_moves_to_pending_review(): void
    {
        $host = User::factory()->host()->create();

        $house = GuestHouse::query()->create([
            'user_id' => $host->id,
            'name' => 'Pending House',
            'slug' => 'pending-house',
            'type' => GuestHouseType::Apartment,
            'status' => GuestHouseStatus::Draft,
            'address' => 'Laugavegur 12',
            'city' => 'Reykjavík',
            'country' => 'Iceland',
            'max_guests' => 2,
            'bedrooms' => 1,
            'bathrooms' => 1,
            'beds' => 1,
            'min_nights' => 1,
            'base_price_per_night' => 10000,
            'thumbnail' => 'guesthouses/test-cover.jpg',
        ]);

        $amenity = GuestHouseAmenity::query()->create([
            'name' => 'Wi-Fi',
            'icon' => 'wifi',
            'group' => 'Essentials',
        ]);
        $house->amenities()->attach($amenity->id);

        foreach (range(1, 5) as $index) {
            GuestHouseImage::query()->create([
                'guest_house_id' => $house->id,
                'path' => "guesthouses/gallery/test-{$index}.jpg",
                'sort_order' => $index - 1,
            ]);
        }

        Sanctum::actingAs($host);

        $this->postJson("/api/host/guest-houses/{$house->id}/submit")
            ->assertOk()
            ->assertJsonPath('data.status', GuestHouseStatus::PendingReview->value);

        $this->assertDatabaseHas('guest_houses', [
            'id' => $house->id,
            'status' => GuestHouseStatus::PendingReview->value,
        ]);
    }

    public function test_submit_guesthouse_requires_complete_address(): void
    {
        $host = User::factory()->host()->create();

        $house = GuestHouse::query()->create([
            'user_id' => $host->id,
            'name' => 'Incomplete House',
            'slug' => 'incomplete-house',
            'type' => GuestHouseType::Apartment,
            'status' => GuestHouseStatus::Draft,
            'city' => 'Reykjavík',
            'country' => 'Iceland',
            'max_guests' => 2,
            'bedrooms' => 1,
            'bathrooms' => 1,
            'beds' => 1,
            'min_nights' => 1,
            'base_price_per_night' => 10000,
        ]);

        Sanctum::actingAs($host);

        $this->postJson("/api/host/guest-houses/{$house->id}/submit")
            ->assertUnprocessable()
            ->assertJsonPath('message', 'A complete street address is required before submitting for review.');
    }

    public function test_public_config_exposes_maps_api_key(): void
    {
        Setting::putValue('system.google_maps_api_key', ['key' => 'test-maps-key']);

        $this->getJson('/api/public-config')
            ->assertOk()
            ->assertJsonPath('maps_api_key', 'test-maps-key');
    }

    public function test_pending_guesthouse_not_in_public_list(): void
    {
        GuestHouse::query()->create([
            'user_id' => User::factory()->host()->create()->id,
            'name' => 'Hidden House',
            'slug' => 'hidden-house',
            'type' => GuestHouseType::Apartment,
            'status' => GuestHouseStatus::PendingReview,
            'city' => 'Reykjavík',
            'max_guests' => 2,
            'bedrooms' => 1,
            'bathrooms' => 1,
            'beds' => 1,
            'min_nights' => 1,
            'base_price_per_night' => 10000,
        ]);

        $response = $this->getJson('/api/guest-houses');

        $response->assertOk();
        $this->assertEmpty($response->json('data'));
    }

    public function test_host_car_not_public_until_approved(): void
    {
        $main = MainCategory::query()->firstOrCreate(['slug' => 'car'], ['name' => 'Car', 'is_active' => true]);
        $category = SubCategory::query()->create(['main_category_id' => $main->id, 'name' => 'Camper', 'is_active' => true, 'is_search_filter' => true]);
        $host = User::factory()->host()->create();

        $car = Car::query()->create([
            'user_id' => $host->id,
            'sub_category_id' => $category->id,
            'name' => 'Host Van',
            'slug' => 'host-van',
            'is_active' => false,
            'listing_status' => ListingApprovalStatus::PendingReview,
            'units_available' => 1,
        ]);

        $this->getJson("/api/cars/{$car->id}")->assertNotFound();

        $car->update([
            'listing_status' => ListingApprovalStatus::Approved,
            'is_active' => true,
        ]);

        $this->getJson("/api/cars/{$car->id}")->assertOk();
    }

    public function test_host_car_auto_generates_seo_and_pickup_dropoff_locations(): void
    {
        $main = MainCategory::query()->firstOrCreate(['slug' => 'car'], ['name' => 'Car', 'is_active' => true]);
        $category = SubCategory::query()->create(['main_category_id' => $main->id, 'name' => 'Camper', 'is_active' => true, 'is_search_filter' => true]);
        $pickup = Location::query()->create(['name' => 'Airport', 'slug' => 'airport', 'is_active' => true]);
        $dropoff = Location::query()->create(['name' => 'Downtown', 'slug' => 'downtown', 'is_active' => true]);
        $host = User::factory()->host()->create();

        Sanctum::actingAs($host);

        $created = $this->postJson('/api/host/cars', [
            'name' => 'Highland Explorer',
            'sub_category_id' => $category->id,
            'description' => '<p>Perfect for two travellers exploring Iceland.</p>',
        ])->assertCreated()->json('data');

        $carId = $created['id'];

        $this->patchJson("/api/host/cars/{$carId}/relations", [
            'pickup_location_ids' => [$pickup->id],
            'dropoff_location_ids' => [$dropoff->id],
        ])->assertOk();

        $response = $this->getJson("/api/host/cars/{$carId}")->assertOk();
        $response->assertJsonPath('data.meta_title', 'Highland Explorer, Car & 4×4 in Iceland');
        $response->assertJsonPath('data.meta_description', 'Perfect for two travellers exploring Iceland.');
        $response->assertJsonPath('data.pickup_location_ids', [$pickup->id]);
        $response->assertJsonPath('data.dropoff_location_ids', [$dropoff->id]);
    }

    public function test_host_guesthouse_auto_generates_seo_and_seasonal_prices(): void
    {
        $host = User::factory()->host()->create();

        $house = GuestHouse::query()->create([
            'user_id' => $host->id,
            'name' => 'Seasonal House',
            'slug' => 'seasonal-house',
            'type' => GuestHouseType::Apartment,
            'status' => GuestHouseStatus::Draft,
            'city' => 'Reykjavík',
            'max_guests' => 2,
            'bedrooms' => 1,
            'bathrooms' => 1,
            'beds' => 1,
            'min_nights' => 1,
            'base_price_per_night' => 10000,
        ]);

        Sanctum::actingAs($host);

        $this->patchJson("/api/host/guest-houses/{$house->id}", [
            'short_description' => 'Cozy place near the centre.',
            'seasonal_prices' => [
                ['name' => 'Summer', 'date_from' => '2026-06-01', 'date_to' => '2026-08-31', 'price_per_night_euros' => 200, 'minimum_nights' => 3],
            ],
        ])->assertOk();

        $response = $this->getJson("/api/host/guest-houses/{$house->id}")->assertOk();
        $response->assertJsonPath('data.meta_title', 'Seasonal House, Guesthouse in Iceland');
        $response->assertJsonPath('data.meta_description', 'Cozy place near the centre.');
        $response->assertJsonPath('data.seasonal_prices.0.name', 'Summer');
        $response->assertJsonPath('data.seasonal_prices.0.price_per_night', 20000);
    }

    public function test_host_catalog_tax_rates_and_characteristics_load(): void
    {
        $taxRate = \App\Models\TaxRate::query()->create(['name' => 'Standard VAT (24%)', 'basis_points' => 2400]);
        \App\Models\Characteristic::query()->create([
            'name' => 'Air Conditioning',
            'group' => 'Comfort & Convenience',
            'sort_order' => 10,
            'is_search_filter' => true,
        ]);

        Sanctum::actingAs(User::factory()->host()->create());

        $this->getJson('/api/host/catalog/tax-rates')
            ->assertOk()
            ->assertJsonPath('data.0.id', $taxRate->id)
            ->assertJsonPath('data.0.basis_points', 2400);

        $this->getJson('/api/host/catalog/characteristics')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Air Conditioning')
            ->assertJsonPath('data.0.group', 'Comfort & Convenience');
    }

    public function test_host_guesthouse_create_persists_seasonal_prices(): void
    {
        Sanctum::actingAs(User::factory()->host()->create());

        $created = $this->postJson('/api/host/guest-houses', [
            'name' => 'New Seasonal House',
            'seasonal_prices' => [
                ['name' => 'Winter', 'date_from' => '2026-12-01', 'date_to' => '2027-02-28', 'price_per_night_euros' => 150, 'minimum_nights' => 2],
            ],
        ])->assertCreated()->json('data');

        $this->assertSame('Winter', $created['seasonal_prices'][0]['name']);
        $this->assertSame(15000, $created['seasonal_prices'][0]['price_per_night']);

        $this->assertDatabaseHas('guest_house_seasonal_prices', [
            'guest_house_id' => $created['id'],
            'name' => 'Winter',
            'price_per_night' => 15000,
        ]);
    }

    public function test_host_can_reload_guesthouse_after_create(): void
    {
        Sanctum::actingAs(User::factory()->host()->create());

        $created = $this->postJson('/api/host/guest-houses', [
            'name' => 'Reload Test House',
        ])->assertCreated()->json('data');

        $this->getJson("/api/host/guest-houses/{$created['id']}")
            ->assertOk()
            ->assertJsonPath('data.name', 'Reload Test House')
            ->assertJsonPath('data.check_in_time', '15:00');
    }

    public function test_host_can_show_guesthouse_when_room_details_table_missing(): void
    {
        Schema::dropIfExists('guest_house_room_details');

        $host = User::factory()->host()->create();
        $house = GuestHouse::query()->create([
            'user_id' => $host->id,
            'name' => 'Legacy House',
            'slug' => 'legacy-house',
            'type' => GuestHouseType::Apartment,
            'status' => GuestHouseStatus::Draft,
            'city' => 'Reykjavík',
            'max_guests' => 2,
            'bedrooms' => 1,
            'bathrooms' => 1,
            'beds' => 1,
            'min_nights' => 1,
            'base_price_per_night' => 10000,
        ]);

        Sanctum::actingAs($host);

        $this->getJson("/api/host/guest-houses/{$house->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $house->id)
            ->assertJsonMissingPath('data.room_details');
    }

    public function test_host_guesthouse_room_details_sync_and_image_upload(): void
    {
        $host = User::factory()->host()->create();

        $house = GuestHouse::query()->create([
            'user_id' => $host->id,
            'name' => 'Room Detail House',
            'slug' => 'room-detail-house',
            'type' => GuestHouseType::Apartment,
            'status' => GuestHouseStatus::Draft,
            'city' => 'Reykjavík',
            'max_guests' => 2,
            'bedrooms' => 1,
            'bathrooms' => 1,
            'beds' => 1,
            'min_nights' => 1,
            'base_price_per_night' => 10000,
        ]);

        Sanctum::actingAs($host);

        $this->patchJson("/api/host/guest-houses/{$house->id}", [
            'room_details' => [
                [
                    'title' => 'Master bedroom',
                    'text' => 'Queen bed with mountain view.',
                    'dim' => 'Queen bed · Sleeps 2',
                ],
                [
                    'title' => 'Bathroom',
                    'text' => 'En-suite shower room.',
                    'dim' => 'Fresh linen included',
                ],
            ],
        ])->assertOk()
            ->assertJsonPath('data.room_details.0.title', 'Master bedroom')
            ->assertJsonPath('data.room_details.1.title', 'Bathroom');

        $detailId = $house->fresh()->roomDetails()->first()->id;

        $this->postJson("/api/host/guest-houses/{$house->id}/images", [
            'room_detail_id' => $detailId,
            'room_detail_image' => \Illuminate\Http\UploadedFile::fake()->image('bedroom.jpg'),
        ])->assertOk()
            ->assertJsonPath('data.room_details.0.image_path', fn ($path) => is_string($path) && str_contains($path, 'guesthouses/room-details'));

        $this->patchJson("/api/host/guest-houses/{$house->id}", [
            'room_details' => [
                [
                    'id' => $detailId,
                    'title' => 'Updated bedroom',
                    'text' => 'Updated copy.',
                    'dim' => 'King bed',
                ],
            ],
        ])->assertOk()
            ->assertJsonCount(1, 'data.room_details')
            ->assertJsonPath('data.room_details.0.title', 'Updated bedroom');
    }

    public function test_host_car_special_price_persists_and_lists(): void
    {
        $main = MainCategory::query()->firstOrCreate(['slug' => 'car'], ['name' => 'Car', 'is_active' => true]);
        $category = SubCategory::query()->create(['main_category_id' => $main->id, 'name' => 'SUV', 'is_active' => true, 'is_search_filter' => true]);
        $host = User::factory()->host()->create();

        Sanctum::actingAs($host);

        $carId = Car::query()->create([
            'user_id' => $host->id,
            'name' => 'Seasonal SUV',
            'sub_category_id' => $category->id,
            'listing_status' => ListingApprovalStatus::Draft,
            'units_available' => 1,
            'seats' => 5,
            'bags' => 2,
            'transmission' => 'automatic',
            'fuel_type' => 'petrol',
            'drive_type' => 'fwd',
        ])->id;

        $this->postJson("/api/host/cars/{$carId}/special-prices", [
            'name' => 'Summer peak',
            'date_from' => '2026-06-01',
            'date_to' => '2026-08-31',
            'type' => 'charge',
            'value_mode' => 'percentage',
            'value_percent_bips' => 1000,
        ])->assertCreated()
            ->assertJsonPath('data.name', 'Summer peak')
            ->assertJsonPath('data.date_from', '2026-06-01');

        $this->getJson("/api/host/cars/{$carId}/special-prices")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Summer peak')
            ->assertJsonPath('data.0.date_from', '2026-06-01');
    }

    public function test_host_guesthouse_update_includes_pending_seasonal_draft_payload(): void
    {
        $host = User::factory()->host()->create();
        $house = GuestHouse::query()->create([
            'user_id' => $host->id,
            'name' => 'Draft House',
            'slug' => 'draft-house',
            'type' => GuestHouseType::Apartment,
            'status' => GuestHouseStatus::Draft,
            'city' => 'Reykjavík',
            'max_guests' => 2,
            'bedrooms' => 1,
            'bathrooms' => 1,
            'beds' => 1,
            'min_nights' => 1,
            'base_price_per_night' => 10000,
        ]);

        Sanctum::actingAs($host);

        $this->patchJson("/api/host/guest-houses/{$house->id}", [
            'seasonal_prices' => [
                [
                    'name' => 'Easter',
                    'date_from' => '2026-04-01',
                    'date_to' => '2026-04-10',
                    'price_per_night_euros' => 180,
                ],
            ],
        ])->assertOk()
            ->assertJsonPath('data.seasonal_prices.0.name', 'Easter');

        $this->assertDatabaseHas('guest_house_seasonal_prices', [
            'guest_house_id' => $house->id,
            'name' => 'Easter',
            'price_per_night' => 18000,
        ]);
    }

    public function test_host_car_persists_capacity_fields(): void
    {
        $main = MainCategory::query()->firstOrCreate(['slug' => 'car'], ['name' => 'Car', 'is_active' => true]);
        $category = SubCategory::query()->create(['main_category_id' => $main->id, 'name' => 'SUV', 'is_active' => true, 'is_search_filter' => true]);

        Sanctum::actingAs(User::factory()->host()->create());

        $carId = $this->postJson('/api/host/cars', [
            'name' => 'Family SUV',
            'sub_category_id' => $category->id,
            'seats' => 5,
            'sleeps' => 0,
            'bags' => 4,
        ])->assertCreated()->json('data.id');

        $this->assertDatabaseHas('cars', [
            'id' => $carId,
            'seats' => 5,
            'bags' => 4,
        ]);

        $this->getJson("/api/host/cars/{$carId}")
            ->assertOk()
            ->assertJsonPath('data.seats', 5)
            ->assertJsonPath('data.sleeps', 0)
            ->assertJsonPath('data.bags', 4);
    }

    public function test_submit_car_requires_locations_units_and_pricing(): void
    {
        $main = MainCategory::query()->firstOrCreate(['slug' => 'car'], ['name' => 'Car', 'is_active' => true]);
        $category = SubCategory::query()->create(['main_category_id' => $main->id, 'name' => 'SUV', 'is_active' => true, 'is_search_filter' => true]);
        $pickup = Location::query()->create([
            'name' => 'Airport',
            'slug' => 'airport-submit-test',
            'is_active' => true,
        ]);
        $dropoff = Location::query()->create([
            'name' => 'Downtown',
            'slug' => 'downtown-submit-test',
            'is_active' => true,
        ]);
        $priceType = PriceType::query()->create(['name' => 'Basic', 'is_active' => true]);
        $host = User::factory()->host()->create();

        Sanctum::actingAs($host);

        $car = Car::query()->create([
            'user_id' => $host->id,
            'name' => 'Bare SUV',
            'sub_category_id' => $category->id,
            'listing_status' => ListingApprovalStatus::Draft,
            'units_available' => 0,
            'seats' => 5,
            'bags' => 2,
            'transmission' => 'automatic',
            'fuel_type' => 'petrol',
            'drive_type' => 'fwd',
        ]);
        $carId = $car->id;

        $this->postJson("/api/host/cars/{$carId}/submit")
            ->assertUnprocessable()
            ->assertJsonPath('message', 'At least one pickup location is required before submitting for review.');

        $this->patchJson("/api/host/cars/{$carId}/relations", [
            'pickup_location_ids' => [$pickup->id],
            'dropoff_location_ids' => [$dropoff->id],
        ])->assertOk();

        $this->postJson("/api/host/cars/{$carId}/submit")
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                'A base daily rental rate (1–365 days) is required before submitting for review.',
            );

        $this->postJson("/api/host/cars/{$carId}/daily-fares", [
            'price_type_id' => $priceType->id,
            'from_days' => 1,
            'to_days' => 365,
            'price_per_day_euros' => 120,
        ])->assertCreated();

        $this->postJson("/api/host/cars/{$carId}/submit")
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Pickup and drop-off times are required before submitting for review.');

        $this->patchJson("/api/host/cars/{$carId}", [
            'pickup_time_from' => '09:00',
            'pickup_time_to' => '17:00',
            'dropoff_time_from' => '10:00',
            'dropoff_time_to' => '16:00',
        ])->assertOk();

        $this->postJson("/api/host/cars/{$carId}/submit")
            ->assertUnprocessable()
            ->assertJsonPath('message', 'A main image is required before submitting for review.');

        $car->update([
            'main_image_path' => 'cars/main-test.jpg',
            'details_image_paths' => array_map(
                fn (int $index) => "cars/details/detail-{$index}.jpg",
                range(1, 4),
            ),
        ]);

        $this->postJson("/api/host/cars/{$carId}/submit")
            ->assertUnprocessable()
            ->assertJsonPath('message', 'At least 5 detail photos are required before submitting for review.');

        $car->update([
            'details_image_paths' => array_map(
                fn (int $index) => "cars/details/detail-{$index}.jpg",
                range(1, 5),
            ),
        ]);

        $this->postJson("/api/host/cars/{$carId}/submit")
            ->assertOk()
            ->assertJsonPath('data.listing_status', ListingApprovalStatus::PendingReview->value);

        $this->assertDatabaseHas('cars', [
            'id' => $carId,
            'listing_status' => ListingApprovalStatus::PendingReview->value,
        ]);
    }

    public function test_host_catalog_locations_include_opening_hours(): void
    {
        Location::query()->create([
            'name' => 'Airport Kef',
            'slug' => 'airport-kef',
            'is_active' => true,
            'default_opening_time' => '08:00:00',
            'default_closing_time' => '20:00:00',
        ]);

        Sanctum::actingAs(User::factory()->host()->create());

        $this->getJson('/api/host/catalog/locations')
            ->assertOk()
            ->assertJsonPath('data.0.default_opening_time', '08:00')
            ->assertJsonPath('data.0.default_closing_time', '20:00');
    }

    public function test_host_car_persists_location_times_and_fees(): void
    {
        $main = MainCategory::query()->firstOrCreate(['slug' => 'car'], ['name' => 'Car', 'is_active' => true]);
        $category = SubCategory::query()->create(['main_category_id' => $main->id, 'name' => 'Camper', 'is_active' => true, 'is_search_filter' => true]);
        $pickup = Location::query()->create([
            'name' => 'Airport',
            'slug' => 'airport',
            'is_active' => true,
            'default_opening_time' => '08:00:00',
            'default_closing_time' => '20:00:00',
        ]);
        $dropoff = Location::query()->create([
            'name' => 'Downtown',
            'slug' => 'downtown',
            'is_active' => true,
            'default_opening_time' => '09:00:00',
            'default_closing_time' => '18:00:00',
        ]);
        $host = User::factory()->host()->create();

        Sanctum::actingAs($host);

        $carId = $this->postJson('/api/host/cars', [
            'name' => 'Timed Van',
            'sub_category_id' => $category->id,
        ])->assertCreated()->json('data.id');

        $this->patchJson("/api/host/cars/{$carId}/relations", [
            'pickup_location_ids' => [$pickup->id],
            'dropoff_location_ids' => [$dropoff->id],
        ])->assertOk();

        $this->patchJson("/api/host/cars/{$carId}", [
            'pickup_time_from' => '09:00',
            'pickup_time_to' => '17:00',
            'dropoff_time_from' => '10:00',
            'dropoff_time_to' => '16:00',
        ])->assertOk()
            ->assertJsonPath('data.pickup_time_from', '09:00')
            ->assertJsonPath('data.dropoff_time_to', '16:00');

        $this->patchJson("/api/host/cars/{$carId}", [
            'pickup_time_from' => '07:00',
            'pickup_time_to' => '17:00',
        ])->assertOk()
            ->assertJsonPath('data.pickup_time_from', '07:00');

        $this->patchJson("/api/host/cars/{$carId}", [
            'pickup_time_from' => '17:00',
            'pickup_time_to' => '09:00',
        ])->assertUnprocessable();

        $fee = $this->postJson("/api/host/cars/{$carId}/location-fees", [
            'pickup_location_id' => $pickup->id,
            'dropoff_location_id' => $dropoff->id,
            'cost_euros' => 49,
            'is_one_way_fee' => true,
        ])->assertCreated()->json('data');

        $this->assertSame($carId, $fee['car_id']);
        $this->assertSame(4900, $fee['cost_cents']);

        $ooh = $this->postJson("/api/host/cars/{$carId}/out-of-hours-fees", [
            'name' => 'Late pickup',
            'time_from' => '20:00',
            'time_to' => '08:00',
            'applies_to' => 'both',
            'pickup_cost_euros' => 35,
            'dropoff_cost_euros' => 35,
        ])->assertCreated()->json('data');

        $this->assertContains($carId, $ooh['vehicle_ids']);

        $this->getJson("/api/host/cars/{$carId}/location-fees")
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->getJson("/api/host/cars/{$carId}/out-of-hours-fees")
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->deleteJson("/api/host/cars/{$carId}/location-fees/{$fee['id']}")->assertOk();
        $this->deleteJson("/api/host/cars/{$carId}/out-of-hours-fees/{$ooh['id']}")->assertOk();

        $this->assertDatabaseMissing('location_fees', ['id' => $fee['id']]);
        $this->assertDatabaseMissing('out_of_hours_fees', ['id' => $ooh['id']]);
    }

    public function test_host_car_ignores_platform_location_fees_in_storage(): void
    {
        $main = MainCategory::query()->firstOrCreate(['slug' => 'car'], ['name' => 'Car', 'is_active' => true]);
        $category = SubCategory::query()->create(['main_category_id' => $main->id, 'name' => 'Van', 'is_active' => true, 'is_search_filter' => true]);
        $pickup = Location::query()->create(['name' => 'P', 'slug' => 'p', 'is_active' => true]);
        $dropoff = Location::query()->create(['name' => 'D', 'slug' => 'd', 'is_active' => true]);
        $host = User::factory()->host()->create();

        LocationFee::query()->create([
            'pickup_location_id' => $pickup->id,
            'dropoff_location_id' => $dropoff->id,
            'cost_cents' => 9999,
            'is_active' => true,
        ]);

        Sanctum::actingAs($host);

        $carId = Car::query()->create([
            'user_id' => $host->id,
            'sub_category_id' => $category->id,
            'name' => 'Host Van',
            'listing_status' => ListingApprovalStatus::Draft,
        ])->id;

        $car = Car::query()->findOrFail($carId);
        $car->locations()->attach([
            $pickup->id => ['allows_pickup' => true, 'allows_dropoff' => true],
            $dropoff->id => ['allows_pickup' => true, 'allows_dropoff' => true],
        ]);

        $this->getJson("/api/host/cars/{$carId}/location-fees")
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_customer_cannot_access_host_dashboard(): void
    {
        Sanctum::actingAs(User::factory()->customer()->create());

        $this->getJson('/api/host/dashboard')->assertForbidden();
    }

    public function test_guest_cannot_access_host_dashboard(): void
    {
        $this->getJson('/api/host/dashboard')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_new_host_can_load_dashboard(): void
    {
        $host = User::factory()->host()->create();

        Sanctum::actingAs($host);

        $this->getJson('/api/host/dashboard')
            ->assertOk()
            ->assertJsonPath('data.guest_houses.live', 0)
            ->assertJsonPath('data.cars.live', 0)
            ->assertJsonPath('data.bookings.pending_car_orders', 0)
            ->assertJsonPath('data.revenue_cents.car_orders', 0);
    }

    public function test_newly_registered_host_can_load_dashboard(): void
    {
        $response = $this->postJson('/api/auth/register-host', [
            'name' => 'Fresh Host',
            'email' => 'fresh-host@example.test',
            'phone' => '+354 555 9999',
            'host_account_type' => 'business',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertCreated();

        $token = $response->json('token');

        $this->withToken($token)
            ->getJson('/api/host/dashboard')
            ->assertOk()
            ->assertJsonPath('data.guest_houses.live', 0);
    }
}
