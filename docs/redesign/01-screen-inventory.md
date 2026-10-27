# Phase 1 — screen inventory

Source: Figma file `HjqfE4nbHKhJLGEKIQgyQT`, read with `get_metadata` only.

- Pages in the file: `0:1` Foundations, `1:2` Components, `1:3` Screens, `44:492` Icon library.
- The linked node `41:16149` is the frame named **Main** inside **Site content · Terms** (`41:15961`). It is not its own screen.
- Every app screen on `1:3` is 1440px wide. There is no mobile, tablet, or collapsed-sidebar frame on that page.
- Every app screen uses the same **Sidebar** instance (`4:20`, 248px) and **Topbar** instance (`4:245`). The Dashboard topbar instance is drawn at height 26; every other screen’s topbar is 63, which is the component height. That difference is recorded, not corrected.
- Button labels and Tab labels live inside component instances. `get_metadata` does not expand that text, so those labels are not listed. They must be read with `get_design_context` before implementation.
- Each sidebar row’s glyph is an instance named `Icon` (16×16 inside a 28×28 tile). The specific icon symbol is not named in metadata. Nav labels below are the instance names.

Status for every row is `todo`. Nothing is implemented.

## Shared chrome (used by every app screen)

Sidebar `4:20`, top to bottom:

1. Logo instance
2. Nav/Home
3. Group label **Bookings** (collapse control)
4. Nav/Calendar
5. Nav/Car reservations — badge text `2` (other badges are `hidden="true"`)
6. Nav/Guest house bookings
7. Nav/Change requests
8. Group label **Catalog**
9. Nav/Guest houses
10. Nav/Vehicles
11. Nav/Pricing
12. Group label **People**
13. Nav/Hosts
14. Nav/Clients
15. Group label **Moderation**
16. Nav/Listing approvals
17. Nav/Listing reviews
18. Group label **Website**
19. Nav/Site content
20. Nav/Good to Know
21. Group label **Marketing**
22. Nav/Newsletter
23. Nav/Email
24. Nav/Analytics
25. Nav/Settings, pinned above the footer
26. Divider, then user block: initials `SA`, name `Super Admin`, email `admin@terrabook.test`, `icon/more`

Topbar `4:245`:

- Search field, placeholder `Search bookings, clients, vehicles, pages…`, hint `Ctrl K`, `icon/search`
- Status text `Coming Soon mode is on` with a dot
- Instance named `New menu`
- Notifications: `icon/bell` plus a dot

## Checklist — screens (`1:3`)

| Figma frame | Node | Existing route / page | Status |
| --- | --- | --- | --- |
| Dashboard | `5:2` | Filament `Dashboard` `/admin` | todo |
| Car reservations | `6:376` | `OrderResource` (label Vehicle Rental), Impact Rent cluster | todo |
| Car reservation · Edit | `8:639` | Order resource edit page | todo |
| Guest houses · Properties | `10:996` | `GuestHouseResource` in `guest-houses` cluster | todo |
| Guest house · Edit | `13:1308` | Guest house resource edit page | todo |
| Vehicles · All vehicles | `14:1707` | `CarResource` in `impact-rent` cluster (card grid) | todo |
| Pricing · Pricing rules | `15:1983` | `SpecialPriceResource` | todo |
| Settings · Shop & rentals | `16:2209` | `GlobalConfiguration` `global-configuration` (part of it) | todo |
| Site content · Homepage | `17:2593` | `HomepageCms` `homepage-cms` and `SiteContentHub` `site-content` | todo |
| Calendar | `18:2870` | `OrdersCalendar` `orders-calendar` plus guest-house `calendar` (one combined frame) | todo |
| Ku shkon çdo faqe (old → new) | `20:3115` | Not a page. Mapping diagram only | todo |
| Vehicles · All vehicles (table view) | `21:3114` | Same `CarResource` list, table variant | todo |
| Vehicles · Fleet units | `21:3673` | `CarUnitResource` | todo |
| Vehicles · Categories | `21:4101` | `CategoryResource` `main-categories` and `SubCategoryResource` `sub-categories` | todo |
| Vehicles · Features | `21:4571` | `CharacteristicResource` | todo |
| Vehicles · Locations | `22:4236` | `LocationResource` | todo |
| Vehicles · Rental conditions | `22:4677` | `RentalConditionResource` | todo |
| Pricing · Out-of-hours fees | `22:5100` | `OutOfHoursFeeResource` | todo |
| Pricing · Location fees | `22:5554` | `LocationFeeResource` | todo |
| Pricing · Add-ons | `23:5123` | `RentalOptionResource` | todo |
| Pricing · Discount codes | `23:5721` | `CouponResource` | todo |
| Pricing · Tax rates & Types of price | `23:6052` | `TaxRateResource` and `PriceTypeResource` on one frame | todo |
| Pricing · Booking restrictions | `23:6428` | `BookingRestrictionResource` | todo |
| Pricing · Base prices | `24:5997` | `DailyFareResource` | todo |
| Pricing · Fares overview | `24:6408` | `FaresOverview` `fares-overview` | todo |
| Pricing · Extra hour fares | `24:6878` | `ExtraHourFareResource` | todo |
| Pricing · Hourly fares | `24:7132` | `HourlyFareResource` | todo |
| Analytics · Reports | `26:6789` | `Reports` `reports` | todo |
| Analytics · Statistics | `26:7059` | `TrackingStatistics` `tracking-statistics` | todo |
| Analytics · Tracking campaigns | `26:7330` | `TrackingCampaignResource` | todo |
| Settings · Conditional texts | `26:7561` | `ConditionalTextResource` | todo |
| Settings · Backups | `26:7845` | `BackupResource` | todo |
| Vehicles · Edit vehicle | `27:7797` | Car resource edit page | todo |
| Guest house bookings | `32:9366` | `GuestHouseBookingResource` | todo |
| Change requests | `32:9803` | `BookingChangeRequestResource` | todo |
| Hosts | `32:10090` | `RegisteredHostResource` `registered-hosts` | todo |
| Clients | `32:10533` | `RegisteredClientResource` `registered-clients` | todo |
| Listing approvals | `34:10294` | `ListingReviewPage` `listing-approvals` | todo |
| Listing reviews | `34:10593` | `ListingReviewResource` `listing-reviews` | todo |
| Good to Know · Posts | `34:10852` | `BlogPostResource` | todo |
| Newsletter · Subscribers | `34:11310` | `NewsletterSubscriberResource`. Campaigns tab has no second frame | todo |
| Email · Templates | `35:11225` | `EmailTemplateResource` | todo |
| Email · SMTP settings | `35:11642` | `EmailSettings` `email-settings` | todo |
| Email · Send test | `35:11977` | `EmailTesting` `email-testing` | todo |
| Email · Log | `35:12252` | `EmailLogResource` | todo |
| Settings · Prices & payments | `36:12183` | `GlobalConfiguration` (prices/payments portion) | todo |
| Settings · Views & layout | `36:12606` | `GlobalConfiguration` (views/layout portion) | todo |
| Settings · Orders & company | `36:12995` | `GlobalConfiguration` (orders/company portion) | todo |
| Settings · Payment methods | `36:13389` | `PaymentMethodResource` `payment-methods` | todo |
| Settings · Custom fields | `38:14000` | `CustomFieldResource` `custom-fields` | todo |
| Settings · Users | `38:14376` | `ManageUsers` `users` | todo |
| Settings · Logged-in users | `38:14862` | `LoggedInUsers` `logged-in-users` | todo |
| Settings · Profile | `38:15244` | Filament profile `EditProfile` | todo |
| Guest houses · Amenities | `40:15043` | `GuestHouseAmenityResource` | todo |
| Site content · Global chrome | `41:15463` | `SiteContentHub`, `FooterSettings` `footer-settings`, branding in configuration | todo |
| Site content · Terms | `41:15961` | `SiteContentHub` / site page slug `terms`. Linked node `41:16149` is this frame’s Main | todo |

## Structure of each screen

Shared sidebar and topbar are not repeated. Header lines are breadcrumb, title, then description, in that order.

### Dashboard `5:2` — 1440×1024

Header: `Tuesday, 27 October 2026` / `Good afternoon, Super Admin`. Two Button instances. KPI cards: Awaiting confirmation (`icon/alert`, value `2`, `car orders · ISK 191.560`, `Review →`), Revenue this month (`icon/chart`, `ISK 0`, `Confirmed bookings only`, `+0% vs last month →`), Bookings this month (`icon/calendar`, `1`, `1 car · 0 guest house`, `View all →`), Live right now (`icon/clock`, `0`, `0 rentals · 0 stays`, `Open calendar →`), Website visitors (`icon/globe`, `1`, `0,0% conversion (30 days)`, `Analytics →`). Blocks: Needs attention, 30-day overview chart, Top countries, Recent orders. Recent-orders columns: Reference, Customer, Booked item, Dates, Status, Total.

### Car reservations `6:376` — 1440×1024

Header: `Bookings` / `Car reservations` / `Every vehicle booking in one list. Open a row to edit, confirm or resend the e-mail.` Six Tab instances. Search `Search bookings…`. Filter chips: `Pickup: next 90 days` (`icon/x`), add chips Vehicle, Pickup location, Payment (`icon/plus`), More filters count `10` (`icon/filter`), Columns (`icon/columns`). Columns: Reference, Customer, Vehicle, Pickup → Drop-off, Total, Payment, Status, plus an actions cell (`icon/pencil` on the sample row). Pagination and a note.

### Car reservation · Edit `8:639` — 1440×1960

Header: `Car reservations` / `ORD-LA17GB6WSD` / `Booked 19 Oct 2026, 11:11 · Payment lock expired · Source: Website`. Sections: Summary, Rental, Customer, Costs, Totals, Administration, Availability, Activity, Danger zone. Sticky bar: `You have unsaved changes` plus two Button instances.

### Guest houses · Properties `10:996` — 1440×1050

Header: `Catalog` / `Guest houses` / `Stays listed by hosts and by MyTerraBook.` Sub-nav (also on Amenities): Properties `icon/house` count 9, Amenities `icon/check` count 25, Bookings `icon/calendar` count 10, Calendar `icon/calendar`, Reviews `icon/star` count 0. Four Tab instances. Search `Search name or city…`. Filters: Type, City, Host. Columns: Property, Type, Capacity, Price / night, Host, Status, Updated. Row menu is drawn open. Pagination.

### Guest house · Edit `13:1308` — 1440×3108

Header: `Guest houses` / `Modern Glass Cottage (Blár)` / `Hella, Iceland · Host: MyTerraBook · Last saved 20 Jun 2026, 08:50`. Sticky section nav and form sections: Photos, Listing card, Stay details, Pricing & fees, Seasonal prices, House rules & check-in, Address & map, Amenities, SEO, Danger zone. More actions labeled on the frame `More (Duplicate, Delete)`. Sticky bar: `All changes saved`.

### Vehicles · All vehicles `14:1707` — 1440×1050

Header: `Catalog` / `Vehicles` / `Cars and campervans across Iceland, from MyTerraBook and partner hosts.` Three Button instances. Sub-nav: All vehicles `icon/car` count 42, Fleet units `icon/columns`, Categories `icon/file`, Features `icon/star`, Locations `icon/pin`, Rental conditions `icon/shield`. Four Tab instances. Search `Search vehicle or host…`. Filters: Category, Host, Drivetrain. View toggle (`icon/columns`, `icon/file`). Vehicle card grid (photo, status pill). This is the card view; `21:3114` is the table view of the same page.

### Vehicles · All vehicles (table view) `21:3114` — 1440×1024

Same header family (`Cars and campervans from MyTerraBook and partner hosts.`), same sub-nav, four tabs, same search and filters. Columns: Vehicle, Category, Drivetrain, Status, Review, Host / partner. Pagination.

### Vehicles · Fleet units `21:3673`

Header: `Catalog · Vehicles` / `Fleet units` / `Each unit is one physical car or camper that can be booked.` Same vehicle sub-nav. Search `Search unit…`, filter Vehicle. Columns: Vehicle unit, Order, Active.

### Vehicles · Categories `21:4101`

Header: `Catalog · Vehicles` / `Categories` / `Main categories group the sub categories shown in search.` Three Tab instances. Two tables: Main category, Description, Sub categories; and Sub category, Show in search filter.

### Vehicles · Features `21:4571`

Header: `Catalog · Vehicles` / `Features` / `Icons and labels shown on vehicle cards and listings. (Same data as “Characteristics”.)` Search `Search feature…`, filter Group. Columns: Icon, Feature name, Text next to icon, Group.

### Vehicles · Locations `22:4236`

Header: `Catalog · Vehicles` / `Locations` / `Pickup and drop-off places. Opening hours decide when out-of-hours fees apply.` Search `Search location…`, filter Destination. Columns: Location, Destination, Open, Vehicles, Tax override.

### Vehicles · Rental conditions `22:4677`

Header: `Catalog · Vehicles` / `Rental conditions` / `Short rules shown on each vehicle listing.` Search `Search condition…`. Columns: Icon, Condition, Description, Active.

### Vehicles · Edit vehicle `27:7797` — 1440×3428

Header: `Vehicles` / `Dacia Duster Extreme 4x4 2026 Model` / `Caren cars · Car · Host: Konvin Car Rental ehf. · 1 unit`. Sections: Identity, Specs, Features (groups Drivetrain & Performance, Comfort & Convenience, Safety & Driver Assistance, Technology & Connectivity, Winter & Iceland, Capacity & Practicality, Camping & Living), Photos, Description, SEO, Notes & extras, Danger zone. Sticky bar: `All changes saved`.

### Pricing screens

Shared page title **Pricing** and description `Every price setting for vehicles in one place. Amounts are in ISK.` Breadcrumb `Catalog · Vehicles` on the rules frame and `Catalog · Pricing` on the others. Two Button instances on the rules header. Section menu:

- Rates: Base prices (8), Pricing rules (6), Fares overview, Booking restrictions
- Time based: Extra hour fares (0), Hourly fares (0), Out-of-hours fees (9)
- Extras & fees: Location fees (4), Add-ons (12)
- Discounts & tax: Discount codes (2), Tax rates (3), Types of price (6)

| Frame | Extra structure |
| --- | --- |
| Pricing rules `15:1983` | Overlap warning, timeline. Columns: Name, Dates, Type, Value, Applies to |
| Out-of-hours fees `22:5100` | Search `Search fee…`, filters Host, Applies to. Columns: Fee, Hours, Applies to, Pickup, Drop-off, Max, Active |
| Location fees `22:5554` | Search `Search route…`, filters Pickup, Drop-off. Columns: Route, Fee, Per day, One way, Inverted, Tax, Active |
| Add-ons `23:5123` | Search `Search add-on…`. Columns: Add-on, Price, Charged, Max cost, Qty, Always on |
| Discount codes `23:5721` | Search `Search code…`, filters Type, Status. Columns: Code, Type, Discount, Valid, Min. order, Status |
| Tax rates & Types of price `23:6052` | Title on the header is `Tax rates`. Two tables: Name, Rate, Used by; and Price type, Attributes, Tax rate |
| Booking restrictions `23:6428` | Columns: Name, Dates, Min days, Max days, Multiplier, Active. Empty state is drawn |
| Base prices `24:5997` | Eight Tab instances. Vehicle & price type fields. Columns: Rental length, Days from, Days to, Price / day (ISK), Text. Day preview |
| Fares overview `24:6408` | Vehicles shown, rates calculator, vehicle strip. No table |
| Extra hour fares `24:6878` | Columns: Vehicle, Price type, Per extra hour. Empty state |
| Hourly fares `24:7132` | Columns: Vehicle, Price type, From, To, Total price. Empty state |

### Calendar `18:2870` — 1440×1024

Header: `Bookings` / `Calendar` / `Every vehicle unit and guest house on its own row. Click a booking to open it.` Seven Tab instances. Controls, a day timeline, and booking blocks. This single frame covers both existing calendars.

### Guest house bookings `32:9366`

Header: `Bookings` / `Guest house bookings` / `Stays booked in guest houses. Confirm or cancel directly from the list.` Four Tab instances. Search `Reference or guest…`. Filters: Property, Check-in date. Columns: Reference, Guest, Property, Stay, Total, Status.

### Change requests `32:9803`

Header: `Bookings` / `Change requests` / `Customers asking to change dates or details of a booking.` Four Tab instances. Search `Booking or customer…`, filter Type. Columns: #, Booking, Request, Customer message, Price change, Status, Created.

### Hosts `32:10090`

Header: `People` / `Hosts` / `7 hosts and partners. Manage profiles, partner API and commission.` Three Tab instances. Search `Search hosts by name…`, filter Country. Columns: Host / partner, Country, Vehicles, Stays, Partner API, Commission.

### Clients `32:10533`

Header: `People` / `Clients` / `4 registered clients and their bookings.` Search `Search name, e-mail, phone…`. Columns: Client, Phone, Car bookings, Stays, Registered.

### Listing approvals `34:10294`

Header: `Moderation` / `Listing approvals` / `Newest submissions first. Approve, request changes or reject.` Three Tab instances. Cards: Total pending, Guest houses, Vehicles. Queue with an empty state and an example submission.

### Listing reviews `34:10593`

Header: `Moderation` / `Listing reviews` / `Guest reviews shown on vehicle and guest house pages.` Three Tab instances. Search `Search guest or listing…`. Filters: Rating, Type. Columns: Guest, Rating, Type, Listing, Visible, Submitted. Empty state is drawn.

### Good to Know · Posts `34:10852`

Header: `Website` / `Good to Know` / `Travel guides and tips shown on the blog. The page header text is edited under Site content › Good to Know.` Four Tab instances. Search `Search title…`, filter Kicker. Columns: Post, Kicker, Featured, Status, Published.

### Newsletter · Subscribers `34:11310`

Header: `Marketing` / `Newsletter` / `People who signed up on the website, and the e-mails sent to them.` Sub-nav: Subscribers `icon/users` count 2, Campaigns `icon/megaphone` count 0. Cards: Active subscribers `0`, Unsubscribed `2`, Campaigns sent. Search `Search e-mail…`. Filters: Source, Status. Columns: E-mail, Status, Source, Subscribed. There is no separate Campaigns frame.

### Email

Shared header: `Marketing` / `Email` / `Every e-mail the system sends, its sender settings and history.` Sub-nav: Templates `icon/file`, SMTP settings `icon/sliders`, Send test `icon/external`, Email log `icon/database`.

| Frame | Structure |
| --- | --- |
| Templates `35:11225` | Groups Account and Orders, each with switches. Two Button instances |
| SMTP settings `35:11642` | Sections Sender and Branding. Sticky bar: `Admin notification e-mail is empty` |
| Send test `35:11977` | Sections Send a test and Preview |
| Log `35:12252` | Columns: Sent, Template, Recipient, Subject, Status, then `icon/eye` |

### Analytics

Shared header: `Marketing` / `Analytics` / `Rental reports, website visits and campaign links.` Sub-nav: Reports `icon/chart`, Statistics `icon/eye`, Tracking campaigns `icon/megaphone`.

| Frame | Structure |
| --- | --- |
| Reports `26:6789` | Five Tab instances. Cards: Booked days, Occupancy, Revenue (confirmed), Average rental. Columns: Vehicle, Units, Booked days, Occupancy, Revenue. Empty state |
| Statistics `26:7059` | Six Tab instances. Cards: Total visitors, Total bookings, Average rental length, Conversion rate, Most demanded days, Best referrers |
| Tracking campaigns `26:7330` | Columns: Campaign, Link, UTM source, UTM medium, UTM campaign, Active. Empty state |

### Settings

Shared left menu, with these icons on the shop frame: Shop & rentals `icon/car`, Prices & payments `icon/tag`, Views & layout `icon/eye`, Orders & company `icon/file`, Conditional texts `icon/file`, Payment methods `icon/tag`, Custom fields `icon/columns`, Users `icon/users`, Logged-in users `icon/shield`, Backups `icon/database`, Profile `icon/users` on the shop frame and `icon/user` on the Users frame.

Groups: **Configuration** (Shop & rentals, Prices & payments, Views & layout, Orders & company, Conditional texts), **Checkout** (Payment methods, Custom fields), **Access** (Users, Logged-in users), **System** (Backups, Profile). Header search on Shop & rentals: `Find a setting…`.

| Frame | Structure |
| --- | --- |
| Shop & rentals `16:2209` — 1440×2281 | Cards: Website status, Booking, Search (`Search & availability`), Advanced. Sticky bar: `1 required field is empty: Admin e-mail` |
| Prices & payments `36:12183` | Cards: Currency, Taxes and payments. Sticky bar: `Currency settings need a check` |
| Views & layout `36:12606` | Cards: Appearance, Colors. Sticky bar: `All changes saved` |
| Orders & company `36:12995` | Cards: Company, Order e-mails, Customer documents. Sticky bar: `All changes saved` |
| Payment methods `36:13389` | Cards: Credit / Debit Card, Pay at Pickup, Bank Transfer |
| Custom fields `38:14000` | Columns: Field, Key, Type, Required, E-mail field, Active. Row grip `icon/grip` and edit `icon/pencil`. Sample field `Flight Number` / `flight_number` |
| Users `38:14376` | Three Tab instances. Columns: User, Role, Phone, Registered |
| Logged-in users `38:14862` | Columns: User, Role, Where, Last active |
| Conditional texts `26:7561` | Columns: Name, Placement, Active. Empty state |
| Backups `26:7845` | Backup settings card. Columns: File, Type, Size, Disk, Created. Empty state |
| Profile `38:15244` | Cards: Account, Change password. Sticky save bar |

### Site content

Page picker (same on Homepage, Global chrome, and Terms), search `Find a page…`:

- Global: Global chrome
- Main pages: Homepage, About, FAQ, Contact, Become a host, Campsite map, Good to Know
- Legal: Terms, Privacy, Refund policy, Cookies, Cardholder terms
- Search & listings: Search · Campervans, Search · Cars, Search · Guesthouses, Listing · Campervan, Listing · Car, Listing · Guesthouse
- Checkout & accounts: Checkout, Login, Register, Host register
- Panels & system: User dashboard, Admin dashboard, Client panel, Host panel, Newsletter unsubscribe, Under construction

Only three of those have their own frame.

| Frame | Structure |
| --- | --- |
| Homepage `17:2593` | Header: `Website · Site content · Main pages` / `Homepage` / `11 sections · same fields as today, grouped and collapsible`. Sections: Hero, Trust strip, Destinations, What we rent, Why MyTerraBook, Hand-picked, How it works, Guesthouses, Blog bento, Host CTA, Guest houses highlight. Live preview. Sticky bar: `1 section changed: Why MyTerraBook` |
| Global chrome `41:15463` | Header: `Website · Site content · Global` / `Global chrome` / `Logo, colors, header, footer and blocks used on every page`. Sections: Branding & logo (`icon/image` pattern on other editors), Site colors, Google Maps, Top bar, Header navigation, Search form & filters, Listing cards, Footer, Newsletter block, FAQ block (layouts), SEO defaults. Sticky bar: `No unsaved changes` |
| Terms `41:15961` | Header: `Website · Site content · Legal` / `Terms` / `Hero, body and SEO for the Terms & Conditions page`. One Button instance in the page header. Sections: Hero (open, `icon/image`, `icon/chevron-down`), Body (collapsed, `icon/file`, `icon/chevron-right`), SEO (collapsed, `icon/globe`, `icon/chevron-right`). Hero contains two Field instances, one full-width Field, and a body note: `The Body section opens a full-width rich text editor, same content as today.` Sticky bar: `No unsaved changes`, two Button instances. Main region of this frame is node `41:16149` |

### Guest houses · Amenities `40:15043`

Same Guest houses header and sub-nav as Properties, with Amenities current. Groups drawn as cards: General (sample row WiFi, `amenity/WiFi`, `icon/grip`, `icon/pencil`), Bathroom & laundry, Kitchen, Outdoor & wellness.

### Mapping diagram `20:3115` — 3000×626

Not a product screen. Title `Ku shkon çdo faqe e vjetër`. Caption: `Asnjë faqe dhe asnjë opsion nuk hiqet. Sidebar-i ka 18 hyrje në vend të 50+; pjesa tjetër hapet si tab ose menu brenda faqes.` It maps the current Filament labels onto the frames above. Canvas captions near the screens (not frames): `Dashboard · Rezervimet`, `Katalogu · Guest houses, Vehicles, Pricing`, `Settings · Site content · Calendar`, `Impact Rent · të gjitha faqet`.

## Other pages in the file (not app screens)

| Frame | Node | Page | Notes | Status |
| --- | --- | --- | --- | --- |
| Foundations | `19:2` | `0:1` | Foundations board, 1440×2229 | todo |
| Brand kit | `30:13` | `0:1` | Brand board, 1600×4546 | todo |
| Icons | `2:2` | `1:2` | Icon symbols used by the UI | todo |
| Logo | `2:182` | `1:2` | Theme=On light, Theme=On dark | todo |
| Button | `3:34` | `1:2` | Primary/Secondary/Ghost/Danger × Size M/S | todo |
| Status pill | `3:50` | `1:2` | Success, Warning, Danger, Info, Neutral | todo |
| Field | `3:77` | `1:2` | Text, Select, Money, Time | todo |
| Setting toggle | `3:90` | `1:2` | On=true, On=false | todo |
| Checkbox | `3:99` | `1:2` | Checked=true, Checked=false | todo |
| Tab | `3:108` | `1:2` | State=Active, State=Default | todo |
| Nav item | `3:125` | `1:2` | State=Default, Active, Hover | todo |
| Sidebar | `4:20` | `1:2` | The shared sidebar symbol | todo |
| Topbar | `4:245` | `1:2` | The shared top bar symbol | todo |
| Ikonat · Amenities (Guest houses) | `44:493` | `44:492` | Amenity icon sheet | todo |
| Ikonat · Vehicle features & rental conditions | `47:492` | `44:492` | Feature and condition icon sheet | todo |

No hover, disabled, error, loading, or collapsed-sidebar **screen** exists beyond the component variants above and the empty states already named inside individual screens (booking restrictions, extra hour fares, hourly fares, reports, tracking campaigns, listing approvals, listing reviews, conditional texts, backups).

## In the repo with no matching frame

Do not invent screens for these. They need a decision before any UI work:

- `ScheduledTasksOverview` — no frame.
- `ModerationSettings` `moderation-settings` — no frame.
- `GuestHouseReviewsPage` `guest-reviews` — Reviews is only a sub-nav item on Guest houses. No reviews screen.
- `NewsletterCampaignResource` — Campaigns is only a sub-nav item on Newsletter. No campaigns screen.
- `SettingResource` `settings-records` — no frame of its own. Likely covered by the Settings configuration frames, not confirmed field-by-field.
- React `/admin`, `/admin/calendar`, and `/admin/embed/calendar` — not in this Figma file.
- The 26 site-content picker entries other than Homepage, Global chrome, and Terms have no own frame. Terms shows the editor pattern (Hero / Body / SEO). Homepage and Global chrome have their own section lists. The other picker pages should not be designed from guesswork.

## Confirm before Phase 2

1. Restyle the Filament `/admin` panel, and leave the React `/admin` shell as it is.
2. Sidebar icon glyphs are not named in metadata. Phase 3 should read them from the component before any icon is chosen.
3. Button and Tab labels need `get_design_context` per screen. They are omitted here on purpose.
4. Pages in “no matching frame” stay on the current UI unless you want a new frame followed as UI only.
