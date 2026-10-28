<?php

namespace App\Filament\GuestHouse\Resources;

use App\Enums\GuestHouseBookingStatus;
use App\Filament\Clusters\GuestHouseCluster;
use App\Filament\GuestHouse\Resources\GuestHouseBookingResource\Pages\ListGuestHouseBookings;
use App\Filament\GuestHouse\Resources\GuestHouseBookingResource\Pages\ViewGuestHouseBooking;
use App\Models\GuestHouseBooking;
use App\Support\BookingConfirmationUrl;
use App\Support\AdminTableBadgeColors;
use App\Support\Money;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class GuestHouseBookingResource extends Resource
{
    protected static ?string $model = GuestHouseBooking::class;

    protected static ?string $recordTitleAttribute = 'booking_reference';

    protected static ?string $cluster = GuestHouseCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'Bookings';

    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Booking')->schema([
                TextEntry::make('booking_reference'),
                TextEntry::make('confirmation_url')
                    ->label('Confirmation page')
                    ->url(fn (?string $state) => $state)
                    ->openUrlInNewTab()
                    ->columnSpanFull(),
                TextEntry::make('status')->badge(),
                TextEntry::make('guest_name'),
                TextEntry::make('guest_email'),
                TextEntry::make('guest_phone'),
                TextEntry::make('guestHouse.name')->label('Property'),
                TextEntry::make('check_in')->date(),
                TextEntry::make('check_out')->date(),
                TextEntry::make('nights'),
                TextEntry::make('guests_count'),
                TextEntry::make('total_amount')
                    ->formatStateUsing(fn ($state) => '€ '.Money::formatDecimalFromCents((int) $state)),
                TextEntry::make('special_requests')->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->view('filament.admin.guest-house-bookings')
            ->modifyQueryUsing(fn ($query) => $query->with('guestHouse'))
            ->columns([
                TextColumn::make('booking_reference')->label('Reference')->searchable(),
                TextColumn::make('guest_name')->label('Guest')->searchable(),
                TextColumn::make('guestHouse.name')
                    ->label('Property')
                    ->placeholder('No property linked')
                    ->description(fn (GuestHouseBooking $record): ?string => $record->guestHouse?->city),
                TextColumn::make('check_in')
                    ->label('Stay')
                    ->formatStateUsing(function ($state, GuestHouseBooking $record): string {
                        $from = $record->check_in?->format('j M Y');
                        $to = $record->check_out?->format('j M Y');

                        return trim($from.' → '.$to);
                    })
                    ->description(fn (GuestHouseBooking $record): string => ((int) $record->nights).' nights'),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => $state ? '€ '.Money::formatDecimalFromCents((int) $state) : '-'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (mixed $state): string => AdminTableBadgeColors::guestHouseBookingStatus($state))
                    ->formatStateUsing(fn (mixed $state): string => AdminTableBadgeColors::humanize($state)),
            ])
            ->filters([
                SelectFilter::make('status')->options(collect(GuestHouseBookingStatus::cases())->mapWithKeys(fn ($c) => [$c->value => AdminTableBadgeColors::humanize($c)])),
                SelectFilter::make('guest_house_id')
                    ->relationship('guestHouse', 'name')
                    ->label('Property'),
                Filter::make('date_range')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from'),
                        \Filament\Forms\Components\DatePicker::make('to'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $d) => $q->where('check_in', '>=', $d))
                            ->when($data['to'] ?? null, fn ($q, $d) => $q->where('check_out', '<=', $d));
                    }),
            ])
            ->recordActions([
                Action::make('confirm')
                    ->label('Confirm')
                    ->visible(fn (GuestHouseBooking $record) => $record->status === GuestHouseBookingStatus::Pending)
                    ->action(fn (GuestHouseBooking $record) => $record->update([
                        'status' => GuestHouseBookingStatus::Confirmed,
                        'confirmed_at' => now(),
                    ])),
                Action::make('cancel')
                    ->label('Cancel')
                    ->visible(fn (GuestHouseBooking $record) => in_array($record->status, [
                        GuestHouseBookingStatus::Pending,
                        GuestHouseBookingStatus::Confirmed,
                    ], true))
                    ->form([
                        Textarea::make('cancellation_reason')->required(),
                    ])
                    ->action(fn (GuestHouseBooking $record, array $data) => $record->update([
                        'status' => GuestHouseBookingStatus::Cancelled,
                        'cancelled_at' => now(),
                        'cancellation_reason' => $data['cancellation_reason'],
                    ])),
                Action::make('viewConfirmation')
                    ->label('Confirmation page')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (GuestHouseBooking $record) => $record->confirmation_url
                        ?: ($record->confirmation_token ? BookingConfirmationUrl::forToken($record->confirmation_token) : null))
                    ->openUrlInNewTab()
                    ->visible(fn (GuestHouseBooking $record) => filled($record->confirmation_token) || filled($record->confirmation_url)),
                Action::make('pdf')
                    ->label('PDF')
                    ->url(fn (GuestHouseBooking $record) => route('admin.guest-house-bookings.contract-pdf', ['booking' => $record->id]))
                    ->openUrlInNewTab()
                    ->visible(fn (GuestHouseBooking $record) => in_array($record->status, [
                        GuestHouseBookingStatus::Confirmed,
                        GuestHouseBookingStatus::Completed,
                    ], true)),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGuestHouseBookings::route('/'),
            'view' => ViewGuestHouseBooking::route('/{record}'),
        ];
    }
}
