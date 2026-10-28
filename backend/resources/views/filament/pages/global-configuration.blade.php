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
            border: 1px solid #d5deea;
            border-radius: 8px;
            background: #ffffff;
            min-height: 40px;
            padding: 8px 12px;
            font-size: 14px;
            color: #041e37;
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
            margin: 0.35rem 0 0.8rem;
            color: #5b6b7d;
            font-size: 13px;
            line-height: 1.45;
        }
        .ir-currency-grid, .ir-pay-grid { display: grid; gap: 12px 16px; margin-top: 12px; }
        .ir-currency-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .ir-pay-grid { grid-template-columns: 1fr 1fr; }
        .tb-banner.is-bad { background: #fde7e5; color: #b42318; }

        @media (max-width: 1024px) {
            .ir-global-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div x-data="{ tab: new URLSearchParams(window.location.search).get('tab') || 'shop', q: '' }">
        <nav class="ir-global-tabs" aria-label="Global configuration tabs">
            <button type="button" class="ir-global-tab" :data-active="tab === 'shop'" @click="tab = 'shop'">Shop and Rentals</button>
            <button type="button" class="ir-global-tab" :data-active="tab === 'prices'" @click="tab = 'prices'">Prices and Payments</button>
            <button type="button" class="ir-global-tab" :data-active="tab === 'views'" @click="tab = 'views'">Views and Layout</button>
            <button type="button" class="ir-global-tab" :data-active="tab === 'orders'" @click="tab = 'orders'">Orders and Company</button>
            <button type="button" class="ir-global-tab" :data-active="tab === 'texts'" @click="tab = 'texts'">Conditional Texts</button>
        </nav>

        <section x-show="tab === 'shop'" x-cloak class="tb-settings">
            <div class="tb-settings-toolbar">
                <input class="ir-global-input tb-settings-search" type="search" x-model="q" placeholder="Find a setting..." aria-label="Find a setting" />
            </div>
            <div class="ir-global-grid tb-settings-stack">
                <article class="ir-global-card" x-show="!q || $el.innerText.toLowerCase().includes(q.toLowerCase())">
                    <h2 class="ir-global-card__title">Website status</h2>
                    <div class="ir-global-fields">
                        <label class="ir-global-toggle tb-toggle-row">
                            <span>
                                Coming soon mode
                                <small>Public visitors see the Under construction page. Admins and designers still see the full site when logged in.</small>
                            </span>
                            <input type="checkbox" wire:model.live="state.coming_soon">
                        </label>
                        <label class="ir-global-toggle tb-toggle-row">
                            <span>
                                Rentals enabled
                                <small>When off, the booking form is hidden and the message below is shown instead.</small>
                            </span>
                            <input type="checkbox" wire:model.live="state.rentals_enabled">
                        </label>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Rentals disabled message</label>
                            <textarea class="ir-global-textarea" wire:model.live="state.rentals_disabled_message" placeholder="e.g. Bookings open again on 1 March."></textarea>
                        </div>
                    </div>
                </article>

                <article class="ir-global-card" x-show="!q || $el.innerText.toLowerCase().includes(q.toLowerCase())">
                    <h2 class="ir-global-card__title">Booking rules</h2>
                    <div class="ir-global-fields">
                        <div class="ir-global-grid" style="grid-template-columns: 1fr 1fr; gap: 0.7rem;">
                            <div class="ir-global-field">
                                <label class="ir-global-label">Admin e-mail</label>
                                <input class="ir-global-input" type="email" wire:model.live="state.admin_email" />
                            </div>
                            <div class="ir-global-field">
                                <label class="ir-global-label">Sender e-mail</label>
                                <input class="ir-global-input" type="email" wire:model.live="state.sender_email" />
                            </div>
                            <div class="ir-global-field">
                                <label class="ir-global-label">Pickup / drop-off date format</label>
                                <select class="ir-global-select" wire:model.live="state.pickup_dropoff_date_format">
                                    <option value="DD/MM/YYYY">DD/MM/YYYY</option>
                                    <option value="MM/DD/YYYY">MM/DD/YYYY</option>
                                    <option value="YYYY-MM-DD">YYYY-MM-DD</option>
                                </select>
                            </div>
                            <div class="ir-global-field">
                                <label class="ir-global-label">Time format</label>
                                <select class="ir-global-select" wire:model.live="state.time_format">
                                    <option value="24">24 hours</option>
                                    <option value="12">12 hours</option>
                                </select>
                            </div>
                            <div class="ir-global-field">
                                <label class="ir-global-label">Minutes to wait for payment</label>
                                <input class="ir-global-input" type="number" min="1" wire:model.live="state.payment_lock_minutes" />
                            </div>
                            <div class="ir-global-field">
                                <label class="ir-global-label">Extended gratuity period (hours)</label>
                                <input class="ir-global-input" type="number" min="0" wire:model.live="state.extended_gratuity_period" />
                            </div>
                            <div class="ir-global-field">
                                <label class="ir-global-label">Car available after drop-off (hours)</label>
                                <input class="ir-global-input" type="number" min="0" wire:model.live="state.dropoff_available_after" />
                            </div>
                            <div class="ir-global-field">
                                <label class="ir-global-label">Car damages in check-in PDF</label>
                                <select class="ir-global-select" wire:model.live="state.car_damages_checkin_pdf">
                                    <option value="damage_marks_and_explanations">Damage marks and explanations</option>
                                    <option value="damage_marks_only">Damage marks only</option>
                                    <option value="none">No</option>
                                </select>
                            </div>
                            <div class="ir-global-field">
                                <label class="ir-global-label">iCal secret key</label>
                                <input class="ir-global-input" type="text" wire:model.live="state.ical_secret_key" placeholder="Not set" />
                            </div>
                            <div class="ir-global-field">
                                <label class="ir-global-label">Apply extra hours charges</label>
                                <select class="ir-global-select" wire:model.live="state.apply_extra_hours_charges">
                                    <option value="before_special_prices">Before the special prices</option>
                                    <option value="after_special_prices">After the special prices</option>
                                </select>
                            </div>
                        </div>
                        <label class="ir-global-toggle tb-toggle-row"><span>Always open<small>Accept pickups and drop-offs outside opening hours.</small></span><input type="checkbox" wire:model.live="state.always_open"></label>
                        <label class="ir-global-toggle tb-toggle-row"><span>Auto-assign car unit<small>Pick a free unit automatically for each booking.</small></span><input type="checkbox" wire:model.live="state.auto_assign_car_unit"></label>
                        <label class="ir-global-toggle tb-toggle-row"><span>Force pickup / drop-off time<small>Customers cannot choose the time.</small></span><input type="checkbox" wire:model.live="state.force_pickup_dropoff_time"></label>
                        <label class="ir-global-toggle tb-toggle-row"><span>Enable coupons<small>Show the discount code field at checkout.</small></span><input type="checkbox" wire:model.live="state.enable_coupons"></label>
                        <label class="ir-global-toggle tb-toggle-row"><span>Allow pickups on drop-off days<small>A car can go out the same day it comes back.</small></span><input type="checkbox" wire:model.live="state.allow_pickups_on_dropoffs"></label>
                        <label class="ir-global-toggle tb-toggle-row"><span>Customer PIN code<small>Returning customers can use a PIN.</small></span><input type="checkbox" wire:model.live="state.enable_customers_pin_code"></label>
                        <label class="ir-global-toggle tb-toggle-row"><span>Rentals for today at any time<small>Same-day bookings ignore the advance limit.</small></span><input type="checkbox" wire:model.live="state.rentals_for_today_any_time"></label>
                        <label class="ir-global-toggle tb-toggle-row"><span>Token on order submit<small>Extra token against duplicate submits.</small></span><input type="checkbox" wire:model.live="state.token_form_order_submit"></label>
                        <label class="ir-global-toggle tb-toggle-row"><span>Require login<small>Customers must sign in before booking.</small></span><input type="checkbox" wire:model.live="state.require_login"></label>
                    </div>
                </article>

                <article class="ir-global-card" x-show="!q || $el.innerText.toLowerCase().includes(q.toLowerCase())">
                    <h2 class="ir-global-card__title">Search & availability</h2>
                    <div class="ir-global-fields">
                        <div class="ir-global-grid" style="grid-template-columns: 1fr 1fr 1fr; gap: 0.7rem;">
                            <div class="ir-global-field">
                                <label class="ir-global-label">Minimum rental days</label>
                                <input class="ir-global-input" type="number" min="1" wire:model.live="state.minimum_rental_days" />
                            </div>
                            <div class="ir-global-field">
                                <label class="ir-global-label">Days in advance for bookings</label>
                                <input class="ir-global-input" type="number" min="0" wire:model.live="state.days_in_advance_for_bookings" />
                            </div>
                            <div class="ir-global-field">
                                <label class="ir-global-label">Bookable up to</label>
                                <div class="ir-global-grid" style="grid-template-columns: 2fr 1fr; gap: 0.5rem;">
                                    <input class="ir-global-input" type="number" min="1" wire:model.live="state.max_date_future_value" />
                                    <select class="ir-global-select" wire:model.live="state.max_date_future_unit">
                                        <option value="days">Days</option>
                                        <option value="months">Months</option>
                                        <option value="years">Years</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <label class="ir-global-toggle tb-toggle-row"><span>Choose pickup location<small>Show the location picker in search.</small></span><input type="checkbox" wire:model.live="state.choose_pickup_location"></label>
                        <label class="ir-global-toggle tb-toggle-row"><span>Filter by features<small>Filter results by vehicle features.</small></span><input type="checkbox" wire:model.live="state.filter_by_characteristics"></label>
                        <label class="ir-global-toggle tb-toggle-row"><span>Category filter<small>Filter results by car category.</small></span><input type="checkbox" wire:model.live="state.cars_category_filter"></label>
                        <label class="ir-global-toggle tb-toggle-row"><span>Suggest alternatives<small>Offer other dates or cars when nothing is free.</small></span><input type="checkbox" wire:model.live="state.suggest_solutions_no_availability"></label>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Preferred countries order</label>
                            <input class="ir-global-input" type="text" wire:model.live="state.preferred_countries_ordering" />
                        </div>
                    </div>
                </article>

                <article class="ir-global-card" x-show="!q || $el.innerText.toLowerCase().includes(q.toLowerCase())">
                    <h2 class="ir-global-card__title">Advanced</h2>
                    <div class="ir-global-fields">
                        <div class="ir-global-grid" style="grid-template-columns: 1fr 1fr 1fr; gap: 0.7rem;">
                            <div class="ir-global-field">
                                <label class="ir-global-label">Admin appearance</label>
                                <select class="ir-global-select" wire:model.live="state.appearance">
                                    <option value="light">Light</option>
                                    <option value="dark">Dark</option>
                                    <option value="auto">Auto</option>
                                </select>
                            </div>
                            <div class="ir-global-field">
                                <label class="ir-global-label">Front-end appearance</label>
                                <select class="ir-global-select" wire:model.live="state.frontend_appearance">
                                    <option value="disabled">Disabled</option>
                                    <option value="light">Light</option>
                                    <option value="dark">Dark</option>
                                </select>
                            </div>
                            <div class="ir-global-field">
                                <label class="ir-global-label">Cron jobs secret key</label>
                                <input class="ir-global-input" type="text" wire:model.live="state.cron_jobs_secret_key" placeholder="Not set" />
                            </div>
                            <div class="ir-global-field">
                                <label class="ir-global-label">Backup export type</label>
                                <select class="ir-global-select" wire:model.live="state.backup_export_type">
                                    <option value="full">Full</option>
                                    <option value="database">Database</option>
                                </select>
                            </div>
                            <div class="ir-global-field">
                                <label class="ir-global-label">Backup folder</label>
                                <input class="ir-global-input" type="text" wire:model.live="state.backup_folder_path" />
                            </div>
                            <div class="ir-global-field">
                                <label class="ir-global-label">Calendar type</label>
                                <select class="ir-global-select" wire:model.live="state.calendar_type">
                                    <option value="jquery_ui">jQuery UI</option>
                                    <option value="flatpickr">Flatpickr</option>
                                </select>
                            </div>
                        </div>
                        <label class="ir-global-toggle tb-toggle-row"><span>Multi-language<small>Translate site content per language.</small></span><input type="checkbox" wire:model.live="state.enable_multilanguage"></label>
                        <label class="ir-global-toggle tb-toggle-row"><span>Load Font Awesome</span><input type="checkbox" wire:model.live="state.load_font_awesome"></label>
                        <label class="ir-global-toggle tb-toggle-row"><span>Bootstrap CSS/JS</span><input type="checkbox" wire:model.live="state.bootstrap_css_js"></label>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Google Maps API key</label>
                            <input class="ir-global-input" type="text" wire:model.live="state.google_maps_api_key" />
                            <p class="ir-global-hint">Used for Google Reviews, host address autocomplete, and listing map previews. Restrict the key to your site referrers in Google Cloud.</p>
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Ipinfo.io API token</label>
                            <input class="ir-global-input" type="text" wire:model.live="state.ipinfo_api_token" />
                        </div>
                    </div>
                </article>
            </div>

            <div class="ir-global-actions">
                <button class="tb-btn-ghost" type="button" wire:click="discard">Discard</button>
                <button class="ir-global-save" type="button" wire:click="save">Save settings</button>
            </div>
        </section>

        <section x-show="tab === 'prices'" x-cloak>
            <div x-data="{
                mismatch() {
                    const name = String($wire.state.currency_name || '').toLowerCase();
                    const symbol = String($wire.state.currency_symbol || '').toLowerCase();
                    const code = String($wire.state.currency_code || '').toUpperCase();
                    const euroName = name.includes('euro');
                    const kr = symbol.includes('kr');
                    const euroSymbol = symbol.includes('€') || symbol === 'eur';
                    return (code === 'ISK' && (euroName || euroSymbol)) || (code === 'EUR' && (kr || name.includes('kron') || name.includes('isk')));
                },
                preview() {
                    const decimals = Math.max(0, Number($wire.state.currency_decimals || 0));
                    const decimal = String($wire.state.currency_decimal_separator || '.');
                    const thousand = String($wire.state.currency_thousand_separator || ',');
                    const symbol = String($wire.state.currency_symbol || '');
                    const digits = (147560).toFixed(decimals);
                    const [whole, frac] = digits.split('.');
                    const grouped = whole.replace(/\B(?=(\d{3})+(?!\d))/g, thousand);
                    return symbol + ' ' + grouped + (frac !== undefined && decimals > 0 ? decimal + frac : '');
                }
            }">
            <div class="ir-global-grid">
                <article class="ir-global-card">
                    <h2 class="ir-global-card__title">Currency</h2>
                    <p class="ir-global-hint">How amounts are written on the website and in e-mails.</p>
                    <div class="tb-banner is-bad" x-show="mismatch()" x-cloak>
                        <span x-text="'Name says “' + ($wire.state.currency_name || '') + '”, symbol is “' + ($wire.state.currency_symbol || '') + '”, code is “' + ($wire.state.currency_code || '') + '”. Pick one currency so prices are not shown as € on the dashboard and ISK on bookings.'"></span>
                    </div>
                    <div class="ir-currency-grid">
                        <div class="ir-global-field">
                            <label class="ir-global-label">Currency name</label>
                            <input class="ir-global-input" type="text" wire:model.live="state.currency_name" />
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Currency symbol</label>
                            <input class="ir-global-input" type="text" wire:model.live="state.currency_symbol" />
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Transactions currency code</label>
                            <input class="ir-global-input" type="text" maxlength="3" wire:model.live="state.currency_code" />
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Number of decimals</label>
                            <input class="ir-global-input" type="number" min="0" max="4" wire:model.live="state.currency_decimals" />
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Decimal separator</label>
                            <input class="ir-global-input" type="text" maxlength="2" wire:model.live="state.currency_decimal_separator" />
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Thousand separator</label>
                            <input class="ir-global-input" type="text" maxlength="2" wire:model.live="state.currency_thousand_separator" />
                        </div>
                    </div>
                    <p class="ir-global-hint">Preview <strong x-text="preview()"></strong></p>
                </article>

                <article class="ir-global-card">
                    <h2 class="ir-global-card__title">Taxes & payments</h2>
                    <p class="ir-global-hint">What customers pay at checkout.</p>
                    <div class="ir-pay-grid">
                        <label class="ir-global-toggle tb-toggle-row"><span>Prices include tax<small>Entered prices already contain VAT.</small></span><input type="checkbox" wire:model.live="state.prices_tax_included"></label>
                        <label class="ir-global-toggle tb-toggle-row"><span>Pay entire amount<small>Ask for the full amount at checkout.</small></span><input type="checkbox" wire:model.live="state.pay_entire_amount"></label>
                        <label class="ir-global-toggle tb-toggle-row"><span>Show tax in summary only<small>Hide tax lines in the price list.</small></span><input type="checkbox" wire:model.live="state.show_tax_summary_only"></label>
                        <label class="ir-global-toggle tb-toggle-row"><span>Let customers leave a deposit<small>Pay part now, the rest later.</small></span><input type="checkbox" wire:model.live="state.allow_deposit"></label>
                        <label class="ir-global-toggle tb-toggle-row"><span>Allow multiple payments for one order<small>Customers can pay in parts.</small></span><input type="checkbox" wire:model.live="state.allow_multiple_payments_same_order"></label>
                    </div>
                    <div class="ir-currency-grid">
                        <div class="ir-global-field">
                            <label class="ir-global-label">Deposit amount</label>
                            <input class="ir-global-input" type="number" min="0" wire:model.live="state.deposit_value" />
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Deposit type</label>
                            <select class="ir-global-select" wire:model.live="state.deposit_type">
                                <option value="percentage">%</option>
                                <option value="fixed">Fixed</option>
                            </select>
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Payment transaction name</label>
                            <input class="ir-global-input" type="text" wire:model.live="state.payment_transaction_name" />
                            <p class="ir-global-hint">Shown on the customer's card statement.</p>
                        </div>
                    </div>
                </article>
            </div>
            </div>

            <div class="ir-global-actions">
                <button class="tb-btn-ghost" type="button" wire:click="discard">Discard</button>
                <button class="ir-global-save" type="button" wire:click="save">Save settings</button>
            </div>
        </section>

        <section x-show="tab === 'views'" x-cloak>
            <div class="ir-global-grid">
                <article class="ir-global-card">
                    <h2 class="ir-global-card__title">Appearance and texts</h2>
                    <div class="ir-global-fields">
                        <div class="ir-global-field">
                            <label class="ir-global-label">Calendars first day of the week</label>
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
                            <label class="ir-global-label">Number of months to show</label>
                            <input class="ir-global-input" type="number" min="0" wire:model.live="state.number_of_months_to_show" />
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Thumbnails size (px)</label>
                            <input class="ir-global-input" type="number" min="1" wire:model.live="state.thumbnails_size_px" />
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Search results style</label>
                            <select class="ir-global-select" wire:model.live="state.search_results_style">
                                <option value="list">List</option>
                                <option value="grid">Grid</option>
                            </select>
                        </div>
                        <label class="ir-global-toggle"><input type="checkbox" wire:model.live="state.show_partly_reserved_days"> Show partly reserved days</label>
                        <label class="ir-global-toggle"><input type="checkbox" wire:model.live="state.show_vikrentcar_footer"> Show VikRentCar footer</label>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Customer e-mail</label>
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
                            <label class="ir-global-label">Custom CSS overrides</label>
                            <textarea class="ir-global-textarea" wire:model.live="state.custom_css_overrides"></textarea>
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Theme</label>
                            <select class="ir-global-select" wire:model.live="state.theme_name">
                                <option value="default">Default</option>
                                <option value="modern">Modern</option>
                            </select>
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Opening page text</label>
                            <textarea class="ir-global-textarea" wire:model.live="state.opening_page_text"></textarea>
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Closing page text</label>
                            <textarea class="ir-global-textarea" wire:model.live="state.closing_page_text"></textarea>
                        </div>
                    </div>
                </article>

                <article class="ir-global-card">
                    <h2 class="ir-global-card__title">Preferred colors</h2>
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
                <button class="tb-btn-ghost" type="button" wire:click="discard">Discard</button>
                <button class="ir-global-save" type="button" wire:click="save">Save settings</button>
            </div>
        </section>

        <section x-show="tab === 'orders'" x-cloak>
            <div class="ir-global-grid">
                <article class="ir-global-card">
                    <h2 class="ir-global-card__title">Orders and company</h2>
                    <div class="ir-global-fields">
                        <div class="ir-global-field">
                            <label class="ir-global-label">Company name</label>
                            <input class="ir-global-input" type="text" wire:model.live="state.company_name" />
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Company logo (path or URL)</label>
                            <input class="ir-global-input" type="text" wire:model.live="state.company_logo" />
                        </div>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Back-end logo (180px path or URL)</label>
                            <input class="ir-global-input" type="text" wire:model.live="state.backend_logo_180" />
                        </div>
                        <label class="ir-global-toggle"><input type="checkbox" wire:model.live="state.attach_pdf_to_order_email"> Attach PDF to the order e-mail</label>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Send e-mails when</label>
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
                    <h2 class="ir-global-card__title">Customer documents</h2>
                    <div class="ir-global-fields">
                        <label class="ir-global-toggle"><input type="checkbox" wire:model.live="state.allow_documents_upload"> Allow documents upload</label>
                        <div class="ir-global-field">
                            <label class="ir-global-label">Upload instructions</label>
                            <textarea class="ir-global-textarea" wire:model.live="state.upload_instructions"></textarea>
                        </div>
                    </div>
                </article>
            </div>

            <div class="ir-global-actions">
                <button class="tb-btn-ghost" type="button" wire:click="discard">Discard</button>
                <button class="ir-global-save" type="button" wire:click="save">Save settings</button>
            </div>
        </section>

        <section x-show="tab === 'texts'" x-cloak>
            <div class="ir-global-placeholder">
                Conditional texts can be stored at
                <a href="{{ url('/admin/impact-rent/conditional-texts') }}">/admin/impact-rent/conditional-texts</a>.
                The public site and checkout do not read those rows. Edit live copy under Site content.
            </div>
        </section>
    </div>
</x-filament-panels::page>
