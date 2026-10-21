<?php

namespace Tests\Feature;

use App\Enums\ListingApprovalStatus;
use App\Enums\OrderStatus;
use App\Models\Car;
use App\Models\Location;
use App\Models\Order;
use App\Models\PriceType;
use App\Models\RapydPayment;
use App\Models\User;
use App\Services\Email\OrderEmailNotifier;
use App\Services\Partners\CarenBookingService;
use App\Services\RapydService;
use App\Support\PricingCurrency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class CarenBookingTest extends TestCase
{
    use RefreshDatabase;

    private string $guid = '12345678-1234-4234-8234-123456789abc';

    private array $calls = [];

    private function trip(): array
    {
        config(['services.caren.bookings_enabled' => true, 'services.caren.payment_options' => ['120' => 99]]);
        $host = User::factory()->host()->create(['partner_provider' => 'caren', 'partner_api_enabled' => true,
            'partner_api_username' => 'user@example.test', 'partner_api_password' => 'password', 'partner_api_key' => 'key',
            'partner_rental_id' => 120, 'partner_commission_rate' => 15]);
        $car = Car::factory()->create(['user_id' => $host->id, 'external_provider' => 'caren',
            'external_vehicle_id' => '120:2180', 'is_active' => true, 'listing_status' => ListingApprovalStatus::Approved, 'units_available' => 0]);
        $location = Location::create(['name' => 'Airport', 'is_active' => true, 'host_user_id' => $host->id,
            'external_provider' => 'caren', 'external_id' => '120:666']);
        $car->locations()->attach($location->id, ['allows_pickup' => true, 'allows_dropoff' => true]);

        return [$car, ['car_id' => $car->id, 'price_type_id' => PriceType::where('slug', 'caren-standard')->value('id'),
            'pickup_location_id' => $location->id, 'dropoff_location_id' => $location->id,
            'pickup_at' => now()->addDays(10)->format('Y-m-d').'T10:00:00Z', 'dropoff_at' => now()->addDays(17)->format('Y-m-d').'T10:00:00Z',
            'customer_name' => 'Test Driver', 'customer_email' => 'driver@example.test', 'customer_phone' => '+3545551234',
            'customer_date_of_birth' => '1990-01-01', 'customer_license_number' => 'TEST-LICENCE',
            'payment_method' => 'card', 'terms_accepted' => true, 'checkout_token' => (string) Str::uuid(),
            'expected_total_cents' => 75000, 'expected_currency' => PricingCurrency::shopDefault()]];
    }

    private function provider(array $overrides = []): void
    {
        $this->calls = [];
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::preventStrayRequests();
        Http::fake(function (Request $request) use ($overrides) {
            $path = str_replace('/vehicleapi/', '', parse_url($request->url(), PHP_URL_PATH));
            $this->calls[] = $path;
            if (array_key_exists($path, $overrides)) {
                if ($overrides[$path] === 'timeout') {
                    throw new ConnectionException('Test transport timeout');
                }

                return Http::response($overrides[$path]);
            }
            $currency = PricingCurrency::shopDefault();

            return Http::response(match ($path) {
                'user/login' => ['Success' => true, 'Session' => 'test-session'],
                'class/checkavailable' => ['Available' => true, 'NumberOfUnits' => 2],
                'price/getprices' => ['VehicleClassId' => 2180, 'Currency' => $currency, 'VehicleTotalPrice' => 700,
                    'LocationTotalPrice' => 50, 'ExtrasTotalPrice' => 0, 'InsurancesTotalPrice' => 0, 'TotalPrice' => 750, 'NumberOfDays' => 7],
                'payment/getoptions' => ['PaymentOptions' => [['Id' => 99]]],
                'reservation/add' => ['Guid' => $this->guid],
                'reservation/getitem' => ['Guid' => $this->guid, 'Currency' => $currency, 'TotalPrice' => 750,
                    'Vehicle' => ['ClassId' => 2180], 'BookingCode' => 'TEST123', 'Rental' => ['Id' => 120],
                    'Location' => ['PickupId' => 666, 'DropoffId' => 666],
                    'DateFrom' => now()->addDays(10)->format('Y-m-d').'T10:00:00',
                    'DateTo' => now()->addDays(17)->format('Y-m-d').'T10:00:00'],
                'payment/addprepaid' => ['Guid' => $this->guid, 'Currency' => $currency, 'Amount' => 112.5],
                'reservation/confirm', 'reservation/cancel' => ['Success' => true],
                default => throw new \RuntimeException('Unexpected endpoint: '.$path),
            });
        });
    }

    private function booked(array $input): Order
    {
        $response = $this->postJson('/api/orders', $input)->assertCreated()->assertJsonPath('data.partner_sync_state', 'reserved');

        return Order::findOrFail($response->json('data.id'));
    }

    private function paid(Order $order): RapydPayment
    {
        return RapydPayment::create(['order_id' => $order->id, 'host_id' => $order->car->user_id,
            'checkout_id' => 'checkout-test', 'payment_id' => 'payment-test', 'total_price' => 750,
            'platform_fee' => 112.5, 'cash_due_on_arrival' => 637.5, 'currency' => $order->currency,
            'status' => 'paid', 'paid_at' => now(), 'metadata' => ['order_type' => 'car']]);
    }

    public function test_disabled_or_unconfigured_bookings_make_no_provider_writes(): void
    {
        [$car, $input] = $this->trip();
        Http::fake();
        config(['services.caren.bookings_enabled' => false]);
        $this->postJson('/api/orders', $input)->assertUnprocessable();
        config(['services.caren.bookings_enabled' => true, 'services.caren.payment_options' => []]);
        $this->postJson('/api/orders', $input)->assertUnprocessable();
        Http::assertNothingSent();
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_booking_checks_availability_price_and_preserves_one_reservation_on_retries(): void
    {
        [$car, $input] = $this->trip();
        $this->provider();
        $order = $this->booked($input);
        $this->postJson('/api/orders', $input)->assertCreated()->assertJsonPath('data.id', $order->id);
        $this->assertSame(1, count(array_filter($this->calls, fn ($path) => $path === 'reservation/add')));
        $this->assertSame(OrderStatus::Pending, $order->order_status);
        $this->assertSame(75000, $order->total_cents);
        $this->assertSame(75000, (int) $order->lineItems()->sum('amount_cents'));
        Http::assertSent(fn (Request $r) => str_ends_with($r->url(), '/reservation/add')
            && $r['confirmReservation'] === false && $r['Reference'] === $order->reference
            && $r['Drivers'][0][2] === 'TEST-LICENCE' && $r['Customer']['Email'] === 'driver@example.test');
        $input['customer_email'] = 'changed@example.test';
        $this->postJson('/api/orders', $input)->assertUnprocessable();
        $this->assertDatabaseCount('orders', 1);
        $this->assertNotContains('user/logout', $this->calls);
    }

    public function test_unavailable_and_changed_price_do_not_create_reservations(): void
    {
        [$car, $input] = $this->trip();
        $this->provider(['class/checkavailable' => ['Available' => false, 'NumberOfUnits' => 0]]);
        $this->postJson('/api/orders', $input)->assertUnprocessable();
        $this->assertNotContains('reservation/add', $this->calls);
        $this->provider();
        $input['expected_total_cents'] = 1;
        $this->postJson('/api/orders', $input)->assertUnprocessable();
        $this->assertNotContains('reservation/add', $this->calls);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_ambiguous_creation_is_recorded_and_never_blindly_retried(): void
    {
        [$car, $input] = $this->trip();
        $this->provider(['reservation/add' => 'timeout']);
        $this->postJson('/api/orders', $input)->assertUnprocessable();
        $this->assertSame('create_unknown', Order::sole()->partner_sync_state);
        $this->postJson('/api/orders', $input)->assertUnprocessable();
        $this->assertSame(1, count(array_filter($this->calls, fn ($path) => $path === 'reservation/add')));
        $this->assertDatabaseCount('rapyd_payments', 0);
    }

    public function test_verified_payment_is_credited_once_then_confirms_caren_and_local_order(): void
    {
        [$car, $input] = $this->trip();
        $this->provider();
        $order = $this->booked($input);
        $payment = $this->paid($order);
        $this->mock(OrderEmailNotifier::class)->shouldReceive('notifyCreated')->once();
        $service = app(CarenBookingService::class);
        $service->confirmPaid($order, $payment);
        $service->confirmPaid($order, $payment);
        $this->assertSame(OrderStatus::Confirmed, $order->fresh()->order_status);
        $this->assertSame('confirmed', $order->fresh()->partner_sync_state);
        $this->assertSame(1, count(array_filter($this->calls, fn ($path) => $path === 'payment/addprepaid')));
        Http::assertSent(fn (Request $r) => str_ends_with($r->url(), '/payment/addprepaid') && $r['Amount'] === 112.5);
        Http::assertSent(fn (Request $r) => str_ends_with($r->url(), '/reservation/confirm') && $r['CheckPayment'] === true);
    }

    public function test_ambiguous_prepaid_credit_keeps_order_pending_and_is_not_repeated(): void
    {
        [$car, $input] = $this->trip();
        $this->provider(['payment/addprepaid' => 'timeout']);
        $order = $this->booked($input);
        $payment = $this->paid($order);
        app(CarenBookingService::class)->confirmPaid($order, $payment);
        app(CarenBookingService::class)->confirmPaid($order, $payment);
        $this->assertSame('payment_unknown', $order->fresh()->partner_sync_state);
        $this->assertSame(OrderStatus::Pending, $order->fresh()->order_status);
        $this->assertSame(1, count(array_filter($this->calls, fn ($path) => $path === 'payment/addprepaid')));
        $this->assertNotContains('reservation/confirm', $this->calls);
    }

    public function test_failed_confirmation_retries_without_duplicating_credit(): void
    {
        [$car, $input] = $this->trip();
        $this->provider(['reservation/confirm' => ['Success' => false, 'Code' => 10000]]);
        $order = $this->booked($input);
        $payment = $this->paid($order);
        app(CarenBookingService::class)->confirmPaid($order, $payment);
        $this->assertSame('confirm_pending', $order->fresh()->partner_sync_state);
        $this->provider();
        $this->mock(OrderEmailNotifier::class)->shouldReceive('notifyCreated')->once();
        $this->artisan('caren:reconcile-orders')->assertSuccessful();
        $this->assertSame('confirmed', $order->fresh()->partner_sync_state);
        $this->assertNotContains('payment/addprepaid', $this->calls);
    }

    public function test_expired_unpaid_reservation_is_cancelled_but_paid_one_is_not(): void
    {
        [$car, $input] = $this->trip();
        $this->provider();
        $order = $this->booked($input);
        $order->update(['payment_lock_expires_at' => now()->subMinute()]);
        $this->artisan('caren:reconcile-orders')->assertSuccessful();
        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->order_status);
        $this->assertContains('reservation/cancel', $this->calls);
    }

    public function test_failed_cancellation_does_not_claim_local_cancellation(): void
    {
        [$car, $input] = $this->trip();
        $this->provider(['reservation/cancel' => ['Success' => false, 'Code' => 10000]]);
        $order = $this->booked($input);
        try {
            $order->transitionOrderStatus(OrderStatus::Cancelled);
            $this->fail('Expected cancellation failure.');
        } catch (\InvalidArgumentException) {
            $this->assertSame(OrderStatus::Pending, $order->fresh()->order_status);
            $this->assertSame('cancel_pending', $order->fresh()->partner_sync_state);
        }
        $this->provider();
        $this->artisan('caren:reconcile-orders')->assertSuccessful();
        $this->assertSame('cancelled', $order->fresh()->partner_sync_state);
    }

    public function test_payment_uses_server_amount_and_requires_private_checkout_token(): void
    {
        [$car, $input] = $this->trip();
        $this->provider();
        $order = $this->booked($input);
        $rapyd = $this->mock(RapydService::class);
        $rapyd->shouldReceive('isConfigured')->once()->andReturn(true);
        $rapyd->shouldReceive('createCheckoutPage')->once()->withArgs(fn ($data) => $data['amount'] === round(750 * .15, RapydService::decimalsFor($order->currency))
            && $data['currency'] === $order->currency)->andReturn(['checkout_id' => 'checkout-test', 'redirect_url' => 'https://example.test/checkout']);
        $payload = ['order_id' => $order->id, 'order_type' => 'car', 'total_price' => .01, 'currency' => 'USD', 'host_id' => 9999];
        $this->postJson('/api/rapyd/initiate-checkout', $payload)->assertForbidden();
        $payload['confirmation_token'] = $order->confirmation_token;
        $this->postJson('/api/rapyd/initiate-checkout', $payload)->assertOk()->assertJsonPath('total_price', 750);
        $this->postJson('/api/rapyd/initiate-checkout', $payload)->assertOk()->assertJsonPath('checkout_id', 'checkout-test');
        $this->assertDatabaseCount('rapyd_payments', 1);
    }

    public function test_duplicate_verified_webhooks_do_not_repeat_prepaid_credit_or_confirmation(): void
    {
        [$car, $input] = $this->trip();
        $this->provider();
        $order = $this->booked($input);
        $payment = $this->paid($order);
        $payment->update(['status' => 'pending']);
        $this->mock(RapydService::class)->shouldReceive('verifyWebhook')->twice()->andReturn(true);
        $this->mock(OrderEmailNotifier::class)->shouldReceive('notifyCreated')->once();
        $event = ['type' => 'CHECKOUT_COMPLETED', 'data' => ['id' => 'checkout-test', 'payment' => 'payment-test']];
        $this->postJson('/api/rapyd/webhook', $event)->assertOk();
        $this->postJson('/api/rapyd/webhook', $event)->assertOk();
        $this->assertSame('confirmed', $order->fresh()->partner_sync_state);
        $this->assertSame(1, count(array_filter($this->calls, fn ($path) => $path === 'payment/addprepaid')));
        $this->assertSame(1, count(array_filter($this->calls, fn ($path) => $path === 'reservation/confirm')));
    }

    public function test_paid_but_unconfirmed_return_never_exposes_a_success_confirmation(): void
    {
        [$car, $input] = $this->trip();
        $this->provider(['reservation/confirm' => ['Success' => false, 'Code' => 10000]]);
        $order = $this->booked($input);
        $payment = $this->paid($order);
        $this->mock(RapydService::class)->shouldReceive('getCheckoutStatus')->once()->andReturn(['payment' => ['paid' => true]]);
        $this->getJson('/api/rapyd/order-status?order_type=car&order_id='.$order->id)->assertOk()
            ->assertJsonPath('status', 'awaiting_partner_confirmation')->assertJsonPath('confirmation_token', null);
        $this->assertSame(OrderStatus::Pending, $order->fresh()->order_status);
        $this->assertSame('paid', $payment->fresh()->status);
    }

    public function test_expiration_does_not_cancel_an_order_with_a_verified_payment(): void
    {
        [$car, $input] = $this->trip();
        $this->provider();
        $order = $this->booked($input);
        $order->update(['payment_lock_expires_at' => now()->subMinute()]);
        $this->paid($order);
        app(CarenBookingService::class)->cancel($order, onlyIfExpiredAndUnpaid: true);
        $this->assertSame('reserved', $order->fresh()->partner_sync_state);
        $this->assertNotContains('reservation/cancel', $this->calls);
    }

    public function test_ambiguous_payment_checkout_is_not_recreated(): void
    {
        [$car, $input] = $this->trip();
        $this->provider();
        $order = $this->booked($input);
        $rapyd = $this->mock(RapydService::class);
        $rapyd->shouldReceive('isConfigured')->once()->andReturn(true);
        $rapyd->shouldReceive('createCheckoutPage')->once()->andThrow(new \RuntimeException('Timeout'));
        $payload = ['order_id' => $order->id, 'order_type' => 'car', 'total_price' => 750, 'confirmation_token' => $order->confirmation_token];
        $this->postJson('/api/rapyd/initiate-checkout', $payload)->assertUnprocessable();
        $this->postJson('/api/rapyd/initiate-checkout', $payload)->assertConflict();
        $this->assertSame('checkout_unknown', $order->fresh()->partner_sync_state);
    }

    public function test_wrong_reservation_details_are_cancelled_before_starting_payment(): void
    {
        [$car, $input] = $this->trip();
        $this->provider(['reservation/getitem' => ['Guid' => $this->guid, 'Currency' => PricingCurrency::shopDefault(), 'TotalPrice' => 1000]]);
        $this->postJson('/api/orders', $input)->assertUnprocessable();
        $this->assertSame('cancelled', Order::sole()->partner_sync_state);
        $this->assertContains('reservation/cancel', $this->calls);
        $this->assertDatabaseCount('rapyd_payments', 0);
    }
}
