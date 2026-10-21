# Caren registered hosts

The Vehicle API integration imports a rental catalogue into Registered Hosts and Cars.
Credentials are encrypted with Laravel's APP_KEY and excluded from user serialization.
No provider credentials belong in source control or migrations.

## Deploy

Deploy the backend changes plus `backend/.htaccess` and the storefront
`frontend/public/.htaccess` (to the live document root). Then, from the backend directory:

```sh
php artisan migrate --force
php artisan optimize:clear
php artisan caren:connect konvin@partners.myterrabook.local --rental=120 --name="Konvin Car Rental ehf."
```

The connect command asks for the API username, Vehicle API key and password using hidden
prompts. The contact email identifies the TerraBook host; it need not equal the API username.
The command verifies access, creates the host and imports the catalogue.

Alternatively use Registered Hosts → Add host / partner, choose Caren, enter
`https://booking.caren.is`, enable the connection and save the credentials. Open Manage host
to Test Caren connection or **Save & import Caren vehicles** (also next to Save changes).
The import button validates and saves the form first; no terminal is needed. Enter rental ID
120 for Konvin to skip the optional rental-discovery call. Blank credential inputs preserve saved values.

The connect command imports its already-verified catalogue using one login session. It does
not authenticate twice. An API failure on the edit page preserves the saved connection for retry.

Repeat an import with `php artisan caren:sync-vehicles HOST_ID` or the host's import action.
Imports update existing host/rental/class records without creating duplicates. Pickup/drop-off
permissions are imported separately. Names beginning with the word Test are skipped.
All returned non-test vehicles are automatically active and approved, including existing
drafts, rejected or inactive listings. Reimport to publish previously imported vehicles.
Publishing does not copy prices or a local availability calendar onto the listing.
The vehicle page reads both live from Caren. An empty catalogue does
not deactivate existing cars. Missing cars in a nonempty successful catalogue are deactivated.

Date-specific rental estimates are fetched live for search results and the vehicle detail price
checker. A separately gated Caren reservation lifecycle is available (see below); it is disabled
until the rental's payment configuration and an end-to-end test have been verified. Caren imports
cannot be booked through local pricing.
No placeholder fares or fleet-unit counts are invented.

## Live rental estimates

Deploy both backend and storefront changes. After pulling, run `php artisan optimize:clear`
from `backend`. Run `npm ci` and `npm run build` from `frontend`, then publish the contents
of `frontend/dist` to the storefront document root using the existing deployment process.
No migration or reimport is required for pricing.

Visitors must choose pickup/drop-off dates, times and locations. `/api/cars` includes
`search_pricing` for returned available Caren classes; the detail page uses the read-only
`/api/cars/{car}/caren-price` endpoint. `class/getlist` uses `ShowPrices: true` and
`SkipDateCheck: false`. Local locations are mapped to the same host/rental's Caren IDs.
Requests are batched per host/trip/currency, with a 60-second cache; failures are not cached.

The vehicle page uses the same date picker as other listings. A day is blocked when
Caren's `class/availabilitycalendar` reports `IsAvailable` is not true or `AvailableUnits`
is 0. Pickup and drop-off `ClosedDates` ranges (`From` / `To`) are also blocked, but only
for the locations the visitor selects. That calendar endpoint is limited to 1 request a
minute and 10 an hour, so TerraBook sends every class on the rental in one request and
caches the result for 10 minutes. Failures are not cached. Changing location uses the
cached calendar and only changes which closures apply.

Prices are rental estimates, not completed booking quotes. No fixed fares are saved.
Missing dates prompt a date selection; zero, missing or unavailable rates are never shown as
free rentals. Provider errors show a temporary-unavailability message. Returned currency is
preserved and only converted through the shop formatter when it matches the shop base.

Live checks on October 21, 2026 returned no priced classes for November 24–28, 2026 and
July 1–8, 2027 at Konvin location 666. A direct `price/getprices` check for class 2180 also
returned zero, so successful display of a nonzero live quote still requires a date/location
combination with rates available in Caren.

## Backend root 405

The live `/backend/` entry returned 405 while `/backend/admin/login` returned 200.
The storefront Apache rules now explicitly redirect `/backend` and `/backend/` to the
login URL before the real-directory guard. The backend Apache rules also send their
empty path to `public/index.php`, where Laravel redirects to Filament login.
Equivalent exact-match redirects are included in the nginx example.
These configuration changes must be deployed/reloaded to affect the live server.

API reference: https://apidocs.caren.io/

## Reservation lifecycle (disabled pending Caren setup)

The account currently has no configured Prepaid - Offline option supplied to TerraBook.
Keep `CAREN_BOOKINGS_ENABLED=false`. Read-only prices/imports continue to work independently.
Do not treat mocked tests as verification that live holds/payment options work for this account.

After pulling this version, run `php artisan migrate --force` and `php artisan optimize:clear`
in `backend`, rebuild/publish the storefront, and ensure Laravel's scheduler runs every minute.
The migration adds durable reservation state and a Caren standard rate type, not a fixed fare.

Before enabling bookings:

1. Obtain the rental's **Prepaid - Offline** payment option ID from Caren. Confirm the online
   Rapyd fee is to be credited against the rental balance and the rest collected on arrival.
2. Obtain a test account/environment and a vehicle/date/location combination with valid rates.
3. Set `CAREN_PAYMENT_OPTIONS` to a JSON object mapping each rental ID to its verified option ID
   (for example `{"120":VERIFIED_OPTION_ID}`, replacing the placeholder with a number).
4. Enable `CAREN_BOOKINGS_ENABLED=true` in the test environment and clear configuration cache.
5. Verify create → pay → confirm and create → expire → cancel in both systems, including the
   provider GUID, amount, currency, class, dates, locations, inventory hold and remaining balance.
6. Only after those checks, apply the verified configuration to live. No live test booking or
   payment was made during implementation.

The checkout rechecks `class/checkavailable` and `price/getprices` without the search cache,
verifies the configured `payment/getoptions` entry, and compares the total/currency against the
guest's quote. Local location IDs must belong to the same vehicle, host and rental. Optional
extras/coupon selections are not yet mapped; they are rejected rather than silently ignored.
Provider-supplied mandatory extras/insurance totals are included in the checked quote.

Each checkout has a UUID retry token and a durable local order before `reservation/add`.
The provider receives TerraBook's order reference and customer/driver information. Reservations
are created with `confirmReservation=false`, then verified through `reservation/getitem` before
any payment checkout begins. Duplicate submissions reuse the existing order. An uncertain
create response is not automatically retried, since the provider may have accepted it.

Rapyd uses server-stored order totals/currency/host, requires the private confirmation token,
and reuses an existing hosted checkout. Only a verified paid Rapyd record causes
`payment/addprepaid`, for the online amount actually collected (not the full rental total).
Then `reservation/confirm` uses `CheckPayment=true`. The local order becomes confirmed only
after Caren acknowledges it. A paid but unconfirmed reservation displays a pending-provider
message and does not redirect to a success confirmation. No card numbers are sent to Caren.

`caren:reconcile-orders` runs every minute. It retries confirmation/cancellation and cancels
expired unpaid reservations. A failed payment can retry the existing hosted checkout until
expiry. Verified paid orders are not expired automatically. Late payments received after a
cancellation need manual review/refund; they do not recreate a reservation.

Admin Orders shows **Partner sync** and any reconciliation message. Use **Cancel Caren
reservation** on the order page; TerraBook waits for provider acknowledgement before marking
it cancelled. Cancellation does not issue a Rapyd refund. Refunds, amendments and optional
protection upgrades require separate handling; direct edits/deletion are blocked for Caren
orders to preserve reservation and payment reconciliation records.

States requiring manual review include `create_unknown`, `checkout_unknown`, `payment_unknown`
and interrupted `creating`/`checkout_creating`/`payment_recording` operations. Find the reservation
in Caren using the TerraBook order reference and reconcile Rapyd/prepaid records before any retry.
Never clear those states and blindly repeat a write. `verify_pending` blocks payment and will
be cancelled if its unpaid hold expires. `confirm_pending` and `cancel_pending` are retryable.

## Repeated imports and Caren error 10000

The client deliberately does not call `user/logout` after catalogue reads, including
failed reads. A live diagnostic reproduced successful reads, successful logout,
then login returning the same invalidated session and reads failing with code 10000
(`Value cannot be null`). Sessions are left to Caren's documented 30-minute idle expiry.

Deploy this fix before retrying an affected account. An already-invalidated session
may still need to expire or be reset by Caren. For the Konvin account, entering its
API email in uppercase was verified to create a separate working session with the
same password/key; it returned 26 classes, 4 pickup locations and 2 drop-off locations.
This is a tested account-specific recovery workaround, not automatic username normalization.
