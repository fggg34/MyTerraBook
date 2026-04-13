<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrdersCsvExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_orders_csv_requires_admin(): void
    {
        $this->seed();
        $customer = User::query()->where('email', 'customer@terrabook.test')->firstOrFail();

        $this->actingAs($customer, 'sanctum')
            ->get('/api/admin/orders/export.csv')
            ->assertForbidden();
    }

    public function test_admin_downloads_csv(): void
    {
        $this->seed();
        $admin = User::query()->where('email', 'admin@terrabook.test')->firstOrFail();

        $response = $this->actingAs($admin, 'sanctum')
            ->get('/api/admin/orders/export.csv');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString('reference', $response->streamedContent());
    }
}
