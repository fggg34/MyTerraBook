<x-filament-panels::page>
    <style>
        .ir-global-tabs {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }

        .ir-global-tab {
            border: 1px solid #cfd7e2;
            background: #ffffff;
            color: #1f2b37;
            border-radius: 0.4rem;
            padding: 0.5rem 0.8rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
        }

        .ir-global-tab[data-active="true"] {
            background: #334e68;
            border-color: #334e68;
            color: #ffffff;
        }

        .ir-global-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .ir-global-card {
            border: 1px solid #d6dce5;
            border-radius: 0.55rem;
            background: #ffffff;
            padding: 1rem;
        }

        .ir-global-card__title {
            margin: 0 0 0.8rem;
            font-size: 1.125rem;
            font-weight: 700;
            color: #1f2b37;
        }

        .ir-global-fields {
            display: grid;
            gap: 0.7rem;
        }

        .ir-global-field {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.3rem;
        }

        .ir-global-label {
            font-size: 0.78rem;
            color: #4a5a6a;
            font-weight: 600;
        }

        .ir-global-input,
        .ir-global-select,
        .ir-global-textarea {
            border: 1px solid #cfd7e2;
            border-radius: 0.35rem;
            background: #ffffff;
            padding: 0.46rem 0.55rem;
            font-size: 0.82rem;
            color: #1f2b37;
            width: 100%;
        }

        .ir-global-textarea {
            min-height: 100px;
            resize: vertical;
        }

        .ir-global-toggle {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.83rem;
            color: #1f2b37;
        }

        .ir-global-actions {
            margin-top: 1rem;
            display: flex;
            justify-content: flex-end;
        }

        .ir-global-save {
            border: 1px solid #334e68;
            background: #334e68;
            color: #ffffff;
            border-radius: 0.4rem;
            padding: 0.5rem 1rem;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
        }

        .ir-global-placeholder {
            border: 1px dashed #d6dce5;
            border-radius: 0.45rem;
            padding: 1rem;
            color: #607080;
            font-size: 0.875rem;
            background: #f8fafc;
        }

        .ir-global-hint {
            margin-top: 0.35rem;
            color: #607080;
            font-size: 0.78rem;
            line-height: 1.45;
        }

        @media (max-width: 1024px) {
            .ir-global-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div x-data="{ tab: 'shop' }">
        <nav class="ir-global-tabs" aria-label="Global configuration tabs">
            <button type="button" class="ir-global-tab" :data-active="tab === 'shop'" @click="tab = 'shop'">Shop and Rentals</button>
            <button type="button" class="ir-global-tab" :data-active="tab === 'prices'" @click="tab = 'prices'">Prices and Payments</button>
            <button type="button" class="ir-global-tab" :data-active="tab === 'views'" @click="tab = 'views'">Views and Layout</button>
            <button type="button" class="ir-global-tab" :data-active="tab === 'orders'" @click="tab = 'orders'">Orders and Company</button>
            <button type="button" class="ir-global-tab" :data-active="tab === 'texts'" @click="tab = 'texts'">Conditional Texts</button>
        </nav>

        <section x-show="tab === 'shop'" x-cloak>
            <div class="ir-global-grid">
                <article class="ir-global-card">
                    <h2 class="ir-global-card__title">Booking</h2>
                    <div class="ir-global-fields">
                        <label class="ir-global-toggle"><input type="checkbox" wire:model.live="state.rentals_enabled"> Rentals Enabled</label>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Rentals Disabled Message</label>
                            <textarea class="ir-global-textarea" wire:model.live="state.rentals_disabled_message"></textarea>
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Admin e-Mail</label>
                            <input class="ir-global-input" type="email" wire:model.live="state.admin_email" />
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Sender e-Mail</label>
                            <input class="ir-global-input" type="email" wire:model.live="state.sender_email" />
                        </div>
                        <label class="ir-global-toggle"><input type="checkbox" wire:model.live="state.always_open"> Always Open</label>
                        <label class="ir-global-toggle"><input type="checkbox" wire:model.live="state.force_pickup_dropoff_time"> Force Pickup/Drop Off Time</label>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Pickup/Drop Off Date Format</label>
                            <select class="ir-global-select" wire:model.live="state.pickup_dropoff_date_format">
                                <option value="DD/MM/YYYY">DD/MM/YYYY</option>
                                <option value="MM/DD/YYYY">MM/DD/YYYY</option>
                                <option value="YYYY-MM-DD">YYYY-MM-DD</option>
                            </select>
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Time Format</label>
                            <select class="ir-global-select" wire:model.live="state.time_format">
                                <option value="24">24 Hours</option>
                                <option value="12">12 Hours</option>
                            </select>
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Hours of Extended Gratuity Period</label>
                            <input class="ir-global-input" type="number" min="0" wire:model.live="state.extended_gratuity_period" />
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Apply Extra Hours Charges</label>
                            <select class="ir-global-select" wire:model.live="state.apply_extra_hours_charges">
                                <option value="before_special_prices">Before the Special Prices</option>
                                <option value="after_special_prices">After the Special Prices</option>
                            </select>
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Car Damages in Check-in PDF</label>
                            <select class="ir-global-select" wire:model.live="state.car_damages_checkin_pdf">
                                <option value="damage_marks_and_explanations">Damage Marks and Explanations</option>
                                <option value="damage_marks_only">Damage Marks Only</option>
                                <option value="none">None</option>
                            </select>
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Dropped Off car is available after (hours)</label>
                            <input class="ir-global-input" type="number" min="0" wire:model.live="state.dropoff_available_after" />
                        </div>
                        <label class="ir-global-toggle"><input type="checkbox" wire:model.live="state.allow_pickups_on_dropoffs"> Allow Pick Ups on Drop Offs</label>
                        <label class="ir-global-toggle"><input type="checkbox" wire:model.live="state.rentals_for_today_any_time"> Rentals for today at any time</label>
                        <label class="ir-global-toggle"><input type="checkbox" wire:model.live="state.auto_assign_car_unit"> Auto-Assign Car Unit</label>
                        <label class="ir-global-toggle"><input type="checkbox" wire:model.live="state.enable_coupons"> Enable Coupons</label>
                        <label class="ir-global-toggle"><input type="checkbox" wire:model.live="state.enable_customers_pin_code"> Enable Customers PIN Code</label>
                        <label class="ir-global-toggle"><input type="checkbox" wire:model.live="state.token_form_order_submit"> Token Form Order Submit</label>
                        <label class="ir-global-toggle"><input type="checkbox" wire:model.live="state.require_login"> Require Login</label>
                        <div class="ir-global-field">
                            <label class="ir-global-label">iCal Secret Key</label>
                            <input class="ir-global-input" type="text" wire:model.live="state.ical_secret_key" />
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Minutes of Waiting for the Payment</label>
                            <input class="ir-global-input" type="number" min="1" wire:model.live="state.payment_lock_minutes" />
                        </div>
                    </div>
                </article>

                <article class="ir-global-card">
                    <h2 class="ir-global-card__title">Search/Rental Parameters</h2>
                    <div class="ir-global-fields">
                        <div class="ir-global-field">
                            <label class="ir-global-label">Minimum # Days of Rental</label>
                            <input class="ir-global-input" type="number" min="1" wire:model.live="state.minimum_rental_days" />
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Days in Advance for bookings</label>
                            <input class="ir-global-input" type="number" min="0" wire:model.live="state.days_in_advance_for_bookings" />
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Maximum Date in the Future from today</label>
                            <div class="ir-global-grid" style="grid-template-columns: 2fr 1fr; gap: 0.5rem;">
                                <input class="ir-global-input" type="number" min="1" wire:model.live="state.max_date_future_value" />
                                <select class="ir-global-select" wire:model.live="state.max_date_future_unit">
                                    <option value="days">Days</option>
                                    <option value="months">Months</option>
                                    <option value="years">Years</option>
                                </select>
                            </div>
                        </div>
                        <label class="ir-global-toggle"><input type="checkbox" wire:model.live="state.choose_pickup_location"> Choose Pickup Location</label>
                        <label class="ir-global-toggle"><input type="checkbox" wire:model.live="state.cars_category_filter"> Cars Category Filter</label>
                        <label class="ir-global-toggle"><input type="checkbox" wire:model.live="state.filter_by_characteristics"> Filter by Characteristics</label>
                        <label class="ir-global-toggle"><input type="checkbox" wire:model.live="state.suggest_solutions_no_availability"> Suggest solutions when no availability</label>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Preferred Countries Ordering</label>
                            <input class="ir-global-input" type="text" wire:model.live="state.preferred_countries_ordering" />
                        </div>
                    </div>
                </article>

                <article class="ir-global-card">
                    <h2 class="ir-global-card__title">System</h2>
                    <div class="ir-global-fields">
                        <div class="ir-global-field">
                            <label class="ir-global-label">Appearance</label>
                            <select class="ir-global-select" wire:model.live="state.appearance">
                                <option value="light">Light</option>
                                <option value="dark">Dark</option>
                                <option value="auto">Auto</option>
                            </select>
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Appearance (front-end)</label>
                            <select class="ir-global-select" wire:model.live="state.frontend_appearance">
                                <option value="disabled">Disabled</option>
                                <option value="light">Light</option>
                                <option value="dark">Dark</option>
                            </select>
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Cron Jobs Secret Key</label>
                            <input class="ir-global-input" type="text" wire:model.live="state.cron_jobs_secret_key" />
                        </div>
                        <label class="ir-global-toggle"><input type="checkbox" wire:model.live="state.enable_multilanguage"> Enable Multi-Language</label>
                        <label class="ir-global-toggle"><input type="checkbox" wire:model.live="state.load_font_awesome"> Load Font Awesome</label>
                        <label class="ir-global-toggle"><input type="checkbox" wire:model.live="state.bootstrap_css_js"> Bootstrap CSS/JS</label>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Calendar Type</label>
                            <select class="ir-global-select" wire:model.live="state.calendar_type">
                                <option value="jquery_ui">jQuery UI</option>
                                <option value="flatpickr">Flatpickr</option>
                            </select>
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Google Maps API Key</label>
                            <input class="ir-global-input" type="text" wire:model.live="state.google_maps_api_key" />
                            <p class="ir-global-hint">Used for Google Reviews, host address autocomplete (Places API + Maps JavaScript API), and listing map previews. Restrict the key to your site referrers in Google Cloud.</p>
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Ipinfo.io API Token</label>
                            <input class="ir-global-input" type="text" wire:model.live="state.ipinfo_api_token" />
                        </div>
                    </div>
                </article>

                <article class="ir-global-card">
                    <h2 class="ir-global-card__title">Backup</h2>
                    <div class="ir-global-fields">
                        <div class="ir-global-field">
                            <label class="ir-global-label">Export Type</label>
                            <select class="ir-global-select" wire:model.live="state.backup_export_type">
                                <option value="full">Full</option>
                                <option value="database">Database</option>
                            </select>
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Folder Path</label>
                            <input class="ir-global-input" type="text" wire:model.live="state.backup_folder_path" />
                        </div>
                    </div>
                </article>
            </div>

            <div class="ir-global-actions">
                <button class="ir-global-save" type="button" wire:click="save">Save configuration</button>
            </div>
        </section>

        <section x-show="tab === 'prices'" x-cloak>
            <div class="ir-global-grid">
                <article class="ir-global-card">
                    <h2 class="ir-global-card__title">Currency</h2>
                    <div class="ir-global-fields">
                        <div class="ir-global-field">
                            <label class="ir-global-label">Currency Name</label>
                            <input class="ir-global-input" type="text" wire:model.live="state.currency_name" />
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Currency Symbol</label>
                            <input class="ir-global-input" type="text" wire:model.live="state.currency_symbol" />
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Transactions Currency Code</label>
                            <input class="ir-global-input" type="text" maxlength="3" wire:model.live="state.currency_code" />
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Number of Decimals</label>
                            <input class="ir-global-input" type="number" min="0" max="4" wire:model.live="state.currency_decimals" />
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Decimal Separator</label>
                            <input class="ir-global-input" type="text" maxlength="2" wire:model.live="state.currency_decimal_separator" />
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Thousand Separator</label>
                            <input class="ir-global-input" type="text" maxlength="2" wire:model.live="state.currency_thousand_separator" />
                        </div>
                    </div>
                </article>

                <article class="ir-global-card">
                    <h2 class="ir-global-card__title">Taxes and Payments</h2>
                    <div class="ir-global-fields">
                        <label class="ir-global-toggle"><input type="checkbox" wire:model.live="state.prices_tax_included"> Prices Tax Included</label>
                        <label class="ir-global-toggle"><input type="checkbox" wire:model.live="state.show_tax_summary_only"> Show Tax in Summary Only</label>
                        <label class="ir-global-toggle"><input type="checkbox" wire:model.live="state.allow_multiple_payments_same_order"> Allow multiple payments for the same order</label>
                        <label class="ir-global-toggle"><input type="checkbox" wire:model.live="state.pay_entire_amount"> Pay Entire Amount</label>
                        <label class="ir-global-toggle"><input type="checkbox" wire:model.live="state.allow_deposit"> Let customers choose to leave a deposit</label>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Leave a deposit of</label>
                            <div class="ir-global-grid" style="grid-template-columns: 2fr 1fr; gap: 0.5rem;">
                                <input class="ir-global-input" type="number" min="0" wire:model.live="state.deposit_value" />
                                <select class="ir-global-select" wire:model.live="state.deposit_type">
                                    <option value="percentage">%</option>
                                    <option value="fixed">Fixed</option>
                                </select>
                            </div>
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Payment Transaction Name</label>
                            <input class="ir-global-input" type="text" wire:model.live="state.payment_transaction_name" />
                        </div>
                    </div>
                </article>
            </div>

            <div class="ir-global-actions">
                <button class="ir-global-save" type="button" wire:click="save">Save configuration</button>
            </div>
        </section>

        <section x-show="tab === 'views'" x-cloak>
            <div class="ir-global-grid">
                <article class="ir-global-card">
                    <h2 class="ir-global-card__title">Appearance and Texts</h2>
                    <div class="ir-global-fields">
                        <div class="ir-global-field">
                            <label class="ir-global-label">Calendars First Day of the Week</label>
                            <select class="ir-global-select" wire:model.live="state.calendar_first_day_of_week">
                                <option value="monday">Monday</option>
                                <option value="tuesday">Tuesday</option>
                                <option value="wednesday">Wednesday</option>
                                <option value="thursday">Thursday</option>
                                <option value="friday">Friday</option>
                                <option value="saturday">Saturday</option>
                                <option value="sunday">Sunday</option>
                            </select>
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Number of Months to Show</label>
                            <input class="ir-global-input" type="number" min="0" wire:model.live="state.number_of_months_to_show" />
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Thumbnails Size (px)</label>
                            <input class="ir-global-input" type="number" min="1" wire:model.live="state.thumbnails_size_px" />
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Search Results Style</label>
                            <select class="ir-global-select" wire:model.live="state.search_results_style">
                                <option value="list">List</option>
                                <option value="grid">Grid</option>
                            </select>
                        </div>
                        <label class="ir-global-toggle"><input type="checkbox" wire:model.live="state.show_partly_reserved_days"> Show Partly Reserved Days</label>
                        <label class="ir-global-toggle"><input type="checkbox" wire:model.live="state.show_vikrentcar_footer"> Show VikRentCar Footer</label>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Customer Email</label>
                            <textarea class="ir-global-textarea" wire:model.live="state.customer_email_template"></textarea>
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Customer PDF</label>
                            <textarea class="ir-global-textarea" wire:model.live="state.customer_pdf_template"></textarea>
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">PDF Check-in</label>
                            <textarea class="ir-global-textarea" wire:model.live="state.pdf_checkin_template"></textarea>
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">PDF Invoice</label>
                            <textarea class="ir-global-textarea" wire:model.live="state.pdf_invoice_template"></textarea>
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Custom CSS Overrides</label>
                            <textarea class="ir-global-textarea" wire:model.live="state.custom_css_overrides"></textarea>
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Theme</label>
                            <select class="ir-global-select" wire:model.live="state.theme_name">
                                <option value="default">default</option>
                                <option value="modern">modern</option>
                            </select>
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Opening Page Text</label>
                            <textarea class="ir-global-textarea" wire:model.live="state.opening_page_text"></textarea>
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Closing Page Text</label>
                            <textarea class="ir-global-textarea" wire:model.live="state.closing_page_text"></textarea>
                        </div>
                    </div>
                </article>

                <article class="ir-global-card">
                    <h2 class="ir-global-card__title">Preferred Colors</h2>
                    <div class="ir-global-fields">
                        <div class="ir-global-field">
                            <label class="ir-global-label">Titles and Headings</label>
                            <input class="ir-global-input" type="color" wire:model.live="state.preferred_color_titles_headings" />
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Elements with backgrounds - Background color</label>
                            <input class="ir-global-input" type="color" wire:model.live="state.preferred_color_elements_bg" />
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Elements with backgrounds - Font color</label>
                            <input class="ir-global-input" type="color" wire:model.live="state.preferred_color_elements_font" />
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Hovered elements - Background color</label>
                            <input class="ir-global-input" type="color" wire:model.live="state.preferred_color_hover_bg" />
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Hovered elements - Font color</label>
                            <input class="ir-global-input" type="color" wire:model.live="state.preferred_color_hover_font" />
                        </div>
                    </div>
                </article>
            </div>

            <div class="ir-global-actions">
                <button class="ir-global-save" type="button" wire:click="save">Save configuration</button>
            </div>
        </section>

        <section x-show="tab === 'orders'" x-cloak>
            <div class="ir-global-grid">
                <article class="ir-global-card">
                    <h2 class="ir-global-card__title">Orders and Company</h2>
                    <div class="ir-global-fields">
                        <div class="ir-global-field">
                            <label class="ir-global-label">Company Name</label>
                            <input class="ir-global-input" type="text" wire:model.live="state.company_name" />
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Company Logo (path or URL)</label>
                            <input class="ir-global-input" type="text" wire:model.live="state.company_logo" />
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Back-end Logo (180px path or URL)</label>
                            <input class="ir-global-input" type="text" wire:model.live="state.backend_logo_180" />
                        </div>
                        <label class="ir-global-toggle"><input type="checkbox" wire:model.live="state.attach_pdf_to_order_email"> Attach PDF to the order eMail</label>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Send Emails When</label>
                            <select class="ir-global-select" wire:model.live="state.send_emails_when">
                                <option value="pending_or_confirmed">Order is Pending or Confirmed</option>
                                <option value="confirmed_only">Order is Confirmed</option>
                                <option value="always">Always</option>
                            </select>
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">iCal Export - Past months</label>
                            <input class="ir-global-input" type="number" min="0" wire:model.live="state.ical_export_past_months" />
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">iCal Events End Date</label>
                            <select class="ir-global-select" wire:model.live="state.ical_events_end_date">
                                <option value="pickup_date">Pick up Date</option>
                                <option value="dropoff_date">Drop off Date</option>
                            </select>
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Attach iCal Reminder</label>
                            <select class="ir-global-select" wire:model.live="state.attach_ical_reminder">
                                <option value="administrator_customer">Administrator + Customer</option>
                                <option value="administrator">Administrator</option>
                                <option value="customer">Customer</option>
                                <option value="none">None</option>
                            </select>
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Tracking Code</label>
                            <textarea class="ir-global-textarea" wire:model.live="state.tracking_code"></textarea>
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Conversion Code</label>
                            <textarea class="ir-global-textarea" wire:model.live="state.conversion_code"></textarea>
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Disclaimer</label>
                            <textarea class="ir-global-textarea" wire:model.live="state.disclaimer"></textarea>
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Footer Text Order eMail</label>
                            <textarea class="ir-global-textarea" wire:model.live="state.footer_text_order_email"></textarea>
                        </div>
                    </div>
                </article>

                <article class="ir-global-card">
                    <h2 class="ir-global-card__title">Customer Documents</h2>
                    <div class="ir-global-fields">
                        <label class="ir-global-toggle"><input type="checkbox" wire:model.live="state.allow_documents_upload"> Allow Documents Upload</label>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Upload instructions</label>
                            <textarea class="ir-global-textarea" wire:model.live="state.upload_instructions"></textarea>
                        </div>
                    </div>
                </article>
            </div>

            <div class="ir-global-actions">
                <button class="ir-global-save" type="button" wire:click="save">Save configuration</button>
            </div>
        </section>

        <section x-show="tab === 'texts'" x-cloak>
            <div class="ir-global-placeholder">Use the existing <a href="{{ url('/admin/impact-rent/conditional-texts') }}">Conditional Texts resource</a> for text rules.</div>
        </section>
    </div>
</x-filament-panels::page>
