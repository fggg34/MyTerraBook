<?php

namespace App\Filament\Resources\BookingRestrictions\Pages;

use App\Filament\Resources\BookingRestrictions\BookingRestrictionResource;
use App\Filament\Resources\BookingRestrictions\Schemas\BookingRestrictionForm;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditBookingRestriction extends EditRecord
{
    protected static string $resource = BookingRestrictionResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return BookingRestrictionForm::persistWeekdayControls($data);
    }

    public function getPageClasses(): array
    {
        return [
            ...parent::getPageClasses(),
            'ir-booking-restriction-form-page',
            'ir-booking-restriction-form-page--edit',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
