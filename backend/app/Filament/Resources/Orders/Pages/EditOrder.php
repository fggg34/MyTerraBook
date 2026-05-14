<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('editReservation')
                ->label('Edit Reservation')
                ->color('primary'),
            Action::make('viewFrontSite')
                ->label('View in front site')
                ->url(function (): string {
                    $frontend = rtrim((string) env('FRONTEND_URL', 'http://127.0.0.1:5173'), '/');

                    return $frontend.'/orders?reference='.urlencode((string) $this->record->reference);
                }, shouldOpenInNewTab: true),
            Action::make('resendEmail')
                ->label('Re-send eMail')
                ->color('success')
                ->action(function (): void {
                    Notification::make()
                        ->title('Re-send email queued')
                        ->body('Email delivery integration is the next step; action placeholder is now available.')
                        ->success()
                        ->send();
                }),
            Action::make('resendEmailPdf')
                ->label('Re-send Order eMail + PDF')
                ->color('success')
                ->action(function (): void {
                    Notification::make()
                        ->title('Email + PDF queued')
                        ->body('PDF mail integration endpoint is pending; action placeholder is now available.')
                        ->success()
                        ->send();
                }),
            Action::make('downloadContractPdf')
                ->label('Download PDF')
                ->color('gray')
                ->url(fn (): string => route('admin.orders.contract-pdf', ['order' => $this->record->id]), shouldOpenInNewTab: true)
                ->visible(fn () => $this->record->order_status === OrderStatus::Confirmed),
            Action::make('customerCheckinPdf')
                ->label('Customer Check-in')
                ->color('info')
                ->url(fn (): string => route('admin.orders.checkin-pdf', ['order' => $this->record->id]), shouldOpenInNewTab: true)
                ->visible(fn () => $this->record->order_status === OrderStatus::Confirmed),
            Action::make('confirm')
                ->label('Confirm order')
                ->color('success')
                ->visible(fn () => $this->record->order_status === OrderStatus::Pending
                    || $this->record->order_status === OrderStatus::StandBy)
                ->requiresConfirmation()
                ->action(function (): void {
                    $this->record->transitionOrderStatus(OrderStatus::Confirmed);
                    $this->refreshFormData(['order_status']);
                }),
            DeleteAction::make(),
        ];
    }
}
