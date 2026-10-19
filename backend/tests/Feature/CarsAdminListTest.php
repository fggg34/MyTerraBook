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
}
