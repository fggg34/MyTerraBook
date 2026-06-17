import { formatDateTimeAt, parseTimeParts, toApiDateTime } from './format'

export function buildListingQuotePayload(listing, startDate, endDate, selectedAddonIds = []) {
  const car = listing?.car
  if (!car?.id || !startDate || !endDate) return null

  const priceType =
    listing.priceTypes?.find((pt) => pt.slug === 'basic')
    || car.price_types?.find((pt) => pt.slug === 'basic')
    || listing.priceTypes?.[0]
    || car.price_types?.[0]

  const pickupList = listing.pickupLocations?.length ? listing.pickupLocations : car.pickup_locations || []
  const dropoffList = listing.dropoffLocations?.length ? listing.dropoffLocations : car.dropoff_locations || []

  // Prefer a single location that allows both pickup and dropoff so the
  // preview estimate doesn't pick up a spurious one-way fee.
  const dropoffIds = new Set(dropoffList.map((loc) => loc?.id).filter(Boolean))
  const sharedLoc = pickupList.find((loc) => loc?.id && dropoffIds.has(loc.id))
  const pickupLoc = sharedLoc || pickupList[0]
  const dropoffLoc = sharedLoc || pickupLoc

  if (!priceType?.id || !pickupLoc?.id || !dropoffLoc?.id) return null

  const pickupParts = parseTimeParts(car.pickup_time_from || listing.pickupTimeWindow?.from) || { hours: 9, minutes: 0 }
  const dropoffParts = parseTimeParts(car.dropoff_time_from || listing.dropoffTimeWindow?.from) || { hours: 10, minutes: 0 }

  const pickupLocal = formatDateTimeAt(startDate, pickupParts.hours, pickupParts.minutes)
  const dropoffLocal = formatDateTimeAt(endDate, dropoffParts.hours, dropoffParts.minutes)

  if (!pickupLocal || !dropoffLocal) return null

  return {
    car_id: Number(car.id),
    price_type_id: Number(priceType.id),
    pickup_location_id: Number(pickupLoc.id),
    dropoff_location_id: Number(dropoffLoc.id),
    pickup_at: toApiDateTime(pickupLocal),
    dropoff_at: toApiDateTime(dropoffLocal),
    rental_options: (selectedAddonIds || []).map(Number).filter((id) => id > 0),
  }
}
