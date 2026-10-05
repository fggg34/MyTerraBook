<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\User;
use App\Services\Partners\GreenlightSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PartnerLogoCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_car_list_includes_the_host_logo(): void
    {
        Storage::fake('public');

        $host = User::factory()->host()->create([
            'name' => 'Green Light',
            'company_logo_path' => 'company-logos/green-light.png',
        ]);
        Storage::disk('public')->put('company-logos/green-light.png', 'logo');

        $car = Car::factory()->create([
            'user_id' => $host->id,
            'name' => 'Fiat Doblo Maxi',
            'is_active' => true,
        ]);

        $this->getJson('/api/cars')
            ->assertOk()
            ->assertJsonPath('data.0.id', $car->id)
            ->assertJsonPath('data.0.partner.id', 'host-'.$host->id)
            ->assertJsonPath('data.0.partner.name', 'Green Light')
            ->assertJsonPath('data.0.partner.logo_url', Storage::disk('public')->url('company-logos/green-light.png'));
    }

    public function test_greenlight_vehicles_share_one_partner(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('company-logos/green-light.png', 'logo');
        \App\Models\Setting::putValue('partners.greenlight', [
            'enabled' => true,
            'base_url' => 'https://greenlight.test/api/partner/v1',
            'api_key' => 'glpk_test',
            'insurance_plan_ids' => [],
            'display_name' => 'Green Light',
            'logo_path' => 'company-logos/green-light.png',
        ]);

        $car = Car::factory()->create([
            'user_id' => null,
            'external_provider' => GreenlightSettings::PROVIDER,
            'external_vehicle_id' => 'GL-1',
            'name' => 'Fiat Doblo Maxi',
            'is_active' => true,
        ]);

        $this->getJson('/api/cars')
            ->assertOk()
            ->assertJsonPath('data.0.id', $car->id)
            ->assertJsonPath('data.0.partner.id', 'greenlight')
            ->assertJsonPath('data.0.partner.name', 'Green Light')
            ->assertJsonPath('data.0.partner.logo_url', Storage::disk('public')->url('company-logos/green-light.png'));
    }

    public function test_host_can_upload_and_remove_a_company_logo(): void
    {
        Storage::fake('public');
        $host = User::factory()->host()->create();
        Sanctum::actingAs($host);

        $this->post('/api/me/company-logo', [
            'logo' => UploadedFile::fake()->image('logo.png', 240, 80),
        ])->assertOk()
            ->assertJsonPath('message', 'Company logo updated.');

        $host->refresh();
        $this->assertNotNull($host->company_logo_path);
        Storage::disk('public')->assertExists($host->company_logo_path);

        $this->deleteJson('/api/me/company-logo')
            ->assertOk()
            ->assertJsonPath('user.company_logo_url', null);

        $this->assertNull($host->fresh()->company_logo_path);
    }

    public function test_traveler_cannot_upload_a_company_logo(): void
    {
        Storage::fake('public');
        Sanctum::actingAs(User::factory()->customer()->create());

        $this->post('/api/me/company-logo', [
            'logo' => UploadedFile::fake()->image('logo.png'),
        ])->assertForbidden();
    }
}
