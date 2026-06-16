<?php

namespace App\Filament\Resources\RegisteredHosts\Pages;

use App\Enums\UserRole;
use App\Filament\Resources\RegisteredHosts\RegisteredHostResource;
use App\Models\User;
use Filament\Resources\Pages\ListRecords;

class ListRegisteredHosts extends ListRecords
{
    protected static string $resource = RegisteredHostResource::class;

    public function getSubheading(): ?string
    {
        $count = User::query()->where('role', UserRole::Host)->count();

        return $count === 1 ? '1 registered host' : "{$count} registered hosts";
    }
}
