<?php

namespace Tests\Feature;

use App\Enums\GuestHouseStatus;
use App\Enums\GuestHouseType;
use App\Models\Car;
use App\Models\GuestHouse;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFigmaScreensTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return list<array{0: string, 1: string, 2: list<string>}>
     */
    public static function figmaAdminScreens(): array
    {
        return [
            ['Dashboard', '/admin', ['Home', 'Car reservations', 'Vehicles', 'Settings']],
            ['Calendar', '/admin/impact-rent/orders-calendar', ['Calendar', 'New booking', 'Month', 'Guest houses']],
            ['Car reservations', '/admin/impact-rent/orders', ['Car reservations', 'New booking', 'Calendar view']],
            ['Guest house bookings', '/admin/guest-houses/guest-house-bookings', ['Guest house bookings']],
            ['Change requests', '/admin/booking-change-requests', ['Change requests']],
            ['Guest houses · Properties', '/admin/guest-houses/guest-houses', ['Guest houses', 'Properties', 'Amenities']],
            ['Guest houses · Amenities', '/admin/guest-houses/guest-house-amenities', ['Amenities', 'Properties', 'New amenity']],
            ['Vehicles · All vehicles', '/admin/impact-rent/cars', ['Vehicles', 'All vehicles', 'Add vehicle', 'Fleet units']],
            ['Vehicles · Fleet units', '/admin/impact-rent/car-units', ['Fleet units', 'All vehicles']],
            ['Vehicles · Categories', '/admin/impact-rent/main-categories', ['Categories', 'Features']],
            ['Vehicles · Features', '/admin/impact-rent/characteristics', ['Features', 'Locations']],
            ['Vehicles · Locations', '/admin/impact-rent/locations', ['Locations', 'Rental conditions']],
            ['Vehicles · Rental conditions', '/admin/impact-rent/rental-conditions', ['Rental conditions']],
            ['Pricing · Pricing rules', '/admin/impact-rent/special-prices', ['Pricing rules', 'Base prices']],
            ['Pricing · Out-of-hours fees', '/admin/impact-rent/out-of-hours-fees', ['Out-of-hours fees']],
            ['Pricing · Location fees', '/admin/impact-rent/location-fees', ['Location fees']],
            ['Pricing · Add-ons', '/admin/impact-rent/rental-options', ['Add-ons']],
            ['Pricing · Discount codes', '/admin/impact-rent/coupons', ['Discount codes']],
            ['Pricing · Tax rates & Types of price', '/admin/impact-rent/tax-rates', ['Tax rates', 'Types of price', 'New tax rate']],
            ['Pricing · Booking restrictions', '/admin/impact-rent/booking-restrictions', ['Booking restrictions']],
            ['Pricing · Base prices', '/admin/impact-rent/daily-fares', ['Base prices']],
            ['Pricing · Fares overview', '/admin/impact-rent/fares-overview', ['Fares overview']],
            ['Pricing · Extra hour fares', '/admin/impact-rent/extra-hour-fares', ['Extra hour']],
            ['Pricing · Hourly fares', '/admin/impact-rent/hourly-fares', ['Hourly']],
            ['Hosts', '/admin/registered-hosts', ['Hosts']],
            ['Clients', '/admin/registered-clients', ['Clients']],
            ['Listing approvals', '/admin/listing-approvals', ['Listing approvals']],
            ['Listing reviews', '/admin/listing-reviews', ['Listing reviews']],
            ['Site content · Homepage', '/admin/site-content?tab=home', ['Site content', 'Homepage']],
            ['Site content · Global chrome', '/admin/site-content?tab=global', ['Site content', 'Global chrome']],
            ['Site content · Terms', '/admin/site-content?tab=terms', ['Site content', 'Terms']],
            ['Good to Know · Posts', '/admin/blog-posts', ['Good to Know']],
            ['Newsletter · Subscribers', '/admin/newsletter-subscribers', ['Newsletter', 'Subscribers']],
            ['Email · Templates', '/admin/email-templates', ['Email', 'Templates', 'New email template']],
            ['Email · SMTP settings', '/admin/email-settings', ['SMTP settings']],
            ['Email · Send test', '/admin/email-testing', ['Send test']],
            ['Email · Log', '/admin/email-logs', ['Log']],
            ['Analytics · Reports', '/admin/impact-rent/reports', ['Reports']],
            ['Analytics · Statistics', '/admin/impact-rent/tracking-statistics', ['Statistics']],
            ['Analytics · Tracking campaigns', '/admin/impact-rent/tracking-campaigns', ['Tracking campaigns']],
            ['Settings · Shop & rentals', '/admin/global-configuration?tab=shop', ['Shop & rentals', 'Website status', 'Booking rules']],
            ['Settings · Prices & payments', '/admin/global-configuration?tab=prices', ['Prices & payments']],
            ['Settings · Views & layout', '/admin/global-configuration?tab=views', ['Views & layout']],
            ['Settings · Orders & company', '/admin/global-configuration?tab=orders', ['Orders & company']],
            ['Settings · Payment methods', '/admin/payment-methods', ['Payment methods']],
            ['Settings · Custom fields', '/admin/custom-fields', ['Custom fields']],
            ['Settings · Users', '/admin/users', ['Users']],
            ['Settings · Logged-in users', '/admin/logged-in-users', ['Logged-in users']],
            ['Settings · Profile', '/admin/profile', ['Profile']],
            ['Settings · Conditional texts', '/admin/impact-rent/conditional-texts', ['Conditional texts']],
            ['Settings · Backups', '/admin/impact-rent/backups', ['Backups']],
        ];
    }

    /**
     * @dataProvider figmaAdminScreens
     *
     * @param  list<string>  $needles
     */
    public function test_figma_admin_screen_loads_with_redesign_chrome(string $label, string $path, array $needles): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $response = $this->get($path);
        $response->assertOk("{$label} ({$path}) should load");
        $response->assertSee('tb-redesign', false);
        $response->assertSee('tb-rail', false);

        foreach ($needles as $needle) {
            $response->assertSee($needle);
        }
    }

    public function test_figma_edit_screens_load(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $car = Car::factory()->create(['name' => 'Audit Camper']);
        $order = Order::factory()->create(['car_id' => $car->id]);

        $this->get('/admin/impact-rent/cars/'.$car->id.'/edit')
            ->assertOk()
            ->assertSee('Audit Camper')
            ->assertSee('Identity')
            ->assertSee('Notes & extras')
            ->assertSee('tb-redesign', false);

        $this->get('/admin/impact-rent/orders/'.$order->id.'/edit')
            ->assertOk()
            ->assertSee($order->reference)
            ->assertSee('Pickup date')
            ->assertSee('Price breakdown')
            ->assertSee('Vehicle availability')
            ->assertSee('tb-redesign', false);

        $house = GuestHouse::query()->create([
            'name' => 'Audit Cottage',
            'slug' => 'audit-cottage',
            'type' => GuestHouseType::Cottage,
            'status' => GuestHouseStatus::Active,
            'max_guests' => 4,
            'min_nights' => 1,
            'base_price_per_night' => 12000,
            'cleaning_fee' => 0,
        ]);

        $this->get('/admin/guest-houses/guest-houses/'.$house->id.'/edit')
            ->assertOk()
            ->assertSee('Audit Cottage')
            ->assertSee('Listing card')
            ->assertSee('Address & map')
            ->assertSee('tb-redesign', false);
    }
}
