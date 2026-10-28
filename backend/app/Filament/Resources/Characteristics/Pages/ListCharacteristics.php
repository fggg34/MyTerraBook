<?php

namespace App\Filament\Resources\Characteristics\Pages;

use App\Filament\Concerns\EmbedsCatalogTable;
use App\Filament\Concerns\ReordersBySortOrder;
use App\Filament\Resources\Characteristics\CharacteristicResource;
use App\Models\Characteristic;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCharacteristics extends ListRecords
{
    use EmbedsCatalogTable;
    use ReordersBySortOrder;

    protected static string $resource = CharacteristicResource::class;

    public function mount(): void
    {
        parent::mount();
        $this->tableFilters = array_replace_recursive([
            'group' => ['value' => null],
        ], $this->tableFilters ?? []);
    }

    public function reorderVisible(array $order): void
    {
        $record = Characteristic::query()->whereKey($order[0] ?? null)->first();
        abort_unless($record && CharacteristicResource::canEdit($record), 403);
        $this->reorderBySortOrder(Characteristic::class, $order);
    }

    public function getPageClasses(): array
    {
        return [
            ...parent::getPageClasses(),
            'ir-characteristics-page',
            'ir-characteristics-page--list',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
