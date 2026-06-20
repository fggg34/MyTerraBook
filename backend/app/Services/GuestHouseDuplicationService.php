<?php

namespace App\Services;

use App\Models\GuestHouse;
use Illuminate\Support\Facades\DB;

class GuestHouseDuplicationService
{
    /**
     * @return array<int, GuestHouse>
     */
    public function duplicate(GuestHouse $guestHouse, int $copies = 1): array
    {
        $copies = max(1, $copies);

        $guestHouse->loadMissing([
            'amenities',
            'images',
            'roomDetails',
            'seasonalPrices',
        ]);

        return DB::transaction(function () use ($guestHouse, $copies): array {
            $replicas = [];

            for ($i = 1; $i <= $copies; $i++) {
                $replicas[] = $this->duplicateSingle($guestHouse, $i);
            }

            return $replicas;
        });
    }

    private function duplicateSingle(GuestHouse $guestHouse, int $copyIndex): GuestHouse
    {
        $replica = $guestHouse->replicate([
            'id',
            'slug',
            'submitted_at',
            'reviewed_at',
            'reviewed_by',
            'rejection_reason',
            'created_at',
            'updated_at',
        ]);
        $replica->name = $this->copyName($guestHouse->name, $copyIndex);
        $replica->slug = null;
        $replica->save();

        $replica->amenities()->sync($guestHouse->amenities->pluck('id')->all());

        foreach ($guestHouse->images as $image) {
            $replica->images()->create([
                'path' => $image->path,
                'caption' => $image->caption,
                'sort_order' => $image->sort_order,
            ]);
        }

        foreach ($guestHouse->roomDetails as $roomDetail) {
            $replica->roomDetails()->create([
                'title' => $roomDetail->title,
                'text' => $roomDetail->text,
                'dim' => $roomDetail->dim,
                'image_path' => $roomDetail->image_path,
                'sort_order' => $roomDetail->sort_order,
            ]);
        }

        foreach ($guestHouse->seasonalPrices as $seasonalPrice) {
            $newPrice = $seasonalPrice->replicate(['id', 'guest_house_id', 'created_at', 'updated_at']);
            $newPrice->guest_house_id = $replica->id;
            $newPrice->save();
        }

        app(ListingSeoService::class)->syncGuestHouse($replica);

        return $replica;
    }

    private function copyName(string $name, int $copyIndex): string
    {
        return $copyIndex === 1
            ? $name.' (copy)'
            : $name.' (copy '.$copyIndex.')';
    }
}
