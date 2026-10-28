# Frontend redesign progress

| Page | Route | Desktop | Mobile | Status | Commit | Remaining differences |
| --- | --- | --- | --- | --- | --- | --- |
| Homepage | `/` | 132-551 | 235-503 header, 235-534 footer | approved | d817441 | Country, guide, and host photos still use the cover image. Filter/sort sheets are functional, not every dropdown state. |
| Search · Campervans | `/campervans` | 162-1716 | 240-33598 | approved | 98a87e8 | On-board icons are painted (Kitchen, Heater, gearbox) because listings do not store that amenity set. Sliders, chevron, and pencil icons are the shared search icons. |
| Search · Cars | `/cars` | 172-3988 | 240-37625 | built | e801e63 | Reuses the campervan search card, so cards show a seats badge, on-board row, and gold Book button rather than the car frame’s spec chips and navy Book. Pickup reads “All destinations” until a location is chosen; the breadcrumb place stays Iceland. No dates are selected, so the strip does not show the sample 12–19 Nov range. Proof: `search-cars-1440.png`, `search-cars-1920.png`, `search-cars-390.png`. |
| Search · Guesthouses | `/guesthouses` | 173-8677 | 240-38279 | built | pending | Live stays are eight copies of one cottage, and the stored photos are campervan images. Prices stay in the guesthouse euro amount. Proof: `search-guesthouses-1440.png`, `search-guesthouses-1920.png`, `search-guesthouses-390.png`. |
