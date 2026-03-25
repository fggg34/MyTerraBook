<?php

namespace App\Filament\Resources\Bookings\Schemas;

use App\Enums\BookingStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('reference')
                    ->required(),
                TextInput::make('user_id')
                    ->numeric(),
                Select::make('car_id')
                    ->relationship('car', 'name')
                    ->required(),
                Select::make('pickup_location_id')
                    ->relationship('pickupLocation', 'name')
                    ->required(),
                Select::make('dropoff_location_id')
                    ->relationship('dropoffLocation', 'name')
                    ->required(),
                DateTimePicker::make('pickup_at')
                    ->required(),
                DateTimePicker::make('dropoff_at')
                    ->required(),
                Select::make('status')
                    ->options([
                        BookingStatus::Pending->value => 'Pending',
                        BookingStatus::Confirmed->value => 'Confirmed',
                        BookingStatus::Cancelled->value => 'Cancelled',
                    ])
                    ->default(BookingStatus::Pending->value)
                    ->required(),
                TextInput::make('customer_name')
                    ->required(),
                TextInput::make('customer_email')
                    ->email()
                    ->required(),
                TextInput::make('customer_phone')
                    ->tel(),
                TextInput::make('rental_subtotal')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('extras_subtotal')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('discount_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('tax_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total')
                    ->required()
                    ->numeric()
                    ->default(0),
                Select::make('coupon_id')
                    ->relationship('coupon', 'code')
                    ->searchable()
                    ->preload(),
                TextInput::make('currency')
                    ->required()
                    ->default('USD'),
                KeyValue::make('pricing_snapshot')
                    ->default([])
                    ->columnSpanFull()
                    ->addActionLabel('Add line')
                    ->keyLabel('Key')
                    ->valueLabel('Value'),
                Textarea::make('notes')
                    ->columnSpanFull(),
                TextInput::make('created_by_admin_id')
                    ->numeric(),
            ]);
    }
}
