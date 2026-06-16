<?php

namespace App\Filament\Resources\RentalConditions\Tables;

use App\Models\RentalCondition;
use App\Support\IconCatalog;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class RentalConditionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Admin name')
                    ->searchable(),
                TextColumn::make('title')
                    ->label('Listing title')
                    ->searchable()
                    ->limit(80),
                TextColumn::make('description')
                    ->label('Description')
                    ->limit(100)
                    ->toggleable(),
                TextColumn::make('icon')
                    ->label('Icon')
                    ->placeholder('—')
                    ->formatStateUsing(function (?string $state): HtmlString|string {
                        if (! $state) {
                            return '—';
                        }

                        $html = IconCatalog::tableIconHtml($state);

                        return $html !== '' ? new HtmlString($html) : $state;
                    }),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label('Ordering')
                    ->alignCenter()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('move_up')
                    ->label('')
                    ->tooltip('Move up')
                    ->icon('heroicon-o-arrow-up')
                    ->action(function (RentalCondition $record): void {
                        $previous = RentalCondition::query()
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
                    ->action(function (RentalCondition $record): void {
                        $next = RentalCondition::query()
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
