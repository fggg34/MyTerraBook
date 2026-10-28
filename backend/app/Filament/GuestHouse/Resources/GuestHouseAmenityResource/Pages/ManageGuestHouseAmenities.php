<?php

namespace App\Filament\GuestHouse\Resources\GuestHouseAmenityResource\Pages;

use App\Filament\GuestHouse\Resources\GuestHouseAmenityResource;
use App\Models\GuestHouseAmenity;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Contracts\View\View;

class ManageGuestHouseAmenities extends ManageRecords
{
    protected static string $resource = GuestHouseAmenityResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('New amenity')];
    }

    public function getFooter(): ?View
    {
        $groups = GuestHouseAmenity::query()
            ->orderBy('group')
            ->orderBy('name')
            ->get()
            ->groupBy(fn (GuestHouseAmenity $amenity): string => $amenity->group ?: 'General');

        return view('filament.guest-houses.amenities-board', [
            'groups' => $groups,
        ]);
    }
}
