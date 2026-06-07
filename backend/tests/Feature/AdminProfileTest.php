<?php

namespace Tests\Feature;

use App\Filament\Pages\Auth\EditProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AdminProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_email_from_profile_page(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@example.com',
        ]);

        $this->actingAs($admin);

        Livewire::test(EditProfile::class)
            ->fillForm([
                'name' => $admin->name,
                'email' => 'new-admin@example.com',
                'currentPassword' => 'password',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertSame('new-admin@example.com', $admin->fresh()->email);
    }

    public function test_admin_can_update_password_from_profile_page(): void
    {
        $admin = User::factory()->admin()->create([
            'password' => Hash::make('old-password'),
        ]);

        $this->actingAs($admin);

        Livewire::test(EditProfile::class)
            ->fillForm([
                'name' => $admin->name,
                'email' => $admin->email,
                'password' => 'new-password-123',
                'passwordConfirmation' => 'new-password-123',
                'currentPassword' => 'old-password',
            ])
            ->call('save')
            ->assertNotified();

        $this->assertTrue(Hash::check('new-password-123', $admin->fresh()->password));
    }

    public function test_password_change_requires_current_password(): void
    {
        $admin = User::factory()->admin()->create([
            'password' => Hash::make('old-password'),
        ]);

        $this->actingAs($admin);

        Livewire::test(EditProfile::class)
            ->fillForm([
                'name' => $admin->name,
                'email' => $admin->email,
                'password' => 'new-password-123',
                'passwordConfirmation' => 'new-password-123',
            ])
            ->call('save')
            ->assertHasFormErrors(['currentPassword' => 'required']);

        $this->assertTrue(Hash::check('old-password', $admin->fresh()->password));
    }

    public function test_password_change_requires_confirmation(): void
    {
        $admin = User::factory()->admin()->create([
            'password' => Hash::make('old-password'),
        ]);

        $this->actingAs($admin);

        Livewire::test(EditProfile::class)
            ->fillForm([
                'name' => $admin->name,
                'email' => $admin->email,
                'currentPassword' => 'old-password',
                'password' => 'new-password-123',
            ])
            ->call('save')
            ->assertHasFormErrors(['passwordConfirmation' => 'required']);

        $this->assertTrue(Hash::check('old-password', $admin->fresh()->password));
    }

    public function test_password_change_rejects_mismatched_confirmation(): void
    {
        $admin = User::factory()->admin()->create([
            'password' => Hash::make('old-password'),
        ]);

        $this->actingAs($admin);

        Livewire::test(EditProfile::class)
            ->fillForm([
                'name' => $admin->name,
                'email' => $admin->email,
                'currentPassword' => 'old-password',
                'password' => 'new-password-123',
                'passwordConfirmation' => 'different-password',
            ])
            ->call('save')
            ->assertHasFormErrors(['password']);

        $this->assertTrue(Hash::check('old-password', $admin->fresh()->password));
    }

    public function test_non_admin_cannot_access_filament_profile_page(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer)
            ->get('/admin/profile')
            ->assertForbidden();
    }
}
