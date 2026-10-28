<?php

namespace App\Filament\Resources\BookingRestrictions\Pages;

use App\Filament\Resources\BookingRestrictions\BookingRestrictionResource;
use App\Filament\Resources\BookingRestrictions\Schemas\BookingRestrictionForm;
use App\Models\BookingRestriction;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\View\View;

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

    public function getHeader(): ?View
    {
        $restriction = $this->getRecord();
        if (! $restriction instanceof BookingRestriction) {
            return null;
        }

        return view('filament.panel.edit-record-header', [
            'title' => $restriction->name,
            'backUrl' => BookingRestrictionResource::getUrl('index'),
            'backLabel' => 'Booking restrictions',
            'meta' => $restriction->date_from && $restriction->date_to
                ? $restriction->date_from->format('j M Y').' – '.$restriction->date_to->format('j M Y')
                : null,
            'pills' => [
                ['label' => $restriction->is_active ? 'Active' : 'Inactive', 'tone' => $restriction->is_active ? 'ok' : 'neutral'],
            ],
            'actions' => $this->getCachedHeaderActions(),
            'sections' => [
                'tb-restrict-details' => 'Details',
                'tb-restrict-settings' => 'Settings',
            ],
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
