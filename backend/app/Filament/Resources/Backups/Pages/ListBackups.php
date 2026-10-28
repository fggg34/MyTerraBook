<?php

namespace App\Filament\Resources\Backups\Pages;

use App\Filament\Concerns\EmbedsCatalogTable;
use App\Filament\Resources\Backups\BackupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBackups extends ListRecords
{
    use EmbedsCatalogTable;

    protected static string $resource = BackupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
