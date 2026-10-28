# Frontend redesign progress

| Page | Route | Desktop | Mobile | Status | Commit | Remaining differences |
| --- | --- | --- | --- | --- | --- | --- |
| Homepage | `/` | 132-551 | 235-503 header, 235-534 footer | approved | d817441 | Country, guide, and host photos still use the cover image. Filter/sort sheets are functional, not every dropdown state. |
| Search · Campervans | `/campervans` | 162-1716 | 240-33598 | approved | 98a87e8 | On-board icons are painted (Kitchen, Heater, gearbox) because listings do not store that amenity set. Sliders, chevron, and pencil icons are the shared search icons. |
| Search · Cars | `/cars` | 172-3988 | 240-37625 | built | e801e63 | Reuses the campervan search card, so cards show a seats badge, on-board row, and gold Book button rather than the car frame’s spec chips and navy Book. Pickup reads “All destinations” until a location is chosen; the breadcrumb place stays Iceland. No dates are selected, so the strip does not show the sample 12–19 Nov range. Proof: `search-cars-1440.png`, `search-cars-1920.png`, `search-cars-390.png`. |
| Search · Guesthouses | `/guesthouses` | 173-8677 | 240-38279 | built | c68e8bc | Live stays are eight copies of one cottage, and the stored photos are campervan images. Prices stay in the guesthouse euro amount. Proof: `search-guesthouses-1440.png`, `search-guesthouses-1920.png`, `search-guesthouses-390.png`. |
| Listing · Campervan | `/campervans/:id` | 163-2337 | 240-45098 | built | 24fd385 | Live vehicle. Spec icons for gearbox, engine and drive reuse the gauge icon. Share uses the arrow icon. Insurance tiers and prepayment come from admin pricing. Pick-up map is a pin placeholder. |
| Listing · Car | `/cars/:id` | 173-5211 | 240-52742 | built | 24fd385 | Same listing component as campervans, with car copy. Screenshot pass skipped on request. |
| Listing · Guesthouse | `/guesthouses/:id` | 173-10430 | 240-53452 | built | 24fd385 | Same listing component, stay copy, no insurance card. Screenshot pass skipped on request. |
| Checkout | `/checkout` | 173-4904 | 240-29854 | built | ca78d2c | Existing 4-step booking, quote, promo and payment stay in place. New checkout header (logo, stepper, help phone) and summary card styling. Screenshot pass skipped on request. |
| Login, register, forgot, reset | `/login` `/register` `/forgot-password` `/reset-password` | 172-3749 | 240-51767 | built | 4b95f22 | Centered card on the cream page. Account menu sheet not rebuilt. Screenshot pass skipped on request. |
| About, FAQ, contact, legal, guides, campsite, coming soon, unsubscribe | existing routes | mixed | mixed | built | pending | Cream page shell and type. Page-specific Figma sections are not rebuilt block by block. Cookie banner unchanged. Screenshot pass skipped on request. |
