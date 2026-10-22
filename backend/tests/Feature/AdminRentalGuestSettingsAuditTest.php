<?php

namespace Tests\Feature;

use App\Enums\DriveType;
use App\Enums\GuestHouseBookingStatus;
use App\Enums\GuestHouseStatus;
use App\Enums\GuestHouseType;
use App\Filament\GuestHouse\Resources\GuestHouseAmenityResource\Pages\ManageGuestHouseAmenities;
use App\Filament\Resources\BookingRestrictions\Pages\CreateBookingRestriction;
use App\Filament\Resources\Cars\Pages\CreateCar;
use App\Models\BookingRestriction;
use App\Models\Car;
use App\Models\GuestHouse;
use App\Models\GuestHouseAmenity;
use App\Models\GuestHouseBooking;
use App\Models\MainCategory;
use App\Models\SubCategory;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminRentalGuestSettingsAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_and_guest_reviews_shortcuts_redirect_without_crashing(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin/moderation-settings')
            ->assertRedirect('/admin/global-configuration');

        $this->actingAs($admin)
            ->get('/admin/guest-houses/guest-reviews')
            ->assertRedirect('/admin/listing-reviews');
    }

    public function test_restriction_weekday_toggles_are_saved(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(CreateBookingRestriction::class)
            ->fillForm([
                'name' => 'Summer close',
                'date_from' => now()->toDateString(),
                'date_to' => now()->addMonth()->toDateString(),
                'cta_enabled' => true,
                'ctd_enabled' => false,
                'forced_pickup_weekday' => 0,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $restriction = BookingRestriction::query()->where('name', 'Summer close')->sole();

        $this->assertSame([0, 1, 2, 3, 4, 5, 6], $restriction->cta_weekdays);
        $this->assertSame([], $restriction->ctd_weekdays);
        $this->assertSame([0], $restriction->forced_pickup_weekdays);
    }

    public function test_admin_create_vehicle_creates_fleet_units(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $main = MainCategory::query()->firstOrCreate(['slug' => 'car'], ['name' => 'Car', 'is_active' => true]);
        $category = SubCategory::query()->create([
            'main_category_id' => $main->id,
            'name' => 'Audit SUV',
            'slug' => 'audit-suv',
            'is_active' => true,
        ]);

        Livewire::test(CreateCar::class)
            ->fillForm([
                'name' => 'Admin Van',
                'is_active' => true,
                'units_available' => 3,
                'transmission' => 'automatic',
                'fuel_type' => 'diesel',
                'drive_type' => DriveType::Awd->value,
                'seats' => 5,
                'bags' => 2,
                'sub_category_id' => $category->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $car = Car::query()->where('name', 'Admin Van')->sole();
        $this->assertSame(3, $car->carUnits()->count());
    }

    public function test_guest_house_amenities_can_be_edited_and_deleted(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $amenity = GuestHouseAmenity::query()->create([
            'name' => 'Parking',
            'group' => 'outdoor',
        ]);

        Livewire::test(ManageGuestHouseAmenities::class)
            ->callTableAction(EditAction::class, $amenity, data: [
                'name' => 'Secure parking',
                'group' => 'outdoor',
            ])
            ->assertHasNoTableActionErrors();

        $this->assertSame('Secure parking', $amenity->fresh()->name);

        Livewire::test(ManageGuestHouseAmenities::class)
            ->callTableAction(DeleteAction::class, $amenity);

        $this->assertDatabaseMissing('guest_house_amenities', ['id' => $amenity->id]);
    }

    public function test_admin_session_can_open_guest_house_booking_pdf(): void
    {
        $admin = User::factory()->admin()->create();
        $house = GuestHouse::query()->create([
            'name' => 'PDF House',
            'slug' => 'pdf-house',
            'type' => GuestHouseType::Apartment,
            'status' => GuestHouseStatus::Active,
            'max_guests' => 4,
            'min_nights' => 1,
            'base_price_per_night' => 8000,
            'cleaning_fee' => 0,
        ]);
        $booking = GuestHouseBooking::query()->create([
            'guest_house_id' => $house->id,
            'status' => GuestHouseBookingStatus::Confirmed,
            'guest_name' => 'Pat Guest',
            'guest_email' => 'pat@example.test',
            'check_in' => now()->addDay()->toDateString(),
            'check_out' => now()->addDays(3)->toDateString(),
            'guests_count' => 2,
            'base_total' => 16000,
            'total_amount' => 16000,
            'confirmed_at' => now(),
        ]);

        $this->get(route('admin.guest-house-bookings.contract-pdf', ['booking' => $booking->id]))
            ->assertRedirect();

        $response = $this->actingAs($admin)
            ->get(route('admin.guest-house-bookings.contract-pdf', ['booking' => $booking->id]));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('Content-Type'));
    }
}
