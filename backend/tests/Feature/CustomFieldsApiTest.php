<?php

namespace Tests\Feature;

use App\Models\CustomField;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomFieldsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_active_custom_fields(): void
    {
        CustomField::query()->create([
            'field_key' => 'flight_number',
            'label' => 'Flight #',
            'type' => 'text',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        CustomField::query()->create([
            'field_key' => 'inactive',
            'label' => 'Inactive',
            'type' => 'text',
            'is_active' => false,
            'sort_order' => 2,
        ]);

        $response = $this->getJson('/api/custom-fields');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.field_key', 'flight_number');
    }
}
