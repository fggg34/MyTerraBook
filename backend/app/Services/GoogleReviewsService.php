<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GoogleReviewsService
{
    private const AVATAR_FILLS = [
        '#a9d4e6',
        '#bcdcab',
        '#f1d79a',
        '#cdbbea',
        '#a4ddcd',
        '#f4c1a4',
        '#c5d8f0',
        '#e8c4b8',
    ];

    /**
     * @return array{rating: string, ratingCount: string, reviews: list<array<string, mixed>>}|null
     */
    public function fetchForPlace(string $placeId): ?array
    {
        $placeId = trim($placeId);
        if ($placeId === '') {
            return null;
        }

        $apiKey = (string) data_get(Setting::getValue('system.google_maps_api_key', ['key' => '']), 'key', '');
        if ($apiKey === '') {
            return null;
        }

        $cacheKey = 'google_reviews.'.md5($placeId);

        return Cache::remember($cacheKey, 3600, function () use ($placeId, $apiKey): ?array {
            return $this->requestPlaceDetails($placeId, $apiKey);
        });
    }

    /**
     * @return array{rating: string, ratingCount: string, reviews: list<array<string, mixed>>}|null
     */
    private function requestPlaceDetails(string $placeId, string $apiKey): ?array
    {
        try {
            $response = Http::timeout(8)->get('https://maps.googleapis.com/maps/api/place/details/json', [
                'place_id' => $placeId,
                'fields' => 'rating,user_ratings_total,reviews',
                'key' => $apiKey,
            ]);
        } catch (\Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $payload = $response->json();
        if (($payload['status'] ?? '') !== 'OK') {
            return null;
        }

        $result = $payload['result'] ?? null;
        if (! is_array($result)) {
            return null;
        }

        $reviews = $this->mapReviews($result['reviews'] ?? []);
        if ($reviews === []) {
            return null;
        }

        $rating = isset($result['rating']) ? (float) $result['rating'] : null;
        $total = isset($result['user_ratings_total']) ? (int) $result['user_ratings_total'] : null;

        return [
            'rating' => $rating !== null ? number_format($rating, 1).' / 5' : '',
            'ratingCount' => $this->formatRatingCount($total),
            'reviews' => $reviews,
        ];
    }

    /**
     * @param  list<mixed>  $rawReviews
     * @return list<array<string, mixed>>
     */
    private function mapReviews(array $rawReviews): array
    {
        $mapped = [];

        foreach ($rawReviews as $index => $review) {
            if (! is_array($review)) {
                continue;
            }

            $text = trim((string) ($review['text'] ?? ''));
            $name = trim((string) ($review['author_name'] ?? ''));

            if ($text === '' || $name === '') {
                continue;
            }

            $mapped[] = [
                'quote' => $this->wrapQuote($text),
                'name' => $name,
                'fill' => self::AVATAR_FILLS[$index % count(self::AVATAR_FILLS)],
                'avatarUrl' => (string) ($review['profile_photo_url'] ?? ''),
                'stars' => max(1, min(5, (int) ($review['rating'] ?? 5))),
                'relativeTime' => (string) ($review['relative_time_description'] ?? ''),
            ];
        }

        return $mapped;
    }

    private function wrapQuote(string $text): string
    {
        $text = trim($text);
        if ($text === '') {
            return '';
        }

        if (str_starts_with($text, '"') || str_starts_with($text, '“')) {
            return $text;
        }

        return '"'.$text.'"';
    }

    private function formatRatingCount(?int $total): string
    {
        if ($total === null || $total <= 0) {
            return '';
        }

        if ($total >= 1000) {
            $rounded = floor($total / 100) * 100;

            return number_format($rounded).'+ reviews';
        }

        return number_format($total).' reviews';
    }
}
