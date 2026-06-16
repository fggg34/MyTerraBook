<?php

namespace App\Filament\Resources\ListingReviews\Tables;

use App\Models\Car;
use App\Models\GuestHouse;
use App\Models\ListingReview;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ListingReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('photo_path')
                    ->label('Photo')
                    ->disk('public')
                    ->square()
                    ->defaultImageUrl(fn (): string => '')
                    ->toggleable(),

                TextColumn::make('guest_name')
                    ->label('Guest')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('rating')
                    ->label('Rating')
                    ->formatStateUsing(fn ($state): string => $state ? "{$state}/5" : '-')
                    ->sortable(),

                TextColumn::make('reviewable_type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Car::class => 'Car / Campervan',
                        GuestHouse::class => 'Guest house',
                        default => class_basename($state),
                    })
                    ->sortable(),

                TextColumn::make('listing_name')
                    ->label('Listing')
                    ->getStateUsing(fn (ListingReview $record): string => $record->listingName())
                    ->searchable(query: function ($query, string $search): void {
                        $query->where(function ($q) use ($search): void {
                            $q->whereHasMorph(
                                'reviewable',
                                [Car::class],
                                fn ($car) => $car->where('name', 'like', "%{$search}%"),
                            )->orWhereHasMorph(
                                'reviewable',
                                [GuestHouse::class],
                                fn ($house) => $house->where('name', 'like', "%{$search}%"),
                            );
                        });
                    }),

                TextColumn::make('body')
                    ->label('Review')
                    ->limit(60)
                    ->wrap()
                    ->toggleable(),

                ToggleColumn::make('is_approved')
                    ->label('Visible'),

                TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime()
                    ->sortable(),

                IconColumn::make('user_id')
                    ->label('Account')
                    ->boolean()
                    ->getStateUsing(fn (ListingReview $record): bool => $record->user_id !== null)
                    ->trueIcon('heroicon-o-user')
                    ->falseIcon('heroicon-o-user-minus')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('reviewable_type')
                    ->label('Listing type')
                    ->options([
                        Car::class => 'Car / Campervan',
                        GuestHouse::class => 'Guest house',
                    ]),
                TernaryFilter::make('is_approved')
                    ->label('Visible on site'),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (ListingReview $record): bool => ! $record->is_approved)
                    ->action(fn (ListingReview $record) => $record->update(['is_approved' => true])),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('approve')
                        ->label('Approve selected')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(fn (Collection $records) => $records->each->update(['is_approved' => true]))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('hide')
                        ->label('Hide selected')
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(fn (Collection $records) => $records->each->update(['is_approved' => false]))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
