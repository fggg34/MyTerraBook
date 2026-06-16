<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Resources\RegisteredHosts\Pages\ListRegisteredHosts;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RegisteredHostsAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_registered_hosts_list(): void
    {
        $admin = User::factory()->admin()->create();
        $host = User::factory()->host()->create(['name' => 'Jordan Host']);
        $customer = User::factory()->customer()->create(['name' => 'Alex Customer']);

        $this->actingAs($admin);

        Livewire::test(ListRegisteredHosts::class)
            ->assertCanSeeTableRecords([$host])
            ->assertCanNotSeeTableRecords([$customer, $admin]);
    }

    public function test_admin_can_delete_registered_host(): void
    {
        $admin = User::factory()->admin()->create();
        $host = User::factory()->host()->create([
            'email' => 'host-to-delete@example.com',
        ]);
        $host->createToken('test-token');

        $this->actingAs($admin);

        Livewire::test(ListRegisteredHosts::class)
            ->callTableAction(DeleteAction::class, $host)
            ->assertNotified();

        $this->assertDatabaseMissing('users', ['id' => $host->id]);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_type' => User::class,
            'tokenable_id' => $host->id,
        ]);
    }

    public function test_non_admin_cannot_access_registered_hosts_page(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer)
            ->get('/admin/registered-hosts')
            ->assertForbidden();
    }

    public function test_registered_hosts_page_only_lists_host_role_users(): void
    {
        $admin = User::factory()->admin()->create();

        User::factory()->host()->count(2)->create();
        User::factory()->customer()->count(3)->create();

        $this->actingAs($admin);

        Livewire::test(ListRegisteredHosts::class)
            ->assertCountTableRecords(2);
    }
}
