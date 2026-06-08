<?php

namespace Tests\Feature;

use App\Mail\TemplatedMail;
use App\Models\User;
use Database\Seeders\EmailTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
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

    public function test_forgot_password_sends_branded_reset_email(): void
    {
        $this->seed(EmailTemplateSeeder::class);
        Mail::fake();

        $user = User::factory()->create();

        $this->postJson('/api/auth/forgot-password', [
            'email' => $user->email,
        ])
            ->assertOk()
            ->assertJsonFragment([
                'message' => 'If an account exists for that email, we sent a password reset link.',
            ]);

        Mail::assertQueued(TemplatedMail::class, fn (TemplatedMail $mail): bool => $mail->templateKey === 'password_reset'
            && $mail->hasTo($user->email));
    }

    public function test_forgot_password_does_not_reveal_missing_accounts(): void
    {
        $this->seed(EmailTemplateSeeder::class);
        Mail::fake();

        $this->postJson('/api/auth/forgot-password', [
            'email' => 'missing@example.com',
        ])
            ->assertOk()
            ->assertJsonFragment([
                'message' => 'If an account exists for that email, we sent a password reset link.',
            ]);

        Mail::assertNothingQueued();
    }

    public function test_reset_password_updates_credentials(): void
    {
        $user = User::factory()->create([
            'password' => 'old-password',
        ]);

        $token = Password::createToken($user);

        $this->postJson('/api/auth/reset-password', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])
            ->assertOk()
            ->assertJsonFragment([
                'message' => 'Your password has been reset. You can sign in now.',
            ]);

        $user->refresh();
        $this->assertTrue(Hash::check('new-password-123', $user->password));
    }
}
