<?php

namespace App\Filament\Resources\ListingReviews\Schemas;

use App\Models\Car;
use App\Models\GuestHouse;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ListingReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('reviewable_type')
                    ->label('Listing type')
                    ->options([
                        Car::class => 'Car / Campervan',
                        GuestHouse::class => 'Guest house',
                    ])
                    ->required()
                    ->live()
                    ->disabled(fn (?string $operation): bool => $operation === 'edit'),

                Select::make('reviewable_id')
                    ->label('Listing')
                    ->options(fn (Get $get): array => self::listingOptions($get('reviewable_type')))
                    ->searchable()
                    ->required()
                    ->disabled(fn (?string $operation): bool => $operation === 'edit'),

                TextInput::make('guest_name')
                    ->label('Guest name')
                    ->required()
                    ->maxLength(80),

                Select::make('rating')
                    ->options([
                        1 => '1, Poor',
                        2 => '2, Fair',
                        3 => '3, Good',
                        4 => '4, Great',
                        5 => '5, Excellent',
                    ])
                    ->required()
                    ->native(false),

                Textarea::make('body')
                    ->label('Review')
                    ->required()
                    ->minLength(10)
                    ->maxLength(2000)
                    ->rows(5)
                    ->columnSpanFull(),

                FileUpload::make('photo_path')
                    ->label('Guest photo')
                    ->disk('public')
                    ->directory('listing-reviews')
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->columnSpanFull(),

                Toggle::make('is_approved')
                    ->label('Visible on site')
                    ->helperText('Only approved reviews appear on the public listing page.')
                    ->default(true),
            ]);
    }

    /**
     * @return array<int|string, string>
     */
    private static function listingOptions(?string $type): array
    {
        return match ($type) {
            Car::class => Car::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->pluck('name', 'id')
                ->all(),
            GuestHouse::class => GuestHouse::query()
                ->orderBy('name')
                ->pluck('name', 'id')
                ->all(),
            default => [],
        };
    }
}
