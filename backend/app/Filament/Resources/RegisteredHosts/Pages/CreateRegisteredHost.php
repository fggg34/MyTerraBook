<?php

namespace App\Filament\Resources\RegisteredHosts\Pages;

use App\Enums\UserRole;
use App\Filament\Resources\RegisteredHosts\RegisteredHostResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateRegisteredHost extends CreateRecord
{
    protected static string $resource = RegisteredHostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['role'] = UserRole::Host;
        $data['password'] = Str::random(64);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
