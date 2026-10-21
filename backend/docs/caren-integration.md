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
Publishing does not add booking availability or prices. An empty catalogue does
not deactivate existing cars. Missing cars in a nonempty successful catalogue are deactivated.

This is a catalogue integration. Date-specific pricing, availability and Caren reservation/payment
lifecycle integration are not yet implemented; Caren imports cannot be booked through local pricing.
No placeholder fares or fleet-unit counts are invented.

## Backend root 405

The live `/backend/` entry returned 405 while `/backend/admin/login` returned 200.
The storefront Apache rules now explicitly redirect `/backend` and `/backend/` to the
login URL before the real-directory guard. The backend Apache rules also send their
empty path to `public/index.php`, where Laravel redirects to Filament login.
Equivalent exact-match redirects are included in the nginx example.
These configuration changes must be deployed/reloaded to affect the live server.

API reference: https://apidocs.caren.io/

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
