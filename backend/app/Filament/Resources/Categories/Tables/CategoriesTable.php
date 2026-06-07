<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Models\MainCategory;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Main Category')
                    ->searchable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sub_categories_count')
                    ->label('Sub-categories')
                    ->counts('subCategories')
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
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('move_up')
                    ->label('')
                    ->tooltip('Move up')
                    ->icon('heroicon-o-arrow-up')
                    ->action(function (MainCategory $record): void {
                        $previous = MainCategory::query()
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
                    ->action(function (MainCategory $record): void {
                        $next = MainCategory::query()
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
                    DeleteBulkAction::make()
                        ->action(function (Collection $records): void {
                            if ($records->contains(fn (MainCategory $record): bool => $record->isCore())) {
                                Notification::make()
                                    ->title('Car and Campervan cannot be deleted')
                                    ->warning()
                                    ->send();
                            }

                            $records
                                ->reject(fn (MainCategory $record): bool => $record->isCore())
                                ->each->delete();
                        }),
                    ForceDeleteBulkAction::make()
                        ->action(function (Collection $records): void {
                            if ($records->contains(fn (MainCategory $record): bool => $record->isCore())) {
                                Notification::make()
                                    ->title('Car and Campervan cannot be permanently deleted')
                                    ->warning()
                                    ->send();
                            }

                            $records
                                ->reject(fn (MainCategory $record): bool => $record->isCore())
                                ->each->forceDelete();
                        }),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
