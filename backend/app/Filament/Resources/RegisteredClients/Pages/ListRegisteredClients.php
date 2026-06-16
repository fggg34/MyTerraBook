<?php

namespace App\Filament\Resources\RegisteredClients\Pages;

use App\Enums\UserRole;
use App\Filament\Resources\RegisteredClients\RegisteredClientResource;
use App\Models\User;
use Filament\Resources\Pages\ListRecords;

class ListRegisteredClients extends ListRecords
{
    protected static string $resource = RegisteredClientResource::class;

    public function getSubheading(): ?string
    {
        $count = User::query()->where('role', UserRole::Customer)->count();

        return $count === 1 ? '1 registered client' : "{$count} registered clients";
    }
}
