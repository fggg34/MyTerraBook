<?php

namespace App\Filament\Resources\Cars\Pages;

use App\Filament\Resources\Cars\CarResource;
use App\Filament\Resources\Cars\Schemas\CarForm;
use App\Models\Car;
use Filament\Actions\Action;
use Filament\Resources\Pages\Concerns\HasWizard;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;

class CreateCar extends CreateRecord
{
    use HasWizard;

    protected static string $resource = CarResource::class;

    protected static ?string $title = 'Add vehicle';

    protected static bool $canCreateAnother = false;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected array $pickupLocationIds = [];

    protected array $dropoffLocationIds = [];

    public function getSubheading(): string|Htmlable|null
    {
        return 'Add one car, or several matching units with their own plates.';
    }

    public function getSteps(): array
    {
        return CarForm::wizardSteps();
    }

    public function form(Schema $schema): Schema
    {
        return static::getResource()::form($schema)
            ->columns(null)
            ->components([
                Wizard::make($this->getSteps())
                    ->startOnStep($this->getStartStep())
                    ->cancelAction($this->getCancelFormAction())
                    ->submitAction($this->getSubmitFormAction())
                    ->alpineSubmitHandler('$wire.'.$this->getSubmitFormLivewireMethodName().'()')
                    ->skippable($this->hasSkippableSteps())
                    ->contained(false)
                    ->nextAction(fn (Action $action): Action => $action
                        ->label('Continue')
                        ->color('primary'))
                    ->previousAction(fn (Action $action): Action => $action->label('Back')),
            ]);
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Create vehicle')
            ->color('success');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Back to fleet');
    }

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
        $this->record->ensureFleetUnits();
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
        return [];
    }
}
