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
php artisan caren:connect konvin@partners.myterrabook.local --rental=120
```

The connect command asks for the API username, Vehicle API key and password using hidden
prompts. The contact email identifies the TerraBook host; it need not equal the API username.
The command verifies access, creates the host and imports the catalogue.

Alternatively use Registered Hosts → Add host / partner, choose Caren, enter
`https://booking.caren.is`, enable the connection and save the credentials. Open Manage host
to Test Caren connection or Import Caren vehicles. Blank credential inputs preserve saved values.

Repeat an import with `php artisan caren:sync-vehicles HOST_ID` or the host's import action.
Imports update existing host/rental/class records without creating duplicates. Pickup/drop-off
permissions are imported separately. Names beginning with the word Test are skipped.
New cars are inactive drafts; existing review status is preserved. An empty catalogue does
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
