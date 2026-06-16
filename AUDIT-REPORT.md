# Backend Audit — MyTerraBook (Iceland car / campervan / guesthouse marketplace) (2026-06-16)

> Produced by the `marketplace-backend-audit` skill in **AUDIT mode** (read-only). No files were
> changed. Hand this report back to drive **FIX mode** ("do all FIX", or name specific IDs).

---

## 1. Business Model Card

- **Vertical & positioning:** Iceland rental/booking marketplace for **cars, campervans, and guesthouses**, with a hybrid **operator-fleet + peer-to-peer (host)** model. Marketing copy promises host onboarding, "payouts within 24h", and host-approved bookings.
- **Principals:** Guests (book/pay/review), Hosts (`UserRole::Host`, own listings), Operator/Admin (`UserRole::Admin`, manages everything via Filament). Distinction is a `role` column on `users` + a `host` middleware (role gate) + per-model policies for ownership.
- **Entities that exist:** `Car` (campervan = car filtered by main-category slug), `GuestHouse`, `Order` (car booking) + `OrderItem`/`OrderPayment`, `GuestHouseBooking` + `GuestHouseBookingPayment`, `RentalOption`/`Characteristic`/`AvailabilityBlock`/`SpecialPrice`/`DailyFare`/etc., `ListingReview` (polymorphic), catalog taxonomy, CMS/site content, email templates, coupons, tax rates, locations.
- **Entities the domain model expects but are missing/inert:** **Payment gateway integration**, **Payout/Commission ledger**, **guest↔host messaging**, **two-way / verified reviews**, **car cancellation/refund**, an explicit **managed-fleet vs P2P** flag, **disputes/claims**, document/KYC verification.
- **Monetization:** Implicit — no commission is computed and no payout is routed; host dashboard shows **gross** booking totals. Pricing supports coupons, taxes, seasonal/special prices, fees.
- **Integrations:** External **iCal import** (scheduled daily), DomPDF (contracts), templated email (queued). **No payment processor**, no SMS, no maps/KYC.
- **Stack:** Laravel 11 (bootstrap/app.php style, Sanctum auth, Filament admin) + React (Vite) SPA frontend. 131 routes, 40 controllers, 55 models, 117 migrations, 4 policies, **0 Job/Event classes**, 1 scheduled command.

**Assumptions:** I treated the React app in `frontend/` as the only client surface (no separate native/host app). I assumed the intended product is a true marketplace (hosts get paid) based on marketing copy; if it is actually operator-fleet-only today, several "Critical" gaps soften to "roadmap".

---

## 2. Architecture & lifecycle map

### Module / account-triangle sketch

```
                         ┌─────────────────────────────────────────┐
                         │  Laravel API (routes/api.php, 131 routes) │
                         └─────────────────────────────────────────┘
   GUEST (Sanctum)           HOST (sanctum+host)          ADMIN (sanctum+admin / Filament)
   ───────────────           ──────────────────           ──────────────────────────────
   /cars, /guest-houses      /host/cars/* (full CRUD)      Filament: approve listings,
   /orders/quote,/orders     /host/guest-houses/*          edit orders/bookings, CMS,
   /guest-houses/bookings    /host/bookings/* (READ +      catalog, email templates
   /me/orders, /me/gh-...    status PATCH = 403 for host)  /api/admin/* (stats, reports,
   /cars|gh/{}/reviews       /host/dashboard (gross rev)   csv, pdf, categories)
        │                          │                              │
        ▼                          ▼                              ▼
   Order / GuestHouseBooking   Car / GuestHouse (+ approval)   ListingApprovalStatus,
   RentalQuoteService          listing_status / status         GuestHouseStatus
   GuestHouseQuoteService      OrderAvailabilityService        OrderObserver / GHBookingObserver → email
```

### Listing lifecycle trace (host posts a car)

| Step | Handler | Writes | Read by | Notes |
|---|---|---|---|---|
| Draft create | `HostCarController::store` :48 | `user_id`, `is_active=false`, `listing_status=Draft` | host editor | host can't set status/active (good) |
| Edit | `HostCarController::update` :75 | car fields; **strips** `is_active`,`listing_status` | host editor | **edits to Approved car do NOT re-trigger review** (GAP-007) |
| Submit | `HostCarController::submit` :97 | `listing_status=PendingReview`, readiness-gated | admin queue | sends `listing_submitted` email |
| Approve | Filament `ListingReviewPage::approveCar` :197 | `Approved`, `is_active=true`, `reviewed_*` | catalog | only Filament; no REST admin approval |
| Published | `CatalogController::cars/car` + `Car::scopePubliclyVisible` :67 | — | guests | **fleet cars (`user_id=null`) bypass approval** (GAP-008) |
| Book | `PublicOrderController::quote/store` :41,92 | Order | guest | **no visibility check → draft/inactive bookable by id** (SEC-001) |

### Booking lifecycle trace (guest books a car)

| Step | Handler | Effect / seam |
|---|---|---|
| Quote | `PublicOrderController::quote` :39 → `RentalQuoteService` | server-computed, integer cents (good) |
| Availability | `OrderAvailabilityService::hasCapacity` :78 | **checked OUTSIDE the txn, no lock** (BUG-002) |
| Create | `PublicOrderController::store` :92 → `DB::transaction` :124 | re-quotes server-side; **no availability re-check inside txn** |
| Payment | — | **none**; order set `Confirmed` :135 with **no `OrderPayment`** (BUG-001/GAP-001) |
| Confirmation | `OrderObserver` :28 | emails guest + admin/host |
| Cancel/refund | — | **no guest cancel, no refund** for cars (GAP-003/004) |
| Review | `ListingReviewController::storeCar` | **unauthenticated, auto-approved, no booking link** (SEC-002) |
| Payout | — | **none** — host dashboard sums gross `total_cents` (GAP-002) |

### Account triangle matrix (selected)

| Capability | Backend endpoint | Host UI | Client UI | Admin | Notes |
|---|---|---|---|---|---|
| Create/edit listing | `/host/cars/*`, `/host/guest-houses/*` | ✓ | — | Filament | ownership enforced by policy ✓ |
| Approve listing | Filament only | — | — | ✓ | no REST API |
| Book | `/orders`, `/guest-houses/bookings` | — | ✓ | — | auto-confirm, no payment |
| Accept/decline booking | `PATCH /host/bookings/*/status` | **defined, returns 403** | — | Filament | policy admin-only (BUG-004); FE export dead (DEAD-005) |
| View payouts | — | gross only | — | — | no payout model (GAP-002) |
| Messaging | — | — | — | — | absent (GAP-005) |
| Reviews | `/cars|gh/{}/reviews` | — | ✓ (post) | Filament moderate | one-way, unverified (SEC-002/GAP-006) |
| Cancel | `/me/guest-house-bookings/{ref}/cancel` | — | ✓ GH only | Filament | no car cancel (GAP-003) |

---

## 3. What works (load-bearing — leave alone unless a finding says otherwise)

- **Server-side quote recomputation** at checkout for both cars and guesthouses — client totals are never trusted (`PublicOrderController::store`, `GuestHouseBookingController` re-call the quote services).
- **Money is integer cents** end-to-end in schema, casts, and quote math (`Order`, `GuestHouseBooking`, migrations, `Support/Money`); floats appear only in display/admin input.
- **Host listing CRUD authorization** is correct: every mutating `/host/*` car & guesthouse endpoint calls `$this->authorize(...)` with `CarPolicy`/`GuestHousePolicy` (owner-or-admin) **plus** nested ownership checks (`car_id` / `vehicle_ids` / scoped `findOrFail`) — no host-to-host IDOR found.
- **No mass-assignment** of `$request->all()`; host payloads are validated and privileged fields stripped.
- **Public catalog hides drafts**: `/cars`, `/cars/{car}`, and all guesthouse public routes filter to publicly-visible/active listings (the bypass is only on the *order* and *calendar* endpoints).
- **Car order price snapshot** (`pricing_snapshot` JSON + frozen cents + line items) protects historical orders from later fare edits.
- **Guesthouse booking creation re-checks booking overlap inside the DB transaction** (better than the car path, though still lock-free).
- **iCal external import** is implemented and scheduled daily (`calendar:import-external`).

---

## 4. Findings

### [BUG-001] Bookings are confirmed without any payment capture
- **Category:** Bug
- **Severity:** Critical
- **Confidence:** Confirmed
- **Where:** `app/Http/Controllers/Api/PublicOrderController.php:135-152` (cars); `app/Http/Controllers/Api/GuestHouseBookingController.php:59-63` (guesthouses); no `OrderPayment`/`GuestHouseBookingPayment` ever created in app code
- **What's happening:** `POST /orders` and `POST /guest-houses/bookings` create `Confirmed` records with full totals but never charge anything. There is no gateway call, no `OrderPayment` row, no authorize/capture.
- **Why it matters:** A "confirmed" booking holds inventory and emails the customer/host, but no money is collected. Direct revenue leakage and inventory loss.
- **Recommendation (add):** Introduce a payment step (see GAP-001). Until then, do not mark orders `Confirmed` on creation — use `Pending`/`StandBy` with the existing `payment_lock_expires_at` and only confirm on payment success.
- **Fix risk:** High — touches the core booking path and frontend checkout. Test quote→pay→confirm and lock expiry.
- **Depends on:** GAP-001
- **Disposition (default):** DISCUSS

### [GAP-001] No payment gateway integration
- **Category:** Missing feature
- **Severity:** Critical
- **Confidence:** Confirmed
- **Where:** `backend/composer.json:8-18` (no Stripe/Braintree/PayPal/Mollie); `app/Enums/PaymentMethod.php` enum unused; `frontend/src/hooks/useRequestToBook.js:500-514` (card UI not sent to API)
- **What's happening:** Payment methods are catalog metadata + admin manual recording only. The checkout UI validates a card but never transmits payment; the backend records nothing.
- **Why it matters:** A marketplace cannot collect funds, hold deposits, do SCA/3DS, or reconcile. This is the keystone gap.
- **Recommendation (add):** Integrate a processor (Stripe recommended; Stripe Connect if hosts are to be paid out — see GAP-002). Add authorize/capture, deposit holds, webhooks (idempotent), and persist `OrderPayment`/`GuestHouseBookingPayment`.
- **Fix risk:** High — new integration, secrets, webhooks; test sandbox flows + failure paths.
- **Depends on:** —
- **Disposition (default):** DISCUSS

### [GAP-002] No commission / payout system (marketplace settlement missing)
- **Category:** Missing feature
- **Severity:** Critical
- **Confidence:** Confirmed
- **Where:** `app/Http/Controllers/Api/Host/HostDashboardController.php:53-55` sums gross `total_cents`; no `Payout`/`commission` model anywhere (only marketing copy in `database/seeders/data/site_content_defaults.json`)
- **What's happening:** Hosts own listings (`user_id`) and receive booking emails, but no platform commission is computed and no payout is routed. Host "revenue" = gross order total.
- **Why it matters:** The P2P side of the hybrid model is non-functional financially; marketing promises payouts that the backend cannot deliver; operator revenue is unmeasurable.
- **Recommendation (add):** Add a commission config (per managed-fleet vs P2P — see GAP-008), compute `host_payout_cents` on the booking snapshot, and a `Payout` ledger (Stripe Connect transfers or manual settlement records).
- **Fix risk:** High — money correctness; reconcile against payments.
- **Depends on:** GAP-001, GAP-008
- **Disposition (default):** DISCUSS

### [BUG-002] Car double-booking race (availability check not atomic)
- **Category:** Bug
- **Severity:** High
- **Confidence:** Confirmed
- **Where:** `app/Http/Controllers/Api/PublicOrderController.php:98-100` (check) vs `:124-152` (create); `app/Services/OrderAvailabilityService.php:78-83` — no `lockForUpdate` anywhere in the backend; no DB exclusion constraint
- **What's happening:** `hasCapacity()` runs before the transaction and is not re-checked or row-locked inside it. Two concurrent requests can both pass when `units_available = 1`.
- **Why it matters:** Overbooking; two guests confirmed for the same vehicle/window.
- **Recommendation (refine):** Move the capacity check inside `DB::transaction` with `lockForUpdate` on the relevant rows (or add a DB-level overlap/exclusion guard). Also count `Pending`/`StandBy` orders toward capacity.
- **Fix risk:** Medium — concurrency logic; add a concurrency test.
- **Depends on:** —
- **Disposition (default):** FIX

### [BUG-003] Guesthouse double-booking race + block bypass in transaction
- **Category:** Bug
- **Severity:** High
- **Confidence:** Confirmed
- **Where:** `app/Http/Controllers/Api/GuestHouseBookingController.php:54-57`; `app/Services/GuestHouseAvailabilityService.php:14-21,51-66`
- **What's happening:** The in-transaction guard only calls `hasBookingConflict()` (bookings), not `isAvailable()` (which also checks availability blocks), and uses a lock-free `exists()`. A block added between quote and commit is ignored, and concurrent bookings can both pass.
- **Why it matters:** Double-booking and booking over host-blocked dates.
- **Recommendation (refine):** Re-check full `isAvailable()` inside the transaction with `lockForUpdate`; add a DB exclusion/unique guard on overlapping ranges per `guest_house_id`.
- **Fix risk:** Medium — concurrency; test block + overlap.
- **Depends on:** —
- **Disposition (default):** FIX

### [SEC-001] Public car quote/order endpoints bypass listing visibility
- **Category:** Security
- **Severity:** High
- **Confidence:** Confirmed
- **Where:** `app/Http/Controllers/Api/PublicOrderController.php:41` (`quote`) and `:94` (`store`) use `Car::findOrFail($id)` with no `isPubliclyVisible()` check
- **What's happening:** `/cars/{car}` correctly 404s drafts (`CatalogController::car` :188 `abort_unless($car->isPubliclyVisible())`), but the order endpoints don't. Knowing a `car_id` lets anyone quote and book a draft/pending/rejected/inactive P2P car.
- **Why it matters:** Unapproved/disabled inventory is bookable; bypasses moderation; potential pricing on incomplete listings.
- **Recommendation (add):** `abort_unless($car->isPubliclyVisible(), 404)` in both `quote()` and `store()`.
- **Fix risk:** Low — one guard each; test a draft car returns 404.
- **Depends on:** —
- **Disposition (default):** FIX

### [SEC-002] Reviews are unauthenticated, auto-approved, and not tied to a stay
- **Category:** Security
- **Severity:** High
- **Confidence:** Confirmed
- **Where:** `app/Http/Requests/Api/StoreListingReviewRequest.php:9-11` (`authorize()` returns true); `app/Http/Controllers/Api/ListingReviewController.php:86-93` (`is_approved => true`); review POST routes have no `auth:sanctum` (`routes/api.php:108,119`); no unique constraint (`database/migrations/2026_06_05_120000_create_listing_reviews_table.php:26-28`)
- **What's happening:** Anyone (no login) can POST a review with a self-reported `guest_name` for any active listing; it is auto-published; the same person can post unlimited reviews.
- **Why it matters:** Trivial review spam / fake ratings / competitor sabotage — directly corrupts the trust signal the marketplace sells.
- **Recommendation (refine):** Require `auth:sanctum`; tie a review to a completed booking by the reviewer; default `is_approved=false` (moderation) or auto-approve only verified stays; add a unique constraint (one review per booking per reviewer).
- **Fix risk:** Medium — changes public behavior + frontend review form; coordinate with GAP-006.
- **Depends on:** —
- **Disposition (default):** FIX

### [BUG-004] Hosts cannot accept/decline bookings (policy blocks the host route)
- **Category:** Bug (broken link / seam)
- **Severity:** High
- **Confidence:** Confirmed
- **Where:** `routes/api.php:207-208` (host status routes) → `app/Http/Controllers/Api/Host/HostBookingController.php:78,94` `authorize('updateStatus', ...)` → `app/Policies/OrderPolicy.php:25-28` & `GuestHouseBookingPolicy.php:25-28` return `Admin` only. Frontend exports `updateHostCarBookingStatus`/`updateHostGuestHouseBookingStatus` (`frontend/src/api/host.js:210-215`) that are never imported.
- **What's happening:** The host booking-status endpoints exist but always 403 for actual hosts, and no host UI calls them. Host booking management is read-only in practice.
- **Why it matters:** Core marketplace capability (host approves/declines) is non-functional; contradicts the "host-approved booking" product promise.
- **Recommendation (refine):** Decide the model (host self-manages vs admin-mediated). If hosts should manage: allow owner in `updateStatus` policies and wire the host UI. If admin-only: remove the host routes + dead FE exports.
- **Fix risk:** Medium — auth + product decision + UI.
- **Depends on:** —
- **Disposition (default):** DISCUSS

### [GAP-003] No cancellation/refund path for car bookings (guests)
- **Category:** Missing feature
- **Severity:** High
- **Confidence:** Confirmed
- **Where:** `routes/api.php:129-133` (`/me/orders` read-only) vs guesthouse `:137` (`/me/guest-house-bookings/{ref}/cancel`); `app/Http/Controllers/Api/MeOrderController.php` is read-only
- **What's happening:** Guesthouse guests can self-cancel (with a cancellation window) but car renters cannot cancel at all via the API.
- **Why it matters:** Inconsistent product; support burden; no policy-driven refunds for the larger (car) inventory.
- **Recommendation (add):** Add `POST /me/orders/{order}/cancel` with a cancellation policy + (once GAP-001 lands) refund handling.
- **Fix risk:** Medium — depends on refund/payment.
- **Depends on:** GAP-004
- **Disposition (default):** FIX

### [DATA-001] Hard-deleting a guesthouse cascades and destroys its bookings
- **Category:** Data integrity
- **Severity:** High
- **Confidence:** Confirmed
- **Where:** `database/migrations/2026_06_04_100000_create_guest_house_tables.php:90` (`guest_house_id ... cascadeOnDelete`) on bookings/images/blocks/reviews/payments
- **What's happening:** Deleting a `GuestHouse` row deletes all its bookings (financial/legal records). Contrast cars: orders use `restrictOnDelete` (`orders` migration :15) which is safe.
- **Why it matters:** Permanent loss of booking/financial history; accidental admin delete is catastrophic and likely non-compliant.
- **Recommendation (refine):** Change booking FK to `restrictOnDelete` (or rely on the existing guesthouse soft-deletes and block hard delete when bookings exist).
- **Fix risk:** Low/Medium — migration; verify admin delete UX.
- **Depends on:** —
- **Disposition (default):** FIX

### [BUG-005] No idempotency on booking creation (duplicate orders on retry)
- **Category:** Bug
- **Severity:** Medium
- **Confidence:** Confirmed
- **Where:** `app/Http/Controllers/Api/PublicOrderController.php:92` & `GuestHouseBookingController.php` (no idempotency key)
- **What's happening:** A client retry / double-submit creates duplicate bookings (no idempotency key, no dedupe window).
- **Why it matters:** Duplicate reservations and (after GAP-001) double charges.
- **Recommendation (add):** Accept an `Idempotency-Key` header and dedupe; or a short-window dedupe on (user, car, dates).
- **Fix risk:** Low.
- **Depends on:** —
- **Disposition (default):** FIX

### [BUG-006] Auto-confirm contradicts the host-approval + prepay UX
- **Category:** Bug (product correctness)
- **Severity:** Medium
- **Confidence:** Confirmed
- **Where:** `PublicOrderController.php:135` & `GuestHouseBookingController.php:62-63` set `Confirmed` immediately; `frontend/src/components/request-to-book/Step3YourDetails.jsx:33` and email templates (`gh_booking_received` vs `gh_booking_confirmed`) assume host review first
- **What's happening:** The UI ("request to book", prepay on approval) and the seeded `*_received` templates assume a request→approve flow, but the backend auto-confirms and sends the *confirmed* template.
- **Why it matters:** Misleading guest experience; `gh_booking_received` template is dead; status semantics drift.
- **Recommendation (refine):** Pick one model end-to-end (instant-book vs request-to-book) and align backend status, emails, and UI. Ties to BUG-001/BUG-004.
- **Fix risk:** Medium — product decision.
- **Depends on:** BUG-004
- **Disposition (default):** DISCUSS

### [SEC-003] Availability-calendar endpoint leaks unapproved cars
- **Category:** Security
- **Severity:** Medium
- **Confidence:** Confirmed
- **Where:** `app/Http/Controllers/Api/CatalogController.php:228-268` — no `isPubliclyVisible()` gate; returns confirmed orders + blocks for any `car_id`
- **What's happening:** `GET /cars/{car}/availability-calendar` returns booking/availability data for any car including drafts/inactive.
- **Why it matters:** Leaks operational data (occupancy) about unpublished inventory.
- **Recommendation (add):** `abort_unless($car->isPubliclyVisible(), 404)`.
- **Fix risk:** Low.
- **Depends on:** —
- **Disposition (default):** FIX

### [GAP-004] No refund processing
- **Category:** Missing feature
- **Severity:** Medium
- **Confidence:** Confirmed
- **Where:** `OrderPayment` factory supports `'refunded'` (`database/factories/OrderPaymentFactory.php:20`) but no code mutates payments on cancel; `OrderObserver.php:30` only emails
- **What's happening:** Cancelling sets a status flag and emails; no money is reversed.
- **Why it matters:** Refunds are manual/absent; no symmetry with capture.
- **Recommendation (add):** Implement refund on cancel against the gateway + write payment rows.
- **Fix risk:** Medium.
- **Depends on:** GAP-001
- **Disposition (default):** DISCUSS

### [GAP-005] No guest↔host messaging
- **Category:** Missing feature / Competitor gap
- **Severity:** Medium
- **Confidence:** Confirmed
- **Where:** no `messages`/`threads`/`conversations` tables, models, or routes; `POST /contact` is email-only
- **What's happening:** No in-app messaging exists.
- **Why it matters:** Category-standard (Airbnb/Turo); needed for booking coordination and trust.
- **Recommendation (add):** Add a booking-scoped messaging model + endpoints + UI.
- **Fix risk:** Medium — new subsystem.
- **Depends on:** —
- **Disposition (default):** HOLD

### [GAP-006] Reviews are one-way and unverified (no two-way reputation)
- **Category:** Missing feature / Competitor gap
- **Severity:** Medium
- **Confidence:** Confirmed
- **Where:** `app/Http/Controllers/Api/ListingReviewController.php:86-93`; no host→guest review, no `reviewer_role`/`reviewee`
- **What's happening:** Only guest→listing reviews exist; hosts cannot review guests; no reveal mechanism.
- **Why it matters:** Trust/reputation is half-built vs category norms.
- **Recommendation (add):** Add host→guest reviews tied to completed bookings + a reveal window. Builds on SEC-002.
- **Fix risk:** Medium.
- **Depends on:** SEC-002
- **Disposition (default):** DISCUSS

### [GAP-007] Approved listings can be edited without re-moderation
- **Category:** Missing feature / Tech debt
- **Severity:** Medium
- **Confidence:** Confirmed
- **Where:** `HostCarController::update:75-86`, `HostGuestHouseController::update:86-106` — no reset to `PendingReview` on material edits
- **What's happening:** A host can materially change an approved, live listing (price, specs, photos) with no re-review.
- **Why it matters:** Moderation can be bypassed post-approval.
- **Recommendation (refine):** On sensitive-field edits to an approved listing, reset to `PendingReview` (or queue a light re-check). Make the set of "sensitive fields" a product decision.
- **Fix risk:** Low/Medium.
- **Depends on:** —
- **Disposition (default):** DISCUSS

### [GAP-008] No explicit managed-fleet vs P2P flag; fleet cars bypass approval
- **Category:** Data integrity / Tech debt
- **Severity:** Medium
- **Confidence:** Confirmed
- **Where:** `app/Models/Car.php:67-76` (`scopePubliclyVisible` treats `user_id=null` as always-visible regardless of `listing_status`); `:78-81` `isOwnedByHost()`
- **What's happening:** Operator vs host inventory is inferred from `user_id` being null. Fleet cars skip the approval gate entirely; guesthouses don't have the same bypass (asymmetric).
- **Why it matters:** Muddled commission/payout logic (GAP-002), inconsistent moderation, fragile assumptions.
- **Recommendation (add):** Add an explicit `ownership_type`/`is_managed_fleet` column; make visibility + commission depend on it explicitly; align car/guesthouse behavior.
- **Fix risk:** Medium — schema + query changes.
- **Depends on:** —
- **Disposition (default):** DISCUSS

### [GAP-009] Shop feature flags are exposed but not enforced
- **Category:** Bug / Tech debt
- **Severity:** Medium
- **Confidence:** Confirmed
- **Where:** `app/Services/PublicShopConfigService.php:53-55` exposes `rentals_enabled`, `enable_coupons`, `allow_deposit`; `RentalQuoteService` never reads `enable_coupons`/`rentals_enabled`; `frontend/src/context/ShopConfigContext.jsx` only uses `prepayPercent`
- **What's happening:** Admin-configurable flags are inert — coupons apply even when "disabled"; rentals can't actually be turned off.
- **Why it matters:** Admin controls give a false sense of control; coupon abuse possible.
- **Recommendation (refine):** Enforce flags in the quote/booking controllers and gate UI.
- **Fix risk:** Low.
- **Depends on:** —
- **Disposition (default):** FIX

### [GAP-010] Incomplete transactional notifications
- **Category:** Missing feature
- **Severity:** Medium
- **Confidence:** Confirmed
- **Where:** `app/Observers/GuestHouseBookingObserver.php:28-31` (guest-only, no Completed arm), `OrderObserver.php:28-31`; host not emailed on cancel; `gh_booking_received` never sent; `app/Notifications/` empty
- **What's happening:** Hosts aren't notified on cancellation/status change; no completion email; one seeded template is never used; no automated `Confirmed→Completed` job.
- **Why it matters:** Hosts/guests miss critical lifecycle events.
- **Recommendation (refine):** Notify hosts on cancel/status change; add completion handling; remove or wire the unused template.
- **Fix risk:** Low.
- **Depends on:** —
- **Disposition (default):** FIX

### [BUG-007] Filament order form can set arbitrary status, bypassing the state machine
- **Category:** Bug
- **Severity:** Medium
- **Confidence:** Confirmed
- **Where:** `app/Filament/Resources/Orders/Schemas/OrderForm.php:96-100` (free `order_status` select) vs `app/Models/Order.php:176-184` (`isAllowedOrderTransition`)
- **What's happening:** The admin edit form writes `order_status` directly without going through `transitionOrderStatus`, so invalid transitions (e.g. `Cancelled → Confirmed`) can be saved.
- **Why it matters:** Corrupt status history; bypasses the guardrails the model defines.
- **Recommendation (refine):** Route admin status changes through the state machine (validate transitions on save, or use dedicated actions).
- **Fix risk:** Low.
- **Depends on:** —
- **Disposition (default):** FIX

### [DATA-002] Filament admin order form pricing can diverge from the quote engine
- **Category:** Data integrity
- **Severity:** Medium
- **Confidence:** Confirmed
- **Where:** `app/Filament/Resources/Orders/Schemas/OrderForm.php:179-208` — admins type `base_rental_cents`/`total_cents` manually; no `RentalQuoteService` call
- **What's happening:** Admin-created/edited orders can have totals that don't match the pricing engine or the line items.
- **Why it matters:** Host/guest/admin numbers can disagree (the highest-value class of money bug).
- **Recommendation (refine):** Compute admin order totals via `RentalQuoteService`, or make these fields read-only/derived.
- **Fix risk:** Medium.
- **Depends on:** —
- **Disposition (default):** DISCUSS

### [DATA-003] Polymorphic reviews orphaned on listing delete
- **Category:** Data integrity
- **Severity:** Low
- **Confidence:** Confirmed
- **Where:** `database/migrations/2026_06_05_120000_create_listing_reviews_table.php:17` (`morphs`, no cleanup); listings deletable
- **What's happening:** Deleting a car/guesthouse leaves `listing_reviews` rows pointing at a dead morph id.
- **Why it matters:** Orphan rows; possible mis-counts.
- **Recommendation (add):** Clean up reviews via model events / observer on listing delete (polymorphic FKs can't cascade in DB).
- **Fix risk:** Low.
- **Depends on:** —
- **Disposition (default):** FIX

### [DATA-004] Guesthouse bookings store no nightly pricing snapshot; PDFs read live data
- **Category:** Data integrity
- **Severity:** Low
- **Confidence:** Confirmed
- **Where:** `GuestHouseBookingController.php:59-80` (totals only, no `pricing_snapshot`); `GuestHouseQuoteService.php:93-95` produces `nightly_breakdown` that is discarded; `resources/views/pdf/guest-house-booking.blade.php:21-26` reads live property/policy fields
- **What's happening:** Unlike car orders, guesthouse bookings don't snapshot the nightly breakdown or policy text; contract PDFs reflect later host edits.
- **Why it matters:** Legal/operational drift between what was agreed and what the contract shows.
- **Recommendation (add):** Persist a `pricing_snapshot` (+ key policy text) at booking time and render PDFs from it.
- **Fix risk:** Low.
- **Depends on:** —
- **Disposition (default):** HOLD

### [DEAD-001] `GuestHouseBookingPayment` model + table are never written or read
- **Category:** Dead code
- **Severity:** Low
- **Confidence:** Confirmed (write/read path) — searched `GuestHouseBookingPayment` / `guest_house_booking_payments`: only model, `hasMany` relation (`GuestHouseBooking.php:102-105`), and migration
- **Where:** `app/Models/GuestHouseBookingPayment.php`; `database/migrations/2026_06_04_100000_create_guest_house_tables.php:117-125`
- **What's happening:** Schema + relation exist; nothing creates or queries payment rows.
- **Why it matters:** Misleading scaffolding; likely intended for the unbuilt payment system.
- **Recommendation (remove or build):** Either wire it as part of GAP-001 or drop it. Because it's a recent migration likely placed for upcoming payments, confirm intent before removal.
- **Fix risk:** Low (removal) — re-confirm unreferenced before dropping.
- **Depends on:** GAP-001
- **Disposition (default):** DISCUSS

### [DEAD-002] `guest_house_reviews` table + `GuestHouseReview` model superseded by `listing_reviews`
- **Category:** Dead code
- **Severity:** Low
- **Confidence:** Confirmed (never written by app code; resource exposes `listingReviews`)
- **Where:** `database/migrations/2026_06_04_100000_create_guest_house_tables.php:127-137`; `app/Models/GuestHouseReview.php`; live path is `GuestHouseDetailResource.php:17-19`
- **What's happening:** A second, older review system exists but is unused (only admin count reads it).
- **Why it matters:** Two review models invite confusion/drift.
- **Recommendation (remove):** Migrate any data and drop the legacy table/model after confirmation.
- **Fix risk:** Low/Medium — confirm no admin dependency.
- **Depends on:** —
- **Disposition (default):** DISCUSS

### [DEAD-003] `App\Enums\PaymentMethod` enum is never imported
- **Category:** Dead code
- **Severity:** Low
- **Confidence:** Confirmed (grep `App\Enums\PaymentMethod` → 0 imports)
- **Where:** `app/Enums/PaymentMethod.php`
- **What's happening:** The `PaymentMethod` *model* (catalog row) is used; this *enum* is not.
- **Why it matters:** Minor dead code; may be intended for GAP-001.
- **Recommendation (remove or use):** Drop, or adopt it when integrating payments.
- **Fix risk:** Low.
- **Depends on:** —
- **Disposition (default):** HOLD

### [DEAD-004] `VehicleType::fromCategoryName()` is `@deprecated` and unreferenced
- **Category:** Dead code
- **Severity:** Low
- **Confidence:** Confirmed (grep `fromCategoryName` → definition only; active code uses `fromSubCategory()`/`fromMainCategorySlug()`)
- **Where:** `app/Support/VehicleType.php:29-33`
- **What's happening:** Deprecated method with no callers.
- **Why it matters:** Cleanup.
- **Recommendation (remove):** Delete the method.
- **Fix risk:** Low.
- **Depends on:** —
- **Disposition (default):** FIX

### [DEAD-005] Frontend dead exports for host booking status & a `me` booking getter
- **Category:** Dead code (seam)
- **Severity:** Low
- **Confidence:** Confirmed (never imported)
- **Where:** `frontend/src/api/host.js:210-215` (`updateHostCarBookingStatus`, `updateHostGuestHouseBookingStatus`), `frontend/src/api/me.js:19-21` (`getMeGuestHouseBooking`)
- **What's happening:** API client functions defined but unused; the host-status ones map to BUG-004's blocked endpoints.
- **Why it matters:** Dead exports; symptom of the unfinished host accept/decline feature.
- **Recommendation (remove or wire):** Resolve with BUG-004 (wire the UI) or delete.
- **Fix risk:** Low.
- **Depends on:** BUG-004
- **Disposition (default):** DISCUSS

### [DEBT-001] `StandBy` / `payment_lock_expires_at` is half-implemented
- **Category:** Tech debt
- **Severity:** Low
- **Confidence:** Confirmed
- **Where:** `PublicOrderController.php:135,151` sets `Confirmed` but also a `payment_lock_expires_at`; `OrderAvailabilityService.php:29-31` only counts locks for `StandBy`
- **What's happening:** A payment-lock/standby mechanism exists but the public flow never uses it (orders go straight to Confirmed).
- **Why it matters:** Misleading "lock" semantics; will conflict with GAP-001/BUG-001 work.
- **Recommendation (refine):** Use StandBy + lock as the pre-payment state once payments land.
- **Fix risk:** Low.
- **Depends on:** BUG-001
- **Disposition (default):** HOLD

### [PERF-001] Availability/search index coverage unverified
- **Category:** Performance
- **Severity:** Low
- **Confidence:** Needs verification
- **Where:** date-range/status queries in `OrderAvailabilityService`, `GuestHouseAvailabilityService`, `CatalogController::cars`
- **What's happening:** Hot availability/search queries filter on date ranges + status + foreign keys; index coverage was not exhaustively confirmed in this read-only pass.
- **Why it matters:** Potential slow search/availability as data grows.
- **Recommendation (verify):** Review migrations/`EXPLAIN` for composite indexes on `(car_id, pickup_at, dropoff_at, order_status)` and equivalent guesthouse columns.
- **Fix risk:** Low.
- **Depends on:** —
- **Disposition (default):** HOLD

---

## 5. Competitor gap matrix

Competitive set: **Turo/Airbnb/Outdoorsy** (model references) + **Indie/Happy Campers/KuKu/CampEasy/Go Campers/Cars Iceland** (Iceland direct). ✓ = present, ◐ = partial, ✗ = absent.

| Capability | MyTerraBook | Category standard |
|---|---|---|
| Date+location search, filters (4WD/seats/transmission) | ✓ | ✓ |
| Real-time availability | ◐ (no atomic/lock; client coordination) | ✓ |
| Transparent server-side quote | ✓ | ✓ |
| Multi-step listing builder + photos | ✓ | ✓ |
| Seasonal / special pricing | ✓ | ✓ |
| Add-ons / insurance tiers | ◐ (insurance modeled as rental options, not first-class) | ✓ |
| Instant-book vs request | ✗ (auto-confirm only; request UI orphaned) | ✓ |
| **Payments / deposits / SCA** | ✗ | ✓ (table stakes) |
| **Host payouts / commission** | ✗ | ✓ (P2P table stakes) |
| Two-way + verified reviews | ✗ (one-way, unverified) | ✓ |
| In-app messaging | ✗ | ✓ |
| Cancellation/refund | ◐ (guesthouse only) | ✓ |
| Multi-currency | ◐ (cents + currency field; conversion unclear) | ✓ |
| Coupons/referrals | ◐ (coupons exist, flag not enforced) | ✓ |
| ID/license/host verification | ✗ | ✓ |
| Admin moderation | ✓ (Filament) | ✓ |
| iCal/channel sync | ◐ (import only, scheduled) | ◐ |
| KEF airport pickup / F-road / gravel-sand&ash | ◐ (locations/options generic; not first-class products) | ✓ (Iceland) |

**Narrative.** MyTerraBook has a genuinely strong **listing + pricing + catalog + admin** foundation at category parity, and good engineering hygiene (cents money, server quotes, real ownership policies). The **top must-have gaps** are the money spine — **payments (GAP-001), payouts/commission (GAP-002), refunds (GAP-004)** — plus **trust** (verified two-way reviews, GAP-006/SEC-002) and **booking semantics** (host accept/decline BUG-004, instant-vs-request BUG-006). **Differentiation opportunities:** lean into Iceland-specific products as first-class (gravel/sand&ash/CDW insurance tiers, F-road permission per vehicle, KEF pickup) and bidirectional iCal channel sync — these would set it apart from generic rental sites once the money spine is in place.

---

## 6. Fix Ledger (human table)

| ID | Title | Category | Severity | Confidence | Disposition | Depends on | Fix risk |
|----|-------|----------|----------|-----------|-------------|-----------|----------|
| BUG-001 | Bookings confirmed without payment capture | Bug | Critical | Confirmed | DISCUSS | GAP-001 | High |
| GAP-001 | No payment gateway integration | Missing | Critical | Confirmed | DISCUSS | — | High |
| GAP-002 | No commission/payout system | Missing | Critical | Confirmed | DISCUSS | GAP-001, GAP-008 | High |
| BUG-002 | Car double-booking race (not atomic) | Bug | High | Confirmed | FIX | — | Medium |
| BUG-003 | Guesthouse double-booking + block bypass | Bug | High | Confirmed | FIX | — | Medium |
| SEC-001 | Order/quote bypass listing visibility | Security | High | Confirmed | FIX | — | Low |
| SEC-002 | Reviews unauth, auto-approved, unverified | Security | High | Confirmed | FIX | — | Medium |
| BUG-004 | Hosts can't accept/decline bookings | Bug | High | Confirmed | DISCUSS | — | Medium |
| GAP-003 | No car cancellation/refund for guests | Missing | High | Confirmed | FIX | GAP-004 | Medium |
| DATA-001 | GH delete cascades and wipes bookings | Data | High | Confirmed | FIX | — | Low |
| BUG-005 | No idempotency on booking creation | Bug | Medium | Confirmed | FIX | — | Low |
| BUG-006 | Auto-confirm vs host-approval/prepay UX | Bug | Medium | Confirmed | DISCUSS | BUG-004 | Medium |
| SEC-003 | Availability-calendar leaks unapproved cars | Security | Medium | Confirmed | FIX | — | Low |
| GAP-004 | No refund processing | Missing | Medium | Confirmed | DISCUSS | GAP-001 | Medium |
| GAP-005 | No guest↔host messaging | Missing | Medium | Confirmed | HOLD | — | Medium |
| GAP-006 | One-way / unverified reviews | Missing | Medium | Confirmed | DISCUSS | SEC-002 | Medium |
| GAP-007 | Approved listings editable w/o re-moderation | Missing | Medium | Confirmed | DISCUSS | — | Low |
| GAP-008 | No managed-fleet vs P2P flag; fleet bypass | Data | Medium | Confirmed | DISCUSS | — | Medium |
| GAP-009 | Shop feature flags exposed but not enforced | Bug | Medium | Confirmed | FIX | — | Low |
| GAP-010 | Incomplete transactional notifications | Missing | Medium | Confirmed | FIX | — | Low |
| BUG-007 | Filament order form bypasses state machine | Bug | Medium | Confirmed | FIX | — | Low |
| DATA-002 | Admin order pricing can diverge from quote | Data | Medium | Confirmed | DISCUSS | — | Medium |
| DATA-003 | Polymorphic reviews orphaned on delete | Data | Low | Confirmed | FIX | — | Low |
| DATA-004 | GH booking has no pricing snapshot; PDFs live | Data | Low | Confirmed | HOLD | — | Low |
| DEAD-001 | GuestHouseBookingPayment unused | Dead code | Low | Confirmed | DISCUSS | GAP-001 | Low |
| DEAD-002 | guest_house_reviews/GuestHouseReview superseded | Dead code | Low | Confirmed | DISCUSS | — | Low |
| DEAD-003 | PaymentMethod enum unused | Dead code | Low | Confirmed | HOLD | — | Low |
| DEAD-004 | VehicleType::fromCategoryName() deprecated/unused | Dead code | Low | Confirmed | FIX | — | Low |
| DEAD-005 | Frontend dead host-status/me-booking exports | Dead code | Low | Confirmed | DISCUSS | BUG-004 | Low |
| DEBT-001 | StandBy/payment_lock half-implemented | Tech debt | Low | Confirmed | HOLD | BUG-001 | Low |
| PERF-001 | Availability/search index coverage unverified | Perf | Low | Needs verification | HOLD | — | Low |

## 7. Fix Ledger (machine block)

```fix-ledger
[
  {"id":"BUG-001","title":"Bookings confirmed without payment capture","category":"Bug","severity":"Critical","confidence":"Confirmed","where":"app/Http/Controllers/Api/PublicOrderController.php:135-152; app/Http/Controllers/Api/GuestHouseBookingController.php:59-63","recommendation":"Do not set Confirmed on create; introduce a payment step and confirm only on payment success.","fix_risk":"High","depends_on":["GAP-001"],"disposition":"DISCUSS"},
  {"id":"GAP-001","title":"No payment gateway integration","category":"Missing feature","severity":"Critical","confidence":"Confirmed","where":"backend/composer.json:8-18; app/Enums/PaymentMethod.php; frontend/src/hooks/useRequestToBook.js:500-514","recommendation":"Integrate a processor (Stripe), add authorize/capture, deposits, idempotent webhooks, persist payment rows.","fix_risk":"High","depends_on":[],"disposition":"DISCUSS"},
  {"id":"GAP-002","title":"No commission/payout system","category":"Missing feature","severity":"Critical","confidence":"Confirmed","where":"app/Http/Controllers/Api/Host/HostDashboardController.php:53-55","recommendation":"Add commission config + host_payout_cents on booking snapshot + Payout ledger.","fix_risk":"High","depends_on":["GAP-001","GAP-008"],"disposition":"DISCUSS"},
  {"id":"BUG-002","title":"Car double-booking race (not atomic)","category":"Bug","severity":"High","confidence":"Confirmed","where":"app/Http/Controllers/Api/PublicOrderController.php:98-100,124-152; app/Services/OrderAvailabilityService.php:78-83","recommendation":"Re-check capacity inside DB::transaction with lockForUpdate; count Pending/StandBy toward capacity.","fix_risk":"Medium","depends_on":[],"disposition":"FIX"},
  {"id":"BUG-003","title":"Guesthouse double-booking + block bypass","category":"Bug","severity":"High","confidence":"Confirmed","where":"app/Http/Controllers/Api/GuestHouseBookingController.php:54-57; app/Services/GuestHouseAvailabilityService.php:14-21,51-66","recommendation":"Re-check full isAvailable() inside txn with lockForUpdate; add overlap exclusion guard.","fix_risk":"Medium","depends_on":[],"disposition":"FIX"},
  {"id":"SEC-001","title":"Order/quote bypass listing visibility","category":"Security","severity":"High","confidence":"Confirmed","where":"app/Http/Controllers/Api/PublicOrderController.php:41,94","recommendation":"abort_unless($car->isPubliclyVisible(),404) in quote() and store().","fix_risk":"Low","depends_on":[],"disposition":"FIX"},
  {"id":"SEC-002","title":"Reviews unauth, auto-approved, unverified","category":"Security","severity":"High","confidence":"Confirmed","where":"app/Http/Requests/Api/StoreListingReviewRequest.php:9-11; app/Http/Controllers/Api/ListingReviewController.php:86-93; routes/api.php:108,119; migration listing_reviews:26-28","recommendation":"Require auth, tie to completed booking, default unapproved, add unique constraint per booking/reviewer.","fix_risk":"Medium","depends_on":[],"disposition":"FIX"},
  {"id":"BUG-004","title":"Hosts cannot accept/decline bookings","category":"Bug","severity":"High","confidence":"Confirmed","where":"routes/api.php:207-208; app/Http/Controllers/Api/Host/HostBookingController.php:78,94; app/Policies/OrderPolicy.php:25-28; app/Policies/GuestHouseBookingPolicy.php:25-28","recommendation":"Decide host-self-manage vs admin-mediated; either allow owner in updateStatus + wire UI, or remove host routes + dead FE exports.","fix_risk":"Medium","depends_on":[],"disposition":"DISCUSS"},
  {"id":"GAP-003","title":"No car cancellation/refund for guests","category":"Missing feature","severity":"High","confidence":"Confirmed","where":"routes/api.php:129-133; app/Http/Controllers/Api/MeOrderController.php","recommendation":"Add POST /me/orders/{order}/cancel with cancellation policy + refund.","fix_risk":"Medium","depends_on":["GAP-004"],"disposition":"FIX"},
  {"id":"DATA-001","title":"GH delete cascades and wipes bookings","category":"Data integrity","severity":"High","confidence":"Confirmed","where":"database/migrations/2026_06_04_100000_create_guest_house_tables.php:90","recommendation":"Change booking FK to restrictOnDelete or block hard delete when bookings exist.","fix_risk":"Low","depends_on":[],"disposition":"FIX"},
  {"id":"BUG-005","title":"No idempotency on booking creation","category":"Bug","severity":"Medium","confidence":"Confirmed","where":"app/Http/Controllers/Api/PublicOrderController.php:92; app/Http/Controllers/Api/GuestHouseBookingController.php","recommendation":"Accept Idempotency-Key and dedupe creation.","fix_risk":"Low","depends_on":[],"disposition":"FIX"},
  {"id":"BUG-006","title":"Auto-confirm vs host-approval/prepay UX","category":"Bug","severity":"Medium","confidence":"Confirmed","where":"app/Http/Controllers/Api/PublicOrderController.php:135; app/Http/Controllers/Api/GuestHouseBookingController.php:62-63; frontend/src/components/request-to-book/Step3YourDetails.jsx:33","recommendation":"Choose instant-book vs request-to-book end to end; align status, emails, UI.","fix_risk":"Medium","depends_on":["BUG-004"],"disposition":"DISCUSS"},
  {"id":"SEC-003","title":"Availability-calendar leaks unapproved cars","category":"Security","severity":"Medium","confidence":"Confirmed","where":"app/Http/Controllers/Api/CatalogController.php:228-268","recommendation":"abort_unless($car->isPubliclyVisible(),404).","fix_risk":"Low","depends_on":[],"disposition":"FIX"},
  {"id":"GAP-004","title":"No refund processing","category":"Missing feature","severity":"Medium","confidence":"Confirmed","where":"database/factories/OrderPaymentFactory.php:20; app/Observers/OrderObserver.php:30","recommendation":"Implement gateway refund on cancel + write payment rows.","fix_risk":"Medium","depends_on":["GAP-001"],"disposition":"DISCUSS"},
  {"id":"GAP-005","title":"No guest-host messaging","category":"Missing feature","severity":"Medium","confidence":"Confirmed","where":"no messages/threads tables/models/routes; ContactController email-only","recommendation":"Add booking-scoped messaging subsystem + UI.","fix_risk":"Medium","depends_on":[],"disposition":"HOLD"},
  {"id":"GAP-006","title":"One-way / unverified reviews","category":"Missing feature","severity":"Medium","confidence":"Confirmed","where":"app/Http/Controllers/Api/ListingReviewController.php:86-93","recommendation":"Add host->guest reviews tied to completed bookings + reveal window.","fix_risk":"Medium","depends_on":["SEC-002"],"disposition":"DISCUSS"},
  {"id":"GAP-007","title":"Approved listings editable without re-moderation","category":"Missing feature","severity":"Medium","confidence":"Confirmed","where":"app/Http/Controllers/Api/Host/HostCarController.php:75-86; app/Http/Controllers/Api/Host/HostGuestHouseController.php:86-106","recommendation":"Reset to PendingReview on sensitive-field edits to approved listings.","fix_risk":"Low","depends_on":[],"disposition":"DISCUSS"},
  {"id":"GAP-008","title":"No managed-fleet vs P2P flag; fleet bypass","category":"Data integrity","severity":"Medium","confidence":"Confirmed","where":"app/Models/Car.php:67-76,78-81","recommendation":"Add explicit ownership_type column; base visibility+commission on it; align car/GH behavior.","fix_risk":"Medium","depends_on":[],"disposition":"DISCUSS"},
  {"id":"GAP-009","title":"Shop feature flags exposed but not enforced","category":"Bug","severity":"Medium","confidence":"Confirmed","where":"app/Services/PublicShopConfigService.php:53-55; app/Services/RentalQuoteService.php; frontend/src/context/ShopConfigContext.jsx","recommendation":"Enforce rentals_enabled/enable_coupons/allow_deposit in controllers and gate UI.","fix_risk":"Low","depends_on":[],"disposition":"FIX"},
  {"id":"GAP-010","title":"Incomplete transactional notifications","category":"Missing feature","severity":"Medium","confidence":"Confirmed","where":"app/Observers/GuestHouseBookingObserver.php:28-31; app/Observers/OrderObserver.php:28-31","recommendation":"Notify hosts on cancel/status change; add completion handling; wire/remove unused template.","fix_risk":"Low","depends_on":[],"disposition":"FIX"},
  {"id":"BUG-007","title":"Filament order form bypasses state machine","category":"Bug","severity":"Medium","confidence":"Confirmed","where":"app/Filament/Resources/Orders/Schemas/OrderForm.php:96-100; app/Models/Order.php:176-184","recommendation":"Validate transitions via transitionOrderStatus on admin save.","fix_risk":"Low","depends_on":[],"disposition":"FIX"},
  {"id":"DATA-002","title":"Admin order pricing can diverge from quote","category":"Data integrity","severity":"Medium","confidence":"Confirmed","where":"app/Filament/Resources/Orders/Schemas/OrderForm.php:179-208","recommendation":"Compute admin order totals via RentalQuoteService or make derived/read-only.","fix_risk":"Medium","depends_on":[],"disposition":"DISCUSS"},
  {"id":"DATA-003","title":"Polymorphic reviews orphaned on delete","category":"Data integrity","severity":"Low","confidence":"Confirmed","where":"database/migrations/2026_06_05_120000_create_listing_reviews_table.php:17","recommendation":"Clean up listing_reviews on listing delete via observer.","fix_risk":"Low","depends_on":[],"disposition":"FIX"},
  {"id":"DATA-004","title":"GH booking has no pricing snapshot; PDFs live","category":"Data integrity","severity":"Low","confidence":"Confirmed","where":"app/Http/Controllers/Api/GuestHouseBookingController.php:59-80; resources/views/pdf/guest-house-booking.blade.php:21-26","recommendation":"Persist pricing_snapshot + policy text at booking; render PDF from it.","fix_risk":"Low","depends_on":[],"disposition":"HOLD"},
  {"id":"DEAD-001","title":"GuestHouseBookingPayment unused","category":"Dead code","severity":"Low","confidence":"Confirmed","where":"app/Models/GuestHouseBookingPayment.php; database/migrations/2026_06_04_100000_create_guest_house_tables.php:117-125","recommendation":"Wire as part of GAP-001 or drop after confirming unreferenced.","fix_risk":"Low","depends_on":["GAP-001"],"disposition":"DISCUSS"},
  {"id":"DEAD-002","title":"guest_house_reviews/GuestHouseReview superseded","category":"Dead code","severity":"Low","confidence":"Confirmed","where":"database/migrations/2026_06_04_100000_create_guest_house_tables.php:127-137; app/Models/GuestHouseReview.php","recommendation":"Migrate data and drop legacy table/model.","fix_risk":"Low","depends_on":[],"disposition":"DISCUSS"},
  {"id":"DEAD-003","title":"PaymentMethod enum unused","category":"Dead code","severity":"Low","confidence":"Confirmed","where":"app/Enums/PaymentMethod.php","recommendation":"Drop or adopt during GAP-001.","fix_risk":"Low","depends_on":[],"disposition":"HOLD"},
  {"id":"DEAD-004","title":"VehicleType::fromCategoryName() deprecated/unused","category":"Dead code","severity":"Low","confidence":"Confirmed","where":"app/Support/VehicleType.php:29-33","recommendation":"Delete the method.","fix_risk":"Low","depends_on":[],"disposition":"FIX"},
  {"id":"DEAD-005","title":"Frontend dead host-status/me-booking exports","category":"Dead code","severity":"Low","confidence":"Confirmed","where":"frontend/src/api/host.js:210-215; frontend/src/api/me.js:19-21","recommendation":"Resolve with BUG-004 (wire UI) or delete.","fix_risk":"Low","depends_on":["BUG-004"],"disposition":"DISCUSS"},
  {"id":"DEBT-001","title":"StandBy/payment_lock half-implemented","category":"Tech debt","severity":"Low","confidence":"Confirmed","where":"app/Http/Controllers/Api/PublicOrderController.php:135,151; app/Services/OrderAvailabilityService.php:29-31","recommendation":"Use StandBy+lock as pre-payment state once payments land.","fix_risk":"Low","depends_on":["BUG-001"],"disposition":"HOLD"},
  {"id":"PERF-001","title":"Availability/search index coverage unverified","category":"Performance","severity":"Low","confidence":"Needs verification","where":"app/Services/OrderAvailabilityService.php; app/Services/GuestHouseAvailabilityService.php; app/Http/Controllers/Api/CatalogController.php cars()","recommendation":"Verify composite indexes on (car_id,pickup_at,dropoff_at,order_status) and GH equivalents.","fix_risk":"Low","depends_on":[],"disposition":"HOLD"}
]
```

## 8. Suggested execution order

**Phase A — Safety & correctness (low-risk, do first):** SEC-001, SEC-003, BUG-002, BUG-003, BUG-005, BUG-007, DATA-001, DATA-003, GAP-009, GAP-010. These are mostly small, high-value, and don't need product decisions.

**Phase B — Trust:** SEC-002 (then GAP-006). Require auth + verified stays + uniqueness before opening reviews further.

**Phase C — The money spine (product decisions, then build):** GAP-001 → BUG-001 → GAP-004 → GAP-003; in parallel decide GAP-008 → GAP-002. This is the largest body of work and unblocks the marketplace model.

**Phase D — Booking semantics & host tooling:** BUG-004 + BUG-006 (decide instant vs request), then DEAD-005 falls out.

**Phase E — Cleanup once confirmed:** DEAD-004 (safe now), then DEAD-001/002/003 after the money decisions; DATA-002, DATA-004, DEBT-001, PERF-001 as polish.

## 9. Open questions for you

1. **Is this live as a true P2P marketplace today, or operator-fleet-first?** It decides whether GAP-002 (payouts) and BUG-004 (host accept/decline) are "Critical now" or "roadmap".
2. **Instant-book or request-to-book?** The backend auto-confirms; the UI says request + prepay-on-approval. Which is the intended product (BUG-006)?
3. **Which payment processor** do you want (Stripe vs other), and should hosts be paid via Stripe Connect or manual settlement (GAP-001/GAP-002)?
4. **Reviews:** verified-stay-only (require a completed booking) or open with moderation? (SEC-002/GAP-006)
5. **Campervans:** keep as a category-filter over `Car` (current), or promote to a first-class type? (Known open structural question; affects search/builder/insurance modeling.)
6. **Insurance/F-road/KEF pickup:** do you want these as first-class Iceland products (recommended differentiator) or keep them as generic rental options?
```
