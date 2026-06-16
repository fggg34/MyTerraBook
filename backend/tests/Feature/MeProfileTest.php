<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MeProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_host_must_provide_phone_on_profile_update(): void
    {
        $host = User::factory()->host()->create([
            'phone' => '+354 555 0000',
        ]);

        Sanctum::actingAs($host);

        $this->patchJson('/api/me/profile', [
            'name' => $host->name,
            'email' => $host->email,
            'phone' => '',
            'currency' => 'EUR',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_host_can_update_profile_name_and_phone(): void
    {
        $host = User::factory()->host()->create([
            'name' => 'Old Name',
            'phone' => '+354 555 0000',
        ]);

        Sanctum::actingAs($host);

        $this->patchJson('/api/me/profile', [
            'name' => 'New Name',
            'email' => $host->email,
            'phone' => '+354 555 1234',
            'currency' => 'EUR',
        ])
            ->assertOk()
            ->assertJsonPath('user.name', 'New Name')
            ->assertJsonPath('user.phone', '+354 555 1234');

        $this->assertDatabaseHas('users', [
            'id' => $host->id,
            'name' => 'New Name',
            'phone' => '+354 555 1234',
        ]);
    }

    public function test_email_change_requires_current_password(): void
    {
        $host = User::factory()->host()->create([
            'email' => 'host@example.test',
            'phone' => '+354 555 0000',
            'password' => Hash::make('password123'),
        ]);

        Sanctum::actingAs($host);

        $this->patchJson('/api/me/profile', [
            'name' => $host->name,
            'email' => 'new-host@example.test',
            'phone' => $host->phone,
            'currency' => 'EUR',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['current_password']);

        $this->patchJson('/api/me/profile', [
            'name' => $host->name,
            'email' => 'new-host@example.test',
            'phone' => $host->phone,
            'currency' => 'EUR',
            'current_password' => 'password123',
        ])
            ->assertOk()
            ->assertJsonPath('user.email', 'new-host@example.test');
    }

    public function test_host_can_update_password(): void
    {
        $host = User::factory()->host()->create([
            'password' => Hash::make('password123'),
        ]);

        Sanctum::actingAs($host);

        $this->patchJson('/api/me/password', [
            'current_password' => 'password123',
            'password' => 'new-password-99',
            'password_confirmation' => 'new-password-99',
        ])->assertOk();

        $host->refresh();
        $this->assertTrue(Hash::check('new-password-99', $host->password));
    }

    public function test_guest_must_be_authenticated(): void
    {
        $this->patchJson('/api/me/profile', [
            'name' => 'Guest',
            'email' => 'guest@example.test',
        ])->assertUnauthorized();
    }

    public function test_customer_can_update_profile(): void
    {
        $customer = User::factory()->customer()->create();

        Sanctum::actingAs($customer);

        $this->patchJson('/api/me/profile', [
            'name' => 'Updated Customer',
            'email' => $customer->email,
            'phone' => '+354 555 7777',
        ])
            ->assertOk()
            ->assertJsonPath('user.role', UserRole::Customer->value)
            ->assertJsonPath('user.phone', '+354 555 7777');
    }

    public function test_host_can_update_profile_currency(): void
    {
        $host = User::factory()->host()->create([
            'phone' => '+354 555 0100',
            'currency' => 'EUR',
        ]);

        Sanctum::actingAs($host);

        $this->patchJson('/api/me/profile', [
            'name' => $host->name,
            'email' => $host->email,
            'phone' => $host->phone,
            'currency' => 'USD',
        ])->assertOk()
            ->assertJsonPath('user.currency', 'USD');

        $this->assertDatabaseHas('users', [
            'id' => $host->id,
            'currency' => 'USD',
        ]);
    }
}
