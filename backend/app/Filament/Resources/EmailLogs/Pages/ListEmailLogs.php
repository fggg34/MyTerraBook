<?php

namespace App\Filament\Resources\EmailLogs\Pages;

use App\Filament\Concerns\EmbedsCatalogTable;
use App\Filament\Resources\EmailLogs\EmailLogResource;
use Filament\Resources\Pages\ListRecords;

class ListEmailLogs extends ListRecords
{
    use EmbedsCatalogTable;

    protected static string $resource = EmailLogResource::class;

    public function mount(): void
    {
        parent::mount();
        $this->tableFilters = array_replace_recursive([
            'status' => ['value' => null],
        ], $this->tableFilters ?? []);
    }
}
