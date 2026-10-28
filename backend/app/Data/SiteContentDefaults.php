<?php

namespace App\Data;

class SiteContentDefaults
{
    /** @var array<string, array<string, mixed>>|null */
    private static ?array $cache = null;

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        if (self::$cache === null) {
            $path = database_path('seeders/data/site_content_defaults.json');
            $json = is_file($path) ? file_get_contents($path) : '{}';
            self::$cache = json_decode($json, true) ?: [];
        }

        return self::$cache;
    }

    /**
     * @return array<string, mixed>
     */
    public static function forPage(string $pageKey): array
    {
        $page = self::all()[$pageKey] ?? [];
        $extra = match ($pageKey) {
            'home' => FigmaHomepage::sections(),
            'global' => FigmaHomepage::chrome(),
            'about' => FigmaContentPages::about(),
            'faq' => FigmaContentPages::faq(),
            'contact' => FigmaContentPages::contact(),
            'good-to-know' => FigmaContentPages::guides(),
            'campsite-map' => FigmaContentPages::campsite(),
            'auth-login' => FigmaContentPages::login(),
            'auth-register' => FigmaContentPages::register(),
            'host-panel' => FigmaContentPages::hostPanel(),
            'client-panel' => FigmaContentPages::clientPanel(),
            'checkout' => FigmaContentPages::checkout(),
            'article' => FigmaContentPages::article(),
            'coming-soon' => FigmaContentPages::comingSoon(),
            default => [],
        };

        if ($extra === []) {
            if ($pageKey === 'search-campervan') {
                $page['presentation'] = FigmaHomepage::searchCampervan();
            }
            if ($pageKey === 'search-car') {
                $page['presentation'] = FigmaHomepage::searchCar();
            }
            if ($pageKey === 'search-guesthouse') {
                $page['presentation'] = FigmaHomepage::searchGuesthouse();
            }
            if ($pageKey === 'listing-campervan') {
                $page['frame'] = FigmaHomepage::listingCampervan();
            }
            if ($pageKey === 'listing-car') {
                $page['frame'] = FigmaHomepage::listingCar();
            }
            if ($pageKey === 'listing-guesthouse') {
                $page['frame'] = FigmaHomepage::listingGuesthouse();
            }

            return $page;
        }

        $merged = array_replace_recursive($page, $extra);

        if (isset($extra['header']['navLinks'])) {
            $merged['header']['navLinks'] = $extra['header']['navLinks'];
        }

        if (isset($extra['footer']['columns'])) {
            $merged['footer']['columns'] = $extra['footer']['columns'];
        }

        if ($pageKey === 'search-campervan') {
            $merged['presentation'] = FigmaHomepage::searchCampervan();
        }

        return $merged;
    }

    /**
     * @return list<string>
     */
    public static function pageKeys(): array
    {
        return array_keys(self::all());
    }

    public static function labelFor(string $pageKey): string
    {
        return config("site_content.pages.{$pageKey}.label") ?? str($pageKey)->headline()->toString();
    }
}
