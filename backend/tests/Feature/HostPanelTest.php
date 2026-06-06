<?php

namespace Tests\Feature;

use App\Enums\GuestHouseStatus;
use App\Enums\GuestHouseType;
use App\Enums\ListingApprovalStatus;
use App\Enums\UserRole;
use App\Models\Car;
use App\Models\Category;
use App\Models\GuestHouse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class HostPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_host_creates_host_user(): void
    {
        $response = $this->postJson('/api/auth/register-host', [
            'name' => 'Host User',
            'email' => 'host@example.test',
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
        $category = Category::query()->create(['name' => 'Camper', 'is_active' => true]);
        $host = User::factory()->host()->create();

        $car = Car::query()->create([
            'user_id' => $host->id,
            'category_id' => $category->id,
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

    public function test_customer_cannot_access_host_dashboard(): void
    {
        Sanctum::actingAs(User::factory()->customer()->create());

        $this->getJson('/api/host/dashboard')->assertForbidden();
    }
}
