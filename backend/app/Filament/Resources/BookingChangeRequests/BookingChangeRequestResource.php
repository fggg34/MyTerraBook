<?php

namespace App\Filament\Resources\BookingChangeRequests;

use App\Filament\Resources\BookingChangeRequests\Pages\ListBookingChangeRequests;
use App\Filament\Resources\BookingChangeRequests\Pages\ViewBookingChangeRequest;
use App\Models\BookingChangeRequest;
use App\Services\BookingChangeRequestService;
use App\Support\Money;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class BookingChangeRequestResource extends Resource
{
    protected static ?string $model = BookingChangeRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

    protected static string|UnitEnum|null $navigationGroup = 'Operations';

    protected static ?string $navigationLabel = 'Change requests';

    protected static ?int $navigationSort = 3;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Request')->schema([
                TextEntry::make('type')->badge(),
                TextEntry::make('status')->badge(),
                TextEntry::make('customer_message')->columnSpanFull(),
                TextEntry::make('requested_changes')
                    ->formatStateUsing(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT) : '—')
                    ->columnSpanFull(),
                TextEntry::make('price_delta_cents')
                    ->label('Price change')
                    ->formatStateUsing(fn ($state) => $state === null ? '—' : Money::formatDecimalFromCents(abs((int) $state)).($state >= 0 ? ' increase' : ' decrease')),
                TextEntry::make('admin_response')->columnSpanFull(),
            ])->columns(2),
            Section::make('Booking')->schema([
                TextEntry::make('bookable_type')->label('Booking type'),
                TextEntry::make('bookable_id')->label('Booking ID'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('type')->badge(),
                TextColumn::make('status')->badge(),
                TextColumn::make('bookable_type')->label('Type')->toggleable(),
                TextColumn::make('bookable_id')->label('Booking ID'),
                TextColumn::make('customer_message')->limit(40),
                TextColumn::make('price_delta_cents')
                    ->label('Δ price')
                    ->formatStateUsing(fn ($state) => $state === null ? '—' : Money::formatDecimalFromCents((int) $state)),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options([
                    'pending' => 'Pending',
                    'applied' => 'Applied',
                    'rejected' => 'Rejected',
                ]),
                SelectFilter::make('type')->options([
                    'modification' => 'Modification',
                    'cancellation' => 'Cancellation',
                ]),
            ])
            ->recordActions([
                Action::make('apply')
                    ->label('Apply & update booking')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (BookingChangeRequest $record) => $record->status->value === 'pending')
                    ->form([
                        Textarea::make('admin_response')->label('Note to customer (optional)')->rows(3),
                    ])
                    ->action(function (BookingChangeRequest $record, array $data): void {
                        app(BookingChangeRequestService::class)->apply(
                            $record,
                            auth()->user(),
                            $data['admin_response'] ?? null,
                        );
                        Notification::make()->title('Booking updated')->success()->send();
                    }),
                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (BookingChangeRequest $record) => $record->status->value === 'pending')
                    ->form([
                        Textarea::make('admin_response')->label('Reason')->required()->rows(3),
                    ])
                    ->action(function (BookingChangeRequest $record, array $data): void {
                        app(BookingChangeRequestService::class)->reject(
                            $record,
                            auth()->user(),
                            $data['admin_response'],
                        );
                        Notification::make()->title('Request rejected')->success()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBookingChangeRequests::route('/'),
            'view' => ViewBookingChangeRequest::route('/{record}'),
        ];
    }
}
