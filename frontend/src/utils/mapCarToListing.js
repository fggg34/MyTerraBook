import { resolveStorageUrl } from '../api'
import { LISTING_TYPES } from '../data/listingConfig'
import { mapApiListingReviews } from './mapListingReviews'

const FALLBACK_IMAGES = {
  campervan: ['/images/homepage/cardcamper.jpg', '/images/homepage/hero-van.jpg'],
  car: ['/images/homepage/cardcar.jpg', '/images/homepage/hero-van.jpg'],
  guesthouse: ['/images/homepage/cardhouse.jpg', '/images/homepage/cardcamper.jpg'],
}

function buildImages(car, listingType) {
  const paths = [car.main_image_path, ...(car.details_image_paths || [])].filter(Boolean)
  const urls = paths.map((p) => resolveStorageUrl(p)).filter(Boolean)
  const fallbacks = FALLBACK_IMAGES[listingType] || FALLBACK_IMAGES.car
  const merged = [...urls, ...fallbacks].slice(0, 5)
  return merged.map((url, i) => ({
    url,
    alt: i === 0 ? `${car.name} — main photo` : `${car.name} — photo ${i + 1}`,
  }))
}

function buildDetailSpecs(car) {
  const specs = []
  if (car.transmission && car.transmission !== '—') specs.push({ label: car.transmission })
  if (car.fuel_type && car.fuel_type !== '—') specs.push({ label: car.fuel_type })
  if (car.seats != null) specs.push({ label: `${car.seats} seats` })
  if (car.sleeps != null) specs.push({ label: `Sleeps ${car.sleeps}` })
  if (car.bags != null) specs.push({ label: `${car.bags} bags` })
  if (car.units_available != null) specs.push({ label: `${car.units_available} available` })
  return specs
}

function buildAmenities(car) {
  return (car.characteristics || []).map((c, i) => ({
    name: c.display_text || c.name,
    featured: i < 4,
  }))
}

function buildAddons(car, priceFormatter) {
  const format = priceFormatter?.format ?? ((v) => String(v))
  return (car.rental_options || []).map((opt) => ({
    name: opt.name,
    sub: opt.is_daily_cost ? 'Per day' : 'One-time',
    price: opt.cost ? format(Number.parseFloat(opt.cost)) : '—',
    free: false,
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
  const priceFrom = priceFromCents != null
    ? format(Number(priceFromCents) / 100)
    : priceFromDecimal != null
      ? format(Number.parseFloat(priceFromDecimal))
      : null

  const desc = car.description || ''
  const shortDesc = desc.length > 280 ? desc.slice(0, 280).trim() : desc
  const moreDesc = desc.length > 280 ? desc.slice(280).trim() : ''
  const reviews = listingReviewsOverride ?? mapApiListingReviews(car.listing_reviews)

  return {
    listingType,
    typeConfig,
    car,
    id: car.id,
    name: car.name,
    title: car.name,
    categoryName: car.category?.name || typeConfig.id,
    images: buildImages(car, listingType),
    photoCount: Math.max(buildImages(car, listingType).length, 1),
    owner: buildOwner(car, reviews),
    rating: buildRating(reviews),
    detailSpecs: buildDetailSpecs(car),
    description: { short: shortDesc || 'Description coming soon.', more: moreDesc },
    amenities: buildAmenities(car),
    conditions: [],
    sleeping: null,
    addons: buildAddons(car, priceFormatter),
    pickupLocations: car.pickup_locations || [],
    dropoffLocations: car.dropoff_locations || [],
    priceFrom,
    priceTypes: car.price_types || [],
    reviews,
    reviewCategories: [],
    guestPhotoUrls: [],
  }
}
