<?php

namespace App\Filament\Resources\RentalConditions\Pages;

use App\Filament\Resources\RentalConditions\RentalConditionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditRentalCondition extends EditRecord
{
    protected static string $resource = RentalConditionResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['slug'] = (string) ($this->record->slug ?? '');
        $data['sort_order'] = isset($data['sort_order'])
            ? (int) $data['sort_order']
            : (int) ($this->record->sort_order ?? 0);

        return $data;
    }

    public function getPageClasses(): array
    {
        return [
            ...parent::getPageClasses(),
            'ir-rental-conditions-page',
            'ir-rental-conditions-page--edit',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
