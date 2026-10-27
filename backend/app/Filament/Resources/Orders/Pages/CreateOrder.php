<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected static ?string $title = 'New booking';

    protected static bool $canCreateAnother = false;

    public function getSubheading(): string|Htmlable|null
    {
        return 'Create a reservation on an existing vehicle.';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset($data['reference']);

        return $data;
    }
}
