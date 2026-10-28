<?php

namespace App\Support;

use App\Filament\GuestHouse\Pages\GuestHouseCalendarPage;
use App\Filament\GuestHouse\Resources\GuestHouseAmenityResource;
use App\Filament\GuestHouse\Resources\GuestHouseBookingResource;
use App\Filament\GuestHouse\Resources\GuestHouseResource;
use App\Filament\Pages\EmailSettings;
use App\Filament\Pages\OrdersCalendar;
use App\Filament\Pages\EmailTesting;
use App\Filament\Pages\FaresOverview;
use App\Filament\Pages\GlobalConfiguration;
use App\Filament\Pages\ListingReviewPage;
use App\Filament\Pages\LoggedInUsers;
use App\Filament\Pages\ManageUsers;
use App\Filament\Pages\Reports;
use App\Filament\Pages\TrackingStatistics;
use App\Filament\Resources\Backups\BackupResource;
use App\Filament\Resources\BlogPosts\BlogPostResource;
use App\Filament\Resources\BookingChangeRequests\BookingChangeRequestResource;
use App\Filament\Resources\BookingRestrictions\BookingRestrictionResource;
use App\Filament\Resources\Cars\CarResource;
use App\Filament\Resources\CarUnits\CarUnitResource;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Characteristics\CharacteristicResource;
use App\Filament\Resources\ConditionalTexts\ConditionalTextResource;
use App\Filament\Resources\Coupons\CouponResource;
use App\Filament\Resources\CustomFields\CustomFieldResource;
use App\Filament\Resources\DailyFares\DailyFareResource;
use App\Filament\Resources\EmailLogs\EmailLogResource;
use App\Filament\Resources\EmailTemplates\EmailTemplateResource;
use App\Filament\Resources\ExtraHourFares\ExtraHourFareResource;
use App\Filament\Resources\HourlyFares\HourlyFareResource;
use App\Filament\Pages\SiteContentHub;
use App\Filament\Resources\ListingReviews\ListingReviewResource;
use App\Filament\Resources\LocationFees\LocationFeeResource;
use App\Filament\Resources\Locations\LocationResource;
use App\Filament\Resources\NewsletterCampaigns\NewsletterCampaignResource;
use App\Filament\Resources\NewsletterSubscribers\NewsletterSubscriberResource;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\OutOfHoursFees\OutOfHoursFeeResource;
use App\Filament\Resources\PaymentMethods\PaymentMethodResource;
use App\Filament\Resources\RentalConditions\RentalConditionResource;
use App\Filament\Resources\RegisteredHosts\RegisteredHostResource;
use App\Filament\Resources\RentalOptions\RentalOptionResource;
use App\Filament\Resources\SpecialPrices\SpecialPriceResource;
use App\Filament\Resources\SubCategories\SubCategoryResource;
use App\Filament\Resources\TaxRates\TaxRateResource;
use App\Filament\Resources\TrackingCampaigns\TrackingCampaignResource;
use Filament\Resources\Resource;
use Throwable;

class AdminScreen
{
    /**
     * @return array{crumb: string, title: ?string, description: ?string, nav: ?string, settings: bool}|null
     */
    public static function current(): ?array
    {
        foreach (self::pages() as $page) {
            if (self::matches($page['match'])) {
                return $page;
            }
        }

        return null;
    }

    /**
     * Livewire updates arrive at /livewire/update, so the page path is the referer.
     */
    private static function pageRequest(): \Illuminate\Http\Request
    {
        $current = request();
        $livewire = $current->headers->has('X-Livewire') || str_contains($current->path(), 'livewire/');
        if (! $livewire) {
            return $current;
        }

        $referer = (string) $current->headers->get('referer');
        $parts = parse_url($referer);
        $path = is_array($parts) ? (string) ($parts['path'] ?? '') : '';
        if ($path === '') {
            return $current;
        }

        $uri = $path.(isset($parts['query']) ? '?'.$parts['query'] : '');

        return \Illuminate\Http\Request::create($uri, 'GET');
    }

    /**
     * @return list<array{label: string, url: string, active: bool, count: ?int, group: ?string}>
     */
    /**
     * Header buttons for the current list frame. Edit pages keep their own header.
     *
     * @return list<array{label: string, url: ?string, primary: bool, wire: ?string}>
     */
    public static function headerActions(): array
    {
        $link = fn (string $label, string $url, bool $primary = false, ?string $icon = null): array => [
            'label' => $label,
            'url' => $url,
            'primary' => $primary,
            'wire' => null,
            'icon' => $icon,
        ];

        if (self::on('admin/impact-rent/car-units') && CarUnitResource::canCreate()) {
            return [$link('New fleet unit', CarUnitResource::getUrl('create'), true)];
        }

        if (self::on('admin/impact-rent/main-categories')) {
            $actions = [];
            if (CategoryResource::canCreate()) {
                $actions[] = $link('New main category', CategoryResource::getUrl('create'));
            }
            if (SubCategoryResource::canCreate()) {
                $actions[] = $link('New sub category', SubCategoryResource::getUrl('create'), true);
            }

            return $actions;
        }

        if (self::on('admin/impact-rent/sub-categories') && SubCategoryResource::canCreate()) {
            return [$link('New sub category', SubCategoryResource::getUrl('create'), true)];
        }

        if (self::on('admin/impact-rent/characteristics') && CharacteristicResource::canCreate()) {
            return [$link('New feature', CharacteristicResource::getUrl('create'), true)];
        }

        if (self::on('admin/impact-rent/locations') && LocationResource::canCreate()) {
            return [$link('New location', LocationResource::getUrl('create'), true)];
        }

        if (self::on('admin/impact-rent/rental-conditions') && RentalConditionResource::canCreate()) {
            return [$link('New rental condition', RentalConditionResource::getUrl('create'), true)];
        }

        if (self::on('admin/impact-rent/orders')) {
            $actions = [$link('Calendar view', OrdersCalendar::getUrl(), false, 'admin-redesign/icon-tb-calendar.svg')];
            if (OrderResource::canCreate()) {
                $actions[] = $link('New booking', OrderResource::getUrl('create'), true);
            }

            return $actions;
        }

        if (self::on('admin/guest-houses/guest-house-bookings')) {
            return [$link('Calendar view', OrdersCalendar::getUrl().'?catalog=stays', false, 'admin-redesign/icon-tb-calendar.svg')];
        }

        if (self::on('admin/impact-rent/orders-calendar', 'admin/orders-calendar') && OrderResource::canCreate()) {
            return [$link('New booking', OrderResource::getUrl('create'), true)];
        }

        if (self::on('admin/guest-houses/guest-house-amenities') && GuestHouseAmenityResource::canCreate()) {
            return [[
                'label' => 'New amenity',
                'url' => null,
                'primary' => true,
                'wire' => null,
                'event' => 'tb-amenity-create',
            ]];
        }

        if (self::on('admin/impact-rent/special-prices')) {
            $actions = [$link('Rates calculator', FaresOverview::getUrl(), false, 'admin-redesign/icon-tb-pricing.svg')];
            if (SpecialPriceResource::canCreate()) {
                $actions[] = $link('New pricing rule', SpecialPriceResource::getUrl('create'), true);
            }

            return $actions;
        }

        if (self::on('admin/impact-rent/hourly-fares') && HourlyFareResource::canCreate()) {
            return [$link('New hourly fare', HourlyFareResource::getUrl('create'), true)];
        }

        if (self::on('admin/impact-rent/extra-hour-fares') && ExtraHourFareResource::canCreate()) {
            return [$link('New extra hour fare', ExtraHourFareResource::getUrl('create'), true)];
        }

        if (self::on('admin/impact-rent/out-of-hours-fees') && OutOfHoursFeeResource::canCreate()) {
            return [$link('New out-of-hours fee', OutOfHoursFeeResource::getUrl('create'), true)];
        }

        if (self::on('admin/impact-rent/location-fees') && LocationFeeResource::canCreate()) {
            return [$link('New location fee', LocationFeeResource::getUrl('create'), true)];
        }

        if (self::on('admin/impact-rent/rental-options') && RentalOptionResource::canCreate()) {
            return [$link('New add-on', RentalOptionResource::getUrl('create'), true)];
        }

        if (self::on('admin/impact-rent/coupons') && CouponResource::canCreate()) {
            return [$link('New discount code', CouponResource::getUrl('create'), true)];
        }

        if (self::on('admin/impact-rent/booking-restrictions') && BookingRestrictionResource::canCreate()) {
            return [$link('New restriction', BookingRestrictionResource::getUrl('create'), true)];
        }

        if (self::on('admin/impact-rent/tax-rates', 'admin/impact-rent/price-types')) {
            $actions = [];
            if (class_exists(\App\Filament\Resources\PriceTypes\PriceTypeResource::class) && \App\Filament\Resources\PriceTypes\PriceTypeResource::canCreate()) {
                $actions[] = $link('New price type', \App\Filament\Resources\PriceTypes\PriceTypeResource::getUrl('create'));
            }
            if (TaxRateResource::canCreate()) {
                $actions[] = $link('New tax rate', TaxRateResource::getUrl('create'), true);
            }

            return $actions;
        }

        if (self::on('admin/registered-hosts') && RegisteredHostResource::canCreate()) {
            return [$link('Add host / partner', RegisteredHostResource::getUrl('create'), true)];
        }

        if (self::on('admin/listing-reviews') && ListingReviewResource::canCreate()) {
            return [$link('New listing review', ListingReviewResource::getUrl('create'), true)];
        }

        if (self::on('admin/blog-posts')) {
            $actions = [$link('Page text', SiteContentHub::getUrl().'?tab=good-to-know', false, 'admin-redesign/icon-tb-copy.svg')];
            if (BlogPostResource::canCreate()) {
                $actions[] = $link('New post', BlogPostResource::getUrl('create'), true);
            }

            return $actions;
        }

        if (self::on('admin/newsletter-subscribers', 'admin/newsletter-campaigns') && NewsletterCampaignResource::canCreate()) {
            return [$link('New campaign', NewsletterCampaignResource::getUrl('create'), true)];
        }

        if (self::on('admin/email-templates')) {
            $actions = [[
                'label' => 'Install default templates',
                'url' => null,
                'primary' => false,
                'wire' => null,
                'mount' => 'installDefaults',
                'plain' => true,
            ]];
            if (EmailTemplateResource::canCreate()) {
                $actions[] = $link('New email template', EmailTemplateResource::getUrl('create'), true);
            }

            return $actions;
        }

        return [];
    }

    public static function links(?string $nav): array
    {
        return match ($nav) {
            'vehicles' => self::vehicleLinks(),
            'guest-houses' => self::guestHouseLinks(),
            'pricing' => self::pricingLinks(),
            'email' => self::emailLinks(),
            'analytics' => self::analyticsLinks(),
            'newsletter' => self::newsletterLinks(),
            'settings' => self::settingsLinks(),
            default => [],
        };
    }

    /**
     * @return list<array{match: list<string>, crumb: string, title: ?string, description: ?string, nav: ?string, settings: bool}>
     */
    private static function pages(): array
    {
        return [
            ['match' => ['admin'], 'crumb' => '', 'title' => null, 'description' => null, 'nav' => null, 'settings' => false],
            ['match' => ['admin/orders-calendar', 'admin/orders-calendar/*', 'admin/impact-rent/orders-calendar', 'admin/impact-rent/orders-calendar/*', 'admin/guest-houses/calendar', 'admin/guest-houses/calendar/*'], 'crumb' => 'Bookings', 'title' => 'Calendar', 'description' => 'Every vehicle unit and guest house on its own row. Click a booking to open it.', 'nav' => null, 'settings' => false],
            ['match' => ['admin/impact-rent/orders'], 'crumb' => 'Bookings', 'title' => 'Car reservations', 'description' => 'Every vehicle booking in one list. Open a row to edit, confirm or resend the e-mail.', 'nav' => null, 'settings' => false],
            ['match' => ['admin/impact-rent/orders/*'], 'crumb' => 'Car reservations', 'title' => null, 'description' => null, 'nav' => null, 'settings' => false],
            ['match' => ['admin/guest-houses/guest-house-bookings'], 'crumb' => 'Bookings', 'title' => 'Guest house bookings', 'description' => 'Stays booked in guest houses. Confirm or cancel directly from the list.', 'nav' => null, 'settings' => false],
            ['match' => ['admin/guest-houses/guest-house-bookings/*'], 'crumb' => 'Guest house bookings', 'title' => null, 'description' => null, 'nav' => null, 'settings' => false],
            ['match' => ['admin/booking-change-requests'], 'crumb' => 'Bookings', 'title' => 'Change requests', 'description' => 'Customers asking to change dates or details of a booking.', 'nav' => null, 'settings' => false],
            ['match' => ['admin/booking-change-requests/*'], 'crumb' => 'Change requests', 'title' => null, 'description' => null, 'nav' => null, 'settings' => false],
            ['match' => ['admin/guest-houses/guest-houses'], 'crumb' => 'Catalog', 'title' => 'Guest houses', 'description' => 'Stays listed by hosts and by MyTerraBook.', 'nav' => 'guest-houses', 'settings' => false],
            ['match' => ['admin/guest-houses/guest-houses/*'], 'crumb' => 'Guest houses', 'title' => null, 'description' => null, 'nav' => 'guest-houses', 'settings' => false],
            ['match' => ['admin/guest-houses/guest-house-amenities', 'admin/guest-houses/guest-house-amenities/*'], 'crumb' => 'Catalog', 'title' => 'Guest houses', 'description' => 'Stays listed by hosts and by MyTerraBook.', 'nav' => 'guest-houses', 'settings' => false],
            ['match' => ['admin/impact-rent/cars'], 'crumb' => 'Catalog', 'title' => 'Vehicles', 'description' => 'Cars and campervans across Iceland, from MyTerraBook and partner hosts.', 'nav' => 'vehicles', 'settings' => false],
            ['match' => ['admin/impact-rent/cars/*'], 'crumb' => 'Vehicles', 'title' => null, 'description' => null, 'nav' => 'vehicles', 'settings' => false],
            ['match' => ['admin/impact-rent/car-units', 'admin/impact-rent/car-units/*'], 'crumb' => 'Catalog · Vehicles', 'title' => 'Fleet units', 'description' => 'Each unit is one physical car or camper that can be booked.', 'nav' => 'vehicles', 'settings' => false],
            ['match' => ['admin/impact-rent/main-categories', 'admin/impact-rent/main-categories/*', 'admin/impact-rent/sub-categories', 'admin/impact-rent/sub-categories/*'], 'crumb' => 'Catalog · Vehicles', 'title' => 'Categories', 'description' => 'Main categories group the sub categories shown in search.', 'nav' => 'vehicles', 'settings' => false],
            ['match' => ['admin/impact-rent/characteristics', 'admin/impact-rent/characteristics/*'], 'crumb' => 'Catalog · Vehicles', 'title' => 'Features', 'description' => 'Icons and labels shown on vehicle cards and listings.', 'nav' => 'vehicles', 'settings' => false],
            ['match' => ['admin/impact-rent/locations', 'admin/impact-rent/locations/*'], 'crumb' => 'Catalog · Vehicles', 'title' => 'Locations', 'description' => 'Pickup and drop-off places. Opening hours decide when out-of-hours fees apply.', 'nav' => 'vehicles', 'settings' => false],
            ['match' => ['admin/impact-rent/rental-conditions', 'admin/impact-rent/rental-conditions/*'], 'crumb' => 'Catalog · Vehicles', 'title' => 'Rental conditions', 'description' => 'Short rules shown on each vehicle listing.', 'nav' => 'vehicles', 'settings' => false],
            ['match' => ['admin/impact-rent/special-prices'], 'crumb' => 'Catalog · Pricing', 'title' => 'Pricing', 'description' => 'Every price setting for vehicles in one place. Amounts are in ISK.', 'nav' => 'pricing', 'settings' => false],
            ['match' => ['admin/impact-rent/daily-fares'], 'crumb' => 'Catalog · Pricing', 'title' => 'Base prices', 'description' => 'Daily price by rental length, per vehicle and price type. (Same data as “Daily Fares / Insert Fares”.)', 'nav' => 'pricing', 'settings' => false],
            ['match' => ['admin/impact-rent/fares-overview'], 'crumb' => 'Catalog · Pricing', 'title' => 'Fares overview', 'description' => 'Final price per day after rules, and how many units are free.', 'nav' => 'pricing', 'settings' => false],
            ['match' => ['admin/impact-rent/booking-restrictions'], 'crumb' => 'Catalog · Pricing', 'title' => 'Booking restrictions', 'description' => 'Minimum or maximum rental length for specific dates, e.g. at least 3 days in July.', 'nav' => 'pricing', 'settings' => false],
            ['match' => ['admin/impact-rent/extra-hour-fares'], 'crumb' => 'Catalog · Pricing', 'title' => 'Extra hour fares', 'description' => 'What a late return costs per extra hour, per vehicle and price type.', 'nav' => 'pricing', 'settings' => false],
            ['match' => ['admin/impact-rent/hourly-fares'], 'crumb' => 'Catalog · Pricing', 'title' => 'Hourly fares', 'description' => 'Price for short rentals measured in minutes, e.g. 3 hours.', 'nav' => 'pricing', 'settings' => false],
            ['match' => ['admin/impact-rent/out-of-hours-fees'], 'crumb' => 'Catalog · Pricing', 'title' => 'Out-of-hours fees', 'description' => 'Extra charge when pickup or drop-off is outside opening hours.', 'nav' => 'pricing', 'settings' => false],
            ['match' => ['admin/impact-rent/location-fees'], 'crumb' => 'Catalog · Pricing', 'title' => 'Location fees', 'description' => 'Fee for picking up in one place and returning in another.', 'nav' => 'pricing', 'settings' => false],
            ['match' => ['admin/impact-rent/rental-options'], 'crumb' => 'Catalog · Pricing', 'title' => 'Add-ons', 'description' => 'Extras customers can add at checkout. (Same data as “Rental Options”.)', 'nav' => 'pricing', 'settings' => false],
            ['match' => ['admin/impact-rent/coupons'], 'crumb' => 'Catalog · Pricing', 'title' => 'Discount codes', 'description' => 'Codes customers type at checkout. (Same data as “Coupons”.)', 'nav' => 'pricing', 'settings' => false],
            ['match' => ['admin/impact-rent/tax-rates'], 'crumb' => 'Catalog · Pricing', 'title' => 'Tax rates', 'description' => 'VAT used by vehicles, fees and add-ons.', 'nav' => 'pricing', 'settings' => false],
            ['match' => ['admin/impact-rent/price-types'], 'crumb' => 'Catalog · Pricing', 'title' => 'Tax rates', 'description' => 'VAT used by vehicles, fees and add-ons.', 'nav' => 'pricing', 'settings' => false],
            ['match' => ['admin/impact-rent/special-prices/*', 'admin/impact-rent/daily-fares/*', 'admin/impact-rent/booking-restrictions/*', 'admin/impact-rent/extra-hour-fares/*', 'admin/impact-rent/hourly-fares/*', 'admin/impact-rent/out-of-hours-fees/*', 'admin/impact-rent/location-fees/*', 'admin/impact-rent/rental-options/*', 'admin/impact-rent/coupons/*', 'admin/impact-rent/tax-rates/*', 'admin/impact-rent/price-types/*'], 'crumb' => '', 'title' => null, 'description' => null, 'nav' => null, 'settings' => false],
            ['match' => ['admin/registered-hosts'], 'crumb' => 'People', 'title' => 'Hosts', 'description' => 'Manage profiles, partner API and commission.', 'nav' => null, 'settings' => false],
            ['match' => ['admin/registered-hosts/*'], 'crumb' => 'Hosts', 'title' => null, 'description' => null, 'nav' => null, 'settings' => false],
            ['match' => ['admin/registered-clients'], 'crumb' => 'People', 'title' => 'Clients', 'description' => 'Registered clients and their bookings.', 'nav' => null, 'settings' => false],
            ['match' => ['admin/registered-clients/*'], 'crumb' => 'Clients', 'title' => null, 'description' => null, 'nav' => null, 'settings' => false],
            ['match' => ['admin/listing-approvals', 'admin/listing-approvals/*'], 'crumb' => 'Moderation', 'title' => 'Listing approvals', 'description' => 'Newest submissions first. Approve, request changes or reject.', 'nav' => null, 'settings' => false],
            ['match' => ['admin/listing-reviews', 'admin/guest-houses/guest-reviews'], 'crumb' => 'Moderation', 'title' => 'Listing reviews', 'description' => 'Guest reviews shown on vehicle and guest house pages.', 'nav' => null, 'settings' => false],
            ['match' => ['admin/listing-reviews/*'], 'crumb' => 'Listing reviews', 'title' => null, 'description' => null, 'nav' => null, 'settings' => false],
            ['match' => ['admin/site-content', 'admin/site-content/*', 'admin/homepage-cms', 'admin/footer-settings'], 'crumb' => 'Website · Site content', 'title' => 'Site content', 'description' => 'Copy and media shown on the public website.', 'nav' => null, 'settings' => false],
            ['match' => ['admin/blog-posts'], 'crumb' => 'Website', 'title' => 'Good to Know', 'description' => 'Travel guides and tips shown on the blog. The page header text is edited under Site content › Good to Know.', 'nav' => null, 'settings' => false],
            ['match' => ['admin/blog-posts/*'], 'crumb' => 'Good to Know', 'title' => null, 'description' => null, 'nav' => null, 'settings' => false],
            ['match' => ['admin/newsletter-subscribers', 'admin/newsletter-campaigns'], 'crumb' => 'Marketing', 'title' => 'Newsletter', 'description' => 'People who signed up on the website, and the e-mails sent to them.', 'nav' => 'newsletter', 'settings' => false],
            ['match' => ['admin/newsletter-subscribers/*', 'admin/newsletter-campaigns/*'], 'crumb' => 'Newsletter', 'title' => null, 'description' => null, 'nav' => null, 'settings' => false],
            ['match' => ['admin/email-templates', 'admin/email-settings', 'admin/email-testing', 'admin/email-logs'], 'crumb' => 'Marketing', 'title' => 'Email', 'description' => 'Every e-mail the system sends, its sender settings and history.', 'nav' => 'email', 'settings' => false],
            ['match' => ['admin/email-templates/*', 'admin/email-logs/*'], 'crumb' => 'Email', 'title' => null, 'description' => null, 'nav' => null, 'settings' => false],
            ['match' => ['admin/reports', 'admin/reports/*', 'admin/tracking-statistics', 'admin/tracking-statistics/*', 'admin/impact-rent/reports', 'admin/impact-rent/reports/*', 'admin/impact-rent/tracking-statistics', 'admin/impact-rent/tracking-statistics/*', 'admin/impact-rent/tracking-campaigns', 'admin/impact-rent/tracking-campaigns/*'], 'crumb' => 'Marketing', 'title' => 'Analytics', 'description' => 'Rental reports, website visits and campaign links.', 'nav' => 'analytics', 'settings' => false],
            ['match' => ['admin/global-configuration', 'admin/global-configuration/*'], 'crumb' => 'Settings', 'title' => null, 'description' => null, 'nav' => 'settings', 'settings' => true],
            ['match' => ['admin/payment-methods', 'admin/payment-methods/*'], 'crumb' => 'Settings', 'title' => 'Payment methods', 'description' => null, 'nav' => 'settings', 'settings' => true],
            ['match' => ['admin/custom-fields', 'admin/custom-fields/*'], 'crumb' => 'Settings', 'title' => 'Custom fields', 'description' => null, 'nav' => 'settings', 'settings' => true],
            ['match' => ['admin/users', 'admin/users/*'], 'crumb' => 'Settings', 'title' => 'Users', 'description' => null, 'nav' => 'settings', 'settings' => true],
            ['match' => ['admin/logged-in-users', 'admin/logged-in-users/*'], 'crumb' => 'Settings', 'title' => 'Logged-in users', 'description' => null, 'nav' => 'settings', 'settings' => true],
            ['match' => ['admin/impact-rent/conditional-texts', 'admin/impact-rent/conditional-texts/*'], 'crumb' => 'Settings', 'title' => 'Conditional texts', 'description' => null, 'nav' => 'settings', 'settings' => true],
            ['match' => ['admin/impact-rent/backups', 'admin/impact-rent/backups/*'], 'crumb' => 'Settings', 'title' => 'Backups', 'description' => null, 'nav' => 'settings', 'settings' => true],
            ['match' => ['admin/profile'], 'crumb' => 'Settings', 'title' => 'Profile', 'description' => null, 'nav' => 'settings', 'settings' => true],
        ];
    }

    /**
     * @return list<array{label: string, url: string, active: bool, count: ?int, group: ?string}>
     */
    private static function vehicleLinks(): array
    {
        return array_values(array_filter([
            self::counted('All vehicles', CarResource::class, ['admin/impact-rent/cars', 'admin/impact-rent/cars/*'], self::safeCount(fn (): int => \App\Models\Car::query()->count()), 'icon-tb-car.svg'),
            self::counted('Fleet units', CarUnitResource::class, ['admin/impact-rent/car-units', 'admin/impact-rent/car-units/*'], self::safeCount(fn (): int => \App\Models\CarUnit::query()->count()), 'icon-tb-fleet.svg'),
            self::counted('Categories', CategoryResource::class, ['admin/impact-rent/main-categories', 'admin/impact-rent/main-categories/*', 'admin/impact-rent/sub-categories', 'admin/impact-rent/sub-categories/*'], self::safeCount(fn (): int => \App\Models\MainCategory::query()->count() + \App\Models\SubCategory::query()->count()), 'icon-tb-categories.svg'),
            self::counted('Features', CharacteristicResource::class, ['admin/impact-rent/characteristics', 'admin/impact-rent/characteristics/*'], self::safeCount(fn (): int => \App\Models\Characteristic::query()->count()), 'icon-tb-feature.svg'),
            self::counted('Locations', LocationResource::class, ['admin/impact-rent/locations', 'admin/impact-rent/locations/*'], self::safeCount(fn (): int => \App\Models\Location::query()->count()), 'icon-tb-pin.svg'),
            self::counted('Rental conditions', RentalConditionResource::class, ['admin/impact-rent/rental-conditions', 'admin/impact-rent/rental-conditions/*'], self::safeCount(fn (): int => \App\Models\RentalCondition::query()->count()), 'icon-tb-shield.svg'),
        ]));
    }

    /**
     * @return list<array{label: string, url: string, active: bool, count: ?int, group: ?string}>
     */
    private static function guestHouseLinks(): array
    {
        return array_values(array_filter([
            self::counted('Properties', GuestHouseResource::class, ['admin/guest-houses/guest-houses', 'admin/guest-houses/guest-houses/*'], self::safeCount(fn (): int => \App\Models\GuestHouse::query()->count()), 'icon-tb-house.svg'),
            self::counted('Amenities', GuestHouseAmenityResource::class, ['admin/guest-houses/guest-house-amenities', 'admin/guest-houses/guest-house-amenities/*'], self::safeCount(fn (): int => \App\Models\GuestHouseAmenity::query()->count()), 'icon-tb-check.svg'),
            self::counted('Bookings', GuestHouseBookingResource::class, ['admin/guest-houses/guest-house-bookings', 'admin/guest-houses/guest-house-bookings/*'], self::safeCount(fn (): int => \App\Models\GuestHouseBooking::query()->count()), 'icon-tb-calendar.svg'),
            self::counted('Calendar', GuestHouseCalendarPage::class, ['admin/guest-houses/calendar', 'admin/guest-houses/calendar/*'], null, 'icon-tb-calendar.svg'),
            self::counted('Reviews', ListingReviewResource::class, ['admin/listing-reviews', 'admin/listing-reviews/*', 'admin/guest-houses/guest-reviews'], self::safeCount(fn (): int => \App\Models\ListingReview::query()->count()), 'icon-tb-star.svg'),
        ]));
    }

    /**
     * @return list<array{label: string, url: string, active: bool, count: ?int, group: ?string}>
     */
    private static function pricingLinks(): array
    {
        $taxUrl = self::url(TaxRateResource::class);
            $types = $taxUrl
            ? self::plain('Types of price', $taxUrl.'#types-of-price', self::matches(['admin/impact-rent/price-types', 'admin/impact-rent/price-types/*']), 'Discounts & tax')
            : null;
        if ($types) {
            $types['count'] = self::safeCount(fn (): int => \App\Models\PriceType::query()->count());
        }

        return array_values(array_filter([
            self::counted('Base prices', DailyFareResource::class, ['admin/impact-rent/daily-fares', 'admin/impact-rent/daily-fares/*'], self::safeCount(fn (): int => \App\Models\DailyFare::query()->count()), null, 'Rates'),
            self::counted('Pricing rules', SpecialPriceResource::class, ['admin/impact-rent/special-prices', 'admin/impact-rent/special-prices/*'], self::safeCount(fn (): int => \App\Models\SpecialPrice::query()->count()), null, 'Rates'),
            self::counted('Fares overview', FaresOverview::class, ['admin/impact-rent/fares-overview', 'admin/impact-rent/fares-overview/*'], null, null, 'Rates'),
            self::counted('Booking restrictions', BookingRestrictionResource::class, ['admin/impact-rent/booking-restrictions', 'admin/impact-rent/booking-restrictions/*'], self::safeCount(fn (): int => \App\Models\BookingRestriction::query()->count()), null, 'Rates'),
            self::counted('Extra hour fares', ExtraHourFareResource::class, ['admin/impact-rent/extra-hour-fares', 'admin/impact-rent/extra-hour-fares/*'], self::safeCount(fn (): int => \App\Models\ExtraHourFare::query()->count()), null, 'Time based'),
            self::counted('Hourly fares', HourlyFareResource::class, ['admin/impact-rent/hourly-fares', 'admin/impact-rent/hourly-fares/*'], self::safeCount(fn (): int => \App\Models\HourlyFare::query()->count()), null, 'Time based'),
            self::counted('Out-of-hours fees', OutOfHoursFeeResource::class, ['admin/impact-rent/out-of-hours-fees', 'admin/impact-rent/out-of-hours-fees/*'], self::safeCount(fn (): int => \App\Models\OutOfHoursFee::query()->count()), null, 'Time based'),
            self::counted('Location fees', LocationFeeResource::class, ['admin/impact-rent/location-fees', 'admin/impact-rent/location-fees/*'], self::safeCount(fn (): int => \App\Models\LocationFee::query()->count()), null, 'Extras & fees'),
            self::counted('Add-ons', RentalOptionResource::class, ['admin/impact-rent/rental-options', 'admin/impact-rent/rental-options/*'], self::safeCount(fn (): int => \App\Models\RentalOption::query()->count()), null, 'Extras & fees'),
            self::counted('Discount codes', CouponResource::class, ['admin/impact-rent/coupons', 'admin/impact-rent/coupons/*'], self::safeCount(fn (): int => \App\Models\Coupon::query()->count()), null, 'Discounts & tax'),
            self::counted('Tax rates', TaxRateResource::class, ['admin/impact-rent/tax-rates', 'admin/impact-rent/tax-rates/*'], self::safeCount(fn (): int => \App\Models\TaxRate::query()->count()), null, 'Discounts & tax'),
            $types,
        ]));
    }

    /**
     * @return list<array{label: string, url: string, active: bool, count: ?int, group: ?string}>
     */
    private static function emailLinks(): array
    {
        return array_values(array_filter([
            self::item('Templates', EmailTemplateResource::class, ['admin/email-templates', 'admin/email-templates/*']),
            self::item('SMTP settings', EmailSettings::class, ['admin/email-settings']),
            self::item('Send test', EmailTesting::class, ['admin/email-testing']),
            self::item('Log', EmailLogResource::class, ['admin/email-logs', 'admin/email-logs/*']),
        ]));
    }

    /**
     * @return list<array{label: string, url: string, active: bool, count: ?int, group: ?string}>
     */
    private static function analyticsLinks(): array
    {
        return array_values(array_filter([
            self::item('Reports', Reports::class, ['admin/reports', 'admin/reports/*', 'admin/impact-rent/reports', 'admin/impact-rent/reports/*']),
            self::item('Statistics', TrackingStatistics::class, ['admin/tracking-statistics', 'admin/tracking-statistics/*', 'admin/impact-rent/tracking-statistics', 'admin/impact-rent/tracking-statistics/*']),
            self::item('Tracking campaigns', TrackingCampaignResource::class, ['admin/impact-rent/tracking-campaigns', 'admin/impact-rent/tracking-campaigns/*']),
        ]));
    }

    /**
     * @return list<array{label: string, url: string, active: bool, count: ?int, group: ?string}>
     */
    private static function newsletterLinks(): array
    {
        return array_values(array_filter([
            self::item('Subscribers', NewsletterSubscriberResource::class, ['admin/newsletter-subscribers', 'admin/newsletter-subscribers/*']),
            self::item('Campaigns', NewsletterCampaignResource::class, ['admin/newsletter-campaigns', 'admin/newsletter-campaigns/*']),
        ]));
    }

    /**
     * @return list<array{label: string, url: string, active: bool, count: ?int, group: ?string}>
     */
    private static function settingsLinks(): array
    {
        $config = self::url(GlobalConfiguration::class);

        return array_values(array_filter([
            $config ? self::plain('Shop & rentals', $config.'?tab=shop', self::pageRequest()->is('admin/global-configuration') && self::pageRequest()->query('tab', 'shop') === 'shop', 'Configuration') : null,
            $config ? self::plain('Prices & payments', $config.'?tab=prices', self::pageRequest()->is('admin/global-configuration') && self::pageRequest()->query('tab') === 'prices', 'Configuration') : null,
            $config ? self::plain('Views & layout', $config.'?tab=views', self::pageRequest()->is('admin/global-configuration') && self::pageRequest()->query('tab') === 'views', 'Configuration') : null,
            $config ? self::plain('Orders & company', $config.'?tab=orders', self::pageRequest()->is('admin/global-configuration') && self::pageRequest()->query('tab') === 'orders', 'Configuration') : null,
            self::item('Conditional texts', ConditionalTextResource::class, ['admin/impact-rent/conditional-texts', 'admin/impact-rent/conditional-texts/*'], 'Configuration'),
            self::item('Payment methods', PaymentMethodResource::class, ['admin/payment-methods', 'admin/payment-methods/*'], 'Checkout'),
            self::item('Custom fields', CustomFieldResource::class, ['admin/custom-fields', 'admin/custom-fields/*'], 'Checkout'),
            self::item('Users', ManageUsers::class, ['admin/users', 'admin/users/*'], 'Access'),
            self::item('Logged-in users', LoggedInUsers::class, ['admin/logged-in-users', 'admin/logged-in-users/*'], 'Access'),
            self::item('Backups', BackupResource::class, ['admin/impact-rent/backups', 'admin/impact-rent/backups/*'], 'System'),
            ($profile = filament()->getProfileUrl()) ? self::plain('Profile', $profile, self::matches(['admin/profile']), 'System') : null,
        ]));
    }

    /**
     * @param  list<string>  $active
     * @return array{label: string, url: string, active: bool, count: ?int, group: ?string}|null
     */
    /**
     * @param  list<string>  $active
     * @return array{label: string, url: string, active: bool, count: ?int, group: ?string, icon: ?string}|null
     */
    private static function counted(string $label, string $class, array $active, ?int $count, ?string $icon, ?string $group = null): ?array
    {
        $item = self::item($label, $class, $active, $group);
        if ($item === null) {
            return null;
        }

        $item['count'] = $count;
        $item['icon'] = $icon;

        return $item;
    }

    private static function safeCount(\Closure $query): ?int
    {
        try {
            return $query();
        } catch (\Throwable) {
            return null;
        }
    }

    private static function item(string $label, string $class, array $active, ?string $group = null): ?array
    {
        $url = self::url($class);
        if ($url === null) {
            return null;
        }

        return self::plain($label, $url, self::matches($active), $group);
    }

    /**
     * @return array{label: string, url: string, active: bool, count: ?int, group: ?string}
     */
    private static function plain(string $label, string $url, bool $active, ?string $group = null): array
    {
        return [
            'label' => $label,
            'url' => $url,
            'active' => $active,
            'count' => null,
            'group' => $group,
        ];
    }

    /**
     * @param  list<string>  $patterns
     */
    public static function on(string ...$patterns): bool
    {
        return self::pageRequest()->is(...$patterns);
    }

    public static function query(string $key, mixed $default = null): mixed
    {
        return self::pageRequest()->query($key, $default);
    }

    /**
     * @param  list<string>  $patterns
     */
    private static function matches(array $patterns): bool
    {
        $page = self::pageRequest();
        foreach ($patterns as $pattern) {
            if ($page->is($pattern)) {
                return true;
            }
        }

        return false;
    }

    private static function url(string $class): ?string
    {
        try {
            if (is_subclass_of($class, Resource::class)) {
                if (! $class::canViewAny()) {
                    return null;
                }
            } elseif (! $class::canAccess()) {
                return null;
            }

            return $class::getUrl();
        } catch (Throwable) {
            return null;
        }
    }
}
