<?php

namespace App\Support;

/**
 * Helper around the curated Lucide icon catalog (config/icons.php).
 *
 * Provides option lists for Filament selects (with rendered previews) and
 * validation helpers. Keep config/icons.php in sync with
 * frontend/src/utils/iconCatalog.js.
 */
class IconCatalog
{
    /**
     * @return array<string, array<string, array{label: string, keywords: string}>>
     */
    public static function groups(): array
    {
        return (array) config('icons.groups', []);
    }

    /**
     * Flat list of every valid icon key.
     *
     * @return list<string>
     */
    public static function keys(): array
    {
        $keys = [];
        foreach (self::groups() as $entries) {
            foreach (array_keys($entries) as $key) {
                $keys[] = $key;
            }
        }

        return array_values(array_unique($keys));
    }

    public static function isValid(?string $key): bool
    {
        return $key !== null && $key !== '' && in_array($key, self::keys(), true);
    }

    /**
     * Grouped options for a Filament Select using HTML labels (icon + text).
     * Requires the select to call ->allowHtml() and ->searchable().
     *
     * @return array<string, array<string, string>>
     */
    public static function filamentOptions(): array
    {
        $options = [];

        foreach (self::groups() as $group => $entries) {
            $groupOptions = [];
            foreach ($entries as $key => $meta) {
                $groupOptions[$key] = self::optionHtml($key, $meta['label'] ?? $key, $meta['keywords'] ?? '');
            }
            $options[$group] = $groupOptions;
        }

        return $options;
    }

    private static function optionHtml(string $key, string $label, string $keywords): string
    {
        $icon = '';

        try {
            if (function_exists('svg')) {
                $icon = svg('lucide-'.$key, 'w-4 h-4')->toHtml();
            }
        } catch (\Throwable) {
            // Fall back to text-only if the blade icon is unavailable.
            $icon = '';
        }

        $hiddenKeywords = $keywords !== ''
            ? '<span style="display:none">'.e($keywords).'</span>'
            : '';

        return '<span style="display:inline-flex;align-items:center;gap:8px">'
            .$icon
            .'<span>'.e($label).'</span>'
            .$hiddenKeywords
            .'</span>';
    }
}
