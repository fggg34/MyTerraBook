<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderStatus;
use App\Enums\RentalStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Orders\Schemas\OrderForm;
use App\Models\Order;
use App\Services\Email\OrderEmailNotifier;
use App\Support\AdminMoney;
use App\Support\BookingConfirmationUrl;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\View\View;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function beforeSave(): void
    {
        if ($this->record->external_provider === 'caren') {
            Notification::make()->title('Caren reservation')
                ->body('Direct edits are disabled to keep Caren and TerraBook consistent. Use Cancel Caren reservation or contact support for amendments.')
                ->warning()->send();
            $this->halt();
        }
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

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return OrderForm::splitSchedule($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = OrderForm::applySchedule($data);
        $data['total_cents'] = max(
            0,
            (int) ($data['base_rental_cents'] ?? 0)
            + (int) ($data['extras_cents'] ?? 0)
            + (int) ($data['fees_cents'] ?? 0)
            - (int) ($data['discount_cents'] ?? 0)
            + (int) ($data['tax_cents'] ?? 0),
        );

        return $data;
    }

    public function getHeader(): ?View
    {
        $order = $this->getRecord();
        if (! $order instanceof Order) {
            return null;
        }

        $order->loadMissing(['car', 'carUnit', 'pickupLocation', 'dropoffLocation', 'payments']);
        $status = match ($order->rental_status) {
            RentalStatus::Started => 'On the road',
            RentalStatus::Terminated => 'Returned',
            RentalStatus::NoShow => 'No show',
            default => match ($order->order_status) {
                OrderStatus::StandBy => 'Stand by',
                OrderStatus::Pending => 'Pending',
                OrderStatus::Confirmed => 'Confirmed',
                OrderStatus::Cancelled => 'Cancelled',
                default => 'Pending',
            },
        };

        $paid = (int) $order->payments()->sum('amount_cents');
        $currency = (string) ($order->currency ?: 'ISK');
        $lock = $order->payment_lock_expires_at;
        $lockText = $lock
            ? ($lock->isPast() ? 'Payment lock expired' : 'Payment lock until '.$lock->format('j M Y, H:i'))
            : null;
        $source = $order->external_provider ?: 'Website';
        $meta = trim('Booked '.($order->created_at?->format('j M Y, H:i') ?? '').($lockText ? ' · '.$lockText : '').' · Source: '.$source, ' ·');

        return view('filament.panel.edit-record-header', [
            'title' => (string) $order->reference,
            'backUrl' => OrderResource::getUrl('index'),
            'backLabel' => 'Car reservations',
            'meta' => $meta,
            'pills' => array_values(array_filter([
                ['label' => $status, 'tone' => $order->order_status === OrderStatus::Pending ? 'warn' : 'ok'],
                ['label' => $paid > 0 ? 'Paid' : 'Unpaid', 'tone' => $paid > 0 ? 'ok' : 'neutral'],
            ])),
            'summary' => [
                ['label' => 'Vehicle', 'value' => $order->car?->name ?? 'Not set', 'hint' => $order->carUnit?->name ?? null],
                ['label' => 'Pickup', 'value' => $order->pickup_at?->format('j M Y, H:i') ?? 'Not set', 'hint' => $order->pickupLocation?->name],
                ['label' => 'Drop-off', 'value' => $order->dropoff_at?->format('j M Y, H:i') ?? 'Not set', 'hint' => $order->dropoffLocation?->name],
                ['label' => 'Driver', 'value' => $order->customer_name ?: 'Not set', 'hint' => trim(($order->customer_email ?? '').' · '.($order->customer_phone ?? ''), ' ·')],
                ['label' => 'Total', 'value' => AdminMoney::fromCents((int) $order->total_cents, $currency), 'hint' => 'Balance due '.AdminMoney::fromCents(max(0, (int) $order->total_cents - $paid), $currency)],
            ],
            'actions' => $this->getCachedHeaderActions(),
            'sections' => [],
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cancelCarenReservation')->label('Cancel Caren reservation')->color('danger')
                ->visible(fn () => $this->record->external_provider === 'caren' && $this->record->partner_sync_state !== 'cancelled')
                ->requiresConfirmation()->modalDescription('Cancel this reservation in Caren and TerraBook. Any payment refund must be handled separately.')
                ->action(function (): void {
                    try {
                        $this->record->transitionOrderStatus(OrderStatus::Cancelled);
                        $this->refreshFormData(['order_status', 'rental_status']);
                        Notification::make()->title('Caren reservation cancelled')->success()->send();
                    } catch (\InvalidArgumentException $e) {
                        Notification::make()->title('Cancellation pending')->body($e->getMessage())->danger()->send();
                    }
                }),
            Action::make('viewFrontSite')
                ->label('Confirmation page')
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
                ->label('Resend e-mail')
                ->color('gray')
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
            Action::make('confirm')
                ->label('Confirm order')
                ->color('success')
                ->visible(fn () => $this->record->external_provider !== 'caren' && ($this->record->order_status === OrderStatus::Pending
                    || $this->record->order_status === OrderStatus::StandBy))
                ->requiresConfirmation()
                ->action(function (): void {
                    $this->record->transitionOrderStatus(OrderStatus::Confirmed);
                    $this->refreshFormData(['order_status']);
                }),
            ActionGroup::make([
                Action::make('resendEmailPdf')
                    ->label('Re-send order e-mail + PDF')
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
                    ->url(fn (): string => route('admin.orders.contract-pdf', ['order' => $this->record->id]), shouldOpenInNewTab: true)
                    ->visible(fn () => $this->record->order_status === OrderStatus::Confirmed),
                Action::make('customerCheckinPdf')
                    ->label('Customer check-in')
                    ->url(fn (): string => route('admin.orders.checkin-pdf', ['order' => $this->record->id]), shouldOpenInNewTab: true)
                    ->visible(fn () => $this->record->order_status === OrderStatus::Confirmed),
            ])
                ->label('More')
                ->icon('heroicon-m-ellipsis-horizontal')
                ->color('gray')
                ->button(),
            DeleteAction::make()
                ->extraAttributes(['class' => 'tb-hidden-action'])
                ->visible(fn () => $this->record->external_provider !== 'caren'),
        ];
    }
}
