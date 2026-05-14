<?php

namespace App\Filament\Resources\Characteristics\Tables;

use App\Models\Characteristic;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CharacteristicsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Characteristic Name')
                    ->searchable(),
                TextColumn::make('display_text')
                    ->label('Text Next to Icon')
                    ->searchable()
                    ->limit(120),
                ImageColumn::make('icon_path')
                    ->label('Characteristic icon'),
                TextColumn::make('sort_order')
                    ->label('Ordering')
                    ->alignCenter()
                    ->sortable()
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('move_up')
                    ->label('')
                    ->tooltip('Move up')
                    ->icon('heroicon-o-arrow-up')
                    ->action(function (Characteristic $record): void {
                        $previous = Characteristic::query()
                            ->where('sort_order', '<', $record->sort_order)
                            ->orderByDesc('sort_order')
                            ->first();

                        if (! $previous) {
                            return;
                        }

                        $currentOrder = (int) $record->sort_order;
                        $record->update(['sort_order' => (int) $previous->sort_order]);
                        $previous->update(['sort_order' => $currentOrder]);
                    }),
                Action::make('move_down')
                    ->label('')
                    ->tooltip('Move down')
                    ->icon('heroicon-o-arrow-down')
                    ->action(function (Characteristic $record): void {
                        $next = Characteristic::query()
                            ->where('sort_order', '>', $record->sort_order)
                            ->orderBy('sort_order')
                            ->first();

                        if (! $next) {
                            return;
                        }

                        $currentOrder = (int) $record->sort_order;
                        $record->update(['sort_order' => (int) $next->sort_order]);
                        $next->update(['sort_order' => $currentOrder]);
                    }),
                EditAction::make(),
            ])
            ->defaultSort('sort_order')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
