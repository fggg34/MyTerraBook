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
        $host = User::factory()->host()->create([
            'integration_token' => 'valid-integration-token',
        ]);
        Car::factory()->for($host, 'host')->create();

        $this->getJson('/api/integrations/blocked-days')
            ->assertUnauthorized();

        $this->getJson('/api/integrations/blocked-days?token=wrong-token')
            ->assertUnauthorized();
    }

    public function test_blocked_days_endpoint_returns_all_host_vehicles(): void
    {
        $host = User::factory()->host()->create([
            'integration_token' => 'valid-integration-token',
        ]);
        $carA = Car::factory()->for($host, 'host')->create([
            'name' => 'Camper A',
            'units_available' => 2,
        ]);
        $carB = Car::factory()->for($host, 'host')->create([
            'name' => 'Camper B',
            'units_available' => 1,
        ]);

        Order::factory()->create([
            'car_id' => $carA->id,
            'order_status' => OrderStatus::Confirmed,
            'pickup_at' => Carbon::parse('2026-08-01 10:00:00'),
            'dropoff_at' => Carbon::parse('2026-08-05 10:00:00'),
        ]);

        AvailabilityBlock::query()->create([
            'car_id' => $carB->id,
            'source' => 'manual',
            'starts_at' => Carbon::parse('2026-09-01 00:00:00'),
            'ends_at' => Carbon::parse('2026-09-03 00:00:00'),
            'units_blocked' => 1,
            'notes' => 'Service',
            'is_active' => true,
        ]);

        $response = $this->withHeader('X-Integration-Token', 'valid-integration-token')
            ->getJson('/api/integrations/blocked-days');

        $response->assertOk()
            ->assertJsonCount(2, 'vehicles')
            ->assertJsonPath('vehicles.0.id', $carA->id)
            ->assertJsonPath('vehicles.0.bookings.0.type', 'booking')
            ->assertJsonPath('vehicles.1.id', $carB->id)
            ->assertJsonPath('vehicles.1.custom_blocks.0.notes', 'Service');
    }

    public function test_host_can_view_integration_credentials(): void
    {
        $host = User::factory()->host()->create([
            'integration_token' => null,
        ]);
        Car::factory()->for($host, 'host')->create();

        Sanctum::actingAs($host);

        $response = $this->getJson('/api/host/integrations')->assertOk();

        $response->assertJsonStructure([
            'data' => [
                'integration_token',
                'blocked_days_endpoint',
                'vehicles' => [
                    ['id', 'name', 'units_available'],
                ],
            ],
        ]);

        $this->assertNotNull($host->fresh()->integration_token);
    }

    public function test_host_can_regenerate_integration_token(): void
    {
        $host = User::factory()->host()->create([
            'integration_token' => 'old-token',
        ]);

        Sanctum::actingAs($host);

        $this->postJson('/api/host/integration-token/regenerate')
            ->assertOk()
            ->assertJsonPath('data.integration_token', fn ($token) => $token !== 'old-token' && strlen($token) === 48);
    }
}
