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
use App\Services\GuestHouseDuplicationService;
use App\Support\AdminTableBadgeColors;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Support\Facades\FilamentView;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class GuestHouseResource extends Resource
{
    protected static ?string $model = GuestHouse::class;

    protected static ?string $recordTitleAttribute = 'name';

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
        return 'primary';
    }

    public static function form(Schema $schema): Schema
    {
        return GuestHouseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->view('filament.admin.guest-house-properties')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['host', 'amenities']))
            ->columns([
                ImageColumn::make('thumbnail')
                    ->label('')
                    ->disk('public')
                    ->square(),
                TextColumn::make('name')
                    ->label('Property')
                    ->description(fn (GuestHouse $record): ?string => $record->slug ? '/guesthouses/'.$record->slug : ($record->city ?: null))
                    ->searchable(['name', 'city'])
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (): string => AdminTableBadgeColors::neutral())
                    ->formatStateUsing(fn (mixed $state): string => AdminTableBadgeColors::humanize($state)),
                TextColumn::make('max_guests')
                    ->label('Capacity')
                    ->formatStateUsing(fn ($state): string => ((int) $state).' guests')
                    ->description(fn (GuestHouse $record): string => ((int) $record->bedrooms).' bedroom'.(((int) $record->bedrooms) === 1 ? '' : 's').' · '.((int) $record->bathrooms).' bath')
                    ->sortable(),
                TextColumn::make('base_price_per_night')
                    ->label('Price / night')
                    ->formatStateUsing(fn ($state) => '€ '.number_format($state / 100, 0))
                    ->description('min. 1 night'),
                TextColumn::make('host.name')->label('Host')->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (mixed $state): string => AdminTableBadgeColors::guestHouseStatus($state))
                    ->formatStateUsing(fn (mixed $state): string => AdminTableBadgeColors::humanize($state)),
                TextColumn::make('updated_at')->label('Updated')->date('d M Y')->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')->options(collect(GuestHouseType::cases())->mapWithKeys(fn ($c) => [$c->value => ucfirst($c->value)])),
                SelectFilter::make('city')->options(fn () => GuestHouse::query()->whereNotNull('city')->distinct()->pluck('city', 'city')),
                SelectFilter::make('user_id')
                    ->label('Host')
                    ->relationship('host', 'name')
                    ->searchable()
                    ->preload(),
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
                    ->color('primary')
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
                Action::make('duplicate')
                    ->label('Duplicate')
                    ->icon(Heroicon::OutlinedSquare2Stack)
                    ->color('gray')
                    ->modalHeading('Duplicate this guesthouse')
                    ->modalDescription('Creates copies with the same details, amenities, photos, room layout cards, and seasonal prices.')
                    ->form([
                        TextInput::make('copies')
                            ->label('Number of copies')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->maxValue(50)
                            ->required(),
                    ])
                    ->action(function (GuestHouse $record, array $data, GuestHouseDuplicationService $duplicationService): void {
                        $duplicationService->duplicate($record, (int) $data['copies']);
                    })
                    ->successNotificationTitle(function (array $data): string {
                        $count = max(1, (int) ($data['copies'] ?? 1));

                        return $count === 1
                            ? 'Guesthouse duplicated'
                            : "{$count} guesthouses duplicated";
                    }),
                DeleteAction::make(),
            ])
            ->searchPlaceholder('Search name or city...')
            ->defaultSort('updated_at', 'desc')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
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
