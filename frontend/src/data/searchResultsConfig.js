export const VEHICLE_TYPES = {
  campervan: {
    id: 'campervan',
    route: '/campervans',
    hcatMode: 'vehicle',
    categoryNames: ['Van', 'SUV'],
    breadcrumb: 'Campervans',
    unitSingular: 'van',
    unitPlural: 'vans',
    titleLead: 'Campervans for the Ring Road',
    subtitle:
      'Every van is winter-checked by our Reykjavík team, with gravel & ash cover and unlimited mileage built into the price you see.',
    loadMoreLabel: 'Show more vans',
    defaultSeats: 5,
    defaultSleeps: 2,
    defaultBags: 2,
    promo: {
      kicker: 'Also on MyTerraBook',
      title: 'Warm guesthouses along the route',
      text: 'Book a room for the nights you want a proper bed — same account, same support team.',
      cta: 'Browse guesthouses',
      href: '#guesthouse',
    },
    midbanner: {
      kicker: 'Fully covered',
      title: 'Gravel, ash & ice protection included',
      text: 'Every campervan booking includes the cover Iceland actually needs — no surprise add-ons at pickup.',
      cta: 'See what’s included',
      href: '#faq',
      image: '/images/homepage/cardcamper.jpg',
    },
  },
  car: {
    id: 'car',
    route: '/cars',
    hcatMode: 'vehicle',
    categoryNames: ['Economy', 'Compact', 'Mid-size', 'Luxury', 'Electric'],
    breadcrumb: 'Cars',
    unitSingular: 'car',
    unitPlural: 'cars',
    titleLead: 'Cars for Iceland’s roads',
    subtitle:
      'Compact runabouts to full-size 4×4s — winter tyres, unlimited mileage and local support included in every price.',
    loadMoreLabel: 'Show more cars',
    defaultSeats: 5,
    defaultSleeps: 0,
    defaultBags: 2,
    promo: {
      kicker: 'Upgrade your trip',
      title: 'Need more space? Try a campervan',
      text: 'Sleep onboard and wake up closer to the next waterfall — hundreds of vans ready near Keflavík.',
      cta: 'Browse campervans',
      href: '/campervans',
    },
    midbanner: {
      kicker: 'Local team',
      title: 'Pick up at KEF, drop off anywhere',
      text: 'One-way rentals across Iceland when you need them — we’ll confirm availability before you pay.',
      cta: 'How pick-up works',
      href: '#faq',
      image: '/images/homepage/cardcar.jpg',
    },
  },
}

export const SORT_OPTIONS = [
  { id: 'rec', label: 'Recommended' },
  { id: 'price-asc', label: 'Price: low to high' },
  { id: 'price-desc', label: 'Price: high to low' },
  { id: 'seats', label: 'Most seats' },
  { id: 'sleeps', label: 'Sleeps the most' },
]

export const QUICK_FILTERS = [
  { id: '4x4', label: '4×4', match: (car) => /suv|4x4|4wd/i.test(`${car.categoryName} ${car.name}`) },
  { id: 'auto', label: 'Automatic', match: (car) => /auto/i.test(car.transmission || '') },
  {
    id: 'winter',
    label: 'Winter-ready',
    match: (car) => /suv|van|4x4|awd|4wd/i.test(`${car.categoryName} ${car.name} ${car.fuel_type}`),
  },
]

export const PAGE_SIZE = 9
