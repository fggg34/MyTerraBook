<?php

namespace App\Services;

use App\Data\SiteContentDefaults;
use App\Models\Car;
use App\Models\DailyFare;
use App\Models\GuestHouse;
use App\Models\SiteContentPage;
use App\Support\ResolvesPublicStorageUrls;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SiteContentService
{
    use ResolvesPublicStorageUrls;

    /**
     * @return array<string, mixed>
     */
    public function pageContent(string $pageKey): array
    {
        $page = SiteContentPage::query()
            ->where('page_key', $pageKey)
            ->where('is_published', true)
            ->first();

        $defaults = SiteContentDefaults::forPage($pageKey);
        $merged = array_replace_recursive($defaults, $page?->content ?? []);

        return $this->resolveContentImages($this->normalizePageContent($pageKey, $merged));
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function allPages(): array
    {
        $pages = SiteContentPage::query()
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->get();

        $result = [];

        foreach ($pages as $page) {
            $result[$page->page_key] = $this->pageContent($page->page_key);
        }

        foreach (SiteContentDefaults::pageKeys() as $key) {
            $result[$key] ??= $this->pageContent($key);
        }

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    public function homepagePayload(): array
    {
        $global = $this->pageContent('global');
        $home = $this->pageContent('home');

        $featuredBlogPosts = \App\Models\BlogPost::query()
            ->published()
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->limit(5)
            ->get();

        return [
            'topbar' => $global['topbar'] ?? [],
            'header' => $global['header'] ?? [],
            'hero' => $home['hero'] ?? [],
            'trustItems' => $home['trustItems'] ?? [],
            'rentSection' => $this->resolveRentSection($home['rentSection'] ?? []),
            'whySection' => $home['whySection'] ?? [],
            'picksSection' => $home['picksSection'] ?? [],
            'howSection' => $home['howSection'] ?? [],
            'staySection' => $home['staySection'] ?? [],
            'blogSection' => $home['blogSection'] ?? [],
            'hostCtaSection' => $home['hostCtaSection'] ?? [],
            'reviewsSection' => $this->resolveReviewsSection($home['reviewsSection'] ?? []),
            'faqSection' => $global['faqSection'] ?? [],
            'newsSection' => $global['newsSection'] ?? [],
            'footer' => $global['footer'] ?? [],
            'guestHousesHighlight' => $home['guestHousesHighlight'] ?? [],
            'featuredBlogPosts' => \App\Http\Resources\Api\BlogPostResource::collection($featuredBlogPosts)->resolve(),
        ];
    }

    public function clearCache(): void
    {
        Cache::forget('site_content.all');
    }

    /**
     * @param  array<string, mixed>  $section
     * @return array<string, mixed>
     */
    public function resolveReviewsSection(array $section): array
    {
        $defaults = SiteContentDefaults::forPage('home')['reviewsSection'] ?? [];
        $demo = array_replace_recursive($defaults, $section);

        $googleEnabled = (bool) ($section['googleEnabled'] ?? false);
        $placeId = trim((string) ($section['googlePlaceId'] ?? ''));

        if ($googleEnabled && $placeId !== '') {
            $google = app(GoogleReviewsService::class)->fetchForPlace($placeId);

            if ($google !== null) {
                return [
                    'eyebrow' => $section['eyebrow'] ?? $defaults['eyebrow'] ?? '',
                    'heading' => $section['heading'] ?? $defaults['heading'] ?? '',
                    'rating' => $google['rating'] ?: ($demo['rating'] ?? ''),
                    'ratingCount' => $google['ratingCount'] ?: ($demo['ratingCount'] ?? ''),
                    'reviews' => $google['reviews'],
                    'source' => 'google',
                    'isDemo' => false,
                ];
            }
        }

        return [
            'eyebrow' => $demo['eyebrow'] ?? '',
            'heading' => $demo['heading'] ?? '',
            'rating' => $demo['rating'] ?? '',
            'ratingCount' => $demo['ratingCount'] ?? '',
            'reviews' => is_array($demo['reviews'] ?? null) ? $demo['reviews'] : [],
            'source' => 'demo',
            'isDemo' => true,
        ];
    }

    /**
     * @param  array<string, mixed>  $content
     * @return array<string, mixed>
     */
    public function normalizePageContent(string $pageKey, array $content): array
    {
        if ($pageKey === 'faq' && isset($content['items']['items']) && ! isset($content['items'][0])) {
            $content['items'] = $content['items']['items'];
        }

        if ($pageKey === 'contact' && isset($content['details']) && is_array($content['details'])) {
            $content = array_replace($content, $content['details']);
            unset($content['details']);
        }

        $content = $this->normalizeUploadFields($content);
        $content = $this->stripEmptyArrayKeys($content);

        return $this->fillEmptyRepeatersFromDefaults($pageKey, $content);
    }

    /**
     * Filament repeaters occasionally persist blank-string keys on nested rows.
     *
     * @param  array<string, mixed>  $content
     * @return array<string, mixed>
     */
    private function stripEmptyArrayKeys(array $content): array
    {
        $result = [];

        foreach ($content as $key => $value) {
            if ($key === '' || $key === null) {
                continue;
            }

            if (is_array($value)) {
                $result[$key] = array_is_list($value)
                    ? array_map(
                        fn (mixed $item): mixed => is_array($item) ? $this->stripEmptyArrayKeys($item) : $item,
                        $value,
                    )
                    : $this->stripEmptyArrayKeys($value);

                continue;
            }

            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $content
     * @return array<string, mixed>
     */
    private function normalizeUploadFields(array $content): array
    {
        $uploadKeys = [
            'branding' => ['logoImage', 'favicon'],
            'hero' => ['backgroundImage', 'image'],
            'newsSection' => ['backgroundImage'],
        ];

        foreach ($uploadKeys as $section => $keys) {
            if (! isset($content[$section]) || ! is_array($content[$section])) {
                continue;
            }

            foreach ($keys as $key) {
                $value = $content[$section][$key] ?? null;

                if (is_array($value)) {
                    $first = reset($value);
                    $content[$section][$key] = is_string($first) && $first !== '' ? $first : null;

                    continue;
                }

                if ($value === '') {
                    $content[$section][$key] = null;
                }
            }
        }

        foreach ([
            ['rentSection', 'cards', 'image'],
            ['howSection', 'steps', 'image'],
            ['staySection', 'cards', 'image'],
            ['blogSection', 'posts', 'image'],
            ['hostCtaSection', null, 'houseImage'],
            ['hostCtaSection', null, 'vanImage'],
            ['whySection', null, 'photo'],
        ] as [$section, $listKey, $fieldKey]) {
            $content = $this->normalizeNestedUploadField($content, $section, $listKey, $fieldKey);
        }

        return $content;
    }

    /**
     * @param  array<string, mixed>  $content
     * @return array<string, mixed>
     */
    private function normalizeNestedUploadField(array $content, string $section, ?string $listKey, string $fieldKey): array
    {
        if (! isset($content[$section]) || ! is_array($content[$section])) {
            return $content;
        }

        if ($listKey === null) {
            $value = $content[$section][$fieldKey] ?? null;
            $content[$section][$fieldKey] = $this->normalizeUploadValue($value);

            return $content;
        }

        $items = $content[$section][$listKey] ?? null;
        if (! is_array($items)) {
            return $content;
        }

        foreach ($items as $index => $item) {
            if (! is_array($item) || ! array_key_exists($fieldKey, $item)) {
                continue;
            }

            $content[$section][$listKey][$index][$fieldKey] = $this->normalizeUploadValue($item[$fieldKey]);
        }

        return $content;
    }

    private function normalizeUploadValue(mixed $value): mixed
    {
        if (is_array($value)) {
            $first = reset($value);

            return is_string($first) && $first !== '' ? $first : null;
        }

        if ($value === '') {
            return null;
        }

        return $value;
    }

    /**
     * Merge form output onto stored content without wiping list sections that
     * Filament did not hydrate (inactive tabs) as empty arrays.
     *
     * @param  array<string, mixed>  $existing
     * @param  array<string, mixed>  $incoming
     * @return array<string, mixed>
     */
    public function mergeSavedPageContent(array $existing, array $incoming): array
    {
        $merged = $existing;

        foreach ($incoming as $key => $value) {
            if (is_array($value) && array_is_list($value)) {
                // File uploads dehydrate as [] before save, never replace a stored scalar path.
                if (
                    $value === []
                    && array_key_exists($key, $merged)
                    && ! is_array($merged[$key])
                    && ! $this->isMissingIncomingValue($merged[$key])
                ) {
                    continue;
                }

                if (
                    ! empty($merged[$key])
                    && is_array($merged[$key])
                    && ($value === [] || $this->isBlankRepeaterList($value))
                ) {
                    continue;
                }

                $merged[$key] = $value;

                continue;
            }

            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key]) && ! array_is_list($merged[$key])) {
                $merged[$key] = $this->mergeSavedPageContent($merged[$key], $value);

                continue;
            }

            // Inactive tabs often submit null/empty for untouched fields, do not erase stored values.
            if ($this->isMissingIncomingValue($value) && array_key_exists($key, $merged) && ! $this->isMissingIncomingValue($merged[$key])) {
                continue;
            }

            $merged[$key] = $value;
        }

        return $merged;
    }

    private function isMissingIncomingValue(mixed $value): bool
    {
        return $value === null || $value === [] || $value === '';
    }

    /**
     * @param  list<mixed>  $items
     */
    private function isBlankRepeaterList(array $items): bool
    {
        if ($items === []) {
            return true;
        }

        foreach ($items as $item) {
            if (! is_array($item)) {
                return false;
            }

            foreach ($item as $value) {
                if ($value !== null && $value !== '' && $value !== [] && $value !== false) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $content
     * @return array<string, mixed>
     */
    private function fillEmptyRepeatersFromDefaults(string $pageKey, array $content): array
    {
        $defaults = SiteContentDefaults::forPage($pageKey);

        $paths = match ($pageKey) {
            'global' => [
                'header.navLinks',
                'footer.columns',
                'footer.legal',
                'footer.social',
            ],
            default => [],
        };

        foreach ($paths as $path) {
            $current = data_get($content, $path);
            $fallback = data_get($defaults, $path);

            if (empty($current) && ! empty($fallback) && is_array($fallback)) {
                data_set($content, $path, $fallback);
            }
        }

        return $content;
    }

    /**
     * @param  array<string, mixed>  $section
     * @return array<string, mixed>
     */
    private function resolveRentSection(array $section): array
    {
        $cards = $section['cards'] ?? [];

        if (! is_array($cards) || $cards === []) {
            return $section;
        }

        $stats = $this->rentCatalogStats();

        $section['cards'] = array_map(function (array $card) use ($stats): array {
            $key = $this->rentCardStatsKey($card['href'] ?? null);

            if ($key !== null && isset($stats[$key])) {
                $card['listingStats'] = $stats[$key];
            }

            unset($card['listingCount']);

            return $card;
        }, $cards);

        return $section;
    }

    /**
     * @return array<string, array{count: int, minPriceCents: ?int, priceUnit: string}>
     */
    private function rentCatalogStats(): array
    {
        $guestHouseMinPrice = GuestHouse::query()->active()->min('base_price_per_night');

        return [
            'campervan' => $this->vehicleRentStats('campervan'),
            'car' => $this->vehicleRentStats('car'),
            'guesthouse' => [
                'count' => GuestHouse::query()->active()->count(),
                'minPriceCents' => $guestHouseMinPrice !== null ? (int) $guestHouseMinPrice : null,
                'priceUnit' => 'night',
            ],
        ];
    }

    /**
     * @return array{count: int, minPriceCents: ?int, priceUnit: string}
     */
    private function vehicleRentStats(string $mainCategorySlug): array
    {
        $minPrices = DailyFare::query()
            ->select('car_id', DB::raw('MIN(price_per_day_cents) as min_daily_price_cents'))
            ->groupBy('car_id');

        $base = Car::query()
            ->publiclyVisible()
            ->whereHas('subCategory.mainCategory', fn ($query) => $query->where('slug', $mainCategorySlug));

        $count = (clone $base)->count();

        $minPrice = (clone $base)
            ->leftJoinSub($minPrices, 'min_fares', 'min_fares.car_id', '=', 'cars.id')
            ->min('min_fares.min_daily_price_cents');

        return [
            'count' => $count,
            'minPriceCents' => $minPrice !== null ? (int) $minPrice : null,
            'priceUnit' => 'day',
        ];
    }

    private function rentCardStatsKey(?string $href): ?string
    {
        $normalized = strtolower(rtrim(trim((string) $href), '/'));

        return match ($normalized) {
            '/campervans' => 'campervan',
            '/cars' => 'car',
            '/guesthouses' => 'guesthouse',
            default => null,
        };
    }
}
