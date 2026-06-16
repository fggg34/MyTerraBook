<?php

namespace App\Filament\Resources\RegisteredClients\Pages;

use App\Filament\Resources\RegisteredClients\RegisteredClientResource;
use App\Filament\Resources\RegisteredClients\Tables\RegisteredClientsTable;
use Filament\Resources\Pages\ViewRecord;

class ViewRegisteredClient extends ViewRecord
{
    protected static string $resource = RegisteredClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            RegisteredClientsTable::makeDeleteAction(),
        ];
    }
}
