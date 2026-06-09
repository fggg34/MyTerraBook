<?php

namespace Tests\Feature;

use App\Models\BookingRestriction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingRestrictionsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_restrictions_include_full_rows(): void
    {
        BookingRestriction::query()->create([
            'name' => 'Summer',
            'date_from' => now()->subDay(),
            'date_to' => now()->addMonth(),
            'min_rental_days' => 3,
            'max_rental_days' => 14,
            'cta_weekdays' => ['saturday'],
            'ctd_weekdays' => ['sunday'],
            'forced_pickup_weekdays' => ['monday'],
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/booking-restrictions');

        $response->assertOk()
            ->assertJsonPath('min_rental_days', 3)
            ->assertJsonPath('restrictions.0.closed_to_arrival', ['saturday'])
            ->assertJsonPath('restrictions.0.closed_to_departure', ['sunday'])
            ->assertJsonPath('restrictions.0.forced_pickup_weekdays', ['monday']);
    }
}
