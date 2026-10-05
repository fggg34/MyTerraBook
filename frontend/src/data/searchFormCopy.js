/** Shared search-form and filter labels. Admin edits live in Global chrome → Search form. */
export const SEARCH_FORM_COPY = {
  pickupLabel: 'Pick-up location',
  dropoffLabel: 'Drop-off location',
  locationPlaceholder: 'Select location',
  vehicleDatesLabel: 'Pick-up → Drop-off',
  pickupDateLabel: 'Pick-up',
  dropoffDateLabel: 'Drop-off',
  cityLabel: 'City or area',
  cityPlaceholder: 'e.g. Reykjavík, Akureyri',
  cityPlaceholderShort: 'e.g. Reykjavík',
  stayDatesLabel: 'Check-in → Check-out',
  checkInLabel: 'Check-in',
  checkOutLabel: 'Check-out',
  guestsLabel: 'Guests',
  guestSingular: 'guest',
  guestPlural: 'guests',
  guestsAria: 'Number of guests',
  emptyLocationsHint: 'Pickup locations are being configured. Assign locations to vehicles in admin to enable search.',
  emptyLocationsHintShort: 'Pickup locations are being configured. Assign locations to vehicles in admin.',
  updateLabel: 'Update',
  priceChipLabel: 'Price',
  priceHeading: 'Price per {unit}',
  transmissionLabel: 'Transmission',
  anyLabel: 'Any',
  automaticLabel: 'Automatic',
  manualLabel: 'Manual',
  allFiltersLabel: 'All filters',
  filtersTitle: 'Filters',
  vehicleTypeLabel: 'Vehicle type',
  featuresLabel: 'Features',
  minSeatsLabel: 'Minimum seats',
  minSleepsLabel: 'Minimum sleeps',
  resetRangeLabel: 'Reset range',
  clearAllLabel: 'Clear all',
  clearAllChipLabel: 'Clear all ✕',
  sortPrefix: 'Sort:',
  loadingLabel: 'Loading…',
  unavailableLabel: 'Unavailable',
  loadingPlural: 'Loading {plural}…',
  loadFailedPlural: "Couldn't load {plural}",
  foundSuffix: 'found',
  editLabel: 'Edit',
  showPrefix: 'Show',
  sortRecommended: 'Recommended',
  sortPriceLow: 'Price: low to high',
  sortPriceHigh: 'Price: high to low',
  sortSeats: 'Most seats',
  sortSleeps: 'Sleeps the most',
  sortGuests: 'Most guests',
}

export function cmsText(value, fallback) {
  const text = typeof value === 'string' ? value.trim() : ''
  return text || fallback
}

export function fillTemplate(template, vars) {
  return Object.entries(vars).reduce(
    (text, [key, value]) => text.replaceAll(`{${key}}`, String(value ?? '')),
    template,
  )
}

const SORT_LABEL_KEYS = {
  rec: 'sortRecommended',
  'price-asc': 'sortPriceLow',
  'price-desc': 'sortPriceHigh',
  seats: 'sortSeats',
  sleeps: 'sortSleeps',
  guests: 'sortGuests',
}

export function applySortLabels(options, copy = {}) {
  return options.map((option) => {
    const key = SORT_LABEL_KEYS[option.id]
    if (!key) return option
    return { ...option, label: cmsText(copy[key], option.label) }
  })
}
