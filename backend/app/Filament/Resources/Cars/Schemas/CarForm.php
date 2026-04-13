<?php

namespace App\Filament\Resources\Cars\Schemas;

use App\Models\Characteristic;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\GridDirection;
use Illuminate\Support\Str;

class CarForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required(),
                TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set, callable $get): void {
                        if (filled($state) && blank($get('slug'))) {
                            $set('slug', Str::slug($state));
                        }
                    }),
                TextInput::make('slug')
                    ->helperText('Leave blank to auto-generate from the vehicle name when you save.')
                    ->maxLength(255),
                Textarea::make('description')
                    ->columnSpanFull(),
                Select::make('transmission')
                    ->label('Transmission')
                    ->helperText('Shown on the public car page. Separate from “Characteristics” (tags like seats or equipment).')
                    ->options([
                        'Automatic' => 'Automatic',
                        'Manual' => 'Manual',
                        'Semi-automatic' => 'Semi-automatic',
                        'CVT' => 'CVT',
                    ])
                    ->searchable()
                    ->placeholder('—'),
                Select::make('fuel_type')
                    ->label('Fuel type')
                    ->helperText('Shown on the public car page.')
                    ->options([
                        'Petrol' => 'Petrol',
                        'Diesel' => 'Diesel',
                        'Electric' => 'Electric',
                        'Hybrid' => 'Hybrid',
                        'Plug-in hybrid' => 'Plug-in hybrid',
                        'Hydrogen' => 'Hydrogen',
                        'LPG' => 'LPG',
                        'Other' => 'Other',
                    ])
                    ->searchable()
                    ->placeholder('—'),
                FileUpload::make('main_image_path')
                    ->image(),
                TextInput::make('units_available')
                    ->label('Bookable unit count (capacity)')
                    ->helperText('How many of this vehicle can be rented at the same time. Use Fleet units only if you track individual physical vehicles (VIN-level) separately.')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('ical_import_url')
                    ->url(),
                Section::make('Characteristics')
                    ->description('Tick every tag that applies. Manage the master list under Catalog → Characteristics. Saved together when you create or update the vehicle.')
                    ->schema([
                        CheckboxList::make('characteristics')
                            ->label('Features')
                            ->relationship(
                                'characteristics',
                                'name',
                                fn ($query) => $query->orderBy('name'),
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn (Characteristic $record): string => filled($record->display_text)
                                    ? (string) $record->display_text
                                    : $record->name,
                            )
                            ->bulkToggleable()
                            ->columns(2)
                            ->gridDirection(GridDirection::Row)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),
                Section::make('Add-ons')
                    ->description('Optional extras linked to this vehicle (Catalog → Rental options).')
                    ->schema([
                        CheckboxList::make('rentalOptions')
                            ->label('Add-ons')
                            ->relationship(
                                'rentalOptions',
                                'name',
                                fn ($query) => $query->orderBy('name'),
                            )
                            ->bulkToggleable()
                            ->columns(2)
                            ->gridDirection(GridDirection::Row)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),
                Section::make('Locations')
                    ->description('Where this vehicle can be picked up or returned. Ticked locations allow both pickup and drop-off by default.')
                    ->schema([
                        CheckboxList::make('locations')
                            ->label('Locations')
                            ->relationship(
                                'locations',
                                'name',
                                fn ($query) => $query->orderBy('name'),
                            )
                            ->pivotData([
                                'allows_pickup' => true,
                                'allows_dropoff' => true,
                            ])
                            ->bulkToggleable()
                            ->columns(2)
                            ->gridDirection(GridDirection::Row)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),
                Toggle::make('is_active')
                    ->default(true)
                    ->required(),
            ]);
    }
}
