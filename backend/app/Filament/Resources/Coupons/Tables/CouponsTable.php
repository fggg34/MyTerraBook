<?php

namespace App\Filament\Resources\Coupons\Tables;

use App\Models\Coupon;
use App\Support\AdminTableBadgeColors;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CouponsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->view('filament.admin.discount-codes')
            ->columns([
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('type')
                    ->searchable(),
                TextColumn::make('discount_type')
                    ->searchable(),
                TextColumn::make('discount_fixed_cents')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('discount_percent_bips')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('valid_from')
                    ->date()
                    ->sortable(),
                TextColumn::make('valid_to')
                    ->date()
                    ->sortable(),
                TextColumn::make('min_order_total_cents')
                    ->numeric()
                    ->sortable(),
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
                SelectFilter::make('type')
                    ->label('Type')
                    ->options(fn (): array => Coupon::query()
                        ->whereNotNull('type')
                        ->where('type', '!=', '')
                        ->distinct()
                        ->orderBy('type')
                        ->pluck('type', 'type')
                        ->mapWithKeys(fn ($type): array => [(string) $type => AdminTableBadgeColors::humanize((string) $type)])
                        ->all()),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Active',
                        'expired' => 'Expired',
                        'inactive' => 'Inactive',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'expired' => $query->whereNotNull('valid_to')->whereDate('valid_to', '<', now()->toDateString()),
                            'inactive' => $query->where('is_active', false)->where(function (Builder $inner): void {
                                $inner->whereNull('valid_to')->orWhereDate('valid_to', '>=', now()->toDateString());
                            }),
                            'active' => $query->where('is_active', true)->where(function (Builder $inner): void {
                                $inner->whereNull('valid_to')->orWhereDate('valid_to', '>=', now()->toDateString());
                            }),
                            default => $query,
                        };
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
