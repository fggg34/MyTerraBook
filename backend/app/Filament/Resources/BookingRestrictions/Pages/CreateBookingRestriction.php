<?php

namespace App\Filament\Resources\BookingRestrictions\Pages;

use App\Filament\Resources\BookingRestrictions\BookingRestrictionResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateBookingRestriction extends CreateRecord
{
    protected static string $resource = BookingRestrictionResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $allWeekdays = [0, 1, 2, 3, 4, 5, 6];

        $data['cta_weekdays'] = ($data['cta_enabled'] ?? false) ? $allWeekdays : [];
        $data['ctd_weekdays'] = ($data['ctd_enabled'] ?? false) ? $allWeekdays : [];

        $forcedWeekday = $data['forced_pickup_weekday'] ?? null;
        $data['forced_pickup_weekdays'] = filled($forcedWeekday) ? [(int) $forcedWeekday] : [];

        unset(
            $data['cta_enabled'],
            $data['ctd_enabled'],
            $data['forced_pickup_weekday'],
            $data['restriction_period_mode'],
            $data['apply_to_all_cars'],
        );

        return $data;
    }

    public function getPageClasses(): array
    {
        return [
            ...parent::getPageClasses(),
            'ir-booking-restriction-form-page',
            'ir-booking-restriction-form-page--create',
        ];
    }
}
