<?php

namespace App\Filament\Resources\Cars\Pages;

use App\Enums\ListingApprovalStatus;
use App\Filament\Resources\Cars\CarResource;
use App\Filament\Resources\SpecialPrices\SpecialPriceResource;
use App\Models\Car;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\View\View;

class EditCar extends EditRecord
{
    protected static string $resource = CarResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected array $pickupLocationIds = [];

    protected array $dropoffLocationIds = [];

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $name = trim((string) ($data['name'] ?? ''));
        $data['slug'] = Car::uniqueSlugFromName($name, (int) $this->record->getKey());

        $this->pickupLocationIds = array_map('intval', $data['pickup_location_ids'] ?? []);
        $this->dropoffLocationIds = array_map('intval', $data['dropoff_location_ids'] ?? []);

        unset($data['pickup_location_ids'], $data['dropoff_location_ids']);

        return $data;
    }

    protected function afterSave(): void
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
            'ir-cars-page--edit',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('pricingRules')
                ->label('Pricing rules')
                ->color('gray')
                ->url(SpecialPriceResource::getUrl('index')),
            Action::make('previewStorefront')
                ->label('Preview')
                ->icon(Heroicon::OutlinedEye)
                ->color('gray')
                ->url(fn (): string => config('app.frontend_url').'/cars/'.$this->getRecord()->getKey())
                ->openUrlInNewTab(),
            DeleteAction::make()->extraAttributes(['class' => 'tb-hidden-action']),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->tooltip('Save changes (⌘S or Ctrl+S)');
    }

    public function getHeader(): ?View
    {
        $car = $this->getRecord();
        if (! $car instanceof Car) {
            return null;
        }

        $review = match ($car->listing_status) {
            ListingApprovalStatus::PendingReview => 'Pending review',
            ListingApprovalStatus::Approved => 'Approved',
            ListingApprovalStatus::Rejected => 'Rejected',
            default => 'Draft',
        };

        return view('filament.panel.edit-record-header', [
            'title' => $car->name,
            'backUrl' => CarResource::getUrl('index'),
            'backLabel' => 'Vehicles',
            'pills' => [
                ['label' => $car->is_active ? 'Active' : 'Inactive', 'tone' => $car->is_active ? 'ok' : 'neutral'],
                ['label' => $review, 'tone' => $car->listing_status === ListingApprovalStatus::Approved ? 'ok' : 'warn'],
            ],
            'actions' => $this->getCachedHeaderActions(),
            'sections' => [
                'tb-identity' => 'Identity',
                'tb-specs' => 'Specs',
                'tb-features' => 'Features',
                'tb-photos' => 'Photos',
                'tb-description' => 'Description',
                'tb-seo' => 'SEO',
                'tb-notes' => 'Notes & extras',
            ],
        ]);
    }
}
