<?php

namespace Tests\Feature;

use App\Filament\Resources\RegisteredClients\Pages\ListRegisteredClients;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RegisteredClientsAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_registered_clients_list(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->customer()->create(['name' => 'Alex Customer']);
        $host = User::factory()->host()->create(['name' => 'Jordan Host']);

        $this->actingAs($admin);

        Livewire::test(ListRegisteredClients::class)
            ->assertCanSeeTableRecords([$client])
            ->assertCanNotSeeTableRecords([$host, $admin]);
    }

    public function test_admin_can_delete_registered_client(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->customer()->create([
            'email' => 'client-to-delete@example.com',
        ]);
        $client->createToken('test-token');

        $this->actingAs($admin);

        Livewire::test(ListRegisteredClients::class)
            ->callTableAction(DeleteAction::class, $client)
            ->assertNotified();

        $this->assertDatabaseMissing('users', ['id' => $client->id]);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_type' => User::class,
            'tokenable_id' => $client->id,
        ]);
    }

    public function test_non_admin_cannot_access_registered_clients_page(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer)
            ->get('/admin/registered-clients')
            ->assertForbidden();
    }

    public function test_registered_clients_page_only_lists_customer_role_users(): void
    {
        $admin = User::factory()->admin()->create();

        User::factory()->customer()->count(3)->create();
        User::factory()->host()->count(2)->create();

        $this->actingAs($admin);

        Livewire::test(ListRegisteredClients::class)
            ->assertCountTableRecords(3);
    }
}
