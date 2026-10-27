# Phase 0 — codebase inventory

The Figma file maps onto the **Filament admin** at `/admin`, not the public site and not the host panel. A small React admin also exists and links out to Filament. No UI was changed in this phase.

## Stack

| Layer | What it is |
| --- | --- |
| Backend | Laravel 12, PHP 8.2, Filament 5.4 |
| Admin URL | Filament panel id `admin`, path `/admin` (`AdminPanelProvider`) |
| Auth | Filament login, session middleware, `RestrictDesignerFilamentAccess` |
| Frontend (public, host, thin admin) | React 18, Vite 5, React Router 7, Tailwind CSS 3.4 |
| Data on the React side | Axios + TanStack Query. Filament pages load their own Livewire data |
| Theme today | Filament primary `#334e68`, dark mode off, fonts Open Sans (400/600/700) and Quicksand (700) |
| Styling | Filament Blade/CSS overrides in `backend/resources/views/filament/`. React uses Tailwind plus plain CSS files. No CSS modules, styled-components, or a third-party component kit |
| Icons today | Filament Heroicons, `mallardduck/blade-lucide-icons` on the backend, `lucide-react` on the React app |

## Filament chrome

`backend/app/Providers/Filament/AdminPanelProvider.php` registers one panel. Custom shell pieces:

- `filament/panel/admin-theme.blade.php` — global admin colors
- `filament/panel/impact-rent-editor-quick-access.blade.php` — Impact Rent rail, canvas, inputs
- `filament/panel/guest-house-editor-quick-access.blade.php`
- `filament/panel/moderation-settings-quick-access.blade.php`
- `filament/panel/sidebar-hover-peek.blade.php`, `sidebar-home-preview.blade.php`, `user-menu-styles.blade.php`, `user-menu-account-info.blade.php`
- `filament/panel/head-sidebar-migration.blade.php`, `body-end-scripts.blade.php`, `brand-logo.blade.php`

Navigation groups declared on the panel: Moderation, Site, Content (collapsed), Marketing, Email. Other groups are set per resource (Operations, Catalog, Pricing, Platform, Advanced, and the two clusters).

Clusters:

- `ImpactRentCluster` — slug `impact-rent`
- `GuestHouseCluster` — slug `guest-houses`

Shared Filament UI (tables, forms, modals, badges, tabs, pagination, notifications) comes from Filament itself. Custom views that replace pieces of it are listed with the pages below.

## Filament pages and resources

Slug is the explicit `$slug` when the class sets one. Otherwise Filament derives it and, for clustered resources, prefixes the cluster slug. Those derived paths are not invented here.

| Existing admin surface | Class | Explicit slug |
| --- | --- | --- |
| Home | `Pages/Dashboard.php` | panel index `/admin` |
| Orders calendar | `Pages/OrdersCalendar.php` | `orders-calendar` |
| Guest house calendar | `GuestHouse/Pages/GuestHouseCalendarPage.php` | `calendar` (guest-houses cluster) |
| Vehicle rental orders | `Resources/Orders/OrderResource.php` | label “Vehicle Rental” |
| Guest house bookings | `GuestHouse/Resources/GuestHouseBookingResource.php` | guest-houses cluster |
| Change requests | `Resources/BookingChangeRequests/BookingChangeRequestResource.php` | |
| Guest house properties | `GuestHouse/Resources/GuestHouseResource.php` | guest-houses cluster |
| Guest house amenities | `GuestHouse/Resources/GuestHouseAmenityResource.php` | |
| Guest reviews | `GuestHouse/Pages/GuestHouseReviewsPage.php` | `guest-reviews` |
| All vehicles | `Resources/Cars/CarResource.php` | impact-rent cluster |
| Edit vehicle | Car resource edit page | |
| Fleet units | `Resources/CarUnits/CarUnitResource.php` | |
| Main categories | `Resources/Categories/CategoryResource.php` | `main-categories` |
| Sub categories | `Resources/SubCategories/SubCategoryResource.php` | `sub-categories` |
| Characteristics | `Resources/Characteristics/CharacteristicResource.php` | |
| Locations | `Resources/Locations/LocationResource.php` | |
| Rental conditions | `Resources/RentalConditions/RentalConditionResource.php` | |
| Special prices | `Resources/SpecialPrices/SpecialPriceResource.php` | |
| Fares overview | `Pages/FaresOverview.php` | `fares-overview` |
| Booking restrictions | `Resources/BookingRestrictions/BookingRestrictionResource.php` | |
| Extra hour fares | `Resources/ExtraHourFares/ExtraHourFareResource.php` | |
| Hourly fares | `Resources/HourlyFares/HourlyFareResource.php` | |
| Out-of-hours fees | `Resources/OutOfHoursFees/OutOfHoursFeeResource.php` | |
| Location fees | `Resources/LocationFees/LocationFeeResource.php` | |
| Rental options | `Resources/RentalOptions/RentalOptionResource.php` | |
| Coupons | `Resources/Coupons/CouponResource.php` | |
| Tax rates | `Resources/TaxRates/TaxRateResource.php` | |
| Price types | `Resources/PriceTypes/PriceTypeResource.php` | |
| Daily fares | `Resources/DailyFares/DailyFareResource.php` | |
| Registered hosts | `Resources/RegisteredHosts/RegisteredHostResource.php` | `registered-hosts` |
| Registered clients | `Resources/RegisteredClients/RegisteredClientResource.php` | `registered-clients` |
| Host listing approvals | `Pages/ListingReviewPage.php` | `listing-approvals` |
| Listing reviews | `Resources/ListingReviews/ListingReviewResource.php` | `listing-reviews` |
| Site content hub | `Pages/SiteContentHub.php` | `site-content` |
| Site pages resource | `Resources/SitePages/SitePageResource.php` | |
| Homepage CMS | `Pages/HomepageCms.php` | `homepage-cms` |
| Footer settings | `Pages/FooterSettings.php` | `footer-settings` |
| Good to Know posts | `Resources/BlogPosts/BlogPostResource.php` | |
| Newsletter subscribers | `Resources/NewsletterSubscribers/NewsletterSubscriberResource.php` | |
| Email campaigns | `Resources/NewsletterCampaigns/NewsletterCampaignResource.php` | |
| Email templates | `Resources/EmailTemplates/EmailTemplateResource.php` | |
| SMTP settings | `Pages/EmailSettings.php` | `email-settings` |
| Send test | `Pages/EmailTesting.php` | `email-testing` |
| Email log | `Resources/EmailLogs/EmailLogResource.php` | |
| Global configuration | `Pages/GlobalConfiguration.php` | `global-configuration` |
| Payment methods | `Resources/PaymentMethods/PaymentMethodResource.php` | `payment-methods` |
| Custom fields | `Resources/CustomFields/CustomFieldResource.php` | `custom-fields` |
| Users | `Pages/ManageUsers.php` | `users` |
| Logged-in users | `Pages/LoggedInUsers.php` | `logged-in-users` |
| Conditional texts | `Resources/ConditionalTexts/ConditionalTextResource.php` | |
| Backups | `Resources/Backups/BackupResource.php` | |
| Profile | `Pages/Auth/EditProfile.php` | Filament profile |
| Reports | `Pages/Reports.php` | `reports` |
| Tracking statistics | `Pages/TrackingStatistics.php` | `tracking-statistics` |
| Tracking campaigns | `Resources/TrackingCampaigns/TrackingCampaignResource.php` | |
| Moderation settings | `Pages/ModerationSettings.php` | `moderation-settings` |
| Scheduled tasks | `Pages/ScheduledTasksOverview.php` | |
| Settings records | `Resources/Settings/SettingResource.php` | `settings-records` |

## React routes that say “admin”

Defined in `frontend/src/App.jsx`. These sit inside the public `SiteLayout` and are a separate shell from Filament.

| Route | Page | Layout |
| --- | --- | --- |
| `/admin` | `pages/AdminDashboardPage.jsx` | `components/admin/AdminLayout.jsx` |
| `/admin/calendar` | `pages/admin/AdminCalendarPage.jsx` | same |
| `/admin/embed/calendar` | `pages/admin/AdminEmbedCalendarPage.jsx` | no site layout |

`AdminLayout` nav is only Dashboard and Calendar, plus a link to the Filament URL. Calendar pieces: `AdminFullCalendar`, `AdminCalendarFilters`, `AdminCalendarToolbar`, `AdminCalendarSummaryBar`, `AdminCalendarAlertsPanel`, `AdminReservationDetailPanel`. Styles: `frontend/src/styles/admin-panel.css`. Icons: lucide-react.

There are also unused-looking React files under `frontend/src/pages/admin/Orders/` and `PaymentMethods/`. They are not registered in `App.jsx`.

## Shared UI already in the repo

Filament supplies buttons, inputs, selects, toggles, checkboxes, tables, tabs, badges, pagination, modals, and notifications. Project-specific pieces on top:

- Impact Rent form schemas (`CarForm`, `OrderForm`) and `CarsTable`
- Order calendar blade `car-reservation-calendar.blade.php` and `fleet-calendar.blade.php`
- Record summary `impact-rent/record-summary.blade.php`
- Dashboard widgets: welcome header, operations chart, insights, recent orders
- Global configuration, site content hub, reports, fares overview, email settings, logged-in users blades

React shared pieces used outside Filament (host and customer, not this redesign’s target unless a later screen says so): `HostLayout`, `AccountSettingsForms`, client settings, `host-panel.css`, `client-panel.css`.

## What this redesign should touch

The Figma sidebar labels (Home, Bookings, Catalog, People, Moderation, Website, Marketing, Settings) match the Filament panel. Implementation later should restyle that Filament UI. The React `/admin` shell is a second, smaller admin and is not the screen set in the file.
