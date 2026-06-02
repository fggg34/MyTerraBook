<?php

namespace Database\Factories;

use App\Models\TrackingCampaign;
use App\Models\TrackingEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TrackingEvent> */
class TrackingEventFactory extends Factory
{
    protected $model = TrackingEvent::class;

    public function definition(): array
    {
        return [
            'tracking_campaign_id' => TrackingCampaign::factory(),
            'event_type' => fake()->randomElement(['page_view', 'quote', 'checkout_start', 'order_complete']),
            'country' => fake()->countryCode(),
            'referrer_host' => fake()->optional(0.6)->domainName(),
            'meta' => ['path' => fake()->randomElement(['/', '/cars', '/checkout'])],
        ];
    }
}
