import { formatDateTimeAt, parseTimeParts, toApiDateTime } from './format'

export function buildListingQuotePayload(listing, startDate, endDate, selectedAddonIds = []) {
  const car = listing?.car
  if (!car?.id || !startDate || !endDate) return null

  const priceType =
    listing.priceTypes?.find((pt) => pt.slug === 'basic')
    || car.price_types?.find((pt) => pt.slug === 'basic')
    || listing.priceTypes?.[0]
    || car.price_types?.[0]

  const pickupLoc = listing.pickupLocations?.[0] || car.pickup_locations?.[0]
  const dropoffLoc = listing.dropoffLocations?.[0] || car.dropoff_locations?.[0]

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
