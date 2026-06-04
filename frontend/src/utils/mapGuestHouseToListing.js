import { resolveStorageUrl } from '../api'
import { LISTING_TYPES } from '../data/listingConfig'
import { mapApiListingReviews } from './mapListingReviews'
import { mapCarToListing } from './mapCarToListing'

function formatPriceCents(cents, fallback = '89') {
  if (cents == null) return fallback
  const n = Number(cents)
  if (Number.isNaN(n)) return fallback
  return String(Math.round(n / 100))
}

/** Maps guest-house API detail payload to the shared listing view model. */
export function mapGuestHouseToListing(house, listingReviews) {
  const listingType = 'guesthouse'
  const typeConfig = LISTING_TYPES.guesthouse
  const images = (house.images || []).map((img, i) => ({
    url: resolveStorageUrl(img.path || house.thumbnail),
    alt: img.caption || `${house.name} — photo ${i + 1}`,
  }))
  if (!images.length && house.thumbnail) {
    images.push({ url: resolveStorageUrl(house.thumbnail), alt: house.name })
  }

  const carShaped = {
    id: house.id,
    name: house.name,
    slug: house.slug,
    description: house.description || house.short_description || '',
    category: { name: 'Guesthouse' },
    main_image_path: house.thumbnail,
    details_image_paths: (house.images || []).map((i) => i.path).filter(Boolean),
    transmission: null,
    fuel_type: null,
    units_available: house.max_guests || 1,
    price_types: [
      {
        id: 1,
        from_price_per_day: formatPriceCents(house.base_price_per_night_cents),
      },
    ],
    characteristics: [],
    rental_options: [],
  }

  const base = mapCarToListing(carShaped, listingType)
  const reviews = listingReviews ?? mapApiListingReviews(house.listing_reviews)

  return {
    ...base,
    car: house,
    slug: house.slug,
    images: images.length ? images : base.images,
    photoCount: Math.max(images.length, 1),
    reviews,
    guestPhotoUrls: [],
  }
}
