<?php

namespace App\Filament\Resources\Coupons\Schemas;

use App\Models\Car;
use App\Support\AdminInputs;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Code')
                    ->extraAttributes(['id' => 'tb-coupon-code'])
                    ->schema([
                TextInput::make('code')
                    ->label('Code')
                    ->required(),
                TextInput::make('type')
                    ->label('Type')
                    ->required(),
                TextInput::make('discount_type')
                    ->label('Discount type')
                    ->required(),
                AdminInputs::isk('discount_fixed_cents', 'Discount amount'),
                AdminInputs::percentFromBips('discount_percent_bips', 'Discount (%)'),
                Select::make('vehicle_ids')
                    ->label('Limit to vehicles')
                    ->multiple()
                    ->options(fn () => Car::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),
                    ]),
                Section::make('Validity')
                    ->extraAttributes(['id' => 'tb-coupon-validity'])
                    ->schema([
                DatePicker::make('valid_from')
                    ->label('Valid from'),
                DatePicker::make('valid_to')
                    ->label('Valid to'),
                AdminInputs::isk('min_order_total_cents', 'Minimum order total'),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->required(),
                    ]),
            ]);
    }
}
