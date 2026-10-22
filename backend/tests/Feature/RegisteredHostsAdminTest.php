<?php

namespace Tests\Feature;

use App\Filament\Resources\RegisteredHosts\Pages\CreateRegisteredHost;
use App\Filament\Resources\RegisteredHosts\Pages\EditRegisteredHost;
use App\Filament\Resources\RegisteredHosts\Pages\ListRegisteredHosts;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class RegisteredHostsAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_caren_host_and_preserve_blank_credentials_on_edit(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        Livewire::test(CreateRegisteredHost::class)->fillForm([
            'name' => 'Konvin', 'email' => 'konvin@example.test', 'country_code' => 'IS',
            'partner_provider' => 'caren', 'partner_api_enabled' => true,
            'partner_api_base_url' => 'https://booking.caren.is',
            'partner_api_key' => 'test-key', 'partner_api_username' => 'api@example.test',
            'partner_api_password' => 'test-password', 'partner_rental_id' => 120,
        ])->call('create')->assertHasNoFormErrors();
        $host = User::query()->where('email', 'konvin@example.test')->sole();
        $this->assertTrue($host->isHost());
        $this->assertSame('test-password', $host->partner_api_password);
        Livewire::test(EditRegisteredHost::class, ['record' => $host->id])
            ->assertFormSet(['partner_api_key' => null, 'partner_api_password' => null, 'partner_api_username' => null])
            ->fillForm(['name' => 'Konvin updated', 'partner_api_key' => '', 'partner_api_password' => '', 'partner_api_username' => ''])
            ->call('save')->assertHasNoFormErrors();
        $this->assertSame('test-password', $host->fresh()->partner_api_password);
        $this->assertSame('test-key', $host->fresh()->partner_api_key);
    }

    public function test_admin_can_upload_a_partner_logo(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->admin()->create());
        $host = User::factory()->host()->create();

        Livewire::test(EditRegisteredHost::class, ['record' => $host->id])
            ->fillForm([
                'company_logo_path' => UploadedFile::fake()->image('logo.png', 240, 80),
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $host->refresh();
        $this->assertNotNull($host->company_logo_path);
        $this->assertStringStartsWith('company-logos/', $host->company_logo_path);
        Storage::disk('public')->assertExists($host->company_logo_path);
        $this->assertNotNull($host->company_logo_url);
    }

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

    public function test_admin_can_find_hosts_by_email_and_filter_api_status(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $enabled = User::factory()->host()->create([
            'email' => 'fleet-search@example.test',
            'partner_api_enabled' => true,
        ]);
        $disabled = User::factory()->host()->create(['partner_api_enabled' => false]);

        Livewire::test(ListRegisteredHosts::class)
            ->searchTable('fleet-search@example.test')
            ->assertCanSeeTableRecords([$enabled])
            ->assertCanNotSeeTableRecords([$disabled]);

        Livewire::test(ListRegisteredHosts::class)
            ->filterTable('partner_api_enabled', true)
            ->assertCanSeeTableRecords([$enabled])
            ->assertCanNotSeeTableRecords([$disabled]);
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
