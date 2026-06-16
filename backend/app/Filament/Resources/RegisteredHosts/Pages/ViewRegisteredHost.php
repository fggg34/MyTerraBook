<?php

namespace App\Filament\Resources\RegisteredHosts\Pages;

use App\Filament\Resources\RegisteredHosts\RegisteredHostResource;
use App\Filament\Resources\RegisteredHosts\Tables\RegisteredHostsTable;
use Filament\Resources\Pages\ViewRecord;

class ViewRegisteredHost extends ViewRecord
{
    protected static string $resource = RegisteredHostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            RegisteredHostsTable::makeDeleteAction(),
        ];
    }
}
