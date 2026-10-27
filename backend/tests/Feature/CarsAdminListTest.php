<?php

namespace Tests\Feature;

use App\Filament\Resources\Cars\Pages\ListCars;
use App\Models\Car;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CarsAdminListTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_search_by_host_and_filter_active_vehicles(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $host = User::factory()->host()->create(['name' => 'Northern Fleet']);
        $active = Car::factory()->create(['user_id' => $host->id, 'is_active' => true]);
        $inactive = Car::factory()->create(['is_active' => false]);

        Livewire::test(ListCars::class)
            ->set('activeTab', 'all')
            ->searchTable('Northern Fleet')
            ->assertCanSeeTableRecords([$active])
            ->assertCanNotSeeTableRecords([$inactive]);

        Livewire::test(ListCars::class)
            ->set('activeTab', 'all')
            ->filterTable('is_active', false)
            ->assertCanSeeTableRecords([$inactive])
            ->assertCanNotSeeTableRecords([$active]);

        Livewire::test(ListCars::class)
            ->set('activeTab', 'active')
            ->assertCanSeeTableRecords([$active])
            ->assertCanNotSeeTableRecords([$inactive]);
    }

    public function test_fleet_list_shows_existing_cars_and_opens_the_editor(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $car = Car::factory()->create(['name' => 'Reykjavik Compact', 'is_active' => true]);

        $this->get('/admin/impact-rent/cars')
            ->assertOk()
            ->assertSee('Fleet')
            ->assertSee('Reykjavik Compact')
            ->assertSee('Category')
            ->assertSee('Drivetrain')
            ->assertSee('Vehicles')
            ->assertSee('Pricing')
            ->assertSee('Bookings')
            ->assertSee('Finance')
            ->assertSee('Tax Rates')
            ->assertSee('Base prices')
            ->assertSee('Reservations');

        $this->get('/admin/impact-rent/cars/'.$car->id.'/edit')
            ->assertOk()
            ->assertSee('Reykjavik Compact')
            ->assertSee('Vehicle details')
            ->assertSee('Identity')
            ->assertSee('Specs');

        $this->get('/admin/impact-rent/cars/create')
            ->assertOk()
            ->assertSee('Add vehicle')
            ->assertSee('Identity')
            ->assertSee('Specs')
            ->assertSee('Features')
            ->assertSee('Photos')
            ->assertSee('Details')
            ->assertSee('Continue');
    }
}
