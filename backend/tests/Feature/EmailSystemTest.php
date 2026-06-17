<?php

namespace Tests\Feature;

use App\Enums\GuestHouseBookingStatus;
use App\Enums\GuestHouseStatus;
use App\Enums\GuestHouseType;
use App\Enums\OrderStatus;
use App\Filament\Pages\EmailSettings;
use App\Filament\Pages\EmailTesting;
use App\Filament\Resources\EmailLogs\Pages\ListEmailLogs;
use App\Filament\Resources\EmailTemplates\Pages\CreateEmailTemplate;
use App\Filament\Resources\EmailTemplates\Pages\EditEmailTemplate;
use App\Filament\Resources\EmailTemplates\Pages\ListEmailTemplates;
use App\Mail\TemplatedMail;
use App\Models\Car;
use App\Models\DailyFare;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\GuestHouse;
use App\Models\Location;
use App\Models\MainCategory;
use App\Models\PriceType;
use App\Models\SubCategory;
use App\Models\GuestHouseBooking;
use App\Models\Order;
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

        Mail::assertQueued(TemplatedMail::class, fn (TemplatedMail $mail): bool => $mail->templateKey === 'gh_booking_confirmed'
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

    public function test_admin_can_create_a_custom_template(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        Livewire::test(CreateEmailTemplate::class)
            ->fillForm([
                'key' => 'promo_summer',
                'name' => 'Summer promo',
                'category' => 'custom',
                'audience' => 'customer',
                'is_enabled' => true,
                'available_variables' => ['customer_name', 'promo_code'],
                'subject' => 'Your {{promo_code}} offer',
                'heading' => 'Summer sale',
                'greeting' => 'Hi {{customer_name}},',
                'body_html' => '<p>Use code {{promo_code}} at checkout.</p>',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $template = EmailTemplate::findByKey('promo_summer');
        $this->assertNotNull($template);
        $this->assertSame('Summer promo', $template->name);
        $this->assertSame(['customer_name', 'promo_code'], $template->available_variables);
    }

    public function test_install_defaults_action_adds_missing_templates(): void
    {
        EmailTemplate::query()->delete();

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $this->assertSame(0, EmailTemplate::query()->count());

        Livewire::test(ListEmailTemplates::class)
            ->callAction('installDefaults')
            ->assertNotified();

        $this->assertSame(EmailTemplateSeeder::defaultTemplateCount(), EmailTemplate::query()->count());
    }

    public function test_seed_email_templates_command_is_idempotent(): void
    {
        EmailTemplate::query()->delete();
        $this->assertSame(0, EmailTemplate::query()->count());

        $this->artisan('email:seed-templates')->assertSuccessful();
        $this->assertSame(EmailTemplateSeeder::defaultTemplateCount(), EmailTemplate::query()->count());

        $this->artisan('email:seed-templates')->assertSuccessful();
        $this->assertSame(EmailTemplateSeeder::defaultTemplateCount(), EmailTemplate::query()->count());
    }

    public function test_admin_can_save_email_settings(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        Livewire::test(EmailSettings::class)
            ->fillForm([
                'accent_color' => '#123456',
                'sender_name' => 'Terra Team',
                'admin_email' => 'orders@myterrabook.com',
            ])
            ->call('save')
            ->assertNotified();

        $this->assertDatabaseHas('settings', ['key' => 'email.accent_color']);
        $this->assertSame('#123456', app(\App\Services\Email\EmailSettingsService::class)->get('accent_color'));
        $this->assertSame('orders@myterrabook.com', app(\App\Services\Email\EmailSettingsService::class)->getAdminEmail());
    }

    public function test_new_order_queues_admin_notification_email(): void
    {
        Mail::fake();

        Setting::putValue('email.admin_email', ['value' => 'admin@myterrabook.com']);
        [$car, $pickup, $dropoff, $priceType] = $this->seedCatalogForOrder();

        $this->postJson('/api/orders', [
            'car_id' => $car->id,
            'price_type_id' => $priceType->id,
            'pickup_location_id' => $pickup->id,
            'dropoff_location_id' => $dropoff->id,
            'pickup_at' => now()->addDay()->toDateTimeString(),
            'dropoff_at' => now()->addDays(4)->toDateTimeString(),
            'customer_name' => 'Alex',
            'customer_email' => 'alex@example.com',
        ])->assertCreated();

        Mail::assertQueued(TemplatedMail::class, fn (TemplatedMail $mail): bool => $mail->templateKey === 'order_new_admin'
            && $mail->hasTo('admin@myterrabook.com'));
    }

    public function test_new_order_queues_customer_confirmed_email(): void
    {
        Mail::fake();
        [$car, $pickup, $dropoff, $priceType] = $this->seedCatalogForOrder();

        $this->postJson('/api/orders', [
            'car_id' => $car->id,
            'price_type_id' => $priceType->id,
            'pickup_location_id' => $pickup->id,
            'dropoff_location_id' => $dropoff->id,
            'pickup_at' => now()->addDay()->toDateTimeString(),
            'dropoff_at' => now()->addDays(4)->toDateTimeString(),
            'customer_name' => 'Alex',
            'customer_email' => 'alex@example.com',
        ])->assertCreated();

        Mail::assertQueued(TemplatedMail::class, fn (TemplatedMail $mail): bool => $mail->templateKey === 'order_confirmed'
            && $mail->hasTo('alex@example.com'));
    }

    public function test_confirmed_only_setting_skips_pending_customer_email(): void
    {
        Mail::fake();
        Setting::putValue('orders.send_emails_when', ['mode' => 'confirmed_only']);
        [$car, $pickup, $dropoff] = $this->seedCatalogForOrder();

        $order = Order::query()->create([
            'reference' => 'ORD-PENDING-00001',
            'car_id' => $car->id,
            'pickup_location_id' => $pickup->id,
            'dropoff_location_id' => $dropoff->id,
            'pickup_at' => now()->addDay(),
            'dropoff_at' => now()->addDays(4),
            'order_status' => OrderStatus::Pending,
            'customer_name' => 'Alex',
            'customer_email' => 'alex@example.com',
            'base_rental_cents' => 15000,
            'extras_cents' => 0,
            'fees_cents' => 0,
            'discount_cents' => 0,
            'tax_cents' => 0,
            'total_cents' => 15000,
            'currency' => 'EUR',
        ]);

        app(\App\Services\Email\OrderEmailNotifier::class)->notifyCreated($order->load('car.host'));

        Mail::assertNotQueued(TemplatedMail::class, fn (TemplatedMail $mail): bool => $mail->hasTo('alex@example.com'));
    }

    public function test_guest_house_booking_queues_admin_notification_email(): void
    {
        Mail::fake();
        Setting::putValue('email.admin_email', ['value' => 'admin@myterrabook.com']);
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

        Mail::assertQueued(TemplatedMail::class, fn (TemplatedMail $mail): bool => $mail->templateKey === 'gh_booking_new_admin'
            && $mail->hasTo('admin@myterrabook.com'));
    }

    public function test_order_status_change_notifies_host(): void
    {
        Mail::fake();
        [$car, $pickup, $dropoff, $priceType] = $this->seedCatalogForOrder();
        $host = User::factory()->host()->create(['email' => 'host@example.com']);
        $car->update(['user_id' => $host->id]);

        $order = Order::query()->create([
            'reference' => 'ORD-HOST-00001',
            'car_id' => $car->id,
            'pickup_location_id' => $pickup->id,
            'dropoff_location_id' => $dropoff->id,
            'pickup_at' => now()->addDay(),
            'dropoff_at' => now()->addDays(3),
            'order_status' => OrderStatus::Pending,
            'customer_name' => 'Alex',
            'customer_email' => 'alex@example.com',
            'base_rental_cents' => 15000,
            'extras_cents' => 0,
            'fees_cents' => 0,
            'discount_cents' => 0,
            'tax_cents' => 0,
            'total_cents' => 15000,
            'currency' => 'EUR',
        ]);

        $order->update(['order_status' => OrderStatus::Confirmed]);

        Mail::assertQueued(TemplatedMail::class, fn (TemplatedMail $mail): bool => $mail->templateKey === 'order_confirmed_host'
            && $mail->hasTo('host@example.com'));
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

        Mail::assertSent(TemplatedMail::class, fn (TemplatedMail $mail): bool => $mail->templateKey === 'customer_welcome'
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

    /**
     * @return array{0: Car, 1: Location, 2: Location, 3: PriceType}
     */
    private function seedCatalogForOrder(): array
    {
        Setting::putValue('shop.currency', ['code' => 'EUR']);
        Setting::putValue('shop.default_tax', ['basis_points' => 0]);

        $main = MainCategory::query()->firstOrCreate(['slug' => 'car'], ['name' => 'Car', 'is_active' => true]);
        $category = SubCategory::query()->create(['main_category_id' => $main->id, 'name' => 'Eco', 'is_active' => true, 'is_search_filter' => true]);
        $car = Car::query()->create([
            'sub_category_id' => $category->id,
            'name' => 'Test Vehicle',
            'units_available' => 1,
            'is_active' => true,
        ]);
        $pickup = Location::query()->create(['name' => 'P1', 'is_active' => true]);
        $dropoff = Location::query()->create(['name' => 'D1', 'is_active' => true]);
        $car->locations()->attach([
            $pickup->id => ['allows_pickup' => true, 'allows_dropoff' => true],
            $dropoff->id => ['allows_pickup' => true, 'allows_dropoff' => true],
        ]);
        $priceType = PriceType::query()->create(['name' => 'Basic', 'is_active' => true]);
        DailyFare::query()->create([
            'car_id' => $car->id,
            'price_type_id' => $priceType->id,
            'from_days' => 1,
            'to_days' => 30,
            'price_per_day_cents' => 5000,
        ]);

        return [$car, $pickup, $dropoff, $priceType];
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
