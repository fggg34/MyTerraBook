<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Services\GoogleReviewsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleReviewsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_fetch_for_place_returns_null_without_api_key(): void
    {
        $result = app(GoogleReviewsService::class)->fetchForPlace('ChIJtest');

        $this->assertNull($result);
    }

    public function test_fetch_for_place_maps_google_response(): void
    {
        Setting::putValue('system.google_maps_api_key', ['key' => 'test-key']);

        Http::fake([
            'maps.googleapis.com/*' => Http::response([
                'status' => 'OK',
                'result' => [
                    'rating' => 4.8,
                    'user_ratings_total' => 1240,
                    'reviews' => [
                        [
                            'author_name' => 'Jane Doe',
                            'rating' => 5,
                            'text' => 'Amazing trip around Iceland.',
                            'relative_time_description' => '2 weeks ago',
                            'profile_photo_url' => 'https://example.com/photo.jpg',
                        ],
                    ],
                ],
            ]),
        ]);

        $result = app(GoogleReviewsService::class)->fetchForPlace('ChIJtest');

        $this->assertNotNull($result);
        $this->assertSame('4.8 / 5', $result['rating']);
        $this->assertSame('1,200+ reviews', $result['ratingCount']);
        $this->assertCount(1, $result['reviews']);
        $this->assertSame('Jane Doe', $result['reviews'][0]['name']);
        $this->assertSame('"Amazing trip around Iceland."', $result['reviews'][0]['quote']);
        $this->assertSame(5, $result['reviews'][0]['stars']);
    }
}
