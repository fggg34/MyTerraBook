<?php

namespace Database\Factories;

use App\Models\TrackingCampaign;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<TrackingCampaign> */
class TrackingCampaignFactory extends Factory
{
    protected $model = TrackingCampaign::class;

    public function definition(): array
    {
        $name = fake()->words(2, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'utm_source' => fake()->randomElement(['google', 'facebook', 'instagram', 'newsletter']),
            'utm_medium' => fake()->randomElement(['cpc', 'social', 'email', 'organic']),
            'utm_campaign' => fake()->slug(2),
            'is_active' => true,
        ];
    }
}
