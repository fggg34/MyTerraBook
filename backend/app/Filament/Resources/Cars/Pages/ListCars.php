<?php

namespace App\Filament\Resources\Cars\Pages;

use App\Enums\ListingApprovalStatus;
use App\Filament\Pages\ListingReviewPage;
use App\Filament\Resources\Cars\CarResource;
use App\Filament\Resources\DailyFares\DailyFareResource;
use App\Models\Car;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class ListCars extends ListRecords
{
    protected static string $resource = CarResource::class;

    protected static ?string $title = 'Vehicles';

    public string $viewMode = 'grid';

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->badge(Car::query()->count() ?: null),
            'active' => Tab::make('Active')
                ->badge(Car::query()->where('is_active', true)->count() ?: null)
                ->modifyQueryUsing(fn ($query) => $query->where('is_active', true)),
            'inactive' => Tab::make('Inactive')
                ->badge(Car::query()->where('is_active', false)->count() ?: null)
                ->modifyQueryUsing(fn ($query) => $query->where('is_active', false)),
            'pending_review' => Tab::make('Pending review')
                ->badge(Car::query()->where('listing_status', ListingApprovalStatus::PendingReview)->count() ?: null)
                ->modifyQueryUsing(fn ($query) => $query->where('listing_status', ListingApprovalStatus::PendingReview)),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'all';
    }

    public function getPageClasses(): array
    {
        return [
            ...parent::getPageClasses(),
            'ir-cars-page',
            'ir-cars-page--list',
            $this->viewMode === 'grid' ? 'ir-cars-page--grid' : 'ir-cars-page--table',
        ];
    }

    public function toggleViewMode(): void
    {
        $this->viewMode = $this->viewMode === 'grid' ? 'table' : 'grid';
    }

    protected function applySearchToTableQuery(Builder $query): Builder
    {
        $search = trim((string) $this->getTableSearch());
        if ($search === '') {
            return $query;
        }

        $like = '%'.$search.'%';

        return $query->where(function (Builder $inner) use ($like): void {
            $inner->where('name', 'like', $like)
                ->orWhere('slug', 'like', $like)
                ->orWhereHas('host', fn (Builder $host) => $host->where('name', 'like', $like));
        });
    }

    protected function getHeaderActions(): array
    {
        return [
            ListingReviewPage::makeApprovalQueueHeaderAction(),
            Action::make('pricing')
                ->label('Pricing')
                ->icon(Heroicon::OutlinedCurrencyDollar)
                ->color('gray')
                ->outlined()
                ->url(DailyFareResource::getUrl('index')),
            Action::make('toggleView')
                ->label(fn (): string => $this->viewMode === 'grid' ? 'Table view' : 'Card view')
                ->icon(fn () => $this->viewMode === 'grid' ? Heroicon::OutlinedTableCells : Heroicon::OutlinedSquares2x2)
                ->color('gray')
                ->outlined()
                ->action(fn () => $this->toggleViewMode()),
            CreateAction::make()->label('Add vehicle'),
        ];
    }
}
