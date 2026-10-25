<?php

namespace Tests\Feature;

use App\Enums\ListingApprovalStatus;
use App\Filament\Resources\RegisteredHosts\Pages\CreateRegisteredHost;
use App\Filament\Resources\RegisteredHosts\Pages\EditRegisteredHost;
use App\Filament\Resources\RegisteredHosts\Pages\ListRegisteredHosts;
use App\Mail\TemplatedMail;
use App\Models\Car;
use App\Models\EmailTemplate;
use App\Models\User;
use Database\Seeders\EmailTemplateSeeder;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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

    public function test_admin_can_email_a_host_login_and_the_host_can_sign_in(): void
    {
        $this->seed(EmailTemplateSeeder::class);
        Mail::fake();
        $admin = User::factory()->admin()->create();
        $host = User::factory()->host()->create([
            'email' => 'fleet-host@example.test',
            'password' => 'old-password',
        ]);
        $host->createToken('existing');
        $previous = $host->fresh()->password;

        $this->actingAs($admin);
        Livewire::test(ListRegisteredHosts::class)
            ->callTableAction('sendHostLogin', $host)
            ->assertNotified();

        $password = null;
        Mail::assertSent(TemplatedMail::class, function (TemplatedMail $mail) use (&$password, $host): bool {
            $password = $mail->data['temporary_password'] ?? null;

            return $mail->templateKey === 'host_login'
                && $mail->hasTo($host->email)
                && is_string($password)
                && $password !== '';
        });

        $host->refresh();
        $this->assertFalse(Hash::check('old-password', $host->password));
        $this->assertNotSame($previous, $host->password);
        $this->assertSame(0, $host->tokens()->count());

        $this->postJson('/api/auth/login', [
            'email' => $host->email,
            'password' => $password,
        ])->assertOk()->assertJsonPath('user.email', $host->email);

        $car = Car::factory()->create([
            'user_id' => $host->id,
            'is_active' => true,
            'listing_status' => ListingApprovalStatus::Approved,
        ]);
        $host->update(['rental_terms_html' => '<p>Fuel is not included.</p>']);
        $this->getJson('/api/cars/'.$car->id)
            ->assertOk()
            ->assertJsonPath('data.rental_terms_html', '<p>Fuel is not included.</p>');
    }

    public function test_failed_login_email_keeps_the_current_password(): void
    {
        Mail::fake();
        EmailTemplate::query()->where('key', 'host_login')->update(['is_enabled' => false]);
        $admin = User::factory()->admin()->create();
        $host = User::factory()->host()->create(['password' => 'keep-this-password']);
        $previous = $host->fresh()->password;

        $this->actingAs($admin);
        Livewire::test(EditRegisteredHost::class, ['record' => $host->id])
            ->callAction('sendHostLogin')
            ->assertNotified();

        Mail::assertNothingSent();
        $this->assertSame($previous, $host->fresh()->password);
        $this->assertTrue(Hash::check('keep-this-password', $host->fresh()->password));
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
