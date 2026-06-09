<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Pages\SiteContentHub;
use App\Models\User;
use App\Support\DesignerPanelAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class DesignerAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_designer_can_preview_site(): void
    {
        $designer = User::factory()->designer()->create();

        $this->assertTrue($designer->canPreviewSite());
    }

    public function test_designer_cannot_access_admin_api_stats(): void
    {
        $designer = User::factory()->designer()->create();

        $response = $this->actingAs($designer, 'sanctum')->getJson('/api/admin/stats');

        $response->assertForbidden();
    }

    public function test_designer_cannot_access_site_content_hub(): void
    {
        $designer = User::factory()->designer()->create();

        $this->actingAs($designer);

        $this->assertFalse(SiteContentHub::canAccess());
    }

    public function test_designer_is_blocked_from_all_panel_routes(): void
    {
        $designer = User::factory()->designer()->create();
        $request = Request::create('/admin/site-content');

        $this->assertFalse(DesignerPanelAccess::userCanAccessRequest($designer, $request));
    }

    public function test_seeded_designer_user_exists(): void
    {
        $this->seed(\Database\Seeders\UserSeeder::class);

        $this->assertDatabaseHas('users', [
            'email' => 'designer@terrabook.test',
            'role' => UserRole::Designer->value,
        ]);
    }
}
