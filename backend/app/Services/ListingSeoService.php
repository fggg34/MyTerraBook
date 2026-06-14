<?php

namespace App\Services;

use App\Models\Car;
use App\Models\GuestHouse;

class ListingSeoService
{
    public function syncCar(Car $car): void
    {
        $car->loadMissing('subCategory.mainCategory');

        $car->update([
            'meta_title' => $this->carMetaTitle($car),
            'meta_description' => $this->carMetaDescription($car),
            'og_image' => $car->main_image_path ?: null,
        ]);
    }

    public function syncGuestHouse(GuestHouse $guestHouse): void
    {
        $guestHouse->update([
            'meta_title' => $this->guestHouseMetaTitle($guestHouse),
            'meta_description' => $this->guestHouseMetaDescription($guestHouse),
            'og_image' => $guestHouse->thumbnail ?: null,
        ]);
    }

    public function carMetaTitle(Car $car): string
    {
        $category = $this->resolveCarCategoryLabel($car);

        return "{$car->name}, {$category} in Iceland";
    }

    public function guestHouseMetaTitle(GuestHouse $guestHouse): string
    {
        return "{$guestHouse->name}, Guesthouse in Iceland";
    }

    public function carMetaDescription(Car $car): string
    {
        return $this->truncateDescription($car->description ?? '');
    }

    public function guestHouseMetaDescription(GuestHouse $guestHouse): string
    {
        if (filled($guestHouse->short_description)) {
            return $this->truncateDescription($guestHouse->short_description);
        }

        return $this->truncateDescription($guestHouse->description ?? '');
    }

    private function resolveCarCategoryLabel(Car $car): string
    {
        $slug = $car->subCategory?->mainCategory?->slug;

        return match ($slug) {
            'campervan' => 'Campervan',
            'car' => 'Car & 4×4',
            default => 'Listing',
        };
    }

    private function truncateDescription(string $value, int $max = 160): string
    {
        $text = $this->stripHtml($value);

        if (mb_strlen($text) <= $max) {
            return $text;
        }

        return rtrim(mb_substr($text, 0, $max - 1)).'…';
    }

    private function stripHtml(string $value): string
    {
        $text = strip_tags($value);
        $text = preg_replace('/\s+/u', ' ', $text) ?? '';

        return trim($text);
    }
}
