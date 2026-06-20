<?php

namespace Tests\Feature;

use App\Enums\GuestHouseStatus;
use App\Enums\GuestHouseType;
use App\Models\GuestHouse;
use App\Models\GuestHouseAmenity;
use App\Models\GuestHouseImage;
use App\Models\GuestHouseRoomDetail;
use App\Models\GuestHouseSeasonalPrice;
use App\Services\GuestHouseDuplicationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestHouseDuplicationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_copies_relations_and_creates_multiple_copies(): void
    {
        $amenity = GuestHouseAmenity::query()->create([
            'name' => 'Wi-Fi',
            'icon' => 'wifi',
            'group' => 'Essentials',
        ]);

        $house = GuestHouse::query()->create([
            'name' => 'Source House',
            'slug' => 'source-house',
            'type' => GuestHouseType::Apartment,
            'status' => GuestHouseStatus::Active,
            'city' => 'Reykjavík',
            'max_guests' => 2,
            'bedrooms' => 1,
            'bathrooms' => 1,
            'beds' => 1,
            'min_nights' => 1,
            'base_price_per_night' => 10000,
            'thumbnail' => 'guesthouses/thumbnails/source.jpg',
        ]);

        $house->amenities()->attach($amenity->id);

        GuestHouseImage::query()->create([
            'guest_house_id' => $house->id,
            'path' => 'guesthouses/gallery/source-1.jpg',
            'sort_order' => 0,
        ]);

        GuestHouseRoomDetail::query()->create([
            'guest_house_id' => $house->id,
            'title' => 'Master bedroom',
            'text' => 'Queen bed with view.',
            'dim' => 'Sleeps 2',
            'image_path' => 'guesthouses/room-details/source-bedroom.jpg',
            'sort_order' => 0,
        ]);

        GuestHouseSeasonalPrice::query()->create([
            'guest_house_id' => $house->id,
            'name' => 'Summer',
            'date_from' => '2026-06-01',
            'date_to' => '2026-08-31',
            'price_per_night' => 15000,
            'minimum_nights' => 2,
        ]);

        $copies = app(GuestHouseDuplicationService::class)->duplicate($house, 2);

        $this->assertCount(2, $copies);
        $this->assertSame('Source House (copy)', $copies[0]->name);
        $this->assertSame('Source House (copy 2)', $copies[1]->name);

        foreach ($copies as $copy) {
            $this->assertNotSame($house->id, $copy->id);
            $this->assertNotSame($house->slug, $copy->slug);
            $this->assertSame('guesthouses/thumbnails/source.jpg', $copy->thumbnail);
            $this->assertTrue($copy->amenities()->whereKey($amenity->id)->exists());
            $this->assertDatabaseHas('guest_house_images', [
                'guest_house_id' => $copy->id,
                'path' => 'guesthouses/gallery/source-1.jpg',
            ]);
            $this->assertDatabaseHas('guest_house_room_details', [
                'guest_house_id' => $copy->id,
                'title' => 'Master bedroom',
                'image_path' => 'guesthouses/room-details/source-bedroom.jpg',
            ]);
            $this->assertDatabaseHas('guest_house_seasonal_prices', [
                'guest_house_id' => $copy->id,
                'name' => 'Summer',
                'price_per_night' => 15000,
            ]);
        }
    }
}
