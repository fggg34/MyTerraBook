<?php

namespace Database\Seeders;

use App\Models\Backup;
use App\Models\ConditionalText;
use App\Models\TrackingCampaign;
use Database\Factories\BackupFactory;
use Database\Factories\TrackingEventFactory;
use Illuminate\Database\Seeder;

class DemoExtrasSeeder extends Seeder
{
    public function run(): void
    {
        ConditionalText::query()->firstOrCreate(
            ['name' => 'Minimum rental notice'],
            [
                'content' => '<p>Minimum rental period is 1 day. Long-term discounts apply from 7 days.</p>',
                'content_plain' => 'Minimum rental period is 1 day. Long-term discounts apply from 7 days.',
                'conditions' => ['placement' => 'checkout'],
                'templates' => ['checkout'],
                'placement' => 'body',
                'is_active' => true,
            ]
        );

        $campaign = TrackingCampaign::query()->firstOrCreate(
            ['slug' => 'summer-google-ads'],
            [
                'name' => 'Summer Google Ads',
                'utm_source' => 'google',
                'utm_medium' => 'cpc',
                'utm_campaign' => 'summer-rentals',
                'is_active' => true,
            ]
        );

        TrackingEventFactory::new()->count(15)->create([
            'tracking_campaign_id' => $campaign->id,
        ]);

        TrackingEventFactory::new()->count(5)->create([
            'tracking_campaign_id' => null,
        ]);

        BackupFactory::new()->count(2)->create();
    }
}
