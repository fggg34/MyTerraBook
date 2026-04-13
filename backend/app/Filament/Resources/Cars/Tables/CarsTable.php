<?php

namespace App\Filament\Resources\Cars\Tables;

use App\Models\Car;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
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
                //
            ])
            ->recordActions([
                EditAction::make(),
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
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
