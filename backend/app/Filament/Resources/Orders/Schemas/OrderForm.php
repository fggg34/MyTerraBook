<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use App\Enums\RentalStatus;
use App\Models\CarUnit;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('reference')
                    ->disabled()
                    ->dehydrated(false)
                    ->visibleOn('edit'),
                Select::make('user_id')
                    ->relationship('user', 'name'),
                Select::make('car_id')
                    ->relationship('car', 'name')
                    ->required(),
                Select::make('car_unit_id')
                    ->relationship('carUnit', 'id', modifyQueryUsing: fn ($q) => $q->with('car'))
                    ->getOptionLabelFromRecordUsing(fn (CarUnit $u) => ($u->car?->name ?? 'Car').' · unit #'.$u->id),
                Select::make('price_type_id')
                    ->relationship('priceType', 'name'),
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
                Select::make('order_status')
                    ->options(collect(OrderStatus::cases())->mapWithKeys(fn (OrderStatus $s) => [$s->value => str_replace('_', ' ', ucfirst($s->value))]))
                    ->default(OrderStatus::Pending->value)
                    ->required(),
                Select::make('rental_status')
                    ->options(collect(RentalStatus::cases())->mapWithKeys(fn (RentalStatus $s) => [$s->value => str_replace('_', ' ', ucfirst($s->value))])),
                TextInput::make('customer_name')
                    ->required(),
                TextInput::make('customer_email')
                    ->email()
                    ->required(),
                TextInput::make('customer_phone')
                    ->tel(),
                TextInput::make('customer_country'),
                TextInput::make('base_rental_cents')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('extras_cents')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('fees_cents')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('discount_cents')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('tax_cents')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_cents')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('currency')
                    ->required()
                    ->default('EUR'),
                Select::make('coupon_id')
                    ->relationship('coupon', 'code'),
                Textarea::make('notes')
                    ->columnSpanFull(),
                Textarea::make('admin_internal_note')
                    ->columnSpanFull(),
                Select::make('created_by_admin_id')
                    ->relationship('createdByAdmin', 'name'),
                DateTimePicker::make('payment_lock_expires_at'),
            ]);
    }
}
