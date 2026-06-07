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
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            'city' => 'Reykjavík',
            'max_guests' => 2,
            'bedrooms' => 1,
            'bathrooms' => 1,
            'beds' => 1,
            'min_nights' => 1,
            'base_price_per_night' => 10000,
        ]);

        Sanctum::actingAs($host);

        $this->postJson("/api/host/guest-houses/{$house->id}/submit")
            ->assertOk()
            ->assertJsonPath('data.status', GuestHouseStatus::PendingReview->value);

        $this->assertDatabaseHas('guest_houses', [
            'id' => $house->id,
            'status' => GuestHouseStatus::PendingReview->value,
        ]);
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

    public function test_host_car_persists_seo_and_pickup_dropoff_locations(): void
    {
        $main = MainCategory::query()->firstOrCreate(['slug' => 'car'], ['name' => 'Car', 'is_active' => true]);
        $category = SubCategory::query()->create(['main_category_id' => $main->id, 'name' => 'Camper', 'is_active' => true, 'is_search_filter' => true]);
        $pickup = Location::query()->create(['name' => 'Airport', 'slug' => 'airport', 'is_active' => true]);
        $dropoff = Location::query()->create(['name' => 'Downtown', 'slug' => 'downtown', 'is_active' => true]);
        $host = User::factory()->host()->create();

        Sanctum::actingAs($host);

        $created = $this->postJson('/api/host/cars', [
            'name' => 'SEO Van',
            'sub_category_id' => $category->id,
            'meta_title' => 'Best Van',
            'meta_description' => 'A great van to rent.',
        ])->assertCreated()->json('data');

        $carId = $created['id'];

        $this->patchJson("/api/host/cars/{$carId}/relations", [
            'pickup_location_ids' => [$pickup->id],
            'dropoff_location_ids' => [$dropoff->id],
        ])->assertOk();

        $response = $this->getJson("/api/host/cars/{$carId}")->assertOk();
        $response->assertJsonPath('data.meta_title', 'Best Van');
        $response->assertJsonPath('data.meta_description', 'A great van to rent.');
        $response->assertJsonPath('data.pickup_location_ids', [$pickup->id]);
        $response->assertJsonPath('data.dropoff_location_ids', [$dropoff->id]);
    }

    public function test_host_guesthouse_persists_seo_and_seasonal_prices(): void
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
            'meta_title' => 'Cozy Stay',
            'meta_description' => 'Cozy place near the centre.',
            'seasonal_prices' => [
                ['name' => 'Summer', 'date_from' => '2026-06-01', 'date_to' => '2026-08-31', 'price_per_night_euros' => 200, 'minimum_nights' => 3],
            ],
        ])->assertOk();

        $response = $this->getJson("/api/host/guest-houses/{$house->id}")->assertOk();
        $response->assertJsonPath('data.meta_title', 'Cozy Stay');
        $response->assertJsonPath('data.meta_description', 'Cozy place near the centre.');
        $response->assertJsonPath('data.seasonal_prices.0.name', 'Summer');
        $response->assertJsonPath('data.seasonal_prices.0.price_per_night', 20000);
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
