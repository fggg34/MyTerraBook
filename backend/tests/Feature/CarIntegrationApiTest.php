<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\AvailabilityBlock;
use App\Models\Car;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CarIntegrationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_blocked_days_endpoint_requires_valid_token(): void
    {
        $host = User::factory()->host()->create();
        $car = Car::factory()->for($host, 'host')->create([
            'integration_token' => 'valid-integration-token',
        ]);

        $this->getJson("/api/integrations/cars/{$car->id}/blocked-days")
            ->assertUnauthorized();

        $this->getJson("/api/integrations/cars/{$car->id}/blocked-days?token=wrong-token")
            ->assertUnauthorized();
    }

    public function test_blocked_days_endpoint_returns_bookings_and_custom_blocks(): void
    {
        $host = User::factory()->host()->create();
        $car = Car::factory()->for($host, 'host')->create([
            'integration_token' => 'valid-integration-token',
            'units_available' => 2,
        ]);

        Order::factory()->create([
            'car_id' => $car->id,
            'order_status' => OrderStatus::Confirmed,
            'pickup_at' => Carbon::parse('2026-08-01 10:00:00'),
            'dropoff_at' => Carbon::parse('2026-08-05 10:00:00'),
        ]);

        AvailabilityBlock::query()->create([
            'car_id' => $car->id,
            'source' => 'manual',
            'starts_at' => Carbon::parse('2026-09-01 00:00:00'),
            'ends_at' => Carbon::parse('2026-09-03 00:00:00'),
            'units_blocked' => 1,
            'notes' => 'Service',
            'is_active' => true,
        ]);

        AvailabilityBlock::query()->create([
            'car_id' => $car->id,
            'source' => 'ical_import',
            'starts_at' => Carbon::parse('2026-10-01 00:00:00'),
            'ends_at' => Carbon::parse('2026-10-02 00:00:00'),
            'units_blocked' => 1,
            'is_active' => true,
        ]);

        $response = $this->withHeader('X-Integration-Token', 'valid-integration-token')
            ->getJson("/api/integrations/cars/{$car->id}/blocked-days");

        $response->assertOk()
            ->assertJsonPath('vehicle.id', $car->id)
            ->assertJsonPath('vehicle.units_available', 2)
            ->assertJsonCount(1, 'bookings')
            ->assertJsonCount(1, 'custom_blocks')
            ->assertJsonPath('custom_blocks.0.notes', 'Service')
            ->assertJsonPath('custom_blocks.0.type', 'custom_block');
    }

    public function test_host_can_list_integration_credentials_per_vehicle(): void
    {
        $host = User::factory()->host()->create();
        $car = Car::factory()->for($host, 'host')->create([
            'integration_token' => null,
        ]);

        Sanctum::actingAs($host);

        $response = $this->getJson('/api/host/integrations')->assertOk();

        $response->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $car->id)
            ->assertJsonStructure([
                'data' => [
                    ['id', 'name', 'integration_token', 'blocked_days_endpoint'],
                ],
            ]);

        $this->assertNotNull($car->fresh()->integration_token);
    }

    public function test_host_can_regenerate_integration_token(): void
    {
        $host = User::factory()->host()->create();
        $car = Car::factory()->for($host, 'host')->create([
            'integration_token' => 'old-token',
        ]);

        Sanctum::actingAs($host);

        $this->postJson("/api/host/cars/{$car->id}/integration-token/regenerate")
            ->assertOk()
            ->assertJsonPath('data.integration_token', fn ($token) => $token !== 'old-token' && strlen($token) === 48);
    }
}
