<?php

namespace Tests\Unit;

use App\Models\Characteristic;
use App\Support\VehicleSpecIconResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleSpecIconResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolves_transmission_icon_from_characteristics_catalogue(): void
    {
        Characteristic::query()->create([
            'name' => 'Manual Transmission',
            'icon' => 'settings-2',
            'display_text' => 'Manual Transmission',
        ]);

        $resolved = VehicleSpecIconResolver::forTransmission('manual');

        $this->assertSame('settings-2', $resolved['icon']);
        $this->assertNull($resolved['icon_url']);
    }

    public function test_resolves_drive_icon_from_characteristics_catalogue(): void
    {
        Characteristic::query()->create([
            'name' => '4WD / AWD',
            'icon' => 'mountain',
            'display_text' => '4WD / AWD',
        ]);

        $resolved = VehicleSpecIconResolver::forDriveType('4wd');

        $this->assertSame('mountain', $resolved['icon']);
    }
}
