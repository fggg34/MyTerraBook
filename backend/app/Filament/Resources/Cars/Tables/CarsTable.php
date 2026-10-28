<?php

namespace App\Filament\Resources\Cars\Tables;

use App\Enums\DriveType;
use App\Enums\ListingApprovalStatus;
use App\Filament\Pages\ListingReviewPage;
use App\Filament\Resources\Cars\CarResource;
use App\Filament\Resources\Cars\Pages\ListCars;
use App\Models\Car;
use App\Services\CarDuplicationService;
use App\Support\AdminTableBadgeColors;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Support\Facades\FilamentView;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CarsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['subCategory.mainCategory', 'host', 'dailyFares']))
            ->searchPlaceholder('Search vehicle or host...')
            ->emptyStateHeading('No vehicles match')
            ->emptyStateDescription('Try clearing filters or add a vehicle.')
            ->contentGrid(function () use ($table): ?array {
                $livewire = $table->getLivewire();

                return $livewire instanceof ListCars && $livewire->viewMode === 'grid'
                    ? ['md' => 2, 'xl' => 4]
                    : null;
            })
            ->recordUrl(fn (Car $record): string => CarResource::getUrl('edit', ['record' => $record]))
            ->columns([
                ViewColumn::make('card')
                    ->label('')
                    ->view('filament.cars.vehicle-card')
                    ->visible(fn ($livewire): bool => $livewire instanceof ListCars && $livewire->viewMode === 'grid'),
                ImageColumn::make('main_image_path')
                    ->label('Photo')
                    ->imageSize(40)
                    ->square()
                    ->hidden(fn ($livewire): bool => $livewire instanceof ListCars && $livewire->viewMode === 'grid'),
                TextColumn::make('name')
                    ->label('Vehicle')
                    ->weight('semibold')
                    ->description(function (Car $record): string {
                        $units = max(1, (int) ($record->units_available ?? 1));
                        $parts = [$units === 1 ? '1 unit' : $units.' units'];
                        if ($record->year) {
                            $parts[] = (string) $record->year;
                        }

                        return implode(' · ', $parts);
                    })
                    ->wrap()
                    ->searchable()
                    ->sortable()
                    ->hidden(fn ($livewire): bool => $livewire instanceof ListCars && $livewire->viewMode === 'grid'),
                TextColumn::make('subCategory.name')
                    ->label('Category')
                    ->description(fn ($record) => $record->subCategory?->mainCategory?->name)
                    ->placeholder('-')
                    ->hidden(fn ($livewire): bool => $livewire instanceof ListCars && $livewire->viewMode === 'grid'),
                TextColumn::make('drive_type')
                    ->label('Drivetrain')
                    ->formatStateUsing(function (mixed $state): string {
                        if ($state instanceof DriveType) {
                            return $state->label();
                        }
                        if (is_string($state) && $state !== '') {
                            return DriveType::tryFrom($state)?->label() ?? $state;
                        }

                        return '-';
                    })
                    ->placeholder('-')
                    ->hidden(fn ($livewire): bool => $livewire instanceof ListCars && $livewire->viewMode === 'grid'),
                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (mixed $state): string => $state ? 'Active' : 'Inactive')
                    ->color(fn (mixed $state): string => $state ? 'success' : 'gray')
                    ->hidden(fn ($livewire): bool => $livewire instanceof ListCars && $livewire->viewMode === 'grid'),
                TextColumn::make('listing_status')
                    ->label('Review status')
                    ->badge()
                    ->color(fn (mixed $state): string => AdminTableBadgeColors::listingApprovalStatus($state))
                    ->formatStateUsing(fn (mixed $state): string => AdminTableBadgeColors::humanize($state))
                    ->hidden(fn ($livewire): bool => $livewire instanceof ListCars && $livewire->viewMode === 'grid'),
                TextColumn::make('subCategory.mainCategory.name')
                    ->label('Main Category')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('transmission')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('fuel_type')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('units_available')
                    ->label('Fleet size')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ical_import_url')
                    ->label('iCal import URL')
                    ->limit(35)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('host.name')->label('Host / partner')->placeholder('Platform fleet')->searchable()->wrap()->toggleable()->hidden(fn ($livewire): bool => $livewire instanceof ListCars && $livewire->viewMode === 'grid'),
                TextColumn::make('submitted_at')->label('Submitted')->dateTime()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('listing_status')->options(collect(ListingApprovalStatus::cases())->mapWithKeys(fn ($c) => [$c->value => str_replace('_', ' ', ucfirst($c->value))])),
                SelectFilter::make('sub_category_id')->label('Category')->relationship('subCategory', 'name')->searchable()->preload(),
                SelectFilter::make('locations')->label('Location')->relationship('locations', 'name')->searchable()->preload(),
                SelectFilter::make('drive_type')
                    ->label('Drivetrain')
                    ->options(collect(DriveType::cases())->mapWithKeys(
                        fn (DriveType $type): array => [$type->value => $type->label()]
                    )),
                SelectFilter::make('user_id')->label('Host / partner')->relationship('host', 'name')->searchable(),
                TernaryFilter::make('is_active')->label('Active')->trueLabel('Active vehicles')->falseLabel('Inactive vehicles'),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn (Car $record): bool => $record->listing_status === ListingApprovalStatus::PendingReview)
                    ->requiresConfirmation()
                    ->action(function (Car $record): void {
                        $record->update([
                            'listing_status' => ListingApprovalStatus::Approved,
                            'is_active' => true,
                            'reviewed_at' => now(),
                            'reviewed_by' => auth()->id(),
                            'rejection_reason' => null,
                        ]);
                    }),
                Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn (Car $record): bool => $record->listing_status === ListingApprovalStatus::PendingReview)
                    ->form([
                        Textarea::make('rejection_reason')->required()->maxLength(2000),
                    ])
                    ->action(function (Car $record, array $data): void {
                        $record->update([
                            'listing_status' => ListingApprovalStatus::Rejected,
                            'is_active' => false,
                            'reviewed_at' => now(),
                            'reviewed_by' => auth()->id(),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                    }),
                ActionGroup::make([
                    Action::make('requestChanges')
                        ->label('Request changes')
                        ->color('primary')
                        ->modalHeading('Request changes')
                        ->visible(fn (Car $record): bool => $record->listing_status === ListingApprovalStatus::PendingReview)
                        ->form(ListingReviewPage::requestChangesFormFields())
                        ->action(function (Car $record, array $data, $livewire): void {
                            if ($data['edit_myself'] ?? false) {
                                $url = CarResource::getUrl('edit', ['record' => $record]);
                                $livewire->redirect($url, navigate: FilamentView::hasSpaMode($url));

                                return;
                            }

                            $record->update([
                                'listing_status' => ListingApprovalStatus::Draft,
                                'is_active' => false,
                                'reviewed_at' => now(),
                                'reviewed_by' => auth()->id(),
                                'rejection_reason' => $data['rejection_reason'],
                            ]);
                        }),
                    Action::make('duplicate')
                        ->label('Duplicate')
                        ->icon(Heroicon::OutlinedSquare2Stack)
                        ->color('gray')
                        ->modalHeading('Duplicate this vehicle')
                        ->modalDescription('Creates copies with the same category, specs, characteristics, add-ons, locations, and pricing.')
                        ->form([
                            TextInput::make('copies')
                                ->label('Number of copies')
                                ->numeric()
                                ->default(1)
                                ->minValue(1)
                                ->maxValue(50)
                                ->required(),
                        ])
                        ->action(function (Car $record, array $data, CarDuplicationService $duplicationService): void {
                            $duplicationService->duplicate($record, (int) $data['copies']);
                        })
                        ->successNotificationTitle(function (array $data): string {
                            $count = max(1, (int) ($data['copies'] ?? 1));

                            return $count === 1
                                ? 'Vehicle duplicated'
                                : "{$count} vehicles duplicated";
                        }),
                    DeleteAction::make(),
                ]),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
