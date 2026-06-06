<?php

namespace App\Filament\GuestHouse\Resources;

use App\Enums\GuestHouseStatus;
use App\Enums\GuestHouseType;
use App\Filament\Clusters\GuestHouseCluster;
use App\Filament\GuestHouse\Resources\GuestHouseResource\Pages\CreateGuestHouse;
use App\Filament\GuestHouse\Resources\GuestHouseResource\Pages\EditGuestHouse;
use App\Filament\GuestHouse\Resources\GuestHouseResource\Pages\ListGuestHouses;
use App\Filament\GuestHouse\Resources\Schemas\GuestHouseForm;
use App\Filament\Pages\ListingReviewPage;
use App\Models\GuestHouse;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Support\Facades\FilamentView;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class GuestHouseResource extends Resource
{
    protected static ?string $model = GuestHouse::class;

    protected static ?string $cluster = GuestHouseCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static string|UnitEnum|null $navigationGroup = 'Properties';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $count = GuestHouse::query()->where('status', GuestHouseStatus::PendingReview)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return GuestHouseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail')
                    ->label('Cover')
                    ->disk('public')
                    ->square(),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('city')->searchable()->sortable(),
                TextColumn::make('max_guests')->label('Sleeps')->sortable(),
                TextColumn::make('bedrooms')->sortable(),
                TextColumn::make('type')->badge(),
                TextColumn::make('base_price_per_night')
                    ->label('Price/night')
                    ->formatStateUsing(fn ($state) => '€ '.number_format($state / 100, 2)),
                TextColumn::make('status')->badge(),
                TextColumn::make('host.name')->label('Host')->toggleable(),
                TextColumn::make('submitted_at')->dateTime()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('type')->options(collect(GuestHouseType::cases())->mapWithKeys(fn ($c) => [$c->value => ucfirst($c->value)])),
                SelectFilter::make('status')->options(collect(GuestHouseStatus::cases())->mapWithKeys(fn ($c) => [$c->value => str_replace('_', ' ', ucfirst($c->value))])),
                SelectFilter::make('city')->options(fn () => GuestHouse::query()->whereNotNull('city')->distinct()->pluck('city', 'city')),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn (GuestHouse $record): bool => $record->status === GuestHouseStatus::PendingReview)
                    ->requiresConfirmation()
                    ->action(function (GuestHouse $record): void {
                        $record->update([
                            'status' => GuestHouseStatus::Active,
                            'reviewed_at' => now(),
                            'reviewed_by' => auth()->id(),
                            'rejection_reason' => null,
                        ]);
                    }),
                Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn (GuestHouse $record): bool => $record->status === GuestHouseStatus::PendingReview)
                    ->form([
                        Textarea::make('rejection_reason')->required()->maxLength(2000),
                    ])
                    ->action(function (GuestHouse $record, array $data): void {
                        $record->update([
                            'status' => GuestHouseStatus::Rejected,
                            'reviewed_at' => now(),
                            'reviewed_by' => auth()->id(),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                    }),
                Action::make('requestChanges')
                    ->label('Request changes')
                    ->color('warning')
                    ->modalHeading('Request changes')
                    ->visible(fn (GuestHouse $record): bool => $record->status === GuestHouseStatus::PendingReview)
                    ->form(ListingReviewPage::requestChangesFormFields())
                    ->action(function (GuestHouse $record, array $data, $livewire): void {
                        if ($data['edit_myself'] ?? false) {
                            $url = GuestHouseResource::getUrl('edit', ['record' => $record]);
                            $livewire->redirect($url, navigate: FilamentView::hasSpaMode($url));

                            return;
                        }

                        $record->update([
                            'status' => GuestHouseStatus::Draft,
                            'reviewed_at' => now(),
                            'reviewed_by' => auth()->id(),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                    }),
            ])
            ->defaultSort('submitted_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGuestHouses::route('/'),
            'create' => CreateGuestHouse::route('/create'),
            'edit' => EditGuestHouse::route('/{record}/edit'),
        ];
    }
}
