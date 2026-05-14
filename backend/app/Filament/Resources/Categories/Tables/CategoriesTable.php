<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Models\Category;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Category Name')
                    ->searchable(),
                TextColumn::make('description')
                    ->label('Description')
                    ->limit(120),
                TextColumn::make('sort_order')
                    ->label('Ordering')
                    ->alignCenter()
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('move_up')
                    ->label('')
                    ->tooltip('Move up')
                    ->icon('heroicon-o-arrow-up')
                    ->action(function (Category $record): void {
                        $previous = Category::query()
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
                    ->action(function (Category $record): void {
                        $next = Category::query()
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
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
