<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Filament\Pages\LoggedInUsers;
use App\Models\Car;
use App\Models\Location;
use App\Models\MainCategory;
use App\Models\Order;
use App\Models\PriceType;
use App\Models\SubCategory;
use App\Models\User;
use App\Support\BookingConfirmationUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

class RentalTermsPdfAndActiveLoginsTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_snapshots_host_terms_and_customer_can_open_the_pdf(): void
    {
        $host = User::factory()->host()->create([
            'rental_terms_html' => '<p>Fuel is not included.</p>',
        ]);
        $customer = User::factory()->customer()->create();
        $order = $this->orderFor($host, $customer);

        $this->assertStringContainsString('Fuel is not included.', (string) $order->rental_terms_html);

        $host->update(['rental_terms_html' => '<p>Changed later.</p>']);

        $this->assertStringContainsString('Fuel is not included.', (string) $order->fresh()->rental_terms_html);

        $this->getJson('/api/booking-confirmation/'.$order->confirmation_token)
            ->assertOk()
            ->assertJsonPath('data.has_rental_terms', true);

        $pdf = $this->get('/api/booking-confirmation/'.$order->confirmation_token.'/rental-terms.pdf');
        $pdf->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $pdf->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $pdf->getContent());

        Sanctum::actingAs($customer);
        $this->get('/api/me/orders/'.$order->id.'/rental-terms.pdf')->assertOk();

        Sanctum::actingAs(User::factory()->customer()->create());
        $this->get('/api/me/orders/'.$order->id.'/rental-terms.pdf')->assertForbidden();
    }

    public function test_booking_without_terms_has_no_pdf(): void
    {
        $host = User::factory()->host()->create();
        $order = $this->orderFor($host, User::factory()->customer()->create());

        $this->assertNull($order->rental_terms_html);
        $this->get('/api/booking-confirmation/'.$order->confirmation_token.'/rental-terms.pdf')
            ->assertNotFound();
    }

    public function test_admin_can_log_out_an_active_user(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();
        $customer->createToken('auth-token');
        DB::table('sessions')->insert([
            'id' => 'session-customer',
            'user_id' => $customer->id,
            'payload' => 'test',
            'last_activity' => now()->getTimestamp(),
        ]);

        $this->actingAs($admin);

        Livewire::test(LoggedInUsers::class)
            ->assertSee($customer->email)
            ->call('logout', $customer->id)
            ->assertDontSee($customer->email);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $customer->id,
        ]);
        $this->assertDatabaseMissing('sessions', [
            'id' => 'session-customer',
        ]);
        $this->assertSame(UserRole::Customer, $customer->fresh()->role);
    }

    private function orderFor(User $host, User $customer): Order
    {
        $main = MainCategory::query()->firstOrCreate(['slug' => 'car'], ['name' => 'Car', 'is_active' => true]);
        $category = SubCategory::query()->create([
            'main_category_id' => $main->id,
            'name' => 'Eco',
            'is_active' => true,
            'is_search_filter' => true,
        ]);
        $car = Car::query()->create([
            'user_id' => $host->id,
            'sub_category_id' => $category->id,
            'name' => 'Test Vehicle',
            'units_available' => 1,
            'is_active' => true,
        ]);
        $pickup = Location::query()->create(['name' => 'Keflavik', 'is_active' => true]);
        $dropoff = Location::query()->create(['name' => 'Reykjavik', 'is_active' => true]);
        $priceType = PriceType::query()->create(['name' => 'Basic', 'is_active' => true]);
        $token = BookingConfirmationUrl::generateToken();

        return Order::query()->create([
            'reference' => 'ORD-TERMS-1',
            'confirmation_token' => $token,
            'confirmation_url' => BookingConfirmationUrl::forToken($token),
            'user_id' => $customer->id,
            'car_id' => $car->id,
            'price_type_id' => $priceType->id,
            'pickup_location_id' => $pickup->id,
            'dropoff_location_id' => $dropoff->id,
            'pickup_at' => now()->addDay(),
            'dropoff_at' => now()->addDays(4),
            'customer_name' => 'Alex Guest',
            'customer_email' => $customer->email,
            'order_status' => OrderStatus::Confirmed,
            'base_rental_cents' => 15000,
            'extras_cents' => 0,
            'fees_cents' => 0,
            'discount_cents' => 0,
            'tax_cents' => 0,
            'total_cents' => 15000,
            'currency' => 'EUR',
        ]);
    }
}
