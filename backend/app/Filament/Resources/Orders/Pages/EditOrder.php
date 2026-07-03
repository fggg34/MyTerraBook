<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use App\Services\Email\OrderEmailNotifier;
use App\Support\BookingConfirmationUrl;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function beforeSave(): void
    {
        $original = $this->record->getOriginal('order_status');
        $from = $original instanceof OrderStatus ? $original : OrderStatus::tryFrom((string) $original);

        $newValue = $this->data['order_status'] ?? null;
        $to = $newValue instanceof OrderStatus ? $newValue : OrderStatus::tryFrom((string) $newValue);

        if ($from === null || $to === null || $from === $to) {
            return;
        }

        if (! Order::isAllowedOrderTransition($from, $to)) {
            Notification::make()
                ->title('Invalid status change')
                ->body("An order cannot move from {$from->value} to {$to->value}.")
                ->danger()
                ->send();

            $this->halt();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('editReservation')
                ->label('Edit Reservation')
                ->color('primary'),
            Action::make('viewFrontSite')
                ->label('View confirmation page')
                ->url(function (): string {
                    if ($this->record->confirmation_url) {
                        return $this->record->confirmation_url;
                    }

                    if ($this->record->confirmation_token) {
                        return BookingConfirmationUrl::forToken($this->record->confirmation_token);
                    }

                    $frontend = rtrim((string) config('app.frontend_url', config('app.url')), '/');

                    return $frontend.'/orders?reference='.urlencode((string) $this->record->reference);
                }, shouldOpenInNewTab: true)
                ->visible(fn () => filled($this->record->confirmation_token) || filled($this->record->confirmation_url)),
            Action::make('resendEmail')
                ->label('Re-send eMail')
                ->color('success')
                ->action(function (OrderEmailNotifier $orderEmails): void {
                    $sent = $orderEmails->resendCustomerEmail($this->record->fresh(), false);

                    $notification = Notification::make()
                        ->title($sent ? 'Email sent' : 'Could not send email')
                        ->body($sent
                            ? 'The customer notification has been sent.'
                            : 'No customer email address is available for this order, or the template is disabled.');

                    if ($sent) {
                        $notification->success()->send();
                    } else {
                        $notification->warning()->send();
                    }
                }),
            Action::make('resendEmailPdf')
                ->label('Re-send Order eMail + PDF')
                ->color('success')
                ->visible(fn () => $this->record->order_status === OrderStatus::Confirmed)
                ->action(function (OrderEmailNotifier $orderEmails): void {
                    $sent = $orderEmails->resendCustomerEmail($this->record->fresh(), true);

                    $notification = Notification::make()
                        ->title($sent ? 'Email + PDF sent' : 'Could not send email')
                        ->body($sent
                            ? 'The customer notification with contract PDF has been sent.'
                            : 'Confirmed orders with a customer email are required for PDF delivery, and the template must be enabled.');

                    if ($sent) {
                        $notification->success()->send();
                    } else {
                        $notification->warning()->send();
                    }
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
