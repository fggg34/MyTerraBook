<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const PAYMENT_VARS = ['total_isk', 'paid_online', 'cash_due_on_arrival'];

    /**
     * Add paid-online / cash-on-arrival merge fields to confirmation templates.
     */
    public function up(): void
    {
        $updates = [
            'order_confirmed' => [
                'body_html' => '<p>Great news - your booking is confirmed!</p>'
                    .'<p><strong>Reference:</strong> {{order_reference}}<br>'
                    .'<strong>Vehicle:</strong> {{car_name}}<br>'
                    .'<strong>Pick-up:</strong> {{pickup_at}}<br>'
                    .'<strong>Drop-off:</strong> {{dropoff_at}}</p>'
                    .'<p><strong>Payment summary</strong><br>'
                    .'<strong>Total booking value:</strong> {{total_isk}}<br>'
                    .'<strong>Paid online (card):</strong> {{paid_online}}<br>'
                    .'<strong>Due on arrival (cash):</strong> {{cash_due_on_arrival}}</p>',
            ],
            'order_confirmed_host' => [
                'body_html' => '<p>The booking <strong>{{order_reference}}</strong> for {{car_name}} has been confirmed.</p>'
                    .'<p><strong>Customer:</strong> {{customer_name}} ({{customer_email}})<br>'
                    .'<strong>Pick-up:</strong> {{pickup_at}}<br>'
                    .'<strong>Drop-off:</strong> {{dropoff_at}}</p>'
                    .'<p><strong>Payment summary</strong><br>'
                    .'<strong>Total booking value:</strong> {{total_isk}}<br>'
                    .'<strong>Paid online (card):</strong> {{paid_online}}<br>'
                    .'<strong>Collect on arrival (cash):</strong> {{cash_due_on_arrival}}</p>',
            ],
            'gh_booking_confirmed' => [
                'body_html' => '<p>Great news - your stay is confirmed!</p>'
                    .'<p><strong>Reference:</strong> {{booking_reference}}<br>'
                    .'<strong>Stay:</strong> {{listing_name}}<br>'
                    .'<strong>Check-in:</strong> {{check_in}}<br>'
                    .'<strong>Check-out:</strong> {{check_out}}<br>'
                    .'<strong>Guests:</strong> {{guests_count}}</p>'
                    .'<p><strong>Payment summary</strong><br>'
                    .'<strong>Total booking value:</strong> {{total_isk}}<br>'
                    .'<strong>Paid online (card):</strong> {{paid_online}}<br>'
                    .'<strong>Due on arrival (cash):</strong> {{cash_due_on_arrival}}</p>',
            ],
            'gh_booking_confirmed_host' => [
                'body_html' => '<p>The stay booking <strong>{{booking_reference}}</strong> for {{listing_name}} has been confirmed.</p>'
                    .'<p><strong>Guest:</strong> {{guest_name}} ({{guest_email}})<br>'
                    .'<strong>Check-in:</strong> {{check_in}}<br>'
                    .'<strong>Check-out:</strong> {{check_out}}<br>'
                    .'<strong>Guests:</strong> {{guests_count}}</p>'
                    .'<p><strong>Payment summary</strong><br>'
                    .'<strong>Total booking value:</strong> {{total_isk}}<br>'
                    .'<strong>Paid online (card):</strong> {{paid_online}}<br>'
                    .'<strong>Collect on arrival (cash):</strong> {{cash_due_on_arrival}}</p>',
            ],
        ];

        foreach ($updates as $key => $fields) {
            $template = DB::table('email_templates')->where('key', $key)->first();
            if ($template === null) {
                continue;
            }

            $variables = json_decode((string) ($template->available_variables ?? '[]'), true);
            if (! is_array($variables)) {
                $variables = [];
            }

            DB::table('email_templates')->where('key', $key)->update([
                'body_html' => $fields['body_html'],
                'available_variables' => json_encode(array_values(array_unique(array_merge($variables, self::PAYMENT_VARS)))),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Body content is managed in admin; no automatic rollback.
    }
};
