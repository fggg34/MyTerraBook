<?php

namespace App\Filament\Resources\BookingRestrictions\Pages;

use App\Filament\Resources\BookingRestrictions\BookingRestrictionResource;
use App\Filament\Resources\BookingRestrictions\Schemas\BookingRestrictionForm;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateBookingRestriction extends CreateRecord
{
    protected static string $resource = BookingRestrictionResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return BookingRestrictionForm::persistWeekdayControls($data);
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
