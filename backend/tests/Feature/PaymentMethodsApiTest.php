<?php

namespace Tests\Feature;

use App\Models\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentMethodsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_enabled_payment_methods_only(): void
    {
        PaymentMethod::query()->create([
            'code' => 'card',
            'name' => 'Credit / Debit Card',
            'is_enabled' => true,
            'sort_order' => 1,
        ]);
        PaymentMethod::query()->create([
            'code' => 'cash',
            'name' => 'Pay at Pickup',
            'is_enabled' => false,
            'sort_order' => 2,
        ]);

        $response = $this->getJson('/api/payment-methods');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', 'card');
    }
}
