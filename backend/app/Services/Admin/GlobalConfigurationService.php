<?php

namespace App\Services\Admin;

use App\Models\Setting;

class GlobalConfigurationService
{
    /**
     * @return array<string, mixed>
     */
    public function load(): array
    {
        return [
            'rentals_enabled' => (bool) data_get(Setting::getValue('shop.rentals_enabled', ['enabled' => true]), 'enabled', true),
            'rentals_disabled_message' => (string) data_get(Setting::getValue('shop.rentals_disabled_message', ['message' => '']), 'message', ''),
            'admin_email' => (string) data_get(Setting::getValue('shop.admin_email', ['email' => '']), 'email', ''),
            'sender_email' => (string) data_get(Setting::getValue('shop.sender_email', ['email' => '']), 'email', ''),
            'always_open' => (bool) data_get(Setting::getValue('shop.always_open', ['enabled' => false]), 'enabled', false),
            'force_pickup_dropoff_time' => (bool) data_get(Setting::getValue('shop.force_pickup_dropoff_time', ['enabled' => false]), 'enabled', false),
            'pickup_dropoff_date_format' => (string) data_get(Setting::getValue('shop.pickup_dropoff_date_format', ['format' => 'DD/MM/YYYY']), 'format', 'DD/MM/YYYY'),
            'time_format' => (string) data_get(Setting::getValue('shop.time_format', ['format' => '24']), 'format', '24'),
            'extended_gratuity_period' => (int) data_get(Setting::getValue('shop.extended_gratuity_period', ['hours' => 0]), 'hours', 0),
            'apply_extra_hours_charges' => (string) data_get(Setting::getValue('shop.apply_extra_hours_charges', ['mode' => 'before_special_prices']), 'mode', 'before_special_prices'),
            'car_damages_checkin_pdf' => (string) data_get(Setting::getValue('shop.car_damages_checkin_pdf', ['mode' => 'damage_marks_and_explanations']), 'mode', 'damage_marks_and_explanations'),
            'dropoff_available_after' => (int) data_get(Setting::getValue('shop.dropoff_available_after', ['hours' => 0]), 'hours', 0),
            'allow_pickups_on_dropoffs' => (bool) data_get(Setting::getValue('shop.allow_pickups_on_dropoffs', ['enabled' => false]), 'enabled', false),
            'rentals_for_today_any_time' => (bool) data_get(Setting::getValue('shop.rentals_for_today_any_time', ['enabled' => false]), 'enabled', false),
            'auto_assign_car_unit' => (bool) data_get(Setting::getValue('shop.auto_assign_car_unit', ['enabled' => false]), 'enabled', false),
            'enable_coupons' => (bool) data_get(Setting::getValue('shop.enable_coupons', ['enabled' => true]), 'enabled', true),
            'enable_customers_pin_code' => (bool) data_get(Setting::getValue('shop.enable_customers_pin_code', ['enabled' => false]), 'enabled', false),
            'token_form_order_submit' => (bool) data_get(Setting::getValue('shop.token_form_order_submit', ['enabled' => false]), 'enabled', false),
            'require_login' => (bool) data_get(Setting::getValue('shop.require_login', ['enabled' => false]), 'enabled', false),
            'ical_secret_key' => (string) data_get(Setting::getValue('shop.ical_secret_key', ['key' => '']), 'key', ''),
            'payment_lock_minutes' => (int) data_get(Setting::getValue('shop.payment_lock_minutes', ['minutes' => 20]), 'minutes', 20),
            'minimum_rental_days' => (int) data_get(Setting::getValue('shop.minimum_rental_days', ['days' => 1]), 'days', 1),
            'days_in_advance_for_bookings' => (int) data_get(Setting::getValue('shop.days_in_advance_for_bookings', ['days' => 0]), 'days', 0),
            'max_date_future_value' => (int) data_get(Setting::getValue('shop.max_date_future', ['value' => 2]), 'value', 2),
            'max_date_future_unit' => (string) data_get(Setting::getValue('shop.max_date_future', ['unit' => 'years']), 'unit', 'years'),
            'choose_pickup_location' => (bool) data_get(Setting::getValue('shop.choose_pickup_location', ['enabled' => true]), 'enabled', true),
            'cars_category_filter' => (bool) data_get(Setting::getValue('shop.cars_category_filter', ['enabled' => false]), 'enabled', false),
            'filter_by_characteristics' => (bool) data_get(Setting::getValue('shop.filter_by_characteristics', ['enabled' => false]), 'enabled', false),
            'suggest_solutions_no_availability' => (bool) data_get(Setting::getValue('shop.suggest_solutions_no_availability', ['enabled' => true]), 'enabled', true),
            'preferred_countries_ordering' => (string) data_get(Setting::getValue('shop.preferred_countries_ordering', ['locale' => 'en_US']), 'locale', 'en_US'),
            'appearance' => (string) data_get(Setting::getValue('system.appearance', ['mode' => 'auto']), 'mode', 'auto'),
            'frontend_appearance' => (string) data_get(Setting::getValue('system.frontend_appearance', ['mode' => 'disabled']), 'mode', 'disabled'),
            'coming_soon' => (bool) data_get(Setting::getValue('system.coming_soon', ['enabled' => false]), 'enabled', false),
            'cron_jobs_secret_key' => (string) data_get(Setting::getValue('system.cron_jobs_secret_key', ['key' => '']), 'key', ''),
            'enable_multilanguage' => (bool) data_get(Setting::getValue('system.enable_multilanguage', ['enabled' => true]), 'enabled', true),
            'load_font_awesome' => (bool) data_get(Setting::getValue('system.load_font_awesome', ['enabled' => true]), 'enabled', true),
            'bootstrap_css_js' => (bool) data_get(Setting::getValue('system.bootstrap_css_js', ['enabled' => true]), 'enabled', true),
            'calendar_type' => (string) data_get(Setting::getValue('system.calendar_type', ['type' => 'jquery_ui']), 'type', 'jquery_ui'),
            'google_maps_api_key' => (string) data_get(Setting::getValue('system.google_maps_api_key', ['key' => '']), 'key', ''),
            'ipinfo_api_token' => (string) data_get(Setting::getValue('system.ipinfo_api_token', ['token' => '']), 'token', ''),
            'backup_export_type' => (string) data_get(Setting::getValue('backup.export_type', ['type' => 'full']), 'type', 'full'),
            'backup_folder_path' => (string) data_get(Setting::getValue('backup.folder_path', ['path' => '/tmp']), 'path', '/tmp'),
            'currency_name' => (string) data_get(Setting::getValue('shop.currency', ['name' => 'Euro']), 'name', 'Euro'),
            'currency_symbol' => (string) data_get(Setting::getValue('shop.currency', ['symbol' => 'EUR']), 'symbol', 'EUR'),
            'currency_code' => (string) data_get(Setting::getValue('shop.currency', ['code' => 'EUR']), 'code', 'EUR'),
            'currency_decimals' => (int) data_get(Setting::getValue('shop.currency', ['decimals' => 2]), 'decimals', 2),
            'currency_decimal_separator' => (string) data_get(Setting::getValue('shop.currency', ['decimal_separator' => '.']), 'decimal_separator', '.'),
            'currency_thousand_separator' => (string) data_get(Setting::getValue('shop.currency', ['thousand_separator' => ',']), 'thousand_separator', ','),
            'prices_tax_included' => (bool) data_get(Setting::getValue('shop.prices_tax_included', ['enabled' => false]), 'enabled', false),
            'show_tax_summary_only' => (bool) data_get(Setting::getValue('shop.show_tax_summary_only', ['enabled' => false]), 'enabled', false),
            'allow_multiple_payments_same_order' => (bool) data_get(Setting::getValue('shop.allow_multiple_payments_same_order', ['enabled' => false]), 'enabled', false),
            'pay_entire_amount' => (bool) data_get(Setting::getValue('shop.pay_entire_amount', ['enabled' => false]), 'enabled', false),
            'allow_deposit' => (bool) data_get(Setting::getValue('shop.allow_deposit', ['enabled' => true]), 'enabled', true),
            'deposit_value' => (int) data_get(Setting::getValue('shop.deposit', ['value' => 15]), 'value', 15),
            'deposit_type' => (string) data_get(Setting::getValue('shop.deposit', ['type' => 'percentage']), 'type', 'percentage'),
            'payment_transaction_name' => (string) data_get(Setting::getValue('shop.payment_transaction_name', ['name' => 'MyTerraRental']), 'name', 'MyTerraRental'),
            'calendar_first_day_of_week' => (string) data_get(Setting::getValue('views.calendar_first_day_of_week', ['day' => 'monday']), 'day', 'monday'),
            'number_of_months_to_show' => (int) data_get(Setting::getValue('views.number_of_months_to_show', ['count' => 1]), 'count', 1),
            'thumbnails_size_px' => (int) data_get(Setting::getValue('views.thumbnails_size_px', ['size' => 100]), 'size', 100),
            'search_results_style' => (string) data_get(Setting::getValue('views.search_results_style', ['style' => 'list']), 'style', 'list'),
            'show_partly_reserved_days' => (bool) data_get(Setting::getValue('views.show_partly_reserved_days', ['enabled' => false]), 'enabled', false),
            'show_vikrentcar_footer' => (bool) data_get(Setting::getValue('views.show_vikrentcar_footer', ['enabled' => false]), 'enabled', false),
            'customer_email_template' => (string) data_get(Setting::getValue('views.customer_email_template', ['content' => '']), 'content', ''),
            'customer_pdf_template' => (string) data_get(Setting::getValue('views.customer_pdf_template', ['content' => '']), 'content', ''),
            'pdf_checkin_template' => (string) data_get(Setting::getValue('views.pdf_checkin_template', ['content' => '']), 'content', ''),
            'pdf_invoice_template' => (string) data_get(Setting::getValue('views.pdf_invoice_template', ['content' => '']), 'content', ''),
            'custom_css_overrides' => (string) data_get(Setting::getValue('views.custom_css_overrides', ['content' => '']), 'content', ''),
            'theme_name' => (string) data_get(Setting::getValue('views.theme_name', ['name' => 'default']), 'name', 'default'),
            'opening_page_text' => (string) data_get(Setting::getValue('views.opening_page_text', ['content' => '']), 'content', ''),
            'closing_page_text' => (string) data_get(Setting::getValue('views.closing_page_text', ['content' => '']), 'content', ''),
            'preferred_color_titles_headings' => (string) data_get(Setting::getValue('views.preferred_colors', ['titles_headings' => '#1f2b37']), 'titles_headings', '#1f2b37'),
            'preferred_color_elements_bg' => (string) data_get(Setting::getValue('views.preferred_colors', ['elements_bg' => '#0f7cab']), 'elements_bg', '#0f7cab'),
            'preferred_color_elements_font' => (string) data_get(Setting::getValue('views.preferred_colors', ['elements_font' => '#ffffff']), 'elements_font', '#ffffff'),
            'preferred_color_hover_bg' => (string) data_get(Setting::getValue('views.preferred_colors', ['hover_bg' => '#0b5f83']), 'hover_bg', '#0b5f83'),
            'preferred_color_hover_font' => (string) data_get(Setting::getValue('views.preferred_colors', ['hover_font' => '#ffffff']), 'hover_font', '#ffffff'),
            'company_name' => (string) data_get(Setting::getValue('orders.company_name', ['name' => '']), 'name', ''),
            'company_logo' => (string) data_get(Setting::getValue('orders.company_logo', ['path' => '']), 'path', ''),
            'backend_logo_180' => (string) data_get(Setting::getValue('orders.backend_logo_180', ['path' => '']), 'path', ''),
            'attach_pdf_to_order_email' => (bool) data_get(Setting::getValue('orders.attach_pdf_to_order_email', ['enabled' => false]), 'enabled', false),
            'send_emails_when' => (string) data_get(Setting::getValue('orders.send_emails_when', ['mode' => 'pending_or_confirmed']), 'mode', 'pending_or_confirmed'),
            'ical_export_past_months' => (int) data_get(Setting::getValue('orders.ical_export_past_months', ['months' => 0]), 'months', 0),
            'ical_events_end_date' => (string) data_get(Setting::getValue('orders.ical_events_end_date', ['mode' => 'pickup_date']), 'mode', 'pickup_date'),
            'attach_ical_reminder' => (string) data_get(Setting::getValue('orders.attach_ical_reminder', ['mode' => 'administrator_customer']), 'mode', 'administrator_customer'),
            'tracking_code' => (string) data_get(Setting::getValue('orders.tracking_code', ['content' => '']), 'content', ''),
            'conversion_code' => (string) data_get(Setting::getValue('orders.conversion_code', ['content' => '']), 'content', ''),
            'disclaimer' => (string) data_get(Setting::getValue('orders.disclaimer', ['content' => '']), 'content', ''),
            'footer_text_order_email' => (string) data_get(Setting::getValue('orders.footer_text_order_email', ['content' => '']), 'content', ''),
            'allow_documents_upload' => (bool) data_get(Setting::getValue('orders.allow_documents_upload', ['enabled' => false]), 'enabled', false),
            'upload_instructions' => (string) data_get(Setting::getValue('orders.upload_instructions', ['content' => '']), 'content', ''),
        ];
    }

    /**
     * @param array<string, mixed> $state
     */
    public function save(array $state): void
    {
        Setting::putValue('shop.rentals_enabled', ['enabled' => (bool) ($state['rentals_enabled'] ?? false)]);
        Setting::putValue('shop.rentals_disabled_message', ['message' => (string) ($state['rentals_disabled_message'] ?? '')]);
        Setting::putValue('shop.admin_email', ['email' => (string) ($state['admin_email'] ?? '')]);
        Setting::putValue('shop.sender_email', ['email' => (string) ($state['sender_email'] ?? '')]);
        Setting::putValue('shop.always_open', ['enabled' => (bool) ($state['always_open'] ?? false)]);
        Setting::putValue('shop.force_pickup_dropoff_time', ['enabled' => (bool) ($state['force_pickup_dropoff_time'] ?? false)]);
        Setting::putValue('shop.pickup_dropoff_date_format', ['format' => (string) ($state['pickup_dropoff_date_format'] ?? 'DD/MM/YYYY')]);
        Setting::putValue('shop.time_format', ['format' => (string) ($state['time_format'] ?? '24')]);
        Setting::putValue('shop.extended_gratuity_period', ['hours' => max(0, (int) ($state['extended_gratuity_period'] ?? 0))]);
        Setting::putValue('shop.apply_extra_hours_charges', ['mode' => (string) ($state['apply_extra_hours_charges'] ?? 'before_special_prices')]);
        Setting::putValue('shop.car_damages_checkin_pdf', ['mode' => (string) ($state['car_damages_checkin_pdf'] ?? 'damage_marks_and_explanations')]);
        Setting::putValue('shop.dropoff_available_after', ['hours' => max(0, (int) ($state['dropoff_available_after'] ?? 0))]);
        Setting::putValue('shop.allow_pickups_on_dropoffs', ['enabled' => (bool) ($state['allow_pickups_on_dropoffs'] ?? false)]);
        Setting::putValue('shop.rentals_for_today_any_time', ['enabled' => (bool) ($state['rentals_for_today_any_time'] ?? false)]);
        Setting::putValue('shop.auto_assign_car_unit', ['enabled' => (bool) ($state['auto_assign_car_unit'] ?? false)]);
        Setting::putValue('shop.enable_coupons', ['enabled' => (bool) ($state['enable_coupons'] ?? false)]);
        Setting::putValue('shop.enable_customers_pin_code', ['enabled' => (bool) ($state['enable_customers_pin_code'] ?? false)]);
        Setting::putValue('shop.token_form_order_submit', ['enabled' => (bool) ($state['token_form_order_submit'] ?? false)]);
        Setting::putValue('shop.require_login', ['enabled' => (bool) ($state['require_login'] ?? false)]);
        Setting::putValue('shop.ical_secret_key', ['key' => (string) ($state['ical_secret_key'] ?? '')]);
        Setting::putValue('shop.payment_lock_minutes', ['minutes' => max(1, (int) ($state['payment_lock_minutes'] ?? 20))]);
        Setting::putValue('shop.minimum_rental_days', ['days' => max(1, (int) ($state['minimum_rental_days'] ?? 1))]);
        Setting::putValue('shop.days_in_advance_for_bookings', ['days' => max(0, (int) ($state['days_in_advance_for_bookings'] ?? 0))]);
        Setting::putValue('shop.max_date_future', [
            'value' => max(1, (int) ($state['max_date_future_value'] ?? 2)),
            'unit' => (string) ($state['max_date_future_unit'] ?? 'years'),
        ]);
        Setting::putValue('shop.choose_pickup_location', ['enabled' => (bool) ($state['choose_pickup_location'] ?? false)]);
        Setting::putValue('shop.cars_category_filter', ['enabled' => (bool) ($state['cars_category_filter'] ?? false)]);
        Setting::putValue('shop.filter_by_characteristics', ['enabled' => (bool) ($state['filter_by_characteristics'] ?? false)]);
        Setting::putValue('shop.suggest_solutions_no_availability', ['enabled' => (bool) ($state['suggest_solutions_no_availability'] ?? false)]);
        Setting::putValue('shop.preferred_countries_ordering', ['locale' => (string) ($state['preferred_countries_ordering'] ?? 'en_US')]);
        Setting::putValue('system.appearance', ['mode' => (string) ($state['appearance'] ?? 'auto')]);
        Setting::putValue('system.frontend_appearance', ['mode' => (string) ($state['frontend_appearance'] ?? 'disabled')]);
        Setting::putValue('system.coming_soon', ['enabled' => (bool) ($state['coming_soon'] ?? false)]);
        Setting::putValue('system.cron_jobs_secret_key', ['key' => (string) ($state['cron_jobs_secret_key'] ?? '')]);
        Setting::putValue('system.enable_multilanguage', ['enabled' => (bool) ($state['enable_multilanguage'] ?? false)]);
        Setting::putValue('system.load_font_awesome', ['enabled' => (bool) ($state['load_font_awesome'] ?? false)]);
        Setting::putValue('system.bootstrap_css_js', ['enabled' => (bool) ($state['bootstrap_css_js'] ?? false)]);
        Setting::putValue('system.calendar_type', ['type' => (string) ($state['calendar_type'] ?? 'jquery_ui')]);
        Setting::putValue('system.google_maps_api_key', ['key' => (string) ($state['google_maps_api_key'] ?? '')]);
        Setting::putValue('system.ipinfo_api_token', ['token' => (string) ($state['ipinfo_api_token'] ?? '')]);
        Setting::putValue('backup.export_type', ['type' => (string) ($state['backup_export_type'] ?? 'full')]);
        Setting::putValue('backup.folder_path', ['path' => (string) ($state['backup_folder_path'] ?? '/tmp')]);
        Setting::putValue('shop.currency', [
            'name' => (string) ($state['currency_name'] ?? 'Euro'),
            'symbol' => (string) ($state['currency_symbol'] ?? 'EUR'),
            'code' => (string) ($state['currency_code'] ?? 'EUR'),
            'decimals' => max(0, (int) ($state['currency_decimals'] ?? 2)),
            'decimal_separator' => (string) ($state['currency_decimal_separator'] ?? '.'),
            'thousand_separator' => (string) ($state['currency_thousand_separator'] ?? ','),
        ]);
        Setting::putValue('shop.prices_tax_included', ['enabled' => (bool) ($state['prices_tax_included'] ?? false)]);
        Setting::putValue('shop.show_tax_summary_only', ['enabled' => (bool) ($state['show_tax_summary_only'] ?? false)]);
        Setting::putValue('shop.allow_multiple_payments_same_order', ['enabled' => (bool) ($state['allow_multiple_payments_same_order'] ?? false)]);
        Setting::putValue('shop.pay_entire_amount', ['enabled' => (bool) ($state['pay_entire_amount'] ?? false)]);
        Setting::putValue('shop.allow_deposit', ['enabled' => (bool) ($state['allow_deposit'] ?? false)]);
        Setting::putValue('shop.deposit', [
            'value' => max(0, (int) ($state['deposit_value'] ?? 15)),
            'type' => (string) ($state['deposit_type'] ?? 'percentage'),
        ]);
        Setting::putValue('shop.payment_transaction_name', ['name' => (string) ($state['payment_transaction_name'] ?? 'MyTerraRental')]);
        Setting::putValue('views.calendar_first_day_of_week', ['day' => (string) ($state['calendar_first_day_of_week'] ?? 'monday')]);
        Setting::putValue('views.number_of_months_to_show', ['count' => max(0, (int) ($state['number_of_months_to_show'] ?? 1))]);
        Setting::putValue('views.thumbnails_size_px', ['size' => max(1, (int) ($state['thumbnails_size_px'] ?? 100))]);
        Setting::putValue('views.search_results_style', ['style' => (string) ($state['search_results_style'] ?? 'list')]);
        Setting::putValue('views.show_partly_reserved_days', ['enabled' => (bool) ($state['show_partly_reserved_days'] ?? false)]);
        Setting::putValue('views.show_vikrentcar_footer', ['enabled' => (bool) ($state['show_vikrentcar_footer'] ?? false)]);
        Setting::putValue('views.customer_email_template', ['content' => (string) ($state['customer_email_template'] ?? '')]);
        Setting::putValue('views.customer_pdf_template', ['content' => (string) ($state['customer_pdf_template'] ?? '')]);
        Setting::putValue('views.pdf_checkin_template', ['content' => (string) ($state['pdf_checkin_template'] ?? '')]);
        Setting::putValue('views.pdf_invoice_template', ['content' => (string) ($state['pdf_invoice_template'] ?? '')]);
        Setting::putValue('views.custom_css_overrides', ['content' => (string) ($state['custom_css_overrides'] ?? '')]);
        Setting::putValue('views.theme_name', ['name' => (string) ($state['theme_name'] ?? 'default')]);
        Setting::putValue('views.opening_page_text', ['content' => (string) ($state['opening_page_text'] ?? '')]);
        Setting::putValue('views.closing_page_text', ['content' => (string) ($state['closing_page_text'] ?? '')]);
        Setting::putValue('views.preferred_colors', [
            'titles_headings' => (string) ($state['preferred_color_titles_headings'] ?? '#1f2b37'),
            'elements_bg' => (string) ($state['preferred_color_elements_bg'] ?? '#0f7cab'),
            'elements_font' => (string) ($state['preferred_color_elements_font'] ?? '#ffffff'),
            'hover_bg' => (string) ($state['preferred_color_hover_bg'] ?? '#0b5f83'),
            'hover_font' => (string) ($state['preferred_color_hover_font'] ?? '#ffffff'),
        ]);
        Setting::putValue('orders.company_name', ['name' => (string) ($state['company_name'] ?? '')]);
        Setting::putValue('orders.company_logo', ['path' => (string) ($state['company_logo'] ?? '')]);
        Setting::putValue('orders.backend_logo_180', ['path' => (string) ($state['backend_logo_180'] ?? '')]);
        Setting::putValue('orders.attach_pdf_to_order_email', ['enabled' => (bool) ($state['attach_pdf_to_order_email'] ?? false)]);
        Setting::putValue('orders.send_emails_when', ['mode' => (string) ($state['send_emails_when'] ?? 'pending_or_confirmed')]);
        Setting::putValue('orders.ical_export_past_months', ['months' => max(0, (int) ($state['ical_export_past_months'] ?? 0))]);
        Setting::putValue('orders.ical_events_end_date', ['mode' => (string) ($state['ical_events_end_date'] ?? 'pickup_date')]);
        Setting::putValue('orders.attach_ical_reminder', ['mode' => (string) ($state['attach_ical_reminder'] ?? 'administrator_customer')]);
        Setting::putValue('orders.tracking_code', ['content' => (string) ($state['tracking_code'] ?? '')]);
        Setting::putValue('orders.conversion_code', ['content' => (string) ($state['conversion_code'] ?? '')]);
        Setting::putValue('orders.disclaimer', ['content' => (string) ($state['disclaimer'] ?? '')]);
        Setting::putValue('orders.footer_text_order_email', ['content' => (string) ($state['footer_text_order_email'] ?? '')]);
        Setting::putValue('orders.allow_documents_upload', ['enabled' => (bool) ($state['allow_documents_upload'] ?? false)]);
        Setting::putValue('orders.upload_instructions', ['content' => (string) ($state['upload_instructions'] ?? '')]);
    }
}
