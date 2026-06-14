<?php

namespace Tests\Unit;

use App\Enums\GuestHouseStatus;
use App\Enums\GuestHouseType;
use App\Models\Car;
use App\Models\GuestHouse;
use App\Models\MainCategory;
use App\Models\SubCategory;
use App\Models\User;
use App\Services\ListingSeoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListingSeoServiceTest extends TestCase
{
    use RefreshDatabase;
    public function test_car_meta_title_uses_campervan_category_label(): void
    {
        $main = MainCategory::query()->firstOrCreate(['slug' => 'campervan'], ['name' => 'Campervan', 'is_active' => true]);
        $sub = SubCategory::query()->create(['main_category_id' => $main->id, 'name' => 'Small', 'is_active' => true, 'is_search_filter' => true]);
        $car = Car::factory()->create([
            'user_id' => User::factory()->host()->create()->id,
            'sub_category_id' => $sub->id,
            'name' => 'Ring Road Camper',
        ]);

        $service = new ListingSeoService;

        $this->assertSame('Ring Road Camper, Campervan in Iceland', $service->carMetaTitle($car->load('subCategory.mainCategory')));
    }

    public function test_car_meta_description_strips_html_and_truncates(): void
    {
        $car = new Car([
            'description' => '<p>'.str_repeat('A', 200).'</p>',
        ]);

        $service = new ListingSeoService;
        $description = $service->carMetaDescription($car);

        $this->assertStringNotContainsString('<p>', $description);
        $this->assertLessThanOrEqual(160, mb_strlen($description));
        $this->assertStringEndsWith('…', $description);
    }

    public function test_guesthouse_meta_description_prefers_short_description(): void
    {
        $house = new GuestHouse([
            'short_description' => 'Short summary.',
            'description' => 'Longer full description.',
        ]);

        $service = new ListingSeoService;

        $this->assertSame('Short summary.', $service->guestHouseMetaDescription($house));
    }

    public function test_sync_car_sets_og_image_from_main_image_path(): void
    {
        $car = Car::factory()->create([
            'user_id' => User::factory()->host()->create()->id,
            'name' => 'Test Car',
            'main_image_path' => 'cars/example.jpg',
            'meta_title' => null,
            'meta_description' => null,
            'og_image' => null,
        ]);

        (new ListingSeoService)->syncCar($car);

        $car->refresh();

        $this->assertSame('cars/example.jpg', $car->og_image);
        $this->assertSame('Test Car, Listing in Iceland', $car->meta_title);
    }

    public function test_sync_guesthouse_sets_og_image_from_thumbnail(): void
    {
        $host = User::factory()->host()->create();
        $house = GuestHouse::query()->create([
            'user_id' => $host->id,
            'name' => 'Harbour View',
            'slug' => 'harbour-view',
            'type' => GuestHouseType::Apartment,
            'status' => GuestHouseStatus::Draft,
            'city' => 'Reykjavík',
            'max_guests' => 2,
            'bedrooms' => 1,
            'bathrooms' => 1,
            'beds' => 1,
            'min_nights' => 1,
            'base_price_per_night' => 10000,
            'thumbnail' => 'guesthouses/thumbnails/example.jpg',
            'short_description' => 'Waterfront stay.',
        ]);

        (new ListingSeoService)->syncGuestHouse($house);

        $house->refresh();

        $this->assertSame('guesthouses/thumbnails/example.jpg', $house->og_image);
        $this->assertSame('Harbour View, Guesthouse in Iceland', $house->meta_title);
        $this->assertSame('Waterfront stay.', $house->meta_description);
    }
}
