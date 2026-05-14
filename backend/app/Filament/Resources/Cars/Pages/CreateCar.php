<?php

namespace App\Filament\Resources\Cars\Pages;

use App\Filament\Resources\Cars\CarResource;
use App\Models\Car;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Icons\Heroicon;
use Filament\Support\Enums\Width;

class CreateCar extends CreateRecord
{
    protected static string $resource = CarResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected array $pickupLocationIds = [];

    protected array $dropoffLocationIds = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $name = trim((string) ($data['name'] ?? ''));
        $data['slug'] = Car::uniqueSlugFromName($name);

        $this->pickupLocationIds = array_map('intval', $data['pickup_location_ids'] ?? []);
        $this->dropoffLocationIds = array_map('intval', $data['dropoff_location_ids'] ?? []);

        unset($data['pickup_location_ids'], $data['dropoff_location_ids']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $locationIds = array_values(array_unique([...$this->pickupLocationIds, ...$this->dropoffLocationIds]));
        $pivot = [];

        foreach ($locationIds as $locationId) {
            $pivot[$locationId] = [
                'allows_pickup' => in_array($locationId, $this->pickupLocationIds, true),
                'allows_dropoff' => in_array($locationId, $this->dropoffLocationIds, true),
            ];
        }

        $this->record->locations()->sync($pivot);
    }

    public function getPageClasses(): array
    {
        return [
            ...parent::getPageClasses(),
            'ir-cars-page',
            'ir-cars-page--create',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('backToList')
                ->label('Back')
                ->icon(Heroicon::OutlinedArrowLeft)
                ->color('gray')
                ->url(fn (): string => CarResource::getUrl('index'))
                ->tooltip('Return to vehicles list'),
        ];
    }
}
