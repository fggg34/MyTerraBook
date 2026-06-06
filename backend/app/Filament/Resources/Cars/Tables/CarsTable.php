<?php

namespace App\Filament\Resources\Cars\Tables;

use App\Enums\ListingApprovalStatus;
use App\Filament\Pages\ListingReviewPage;
use App\Filament\Resources\Cars\CarResource;
use App\Models\Car;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Facades\FilamentView;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CarsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category.name')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('transmission')
                    ->toggleable(),
                TextColumn::make('fuel_type')
                    ->toggleable(),
                TextColumn::make('slug')
                    ->searchable(),
                ImageColumn::make('main_image_path'),
                TextColumn::make('units_available')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('ical_import_url')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('listing_status')->badge(),
                TextColumn::make('host.name')->label('Host')->toggleable(),
                TextColumn::make('submitted_at')->dateTime()->toggleable(),
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
                Action::make('requestChanges')
                    ->label('Request changes')
                    ->color('warning')
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
                    ->requiresConfirmation()
                    ->modalHeading('Duplicate this vehicle?')
                    ->modalDescription('Creates a copy with the same category, specs, characteristics, add-ons, and locations. Daily prices are not copied.')
                    ->action(function (Car $record): void {
                        $record->loadMissing(['characteristics', 'rentalOptions', 'locations']);

                        $replica = $record->replicate([
                            'id',
                            'slug',
                            'created_at',
                            'updated_at',
                        ]);
                        $replica->name = $record->name.' (copy)';
                        $replica->slug = null;
                        $replica->save();

                        $replica->characteristics()->sync($record->characteristics->pluck('id')->all());
                        $replica->rentalOptions()->sync($record->rentalOptions->pluck('id')->all());

                        $pivotRows = [];
                        foreach ($record->locations as $location) {
                            $pivotRows[$location->id] = [
                                'allows_pickup' => (bool) $location->pivot->allows_pickup,
                                'allows_dropoff' => (bool) $location->pivot->allows_dropoff,
                            ];
                        }
                        $replica->locations()->sync($pivotRows);
                    })
                    ->successNotificationTitle('Vehicle duplicated'),
                DeleteAction::make(),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
