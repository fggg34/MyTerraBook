<?php

namespace App\Filament\Resources\Characteristics\Pages;

use App\Filament\Resources\Characteristics\CharacteristicResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Schema as DatabaseSchema;

class EditCharacteristic extends EditRecord
{
    protected static string $resource = CharacteristicResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['slug'] = (string) ($this->record->slug ?? '');

        if (DatabaseSchema::hasColumn('characteristics', 'sort_order')) {
            $data['sort_order'] = isset($data['sort_order'])
                ? (int) $data['sort_order']
                : (int) ($this->record->sort_order ?? 0);
        } else {
            unset($data['sort_order']);
        }

        return $data;
    }

    public function getPageClasses(): array
    {
        return [
            ...parent::getPageClasses(),
            'ir-characteristics-page',
            'ir-characteristics-page--edit',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
