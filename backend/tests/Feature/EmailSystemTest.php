<?php

namespace Tests\Feature;

use App\Enums\GuestHouseBookingStatus;
use App\Enums\GuestHouseStatus;
use App\Enums\GuestHouseType;
use App\Filament\Pages\EmailSettings;
use App\Filament\Pages\EmailTesting;
use App\Filament\Resources\EmailLogs\Pages\ListEmailLogs;
use App\Filament\Resources\EmailTemplates\Pages\EditEmailTemplate;
use App\Filament\Resources\EmailTemplates\Pages\ListEmailTemplates;
use App\Mail\TemplatedMail;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\GuestHouse;
use App\Models\GuestHouseBooking;
use App\Models\Setting;
use App\Models\User;
use App\Services\Email\EmailService;
use App\Services\Email\EmailTemplateRenderer;
use Database\Seeders\EmailTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class EmailSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(EmailTemplateSeeder::class);
    }

    public function test_customer_registration_queues_welcome_email(): void
    {
        Mail::fake();

        $this->postJson('/api/auth/register', [
            'name' => 'Alex Traveller',
            'email' => 'alex@example.com',
            'phone' => '+354 555 1234',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertCreated();

        Mail::assertQueued(TemplatedMail::class, fn (TemplatedMail $mail): bool => $mail->templateKey === 'customer_welcome'
            && $mail->hasTo('alex@example.com'));
    }

    public function test_host_registration_queues_host_welcome_email(): void
    {
        Mail::fake();

        $this->postJson('/api/auth/register-host', [
            'name' => 'Jordan Host',
            'email' => 'jordan@example.com',
            'phone' => '+354 555 9876',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertCreated();

        Mail::assertQueued(TemplatedMail::class, fn (TemplatedMail $mail): bool => $mail->templateKey === 'host_welcome'
            && $mail->hasTo('jordan@example.com'));
    }

    public function test_guest_house_booking_queues_received_email(): void
    {
        Mail::fake();
        $house = $this->seedHouse();

        $this->postJson('/api/guest-houses/bookings', [
            'guest_house_slug' => $house->slug,
            'check_in' => now()->addDays(3)->toDateString(),
            'check_out' => now()->addDays(5)->toDateString(),
            'guests_count' => 2,
            'guest_name' => 'Jane Doe',
            'guest_email' => 'jane@example.com',
            'guest_phone' => '+123456789',
        ])->assertCreated();

        Mail::assertQueued(TemplatedMail::class, fn (TemplatedMail $mail): bool => $mail->templateKey === 'gh_booking_received'
            && $mail->hasTo('jane@example.com'));
    }

    public function test_booking_status_change_to_confirmed_queues_confirmation(): void
    {
        Mail::fake();
        $house = $this->seedHouse();

        $booking = GuestHouseBooking::query()->create([
            'guest_house_id' => $house->id,
            'booking_reference' => 'GH-EMAIL-00001',
            'status' => GuestHouseBookingStatus::Pending,
            'guest_name' => 'Guest',
            'guest_email' => 'guest@example.com',
            'check_in' => now()->addDays(2),
            'check_out' => now()->addDays(4),
            'nights' => 2,
            'guests_count' => 2,
            'base_total' => 16000,
            'total_amount' => 16000,
        ]);

        $booking->update(['status' => GuestHouseBookingStatus::Confirmed, 'confirmed_at' => now()]);

        Mail::assertQueued(TemplatedMail::class, fn (TemplatedMail $mail): bool => $mail->templateKey === 'gh_booking_confirmed'
            && $mail->hasTo('guest@example.com'));
    }

    public function test_disabled_template_is_not_sent(): void
    {
        Mail::fake();

        EmailTemplate::query()->where('key', 'customer_welcome')->update(['is_enabled' => false]);

        $sent = app(EmailService::class)->send('customer_welcome', 'nobody@example.com', ['customer_name' => 'Nobody']);

        $this->assertFalse($sent);
        Mail::assertNothingQueued();
    }

    public function test_email_service_logs_a_delivery_row(): void
    {
        Mail::fake();

        app(EmailService::class)->send('customer_welcome', 'logme@example.com', ['customer_name' => 'Log Me']);

        $this->assertDatabaseHas('email_logs', [
            'template_key' => 'customer_welcome',
            'recipient' => 'logme@example.com',
            'status' => 'queued',
        ]);
    }

    public function test_renderer_substitutes_merge_variables(): void
    {
        $template = EmailTemplate::findByKey('order_received');

        $rendered = app(EmailTemplateRenderer::class)->render($template, [
            'customer_name' => 'Alex',
            'order_reference' => 'ORD-ABC123',
            'car_name' => 'Toyota Hilux',
            'pickup_at' => 'Mon, 15 Jun',
            'dropoff_at' => 'Fri, 19 Jun',
            'total' => '480.00 EUR',
        ]);

        $this->assertStringContainsString('ORD-ABC123', $rendered['subject']);
        $this->assertStringContainsString('Toyota Hilux', $rendered['bodyHtml']);
        $this->assertStringNotContainsString('{{', $rendered['bodyHtml']);
    }

    public function test_rendered_layout_forces_white_background_for_dark_mode(): void
    {
        $template = EmailTemplate::findByKey('customer_welcome');
        $rendered = app(EmailTemplateRenderer::class)->render($template, ['customer_name' => 'Alex']);

        $html = view('mail.layouts.brand', $rendered)->render();

        // Light-only declarations so dark-mode clients do not invert the white background.
        $this->assertStringContainsString('color-scheme', $html);
        $this->assertStringContainsString('light only', $html);
        // Outlook.com dark-mode overrides.
        $this->assertStringContainsString('data-ogsc', $html);
        $this->assertStringContainsString('data-ogsb', $html);
        // Apple Mail dark-mode override.
        $this->assertStringContainsString('prefers-color-scheme: dark', $html);
        // Explicit white card background.
        $this->assertStringContainsString('background-color:#ffffff', $html);
    }

    public function test_admin_email_pages_render(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        Livewire::test(ListEmailTemplates::class)->assertOk();
        Livewire::test(ListEmailLogs::class)->assertOk();
        Livewire::test(EmailSettings::class)->assertOk();
        Livewire::test(EmailTesting::class)->assertOk();
    }

    public function test_admin_can_edit_a_template(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $template = EmailTemplate::findByKey('customer_welcome');

        Livewire::test(EditEmailTemplate::class, ['record' => $template->getRouteKey()])
            ->fillForm([
                'subject' => 'A brand new subject',
                'is_enabled' => false,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('A brand new subject', $template->fresh()->subject);
        $this->assertFalse($template->fresh()->is_enabled);
    }

    public function test_admin_can_save_email_settings(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        Livewire::test(EmailSettings::class)
            ->fillForm([
                'accent_color' => '#123456',
                'sender_name' => 'Terra Team',
            ])
            ->call('save')
            ->assertNotified();

        $this->assertDatabaseHas('settings', ['key' => 'email.accent_color']);
        $this->assertSame('#123456', app(\App\Services\Email\EmailSettingsService::class)->get('accent_color'));
    }

    public function test_admin_can_send_test_email_from_testing_page(): void
    {
        Mail::fake();

        $admin = User::factory()->admin()->create(['email' => 'admin@example.com']);
        $this->actingAs($admin);

        Livewire::test(EmailTesting::class)
            ->set('testEmail', 'tester@example.com')
            ->set('templateKey', 'customer_welcome')
            ->call('sendTest')
            ->assertNotified();

        Mail::assertQueued(TemplatedMail::class, fn (TemplatedMail $mail): bool => $mail->templateKey === 'customer_welcome'
            && $mail->hasTo('tester@example.com'));

        $this->assertSame('tester@example.com', app(\App\Services\Email\EmailSettingsService::class)->getTestRecipient());
    }

    public function test_admin_can_save_test_recipient_without_sending(): void
    {
        Mail::fake();

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        Livewire::test(EmailTesting::class)
            ->set('testEmail', 'saved@example.com')
            ->call('saveTestEmail')
            ->assertNotified();

        Mail::assertNothingQueued();
        $this->assertSame('saved@example.com', app(\App\Services\Email\EmailSettingsService::class)->getTestRecipient());
    }

    private function seedHouse(): GuestHouse
    {
        Setting::putValue('shop.currency', ['code' => 'EUR', 'symbol' => '€']);
        Setting::putValue('shop.default_tax', ['basis_points' => 0]);

        return GuestHouse::query()->create([
            'name' => 'Email Test House',
            'slug' => 'email-test-house',
            'type' => GuestHouseType::Apartment,
            'status' => GuestHouseStatus::Active,
            'max_guests' => 4,
            'min_nights' => 1,
            'base_price_per_night' => 8000,
            'cleaning_fee' => 0,
        ]);
    }
}
