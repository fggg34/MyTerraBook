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
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

class ListCars extends ListRecords
{
    protected static string $resource = CarResource::class;

    protected static ?string $title = 'Vehicles';

    #[Url(as: 'view')]
    public string $viewMode = 'grid';

    public function mount(): void
    {
        parent::mount();

        $this->tableFilters = array_replace_recursive([
            'listing_status' => ['value' => null],
            'sub_category_id' => ['value' => null],
            'locations' => ['value' => null],
            'drive_type' => ['value' => null],
            'user_id' => ['value' => null],
            'is_active' => ['value' => null],
        ], $this->tableFilters ?? []);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            EmbeddedTable::make(),
        ]);
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->badge((string) Car::query()->count()),
            'active' => Tab::make('Active')
                ->badge((string) Car::query()->where('is_active', true)->count())
                ->modifyQueryUsing(fn ($query) => $query->where('is_active', true)),
            'inactive' => Tab::make('Inactive')
                ->badge((string) Car::query()->where('is_active', false)->count())
                ->modifyQueryUsing(fn ($query) => $query->where('is_active', false)),
            'pending_review' => Tab::make('Pending review')
                ->badge((string) Car::query()->where('listing_status', ListingApprovalStatus::PendingReview)->count())
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
