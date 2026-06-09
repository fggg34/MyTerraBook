<?php

namespace App\Filament\Resources\Locations\Pages;

use App\Filament\Resources\Locations\LocationResource;
use App\Models\Car;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditLocation extends EditRecord
{
    protected static string $resource = LocationResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    public function getPageClasses(): array
    {
        return [
            ...parent::getPageClasses(),
            'ir-location-form-page',
            'ir-location-form-page--edit',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('assignToAllCars')
                ->label('Assign to all active vehicles')
                ->icon('heroicon-o-truck')
                ->color('gray')
                ->requiresConfirmation()
                ->modalDescription(
                    'This location will be enabled for pick-up and drop-off on every active vehicle. '
                    .'Required for the location to appear in homepage search.'
                )
                ->action(function (): void {
                    $location = $this->getRecord();
                    $cars = Car::query()->where('is_active', true)->get();
                    $count = 0;

                    foreach ($cars as $car) {
                        if ($car->locations()->whereKey($location->id)->exists()) {
                            $car->locations()->updateExistingPivot($location->id, [
                                'allows_pickup' => true,
                                'allows_dropoff' => true,
                            ]);
                        } else {
                            $car->locations()->attach($location->id, [
                                'allows_pickup' => true,
                                'allows_dropoff' => true,
                            ]);
                        }
                        $count++;
                    }

                    Notification::make()
                        ->success()
                        ->title('Location assigned')
                        ->body("Linked to {$count} active vehicle(s).")
                        ->send();
                }),
            DeleteAction::make(),
        ];
    }
}
