<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->templates() as $template) {
            EmailTemplate::query()->firstOrCreate(
                ['key' => $template['key']],
                $template,
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function templates(): array
    {
        return [
            // ---- Account ----
            [
                'key' => 'customer_welcome',
                'name' => 'Customer welcome',
                'category' => 'account',
                'audience' => 'customer',
                'is_enabled' => true,
                'subject' => 'Welcome to {{brand_name}}',
                'preheader' => 'Your account is ready - start exploring rentals and stays.',
                'heading' => 'Welcome aboard',
                'greeting' => 'Hi {{customer_name}},',
                'body_html' => '<p>Thanks for joining {{brand_name}}! Your account is ready.</p>'
                    .'<p>You can now book vehicles and guest houses across Iceland, save your favourites, and manage everything from your account.</p>',
                'cta_label' => 'Start exploring',
                'cta_url_template' => '{{frontend_url}}',
                'footer_note' => 'If you did not create this account, please ignore this email.',
                'available_variables' => ['customer_name', 'brand_name', 'frontend_url'],
                'sort_order' => 10,
            ],
            [
                'key' => 'host_welcome',
                'name' => 'Host welcome',
                'category' => 'account',
                'audience' => 'host',
                'is_enabled' => true,
                'subject' => "You're now a host on {{brand_name}}",
                'preheader' => 'Your host account is active - list your first vehicle or stay.',
                'heading' => 'Welcome, host!',
                'greeting' => 'Hi {{host_name}},',
                'body_html' => '<p>Your host account on {{brand_name}} is now active.</p>'
                    .'<p>From your host dashboard you can list vehicles and guest houses, manage availability and pricing, and track your bookings.</p>',
                'cta_label' => 'Go to host dashboard',
                'cta_url_template' => '{{frontend_url}}/host',
                'footer_note' => 'Need a hand getting started? Just reply to this email.',
                'available_variables' => ['host_name', 'brand_name', 'frontend_url'],
                'sort_order' => 20,
            ],
            [
                'key' => 'password_reset',
                'name' => 'Password reset',
                'category' => 'account',
                'audience' => 'customer',
                'is_enabled' => true,
                'subject' => 'Reset your {{brand_name}} password',
                'preheader' => 'Use the button below to choose a new password.',
                'heading' => 'Password reset',
                'greeting' => 'Hi {{customer_name}},',
                'body_html' => '<p>We received a request to reset the password for your {{brand_name}} account.</p>'
                    .'<p>Click the button below to choose a new password. This link will expire in {{expires}} minutes.</p>',
                'cta_label' => 'Reset password',
                'cta_url_template' => '{{reset_url}}',
                'footer_note' => "If you didn't request a password reset, you can safely ignore this email.",
                'available_variables' => ['customer_name', 'reset_url', 'expires', 'brand_name'],
                'sort_order' => 30,
            ],
            [
                'key' => 'password_changed',
                'name' => 'Password changed',
                'category' => 'account',
                'audience' => 'customer',
                'is_enabled' => true,
                'subject' => 'Your {{brand_name}} password was changed',
                'preheader' => 'This is a confirmation that your password was updated.',
                'heading' => 'Password updated',
                'greeting' => 'Hi {{customer_name}},',
                'body_html' => '<p>This is a confirmation that the password for your {{brand_name}} account was just changed.</p>'
                    .'<p>If this was you, no further action is needed.</p>',
                'cta_label' => null,
                'cta_url_template' => null,
                'footer_note' => "If you didn't make this change, contact us immediately at {{support_email}}.",
                'available_variables' => ['customer_name', 'support_email', 'brand_name'],
                'sort_order' => 40,
            ],

            // ---- Car rental orders ----
            [
                'key' => 'order_received',
                'name' => 'Order received (customer)',
                'category' => 'orders',
                'audience' => 'customer',
                'is_enabled' => true,
                'subject' => 'We received your booking {{order_reference}}',
                'preheader' => "We've got your request - confirmation is on the way.",
                'heading' => 'Booking received',
                'greeting' => 'Hi {{customer_name}},',
                'body_html' => '<p>Thanks for your booking! We have received your request and it is now being processed.</p>'
                    .'<p><strong>Reference:</strong> {{order_reference}}<br>'
                    .'<strong>Vehicle:</strong> {{car_name}}<br>'
                    .'<strong>Pick-up:</strong> {{pickup_at}}<br>'
                    .'<strong>Drop-off:</strong> {{dropoff_at}}<br>'
                    .'<strong>Total:</strong> {{total}}</p>',
                'cta_label' => 'View my bookings',
                'cta_url_template' => '{{frontend_url}}/account/bookings',
                'footer_note' => "We'll email you again as soon as your booking is confirmed.",
                'available_variables' => ['customer_name', 'order_reference', 'car_name', 'pickup_at', 'dropoff_at', 'total', 'frontend_url'],
                'sort_order' => 50,
            ],
            [
                'key' => 'order_confirmed',
                'name' => 'Order confirmed (customer)',
                'category' => 'orders',
                'audience' => 'customer',
                'is_enabled' => true,
                'subject' => 'Your booking {{order_reference}} is confirmed',
                'preheader' => 'Your vehicle is booked - here are your details.',
                'heading' => 'Booking confirmed',
                'greeting' => 'Hi {{customer_name}},',
                'body_html' => '<p>Great news - your booking is confirmed!</p>'
                    .'<p><strong>Reference:</strong> {{order_reference}}<br>'
                    .'<strong>Vehicle:</strong> {{car_name}}<br>'
                    .'<strong>Pick-up:</strong> {{pickup_at}}<br>'
                    .'<strong>Drop-off:</strong> {{dropoff_at}}<br>'
                    .'<strong>Total:</strong> {{total}}</p>',
                'cta_label' => 'View my bookings',
                'cta_url_template' => '{{frontend_url}}/account/bookings',
                'footer_note' => 'We look forward to seeing you at pick-up.',
                'available_variables' => ['customer_name', 'order_reference', 'car_name', 'pickup_at', 'dropoff_at', 'total', 'frontend_url'],
                'sort_order' => 60,
            ],
            [
                'key' => 'order_cancelled',
                'name' => 'Order cancelled (customer)',
                'category' => 'orders',
                'audience' => 'customer',
                'is_enabled' => true,
                'subject' => 'Your booking {{order_reference}} was cancelled',
                'preheader' => 'Your booking has been cancelled.',
                'heading' => 'Booking cancelled',
                'greeting' => 'Hi {{customer_name}},',
                'body_html' => '<p>Your booking <strong>{{order_reference}}</strong> for {{car_name}} has been cancelled.</p>'
                    .'<p>If you believe this is a mistake or you have any questions, just reply to this email.</p>',
                'cta_label' => 'Browse vehicles',
                'cta_url_template' => '{{frontend_url}}',
                'footer_note' => null,
                'available_variables' => ['customer_name', 'order_reference', 'car_name', 'frontend_url'],
                'sort_order' => 70,
            ],
            [
                'key' => 'order_new_admin',
                'name' => 'New order alert (staff)',
                'category' => 'orders',
                'audience' => 'staff',
                'is_enabled' => true,
                'subject' => 'New booking {{order_reference}}',
                'preheader' => 'A new vehicle booking just came in.',
                'heading' => 'New booking received',
                'greeting' => 'Hello,',
                'body_html' => '<p>A new vehicle booking has been placed.</p>'
                    .'<p><strong>Reference:</strong> {{order_reference}}<br>'
                    .'<strong>Vehicle:</strong> {{car_name}}<br>'
                    .'<strong>Customer:</strong> {{customer_name}} ({{customer_email}})<br>'
                    .'<strong>Pick-up:</strong> {{pickup_at}}<br>'
                    .'<strong>Drop-off:</strong> {{dropoff_at}}<br>'
                    .'<strong>Total:</strong> {{total}}</p>',
                'cta_label' => 'Open in admin',
                'cta_url_template' => '{{admin_url}}',
                'footer_note' => null,
                'available_variables' => ['order_reference', 'car_name', 'customer_name', 'customer_email', 'pickup_at', 'dropoff_at', 'total', 'admin_url'],
                'sort_order' => 80,
            ],

            // ---- Guest house bookings ----
            [
                'key' => 'gh_booking_received',
                'name' => 'Stay request received (guest)',
                'category' => 'bookings',
                'audience' => 'customer',
                'is_enabled' => true,
                'subject' => 'We received your stay request {{booking_reference}}',
                'preheader' => "We've got your request - the host will confirm shortly.",
                'heading' => 'Stay request received',
                'greeting' => 'Hi {{guest_name}},',
                'body_html' => '<p>Thanks for your reservation request! Here are the details:</p>'
                    .'<p><strong>Reference:</strong> {{booking_reference}}<br>'
                    .'<strong>Stay:</strong> {{listing_name}}<br>'
                    .'<strong>Check-in:</strong> {{check_in}}<br>'
                    .'<strong>Check-out:</strong> {{check_out}}<br>'
                    .'<strong>Guests:</strong> {{guests_count}}<br>'
                    .'<strong>Total:</strong> {{total}}</p>',
                'cta_label' => 'View my stays',
                'cta_url_template' => '{{frontend_url}}/account/stays',
                'footer_note' => "We'll email you again once the host confirms your stay.",
                'available_variables' => ['guest_name', 'booking_reference', 'listing_name', 'check_in', 'check_out', 'guests_count', 'total', 'frontend_url'],
                'sort_order' => 90,
            ],
            [
                'key' => 'gh_booking_confirmed',
                'name' => 'Stay confirmed (guest)',
                'category' => 'bookings',
                'audience' => 'customer',
                'is_enabled' => true,
                'subject' => 'Your stay {{booking_reference}} is confirmed',
                'preheader' => 'Your stay is confirmed - here are your details.',
                'heading' => 'Stay confirmed',
                'greeting' => 'Hi {{guest_name}},',
                'body_html' => '<p>Great news - your stay is confirmed!</p>'
                    .'<p><strong>Reference:</strong> {{booking_reference}}<br>'
                    .'<strong>Stay:</strong> {{listing_name}}<br>'
                    .'<strong>Check-in:</strong> {{check_in}}<br>'
                    .'<strong>Check-out:</strong> {{check_out}}<br>'
                    .'<strong>Guests:</strong> {{guests_count}}<br>'
                    .'<strong>Total:</strong> {{total}}</p>',
                'cta_label' => 'View my stays',
                'cta_url_template' => '{{frontend_url}}/account/stays',
                'footer_note' => 'We hope you enjoy your stay.',
                'available_variables' => ['guest_name', 'booking_reference', 'listing_name', 'check_in', 'check_out', 'guests_count', 'total', 'frontend_url'],
                'sort_order' => 100,
            ],
            [
                'key' => 'gh_booking_declined',
                'name' => 'Stay declined / cancelled (guest)',
                'category' => 'bookings',
                'audience' => 'customer',
                'is_enabled' => true,
                'subject' => 'Update on your stay request {{booking_reference}}',
                'preheader' => 'Unfortunately your stay request could not be confirmed.',
                'heading' => 'Stay not confirmed',
                'greeting' => 'Hi {{guest_name}},',
                'body_html' => '<p>Unfortunately your stay request <strong>{{booking_reference}}</strong> for {{listing_name}} could not be confirmed.</p>'
                    .'<p>{{reason}}</p>'
                    .'<p>You are welcome to browse other available stays.</p>',
                'cta_label' => 'Browse stays',
                'cta_url_template' => '{{frontend_url}}/guest-houses',
                'footer_note' => null,
                'available_variables' => ['guest_name', 'booking_reference', 'listing_name', 'reason', 'frontend_url'],
                'sort_order' => 110,
            ],
            [
                'key' => 'gh_booking_new_host',
                'name' => 'New stay booking alert (host)',
                'category' => 'bookings',
                'audience' => 'host',
                'is_enabled' => true,
                'subject' => 'New stay booking {{booking_reference}}',
                'preheader' => 'A new guest house booking just came in.',
                'heading' => 'New stay booking',
                'greeting' => 'Hello,',
                'body_html' => '<p>A new booking has been requested for one of your stays.</p>'
                    .'<p><strong>Reference:</strong> {{booking_reference}}<br>'
                    .'<strong>Stay:</strong> {{listing_name}}<br>'
                    .'<strong>Guest:</strong> {{guest_name}} ({{guest_email}})<br>'
                    .'<strong>Check-in:</strong> {{check_in}}<br>'
                    .'<strong>Check-out:</strong> {{check_out}}<br>'
                    .'<strong>Guests:</strong> {{guests_count}}<br>'
                    .'<strong>Total:</strong> {{total}}</p>',
                'cta_label' => 'Open host bookings',
                'cta_url_template' => '{{frontend_url}}/host/bookings',
                'footer_note' => 'Please review and confirm the booking from your dashboard.',
                'available_variables' => ['booking_reference', 'listing_name', 'guest_name', 'guest_email', 'check_in', 'check_out', 'guests_count', 'total', 'frontend_url'],
                'sort_order' => 120,
            ],

            // ---- Host listings ----
            [
                'key' => 'listing_submitted',
                'name' => 'Listing submitted (host)',
                'category' => 'listings',
                'audience' => 'host',
                'is_enabled' => true,
                'subject' => 'Your listing "{{listing_name}}" was submitted for review',
                'preheader' => "We've received your listing - our team will review it shortly.",
                'heading' => 'Submitted for review',
                'greeting' => 'Hi {{host_name}},',
                'body_html' => '<p>Thanks for submitting <strong>{{listing_name}}</strong> for review.</p>'
                    .'<p>Our team will review your listing and let you know as soon as it is approved and live.</p>',
                'cta_label' => 'View my listings',
                'cta_url_template' => '{{frontend_url}}/host',
                'footer_note' => 'Reviews usually take 1-2 business days.',
                'available_variables' => ['host_name', 'listing_name', 'frontend_url'],
                'sort_order' => 130,
            ],
            [
                'key' => 'listing_approved',
                'name' => 'Listing approved (host)',
                'category' => 'listings',
                'audience' => 'host',
                'is_enabled' => true,
                'subject' => 'Your listing "{{listing_name}}" is now live',
                'preheader' => 'Your listing has been approved and is now visible to guests.',
                'heading' => 'Listing approved',
                'greeting' => 'Hi {{host_name}},',
                'body_html' => '<p>Good news - your listing <strong>{{listing_name}}</strong> has been approved and is now live.</p>'
                    .'<p>Guests can now find and book it.</p>',
                'cta_label' => 'View my listings',
                'cta_url_template' => '{{frontend_url}}/host',
                'footer_note' => null,
                'available_variables' => ['host_name', 'listing_name', 'frontend_url'],
                'sort_order' => 140,
            ],
            [
                'key' => 'listing_rejected',
                'name' => 'Listing rejected (host)',
                'category' => 'listings',
                'audience' => 'host',
                'is_enabled' => true,
                'subject' => 'Action needed on your listing "{{listing_name}}"',
                'preheader' => 'Your listing needs some changes before it can go live.',
                'heading' => 'Changes needed',
                'greeting' => 'Hi {{host_name}},',
                'body_html' => '<p>Thanks for submitting <strong>{{listing_name}}</strong>. Before it can go live, some changes are needed:</p>'
                    .'<p>{{rejection_reason}}</p>'
                    .'<p>Please update your listing and resubmit it for review.</p>',
                'cta_label' => 'Edit my listing',
                'cta_url_template' => '{{frontend_url}}/host',
                'footer_note' => 'Reply to this email if you have any questions.',
                'available_variables' => ['host_name', 'listing_name', 'rejection_reason', 'frontend_url'],
                'sort_order' => 150,
            ],
        ];
    }
}
