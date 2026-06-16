<?php

namespace App\Support;

/**
 * Helper around the curated Lucide icon catalog (config/icons.php).
 *
 * Provides option lists for Filament selects (with rendered previews) and
 * validation helpers. Keep config/icons.php in sync with
 * frontend/src/utils/iconCatalog.jsx.
 */
class IconCatalog
{
    private const PREVIEW_SIZE = 16;

    private const TABLE_SIZE = 20;

    private const STROKE_LIGHT = '#374151';
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

    /**
     * Inline SVG preview for Filament selects/tables.
     *
     * Uses explicit pixel dimensions and CSS `color` so Lucide's stroke="currentColor"
     * renders reliably (data-URI <img> tags cannot resolve currentColor).
     */
    public static function previewSvgHtml(string $key, int $size = self::PREVIEW_SIZE): string
    {
        if (! function_exists('svg')) {
            return '';
        }

        try {
            $svgHtml = svg('lucide-'.$key)->toHtml();

            return self::prepareInlineSvg($svgHtml, $size);
        } catch (\Throwable) {
            return '';
        }
    }

    private static function prepareInlineSvg(string $svgHtml, int $size): string
    {
        $style = sprintf(
            'width:%1$dpx;height:%1$dpx;flex-shrink:0;display:block;color:%2$s',
            $size,
            self::STROKE_LIGHT,
        );

        $svgHtml = preg_replace(
            '/<svg\b/',
            sprintf(
                '<svg class="tb-icon-catalog-svg" width="%d" height="%d" style="%s" ',
                $size,
                $size,
                $style,
            ),
            $svgHtml,
            1,
        ) ?? $svgHtml;

        // Lucide SVGs ship stroke="currentColor"; keep it and drive color via CSS above.
        return $svgHtml;
    }

    public static function tableIconHtml(?string $key): string
    {
        if (! $key || ! self::isValid($key)) {
            return '';
        }

        return self::previewSvgHtml($key, self::TABLE_SIZE);
    }

    private static function optionHtml(string $key, string $label, string $keywords): string
    {
        $icon = self::previewSvgHtml($key);

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
