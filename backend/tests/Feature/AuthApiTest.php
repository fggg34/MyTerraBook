<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_and_login_return_token(): void
    {
        $register = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+354 555 1234',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $register->assertCreated()->assertJsonStructure(['token', 'user']);

        $login = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $login->assertOk()->assertJsonStructure(['token', 'user']);
    }

    public function test_register_requires_phone(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_authenticated_user_route(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/user');
        $response->assertOk()->assertJsonFragment(['email' => $user->email]);
    }
}
