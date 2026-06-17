<?php

namespace App\Services\Order;

use App\Models\Setting;

class OrderSettingsService
{
    public function getSendEmailsWhen(): string
    {
        return (string) data_get(
            Setting::getValue('orders.send_emails_when', ['mode' => 'pending_or_confirmed']),
            'mode',
            'pending_or_confirmed',
        );
    }

    public function shouldAttachPdfToOrderEmail(): bool
    {
        return (bool) data_get(
            Setting::getValue('orders.attach_pdf_to_order_email', ['enabled' => false]),
            'enabled',
            false,
        );
    }

    public function getFooterTextOrderEmail(): string
    {
        return (string) data_get(
            Setting::getValue('orders.footer_text_order_email', ['content' => '']),
            'content',
            '',
        );
    }

    public function shouldSendCustomerEmailOnCreate(string $orderStatusValue): bool
    {
        return match ($this->getSendEmailsWhen()) {
            'confirmed_only' => $orderStatusValue === 'confirmed',
            'always' => true,
            default => in_array($orderStatusValue, ['pending', 'confirmed'], true),
        };
    }
}
