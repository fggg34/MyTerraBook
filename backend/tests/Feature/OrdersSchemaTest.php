<?php

namespace Tests\Feature;

use App\Models\Backup;
use App\Models\Car;
use App\Models\Category;
use App\Models\ConditionalText;
use App\Models\CustomField;
use App\Models\Location;
use App\Models\Order;
use App\Models\OrderLineItem;
use App\Models\OrderPayment;
use App\Models\OrderRentalOption;
use App\Models\PaymentMethod;
use App\Models\RentalOption;
use App\Models\Setting;
use App\Models\TrackingCampaign;
use App\Models\TrackingEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrdersSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_graph_and_settings(): void
    {
        $category = Category::query()->create(['name' => 'X', 'is_active' => true]);
        $car = Car::query()->create([
            'category_id' => $category->id,
            'name' => 'Car',
            'units_available' => 1,
            'is_active' => true,
        ]);
        $pickup = Location::query()->create(['name' => 'P', 'is_active' => true]);
        $dropoff = Location::query()->create(['name' => 'D', 'is_active' => true]);
        $option = RentalOption::query()->create([
            'name' => 'GPS',
            'cost_cents' => 500,
            'is_daily_cost' => true,
            'has_quantity' => false,
            'is_mandatory' => false,
            'is_active' => true,
        ]);

        PaymentMethod::query()->create([
            'code' => 'offline_card',
            'name' => 'Offline Card',
            'is_enabled' => true,
        ]);

        CustomField::query()->create([
            'field_key' => 'flight_number',
            'label' => 'Flight #',
            'type' => 'text',
            'sort_order' => 1,
        ]);

        ConditionalText::query()->create([
            'name' => 'Airport pickup',
            'content' => '<p>Meet at desk</p>',
            'conditions' => ['pickup_location_slug' => $pickup->slug],
            'templates' => ['customer_email'],
        ]);

        $order = Order::query()->create([
            'car_id' => $car->id,
            'pickup_location_id' => $pickup->id,
            'dropoff_location_id' => $dropoff->id,
            'pickup_at' => now()->addDay(),
            'dropoff_at' => now()->addDays(3),
            'customer_name' => 'Jane',
            'customer_email' => 'jane@example.com',
            'base_rental_cents' => 10000,
            'total_cents' => 10500,
        ]);

        OrderLineItem::query()->create([
            'order_id' => $order->id,
            'kind' => 'base_rental',
            'label' => 'Rental',
            'amount_cents' => 10000,
        ]);

        OrderRentalOption::query()->create([
            'order_id' => $order->id,
            'rental_option_id' => $option->id,
            'quantity' => 1,
            'unit_price_cents' => 500,
            'total_cents' => 500,
        ]);

        OrderPayment::query()->create([
            'order_id' => $order->id,
            'amount_cents' => 10500,
            'method_code' => 'offline_card',
            'status' => 'completed',
            'processed_at' => now(),
        ]);

        $campaign = TrackingCampaign::query()->create([
            'name' => 'Spring',
            'slug' => 'spring-2026',
        ]);
        TrackingEvent::query()->create([
            'tracking_campaign_id' => $campaign->id,
            'event_type' => 'page_view',
        ]);

        Setting::putValue('shop.currency', ['code' => 'EUR']);

        Backup::query()->create([
            'path' => 'backups/full-1.zip',
            'filename' => 'full-1.zip',
            'size_bytes' => 1024,
            'backup_type' => 'full',
        ]);

        $this->assertNotEmpty($order->fresh()->reference);
        $this->assertSame('EUR', Setting::getValue('shop.currency')['code']);
        $this->assertCount(1, $order->fresh()->lineItems);
        $this->assertCount(1, $order->fresh()->payments);
    }
}
