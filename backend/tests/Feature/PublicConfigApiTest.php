<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicConfigApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_config_returns_shop_settings(): void
    {
        Setting::putValue('shop.currency', [
            'name' => 'Euro',
            'symbol' => '€',
            'code' => 'EUR',
            'decimals' => 2,
            'decimal_separator' => '.',
            'thousand_separator' => ',',
        ]);
        Setting::putValue('shop.deposit', ['value' => 15, 'type' => 'percentage']);
        Setting::putValue('shop.allow_deposit', ['enabled' => true]);
        Setting::putValue('shop.exchange_rates', [
            'EUR' => 1,
            'USD' => 1.08,
            'GBP' => 0.86,
            'ISK' => 150,
        ]);

        $response = $this->getJson('/api/public-config');

        $response->assertOk()
            ->assertJsonPath('currency.code', 'EUR')
            ->assertJsonPath('prepay_percent', 15)
            ->assertJsonPath('deposit.value', 15)
            ->assertJsonPath('exchange_rates.EUR', 1)
            ->assertJsonPath('exchange_rates.USD', 1.08);
    }
}
