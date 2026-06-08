<?php

namespace App\Services\Email;

use App\Models\EmailTemplate;

class EmailTestSampleData
{
    /**
     * @return array<string, string>
     */
    public static function samples(): array
    {
        return [
            'customer_name' => 'Alex Traveller',
            'host_name' => 'Jordan Host',
            'guest_name' => 'Alex Traveller',
            'order_reference' => 'ORD-SAMPLE123',
            'booking_reference' => 'GH-20260607-AB12',
            'car_name' => 'Toyota Land Cruiser',
            'listing_name' => 'Cosy Cabin by the Fjord',
            'customer_email' => 'customer@example.com',
            'guest_email' => 'guest@example.com',
            'pickup_at' => 'Mon, 15 Jun 2026 10:00',
            'dropoff_at' => 'Fri, 19 Jun 2026 10:00',
            'check_in' => '15 Jun 2026',
            'check_out' => '19 Jun 2026',
            'guests_count' => '2',
            'total' => '€480.00',
            'reset_url' => rtrim((string) config('app.frontend_url', config('app.url')), '/').'/reset-password?token=sample',
            'expires' => '60',
            'reason' => 'These dates are no longer available.',
            'rejection_reason' => 'Please add at least 3 clear photos of the property.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function forTemplate(EmailTemplate $template): array
    {
        $samples = self::samples();
        $data = [];

        foreach ((array) ($template->available_variables ?? []) as $var) {
            if (isset($samples[$var])) {
                $data[$var] = $samples[$var];
            }
        }

        return $data;
    }
}
