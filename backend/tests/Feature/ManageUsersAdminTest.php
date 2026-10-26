<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Pages\ManageUsers;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ManageUsersAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_filter_users_by_role_and_change_a_password(): void
    {
        $admin = User::factory()->admin()->create();
        $host = User::factory()->host()->create(['name' => 'Jordan Host']);
        $customer = User::factory()->customer()->create(['name' => 'Alex Customer']);
        $customer->createToken('auth-token');

        $this->actingAs($admin);

        Livewire::test(ManageUsers::class)
            ->assertCanSeeTableRecords([$admin, $host, $customer])
            ->filterTable('role', UserRole::Host->value)
            ->assertCanSeeTableRecords([$host])
            ->assertCanNotSeeTableRecords([$customer, $admin])
            ->resetTableFilters()
            ->callAction(TestAction::make('changePassword')->table($customer), [
                'password' => 'new-password-1',
                'password_confirmation' => 'new-password-1',
            ])
            ->assertNotified();

        $this->assertTrue(Hash::check('new-password-1', $customer->fresh()->password));
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $customer->id,
        ]);
    }
}
