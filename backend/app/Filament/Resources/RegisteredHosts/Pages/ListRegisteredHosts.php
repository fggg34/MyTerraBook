<?php

namespace App\Filament\Resources\RegisteredHosts\Pages;

use App\Enums\UserRole;
use App\Filament\Concerns\EmbedsCatalogTable;
use App\Filament\Resources\RegisteredHosts\RegisteredHostResource;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRegisteredHosts extends ListRecords
{
    use EmbedsCatalogTable;

    protected static string $resource = RegisteredHostResource::class;

    public function mount(): void
    {
        parent::mount();
        $this->tableFilters = array_replace_recursive([
            'country_code' => ['value' => null],
        ], $this->tableFilters ?? []);
    }

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Add host / partner')];
    }

    public function getSubheading(): ?string
    {
        $count = User::query()->where('role', UserRole::Host)->count();

        return ($count === 1 ? '1 registered host' : "{$count} registered hosts").' · Manage partner profiles, API connections and commissions.';
    }
}
