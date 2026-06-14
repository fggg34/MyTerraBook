# Dynamic gaps, manual / out-of-scope items

These items were identified in the dynamic audit. Code changes are complete where possible; the following still require action outside this repository or are intentionally limited.

## Requires production / admin action

| Item | What to do |
|------|------------|
| **Empty pickup/dropoff on live site** | On production: `php artisan locations:link-to-cars --car=ID` or assign locations in admin (Cars → Pickup/Drop Off Locations). |
| **Google Reviews on homepage** | Admin → Site Content → enable Google reviews, set Place ID, configure Google Maps API key. |
| **Exchange rates** | Seeded defaults exist (`shop.exchange_rates`). Update in admin/settings or DB when rates change. No live FX API is wired. |
| **Car seats/sleeps/bags** | New fields exist in admin and API; fill per vehicle for accurate search cards. |

## Cannot be fully fixed in code (by design)

| Item | Reason |
|------|--------|
| **Real payment processing** | Checkout UI lists admin payment methods; card/PayPal/instalments are not connected to a payment gateway. Orders are created without capture. |
| **Multi-currency charging** | Display conversion only. Orders and quotes remain in shop base currency. |
| **Full site i18n** | Language selector exists; CMS and copy are English-only. |
| **Hourly / extra-hour fare parity** | See `backend/docs/impact-rent-vik-parity-gap-spec.md`. |
| **Legacy orphan pages** | `HomeSearchPage.jsx`, `components/home/*` are not routed; safe to ignore or delete later. |

## Verified API endpoints (new/expanded)

- `GET /api/public-config`, currency, prepay %, deposit, exchange rates, shop flags
- `GET /api/payment-methods`, enabled checkout methods
- `GET /api/custom-fields`, active checkout fields
- `GET /api/booking-restrictions`, min/max days + CTA/CTD restriction rows
- `GET /api/cars/{id}/availability-calendar`, used by checkout date picker
- `GET /api/homepage`, Google reviews + featured blog (when configured)
