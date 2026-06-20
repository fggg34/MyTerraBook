<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

trait ResolvesPublicStorageUrls
{
    /**
     * @param  array<string, mixed>  $data
     * @param  list<string>  $imageKeys
     * @return array<string, mixed>
     */
    protected function resolveImageKeys(array $data, array $imageKeys): array
    {
        foreach ($imageKeys as $key) {
            if (! array_key_exists($key, $data)) {
                continue;
            }
            $data[$key] = $this->resolvePublicUrl($data[$key]);
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function resolveNestedImages(array $data, string $parentKey, string $childImageKey): array
    {
        if (! isset($data[$parentKey]) || ! is_array($data[$parentKey])) {
            return $data;
        }

        $data[$parentKey] = array_map(function (mixed $item) use ($childImageKey): mixed {
            if (! is_array($item) || ! isset($item[$childImageKey])) {
                return $item;
            }
            $item[$childImageKey] = $this->resolvePublicUrl($item[$childImageKey]);

            return $item;
        }, $data[$parentKey]);

        return $data;
    }

    protected function resolvePublicUrl(mixed $value): mixed
    {
        if (is_array($value)) {
            $first = reset($value);

            return is_string($first) ? $this->resolvePublicUrl($first) : $value;
        }

        if (! is_string($value) || $value === '') {
            return $value;
        }

        if (str_starts_with($value, 'http') || str_starts_with($value, '/')) {
            return $value;
        }

        $value = $this->promoteToPublicDisk($value);

        return Storage::disk('public')->url($value);
    }

    /**
     * Legacy uploads were stored on the default (private) disk before site-content
     * forms specified the public disk. Move them so /storage URLs work.
     */
    protected function promoteToPublicDisk(string $path): string
    {
        if (Storage::disk('public')->exists($path)) {
            if (Storage::disk('local')->exists($path)) {
                Storage::disk('local')->delete($path);
            }

            return $path;
        }

        if (! Storage::disk('local')->exists($path)) {
            return $path;
        }

        $directory = dirname($path);
        if ($directory !== '.' && $directory !== '') {
            Storage::disk('public')->makeDirectory($directory);
        }

        Storage::disk('public')->put($path, Storage::disk('local')->get($path));
        Storage::disk('local')->delete($path);

        return $path;
    }

    /**
     * @param  array<string, mixed>  $content
     * @return array<string, mixed>
     */
    protected function resolveContentImages(array $content): array
    {
        $imageSectionKeys = [
            'branding' => ['logoImage', 'favicon'],
            'hero' => ['backgroundImage', 'mobileBackgroundImage', 'image'],
            'header' => ['image'],
            'map' => ['image'],
            'rentSection' => [],
            'whySection' => ['photo'],
            'howSection' => [],
            'staySection' => [],
            'hostCtaSection' => ['houseImage', 'vanImage'],
            'newsSection' => ['backgroundImage'],
            'cta' => ['patternImage'],
        ];

        foreach ($imageSectionKeys as $section => $keys) {
            if (! isset($content[$section]) || ! is_array($content[$section])) {
                continue;
            }
            $content[$section] = $this->resolveImageKeys($content[$section], $keys);
        }

        foreach (['rentSection', 'howSection', 'staySection', 'blogSection', 'picksSection'] as $section) {
            if (isset($content[$section]['cards']) && is_array($content[$section]['cards'])) {
                $content[$section]['cards'] = $this->resolveListImages($content[$section]['cards'], 'image');
            }
            if (isset($content[$section]['steps']) && is_array($content[$section]['steps'])) {
                $content[$section]['steps'] = $this->resolveListImages($content[$section]['steps'], 'image');
            }
            if (isset($content[$section]['posts']) && is_array($content[$section]['posts'])) {
                $content[$section]['posts'] = $this->resolveListImages($content[$section]['posts'], 'image');
            }
            if (isset($content[$section]['items']) && is_array($content[$section]['items'])) {
                foreach ($content[$section]['items'] as $groupKey => $items) {
                    if (! is_array($items)) {
                        continue;
                    }
                    $content[$section]['items'][$groupKey] = $this->resolveListImages($items, 'image');
                }
            }
        }

        foreach (['howTabs', 'features', 'storyBlocks', 'photos'] as $listKey) {
            if (isset($content[$listKey]) && is_array($content[$listKey])) {
                $content[$listKey] = $this->resolveListImages($content[$listKey], 'image');
            }
        }

        if (isset($content['footer']['social']) && is_array($content['footer']['social'])) {
            $content['footer']['social'] = $this->resolveListImages($content['footer']['social'], 'iconImage');
        }

        $content = $this->resolveIconImagesRecursively($content);

        if (isset($content['proof']['stats']) && is_array($content['proof']['stats'])) {
            $content['proof']['stats'] = array_map(function (array $stat): array {
                if (isset($stat['tall']['image'])) {
                    $stat['tall']['image'] = $this->resolvePublicUrl($stat['tall']['image']);
                }
                if (isset($stat['stack']) && is_array($stat['stack'])) {
                    $stat['stack'] = array_map(function (array $item): array {
                        if (isset($item['image'])) {
                            $item['image'] = $this->resolvePublicUrl($item['image']);
                        }

                        return $item;
                    }, $stat['stack']);
                }

                return $stat;
            }, $content['proof']['stats']);
        }

        return $content;
    }

    /**
     * Walk the whole content tree and resolve any `iconImage` value to a public URL.
     * Lets custom per-item icon uploads work in every repeater uniformly.
     *
     * @param  array<string, mixed>  $content
     * @return array<string, mixed>
     */
    protected function resolveIconImagesRecursively(array $content): array
    {
        foreach ($content as $key => $value) {
            if ($key === 'iconImage') {
                $content[$key] = $this->resolvePublicUrl($value);

                continue;
            }

            if (is_array($value)) {
                $content[$key] = $this->resolveIconImagesRecursively($value);
            }
        }

        return $content;
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return list<array<string, mixed>>
     */
    protected function resolveListImages(array $items, string $imageKey): array
    {
        return array_map(function (array $item) use ($imageKey): array {
            if (isset($item[$imageKey])) {
                $item[$imageKey] = $this->resolvePublicUrl($item[$imageKey]);
            }

            return $item;
        }, array_values(array_filter($items, fn (mixed $item): bool => is_array($item))));
    }
}
