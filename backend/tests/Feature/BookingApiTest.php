<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Car;
use App\Models\CarUnavailability;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_quote_endpoint_returns_price_breakdown(): void
    {
        [$car, $pickup, $dropoff] = $this->seedCatalog();

        $response = $this->postJson('/api/bookings/quote', [
            'car_id' => $car->id,
            'pickup_location_id' => $pickup->id,
            'dropoff_location_id' => $dropoff->id,
            'pickup_at' => now()->addDay()->toDateTimeString(),
            'dropoff_at' => now()->addDays(3)->toDateTimeString(),
        ]);

        $response->assertOk()->assertJsonStructure([
            'duration',
            'pricing_mode',
            'rental_subtotal',
            'extras_subtotal',
            'discount_amount',
            'tax_amount',
            'total',
        ]);
    }

    public function test_booking_creation_blocks_unavailable_slots(): void
    {
        [$car, $pickup, $dropoff] = $this->seedCatalog();

        Booking::query()->create([
            'reference' => 'TBK-EXISTS1',
            'car_id' => $car->id,
            'pickup_location_id' => $pickup->id,
            'dropoff_location_id' => $dropoff->id,
            'pickup_at' => now()->addDay(),
            'dropoff_at' => now()->addDays(3),
            'status' => 'confirmed',
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'rental_subtotal' => 100,
            'extras_subtotal' => 0,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total' => 100,
            'currency' => 'USD',
        ]);

        $response = $this->postJson('/api/bookings', [
            'car_id' => $car->id,
            'pickup_location_id' => $pickup->id,
            'dropoff_location_id' => $dropoff->id,
            'pickup_at' => now()->addDay()->toDateTimeString(),
            'dropoff_at' => now()->addDays(2)->toDateTimeString(),
            'customer_name' => 'Conflict User',
            'customer_email' => 'conflict@example.com',
        ]);

        $response->assertStatus(422);
    }

    public function test_coupon_can_apply_discount(): void
    {
        [$car, $pickup, $dropoff] = $this->seedCatalog();

        Coupon::query()->create([
            'code' => 'SAVE20',
            'discount_type' => 'fixed',
            'discount_value' => 20,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/bookings/quote', [
            'car_id' => $car->id,
            'pickup_location_id' => $pickup->id,
            'dropoff_location_id' => $dropoff->id,
            'pickup_at' => now()->addDay()->toDateTimeString(),
            'dropoff_at' => now()->addDays(2)->toDateTimeString(),
            'coupon_code' => 'SAVE20',
        ]);

        $response->assertOk();
        $this->assertGreaterThan(0, $response->json('discount_amount'));
    }

    public function test_admin_routes_are_protected(): void
    {
        $user = User::factory()->create(['role' => UserRole::Customer]);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/admin/stats');
        $response->assertForbidden();
    }

    public function test_availability_calendar_returns_booked_and_blocked_ranges(): void
    {
        [$car, $pickup, $dropoff] = $this->seedCatalog();

        Booking::query()->create([
            'reference' => 'TBK-CAL001',
            'car_id' => $car->id,
            'pickup_location_id' => $pickup->id,
            'dropoff_location_id' => $dropoff->id,
            'pickup_at' => now()->addDay(),
            'dropoff_at' => now()->addDays(2),
            'status' => 'confirmed',
            'customer_name' => 'Calendar User',
            'customer_email' => 'calendar@example.com',
            'rental_subtotal' => 100,
            'extras_subtotal' => 0,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total' => 100,
            'currency' => 'USD',
        ]);

        CarUnavailability::query()->create([
            'car_id' => $car->id,
            'starts_at' => now()->addDays(5),
            'ends_at' => now()->addDays(6),
            'reason' => 'maintenance',
        ]);

        $response = $this->getJson("/api/cars/{$car->id}/availability-calendar");
        $response->assertOk()
            ->assertJsonCount(1, 'booked')
            ->assertJsonCount(1, 'blocked');
    }

    private function seedCatalog(): array
    {
        $category = Category::query()->create([
            'name' => 'Economy',
            'slug' => 'economy',
            'is_active' => true,
        ]);

        $pickup = Location::query()->create([
            'name' => 'Airport',
            'slug' => 'airport',
            'is_active' => true,
        ]);

        $dropoff = Location::query()->create([
            'name' => 'Downtown',
            'slug' => 'downtown',
            'is_active' => true,
        ]);

        $car = Car::query()->create([
            'category_id' => $category->id,
            'name' => 'Toyota Yaris',
            'slug' => 'toyota-yaris',
            'transmission' => 'automatic',
            'fuel_type' => 'petrol',
            'seats' => 5,
            'bags' => 2,
            'availability_status' => 'available',
            'base_daily_price' => 50,
            'min_rental_days' => 1,
            'is_active' => true,
        ]);

        return [$car, $pickup, $dropoff];
    }
}
