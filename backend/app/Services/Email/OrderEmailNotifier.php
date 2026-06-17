<?php

namespace App\Services\Email;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\Order\OrderSettingsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Mail\Mailables\Attachment;

class OrderEmailNotifier
{
    public function __construct(
        private readonly EmailService $email,
        private readonly EmailSettingsService $emailSettings,
        private readonly OrderSettingsService $orderSettings,
    ) {}

    public function notifyCreated(Order $order): void
    {
        $order->loadMissing('car.host');
        $payload = $this->payloadFor($order);

        if ($order->customer_email && $this->orderSettings->shouldSendCustomerEmailOnCreate($order->order_status->value)) {
            $this->sendCustomerTemplate(
                $order,
                $this->customerTemplateForStatus($order->order_status),
                $payload,
            );
        }

        $this->notifyStaffOfNewOrder($order, $payload);
    }

    public function notifyStatusChanged(Order $order, OrderStatus $from, OrderStatus $to): void
    {
        if ($from === $to) {
            return;
        }

        $order->loadMissing('car.host');
        $payload = $this->payloadFor($order);

        if ($order->customer_email) {
            match ($to) {
                OrderStatus::Confirmed => $this->sendCustomerTemplate($order, 'order_confirmed', $payload),
                OrderStatus::Cancelled => $this->email->send('order_cancelled', $order->customer_email, $payload),
                default => null,
            };
        }

        if ($hostEmail = $order->car?->host?->email) {
            match ($to) {
                OrderStatus::Confirmed => $this->email->send('order_confirmed_host', $hostEmail, $payload),
                OrderStatus::Cancelled => $this->email->send('order_cancelled_host', $hostEmail, $payload),
                default => null,
            };
        }
    }

    public function resendCustomerEmail(Order $order, bool $withPdf = false): bool
    {
        if (! $order->customer_email) {
            return false;
        }

        $order->loadMissing('car.host');

        return $this->sendCustomerTemplate(
            $order,
            $this->customerTemplateForStatus($order->order_status),
            $this->payloadFor($order),
            $withPdf || $this->orderSettings->shouldAttachPdfToOrderEmail(),
        );
    }

    /**
     * @param  array<string, string>  $payload
     */
    private function notifyStaffOfNewOrder(Order $order, array $payload): void
    {
        $recipients = [];
        $adminEmail = $this->emailSettings->getAdminEmail();
        if ($adminEmail !== '') {
            $recipients[] = $adminEmail;
        }
        if ($hostEmail = $order->car?->host?->email) {
            $recipients[] = $hostEmail;
        }

        if ($recipients === []) {
            return;
        }

        $this->email->send('order_new_admin', $recipients, $payload + [
            'admin_url' => rtrim((string) config('app.url'), '/').'/admin',
        ]);
    }

    /**
     * @param  array<string, string>  $payload
     */
    private function sendCustomerTemplate(Order $order, string $templateKey, array $payload, ?bool $attachPdf = null): bool
    {
        $shouldAttach = $attachPdf ?? $this->orderSettings->shouldAttachPdfToOrderEmail();
        $attachments = $shouldAttach ? $this->contractPdfAttachments($order) : [];

        return $this->email->send($templateKey, $order->customer_email, $payload, $attachments);
    }

    /**
     * @return array<string, string>
     */
    private function payloadFor(Order $order): array
    {
        return OrderEmailPayload::for($order);
    }

    private function customerTemplateForStatus(OrderStatus $status): string
    {
        return $status === OrderStatus::Confirmed ? 'order_confirmed' : 'order_received';
    }

    /**
     * @return array<int, Attachment>
     */
    private function contractPdfAttachments(Order $order): array
    {
        if ($order->order_status !== OrderStatus::Confirmed) {
            return [];
        }

        $order->loadMissing(['car', 'pickupLocation', 'dropoffLocation']);
        $reference = (string) $order->reference;

        return [
            Attachment::fromData(
                fn (): string => Pdf::loadView('pdf.order-contract', ['order' => $order])->output(),
                'contract-'.$reference.'.pdf',
            )->withMime('application/pdf'),
        ];
    }
}
