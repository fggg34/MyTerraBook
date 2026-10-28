<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Orders\Schemas\OrderForm;
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
        $data = OrderForm::applySchedule($data);

        $data['total_cents'] = max(
            0,
            (int) ($data['base_rental_cents'] ?? 0)
            + (int) ($data['extras_cents'] ?? 0)
            + (int) ($data['fees_cents'] ?? 0)
            - (int) ($data['discount_cents'] ?? 0)
            + (int) ($data['tax_cents'] ?? 0),
        );

        return $data;
    }
}
