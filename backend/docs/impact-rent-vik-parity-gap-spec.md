# Impact Rent strict-parity gap spec

## Scope

This document maps Vik Rent Car reference capabilities to the current `Impact Rent` Laravel implementation and highlights strict-parity gaps before execution work.

## Capability matrix

| Domain | Capability | Status | Primary touchpoints | Gap summary |
| --- | --- | --- | --- | --- |
| Rental setup | Tax rates CRUD | Implemented | `app/Models/TaxRate.php`, `database/migrations/2026_04_13_110000_create_tax_rates_table.php` | Quote engine currently uses global tax bips fallback rather than fully line-taxed composition. |
| Rental setup | Price types (name, attribute, tax) | Implemented | `app/Models/PriceType.php`, `database/migrations/2026_04_13_110070_create_price_types_table.php` | Price-type tax not fully propagated to all quote line totals. |
| Rental setup | Pickup/dropoff locations + schedules + closing days | Implemented | `app/Models/Location.php`, `app/Models/LocationSchedule.php`, `app/Models/LocationClosingDay.php` | Needs stronger availability wiring for closures in quote/search availability. |
| Rental setup | Booking restrictions (min/max, CTA/CTD, forced weekdays) | Implemented (UX weak) | `app/Models/BookingRestriction.php`, `app/Services/RentalQuoteService.php`, `app/Filament/Resources/BookingRestrictions/Schemas/BookingRestrictionForm.php` | Admin form uses free text arrays for weekdays; high error risk vs strict controlled input. |
| Fleet | Categories | Implemented | `app/Models/Category.php`, `database/migrations/2026_04_13_110010_create_categories_table.php` | No critical parity gap. |
| Fleet | Rental options | Implemented | `app/Models/RentalOption.php`, `database/migrations/2026_04_13_110080_create_rental_options_table.php` | Option taxes and quantity logic need stricter line-level tax parity. |
| Fleet | Characteristics | Implemented | `app/Models/Characteristic.php`, `database/migrations/2026_04_13_110020_create_characteristics_table.php` | No critical parity gap. |
| Fleet | Cars list, units, iCal URL field | Implemented | `app/Models/Car.php`, `database/migrations/2026_04_13_110090_create_cars_table.php`, `app/Filament/Resources/Cars/Schemas/CarForm.php` | External iCal URL exists but import-to-availability not complete. |
| Pricing | Daily fares table | Implemented | `app/Models/DailyFare.php`, `database/migrations/2026_04_13_120040_create_daily_fares_table.php` | No critical parity gap. |
| Pricing | Hourly fares (<24h) | Partial | `database/migrations/2026_04_13_120050_create_hourly_fares_table.php`, `app/Models/HourlyFare.php` | Quote flow currently rounds to rental days and ignores hourly fare path. |
| Pricing | Extra hours (>24h) + gratuity period | Partial | `database/migrations/2026_04_13_120060_create_extra_hour_fares_table.php`, `app/Models/ExtraHourFare.php` | Quote flow does not execute explicit extra-hour charge algorithm yet. |
| Pricing | Special prices (charge/discount, weekday/date, round integer, year) | Implemented (algorithm simplifiable) | `app/Models/SpecialPrice.php`, `app/Services/RentalQuoteService.php` | Core works, but day override behavior can be expanded for strict parity. |
| Pricing | Pickup/dropoff fees (inverted, one-way) | Partial | `app/Models/LocationFee.php`, `database/migrations/2026_04_13_120080_create_location_fees_table.php`, `app/Services/RentalQuoteService.php` | `apply_inverted` and day overrides are not fully enforced in quote logic. |
| Pricing | Out-of-hours fees with weekday filters | Implemented | `app/Models/OutOfHoursFee.php`, `app/Services/RentalQuoteService.php` | Requires tax-line integration for strict parity. |
| Orders/availability | Pending/stand-by/confirmed/cancelled + rental statuses | Implemented | `app/Enums/OrderStatus.php`, `app/Enums/RentalStatus.php`, `app/Models/Order.php` | Locking and availability projection need a unified blocker model. |
| Orders/availability | Availability overview API | Partial | `app/Http/Controllers/Api/CatalogController.php` | Returns `blocked: []`; does not include stand-by lock, close-car blocks, imported iCal blocks. |
| Orders/availability | Quick reservation / close-car blocking | Missing | (no dedicated model yet) | No explicit close-car reservation/block table and flow yet. |
| Management | Coupons | Implemented | `app/Models/Coupon.php`, `app/Services/RentalQuoteService.php` | No critical parity gap. |
| Management | Dashboard widgets | Partial | Filament default resources | No custom containerized widget composer equivalent yet. |
| Advanced | Statistics tracking | Partial | `app/Models/TrackingEvent.php`, `app/Models/TrackingCampaign.php`, `app/Http/Controllers/Api/Admin/AdminStatsController.php` | Basic stats only; needs richer conversion/reporting parity. |
| Advanced | Reports framework (revenue, occupancy, top countries) | Partial | `app/Http/Controllers/Api/Admin/AdminStatsController.php` | Needs dedicated report endpoints/queries and export-ready payloads. |
| Global | Shop/rental settings, backups, conditional texts, payments, custom fields | Implemented (core) | `app/Models/Setting.php`, `app/Models/Backup.php`, `app/Models/ConditionalText.php`, `app/Models/PaymentMethod.php`, `app/Models/CustomField.php` | Some settings are not yet consumed by pricing/availability engine. |
| Technical | PDF contract/invoice | Implemented | `app/Http/Controllers/Api/Admin/OrderContractPdfController.php`, `resources/views/pdf/order-contract.blade.php` | Needs check-in PDF parity and richer template tag behavior. |
| Technical | Distinctive features + unit assignment | Implemented | `app/Models/CarDistinctiveFeatureDefinition.php`, `app/Models/CarUnitDistinctiveValue.php`, `app/Models/Order.php` | No critical parity gap. |
| Technical | Car damages/check-in workflow | Partial | `app/Models/CarDamageMarker.php`, `database/migrations/2026_04_13_120030_create_car_damage_markers_table.php` | Missing full inspection/check-in operation flow and PDF integration. |
| Technical | iCal sync import/export | Partial | `app/Services/OrderIcsBuilder.php`, `app/Console/Commands/ImportExternalIcalendarCommand.php` | Import command parses local file only and does not write availability blocks yet. |

## Execution order

1. Pricing and availability integrity parity.
2. Admin UX hardening for restrictions/pricing operations.
3. Integration parity: iCal import blockers, check-in/damage documents, richer reports.
