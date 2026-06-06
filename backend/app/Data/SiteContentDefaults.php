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
        return self::all()[$pageKey] ?? [];
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
