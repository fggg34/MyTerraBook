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
  },
  car: {
    id: 'car',
    route: '/cars',
    hcatMode: 'vehicle',
    categoryNames: ['Economy', 'Compact', 'Mid-size', 'Luxury', 'Electric', 'SUV'],
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
  },
  guesthouse: {
    id: 'guesthouse',
    route: '/guesthouses',
    hcatMode: 'guesthouse',
    categoryNames: [],
    breadcrumb: 'Guesthouses',
    unitSingular: 'stay',
    unitPlural: 'stays',
    titleLead: 'Guesthouses along the route',
    subtitle:
      'Hand-picked stays spaced a comfortable day’s drive apart — book a warm bed when you want a night off the road.',
    loadMoreLabel: 'Show more stays',
    introLocationDefault: 'Iceland',
  },
}

export const SORT_OPTIONS = [
  { id: 'rec', label: 'Recommended' },
  { id: 'price-asc', label: 'Price: low to high' },
  { id: 'price-desc', label: 'Price: high to low' },
  { id: 'seats', label: 'Most seats' },
  { id: 'sleeps', label: 'Sleeps the most' },
]

export const GUESTHOUSE_SORT_OPTIONS = [
  { id: 'rec', label: 'Recommended' },
  { id: 'price-asc', label: 'Price: low to high' },
  { id: 'price-desc', label: 'Price: high to low' },
  { id: 'guests', label: 'Most guests' },
]

export const PAGE_SIZE = 9
