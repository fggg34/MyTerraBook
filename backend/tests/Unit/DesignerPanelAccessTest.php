<?php

namespace Tests\Unit;

use App\Models\User;
use App\Support\DesignerPanelAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class DesignerPanelAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_panel_routes(): void
    {
        $admin = User::factory()->admin()->create();
        $request = Request::create('/admin/site-content');

        $this->assertTrue(DesignerPanelAccess::userCanAccessRequest($admin, $request));
    }

    public function test_designer_cannot_access_panel_routes(): void
    {
        $designer = User::factory()->designer()->create();
        $request = Request::create('/admin/site-content');

        $this->assertFalse(DesignerPanelAccess::userCanAccessRequest($designer, $request));
    }
}
