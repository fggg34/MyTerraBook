import { resolveStorageUrl } from '../api'
import { LISTING_TYPES } from '../data/listingConfig'
import { mapApiListingReviews } from './mapListingReviews'
import { driveLabel } from './buildVehicleCardSpecs'

const FALLBACK_IMAGES = {
  campervan: ['/images/homepage/cardcamper.jpg', '/images/homepage/hero-van.jpg'],
  car: ['/images/homepage/cardcar.jpg', '/images/homepage/hero-van.jpg'],
  guesthouse: ['/images/homepage/cardhouse.jpg', '/images/homepage/cardcamper.jpg'],
}

function buildImages(car, listingType) {
  const detailPaths = (car.details_image_paths || []).filter(Boolean)
  const urls = detailPaths.map((p) => resolveStorageUrl(p)).filter(Boolean)
  const fallbacks = FALLBACK_IMAGES[listingType] || FALLBACK_IMAGES.car
  const merged = urls.length > 0 ? urls.slice(0, 5) : [...fallbacks].slice(0, 5)

  return merged.map((url, i) => ({
    url,
    alt: `${car.name}, photo ${i + 1}`,
  }))
}

function formatSpecLabel(value) {
  const text = String(value || '').trim()
  if (!text || text === '-') return null
  return text.charAt(0).toUpperCase() + text.slice(1)
}

function buildDetailSpecs(car) {
  const specs = []
  const transmission = formatSpecLabel(car.transmission)
  const fuel = formatSpecLabel(car.fuel_type)
  const drive = car.drive_type ? driveLabel(car.drive_type) : null
  if (transmission) specs.push({ label: transmission, icon: 'gearbox' })
  if (fuel) specs.push({ label: fuel, icon: 'fuel' })
  if (drive) specs.push({ label: drive, icon: car.drive_type || 'drive' })
  if (car.seats != null) specs.push({ label: `${car.seats} seats`, icon: 'seats' })
  if (car.sleeps != null && car.sleeps > 0) specs.push({ label: `Sleeps ${car.sleeps}`, icon: 'sleeps' })
  if (car.bags != null) specs.push({ label: `${car.bags} bags`, icon: 'bags' })
  if (car.units_available != null) {
    const n = Number(car.units_available)
    specs.push({ label: `${n} unit${n === 1 ? '' : 's'} available`, icon: 'units' })
  }
  return specs
}

function buildAmenities(car) {
  return (car.characteristics || []).map((c, i) => ({
    name: c.display_text || c.name,
    icon: c.icon,
    iconUrl: c.icon_url || null,
    featured: i < 4,
  }))
}

function buildConditions(car) {
  return (car.rental_conditions || []).map((item) => ({
    title: item.title,
    desc: item.description || '',
    icon: item.icon || null,
  }))
}

function buildAddons(car, priceFormatter) {
  const format = priceFormatter?.format ?? ((v) => String(v))
  return (car.rental_options || []).map((opt) => ({
    id: opt.id,
    name: opt.name,
    icon: opt.icon,
    iconUrl: opt.icon_url || null,
    description: opt.description || '',
    sub: opt.is_daily_cost ? 'Per day' : 'One-time',
    price: opt.cost ? format(Number.parseFloat(opt.cost)) : ',',
    cost_cents: opt.cost_cents ?? 0,
    is_daily_cost: !!opt.is_daily_cost,
    free: (opt.cost_cents ?? 0) === 0,
  }))
}

function buildOwner(car, reviews = []) {
  if (!car.host?.name) return null
  const name = car.host.name
  return {
    name,
    initial: (name.trim()[0] || 'H').toUpperCase(),
    tripsLabel: car.host.member_since ? `Member since ${car.host.member_since.slice(0, 4)}` : 'Verified host',
    reviewsLabel: reviews.length ? `${reviews.length} review${reviews.length === 1 ? '' : 's'}` : 'New host',
    badge: null,
  }
}

function buildRating(reviews = []) {
  if (!reviews.length) return null
  const scores = reviews.map((r) => Number(r.score)).filter((n) => !Number.isNaN(n))
  if (!scores.length) return null
  const avg = scores.reduce((a, b) => a + b, 0) / scores.length
  const score = avg.toFixed(1)
  return {
    score,
    label: avg >= 4.8 ? 'Excellent' : avg >= 4.5 ? 'Great' : 'Good',
    reviewCount: reviews.length,
    reviewLinkLabel: `${reviews.length} review${reviews.length === 1 ? '' : 's'}`,
  }
}

export function mapCarToListing(car, listingType = 'campervan', listingReviewsOverride, priceFormatter) {
  const typeConfig = LISTING_TYPES[listingType] || LISTING_TYPES.campervan
  const priceType = car.price_types?.[0]
  const format = priceFormatter?.format ?? ((v) => String(Math.round(Number(v))))
  const priceFromCents = priceType?.from_price_per_day_cents
  const priceFromDecimal = priceType?.from_price_per_day
  const priceFromAmount =
    priceFromCents != null
      ? Number(priceFromCents) / 100
      : priceFromDecimal != null
        ? Number.parseFloat(priceFromDecimal)
        : null
  const priceFrom =
    priceFromAmount != null && !Number.isNaN(priceFromAmount) ? format(priceFromAmount) : null

  const desc = car.description || ''
  const shortDesc = desc.length > 280 ? desc.slice(0, 280).trim() : desc
  const moreDesc = desc.length > 280 ? desc.slice(280).trim() : ''
  const reviews = listingReviewsOverride ?? mapApiListingReviews(car.listing_reviews)

  const images = buildImages(car, listingType)

  return {
    listingType,
    typeConfig,
    car,
    id: car.id,
    name: car.name,
    title: car.name,
    categoryName: car.category?.name || typeConfig.id,
    images,
    photoCount: Math.max(images.length, 1),
    owner: buildOwner(car, reviews),
    rating: buildRating(reviews),
    detailSpecs: buildDetailSpecs(car),
    description: { short: shortDesc || 'Description coming soon.', more: moreDesc },
    amenities: buildAmenities(car),
    conditions: buildConditions(car),
    sleeping: null,
    addons: buildAddons(car, priceFormatter),
    pickupLocations: car.pickup_locations || [],
    dropoffLocations: car.dropoff_locations || [],
    pickupTimeWindow: car.pickup_time_from && car.pickup_time_to
      ? { from: car.pickup_time_from, to: car.pickup_time_to }
      : null,
    dropoffTimeWindow: car.dropoff_time_from && car.dropoff_time_to
      ? { from: car.dropoff_time_from, to: car.dropoff_time_to }
      : null,
    priceFrom,
    priceFromAmount: priceFromAmount != null && !Number.isNaN(priceFromAmount) ? priceFromAmount : null,
    priceTypes: car.price_types || [],
    reviews,
    reviewCategories: [],
    guestPhotoUrls: [],
  }
}
