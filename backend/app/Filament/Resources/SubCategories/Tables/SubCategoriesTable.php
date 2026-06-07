<?php

namespace App\Filament\Resources\SubCategories\Tables;

use App\Models\SubCategory;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class SubCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('mainCategory.name')
                    ->label('Main Category')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Sub Category')
                    ->searchable(),
                IconColumn::make('is_search_filter')
                    ->label('Search filter')
                    ->boolean()
                    ->alignCenter(),
                TextColumn::make('description')
                    ->label('Description')
                    ->limit(120),
                TextColumn::make('sort_order')
                    ->label('Ordering')
                    ->alignCenter()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('main_category_id')
                    ->label('Main Category')
                    ->relationship('mainCategory', 'name'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('move_up')
                    ->label('')
                    ->tooltip('Move up')
                    ->icon('heroicon-o-arrow-up')
                    ->action(function (SubCategory $record): void {
                        $previous = SubCategory::query()
                            ->where('main_category_id', $record->main_category_id)
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
                    ->action(function (SubCategory $record): void {
                        $next = SubCategory::query()
                            ->where('main_category_id', $record->main_category_id)
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
