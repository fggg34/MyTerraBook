<?php

namespace App\Filament\GuestHouse\Resources\Concerns;

use App\Models\GuestHouse;

trait NormalizesGuestHouseFormData
{
    /** @var list<string>|null */
    protected ?array $pendingGalleryPaths = null;

    protected function normalizeGuestHouseFormDataForFill(array $data): array
    {
        $data['base_price_per_night_euros'] = isset($data['base_price_per_night'])
            ? round($data['base_price_per_night'] / 100, 2)
            : null;

        $data['cleaning_fee_euros'] = isset($data['cleaning_fee']) && $data['cleaning_fee'] !== null
            ? round($data['cleaning_fee'] / 100, 2)
            : null;

        $data['security_deposit_euros'] = isset($data['security_deposit']) && $data['security_deposit'] !== null
            ? round($data['security_deposit'] / 100, 2)
            : null;

        if (isset($this->record)) {
            $data['gallery_paths'] = $this->record->images()
                ->orderBy('sort_order')
                ->pluck('path')
                ->all();
        }

        return $data;
    }

    protected function normalizeGuestHouseFormDataForSave(array $data): array
    {
        $name = trim((string) ($data['name'] ?? ''));
        if ($name !== '') {
            $exceptId = isset($this->record) ? (int) $this->record->getKey() : null;
            $data['slug'] = filled($data['slug'] ?? null)
                ? $data['slug']
                : GuestHouse::uniqueSlugFromName($name, $exceptId);
        }

        if (array_key_exists('base_price_per_night_euros', $data)) {
            $data['base_price_per_night'] = (int) round(((float) ($data['base_price_per_night_euros'] ?? 0)) * 100);
            unset($data['base_price_per_night_euros']);
        }

        if (array_key_exists('cleaning_fee_euros', $data)) {
            $data['cleaning_fee'] = filled($data['cleaning_fee_euros'] ?? null)
                ? (int) round(((float) $data['cleaning_fee_euros']) * 100)
                : null;
            unset($data['cleaning_fee_euros']);
        }

        if (array_key_exists('security_deposit_euros', $data)) {
            $data['security_deposit'] = filled($data['security_deposit_euros'] ?? null)
                ? (int) round(((float) $data['security_deposit_euros']) * 100)
                : null;
            unset($data['security_deposit_euros']);
        }

        $data['thumbnail'] = $this->normalizeUploadedPath($data['thumbnail'] ?? null);

        if (array_key_exists('gallery_paths', $data)) {
            $this->pendingGalleryPaths = collect($data['gallery_paths'] ?? [])
                ->map(fn (mixed $path) => $this->normalizeUploadedPath($path))
                ->filter()
                ->values()
                ->all();
            unset($data['gallery_paths']);
        }

        if (empty($data['thumbnail']) && ! empty($this->pendingGalleryPaths[0])) {
            $data['thumbnail'] = $this->pendingGalleryPaths[0];
        }

        return $data;
    }

    protected function normalizeUploadedPath(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            $value = $value[array_key_first($value)] ?? null;
        }

        $path = trim((string) $value);

        return $path !== '' ? $path : null;
    }

    protected function syncGalleryImages(GuestHouse $house): void
    {
        if ($this->pendingGalleryPaths === null) {
            return;
        }

        $paths = $this->pendingGalleryPaths;
        $this->pendingGalleryPaths = null;

        $existing = $house->images()->orderBy('sort_order')->get();

        $existing
            ->filter(fn ($image) => ! in_array($image->path, $paths, true))
            ->each->delete();

        foreach ($paths as $sortOrder => $path) {
            $image = $existing->firstWhere('path', $path);

            if ($image) {
                if ($image->sort_order !== $sortOrder) {
                    $image->update(['sort_order' => $sortOrder]);
                }

                continue;
            }

            $house->images()->create([
                'path' => $path,
                'sort_order' => $sortOrder,
            ]);
        }
    }

    protected function syncThumbnailFromGallery(GuestHouse $house): void
    {
        if (filled($house->thumbnail)) {
            return;
        }

        $first = $house->images()->orderBy('sort_order')->value('path');
        if ($first) {
            $house->forceFill(['thumbnail' => $first])->saveQuietly();
        }
    }
}
