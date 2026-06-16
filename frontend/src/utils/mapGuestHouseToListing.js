import { resolveStorageUrl } from '../api'
import { LISTING_TYPES } from '../data/listingConfig'
import { mapApiListingReviews } from './mapListingReviews'
import { mapCarToListing } from './mapCarToListing'
import { buildGoogleMapsUrl, formatLocationLine } from './parseGooglePlace'

function formatPriceCents(cents, fallback = '89') {
  if (cents == null) return fallback
  const n = Number(cents)
  if (Number.isNaN(n)) return fallback
  return String(Math.round(n / 100))
}

function formatPriceDisplay(house) {
  if (house.base_price_per_night_formatted) {
    return house.base_price_per_night_formatted.replace(/\s+/g, '').replace('€', '')
  }
  return formatPriceCents(house.base_price_per_night_cents)
}

function typeLabel(type) {
  if (!type) return 'Stay'
  return type.charAt(0).toUpperCase() + type.slice(1)
}

function policyLabel(policy) {
  if (!policy) return 'Cancellation policy'
  return `${policy.charAt(0).toUpperCase()}${policy.slice(1)} cancellation`
}

function formatTime(value) {
  if (!value) return null
  const str = String(value)
  return str.length >= 5 ? str.slice(0, 5) : str
}

function buildDetailSpecs(house) {
  const specs = []
  if (house.max_guests) specs.push({ label: `Sleeps ${house.max_guests}`, icon: 'sleeps' })
  if (house.bedrooms) {
    specs.push({
      label: `${house.bedrooms} bedroom${house.bedrooms === 1 ? '' : 's'}`,
      icon: 'bedroom',
    })
  }
  if (house.bathrooms) {
    specs.push({
      label: `${house.bathrooms} bathroom${house.bathrooms === 1 ? '' : 's'}`,
      icon: 'bathroom',
    })
  }
  if (house.type) specs.push({ label: typeLabel(house.type), icon: 'type' })
  if (house.city) specs.push({ label: house.city, icon: 'city' })
  return specs.length ? specs : [{ label: 'Guesthouse stay', icon: 'stay' }]
}

function buildAmenities(house) {
  const flat = (house.amenities || []).flatMap((group) =>
    (group.items || [])
      .filter((item) => item.name)
      .map((item) => ({ name: item.name, icon: item.icon })),
  )
  if (!flat.length) return []
  return flat.map((item, index) => ({ ...item, featured: index < 4 }))
}

function buildConditions(house) {
  const conditions = []
  const checkIn = formatTime(house.check_in_time)
  const checkOut = formatTime(house.check_out_time)
  if (checkIn) {
    conditions.push({
      title: `Check-in from ${checkIn}`,
      desc: 'Contact the host if you need an earlier arrival.',
    })
  }
  if (checkOut) {
    conditions.push({
      title: `Check-out by ${checkOut}`,
      desc: 'Late check-out may be available on request.',
    })
  }
  if (house.min_nights) {
    conditions.push({
      title: `Minimum ${house.min_nights} night${house.min_nights === 1 ? '' : 's'}`,
      desc: 'Shorter stays may not be available in peak season.',
    })
  }
  if (house.max_nights) {
    conditions.push({
      title: `Maximum ${house.max_nights} nights`,
      desc: 'Message the host for longer stays.',
    })
  }
  if (house.max_guests) {
    conditions.push({
      title: `Up to ${house.max_guests} guests`,
      desc: 'Includes all registered guests staying overnight.',
    })
  }
  if (house.cancellation_policy) {
    conditions.push({
      title: policyLabel(house.cancellation_policy),
      desc: 'Refund terms depend on how close to check-in you cancel.',
    })
  }
  return conditions
}

function buildSleeping(house) {
  const maxGuests = house.max_guests || 1
  const bedrooms = house.bedrooms || 1
  const beds = house.beds || bedrooms
  const bedsList = []

  if (bedrooms > 0) {
    bedsList.push({
      title: `${bedrooms} bedroom${bedrooms === 1 ? '' : 's'}`,
      text: `${beds} bed${beds === 1 ? '' : 's'} across the property.`,
      dim: `Up to ${maxGuests} guests`,
      image: resolveStorageUrl(house.thumbnail) || '/images/homepage/cardhouse.jpg',
    })
  }

  if (house.bathrooms) {
    bedsList.push({
      title: 'Bathrooms',
      text: `${house.bathrooms} bathroom${house.bathrooms === 1 ? '' : 's'} available for guests.`,
      dim: 'Fresh linen included',
      image: '/images/homepage/cardhouse.jpg',
    })
  }

  if (!bedsList.length) {
    bedsList.push({
      title: 'Sleeping arrangements',
      text: house.short_description || 'Comfortable beds for your stay.',
      dim: `Sleeps ${maxGuests}`,
      image: resolveStorageUrl(house.thumbnail) || '/images/homepage/cardhouse.jpg',
    })
  }

  return {
    kicker: `Sleeps up to ${maxGuests} guest${maxGuests === 1 ? '' : 's'}`,
    beds: bedsList,
  }
}

function buildRating(house, reviews) {
  const count = reviews?.length ?? house.listing_reviews?.length ?? 0
  const score = house.rating != null ? String(house.rating) : count > 0 ? '5.0' : ','
  return {
    score,
    label: count > 0 ? 'Guest rating' : 'New listing',
    reviewCount: count,
    reviewLinkLabel: count === 1 ? '1 review' : `${count} reviews`,
  }
}

/** Maps guest-house API detail payload to the shared listing view model. */
export function mapGuestHouseToListing(house, listingReviews, priceFormatter) {
  const listingType = 'guesthouse'
  const images = (house.images || []).map((img, i) => ({
    url: resolveStorageUrl(img.path || house.thumbnail),
    alt: img.caption || `${house.name}, photo ${i + 1}`,
  }))
  if (!images.length && house.thumbnail) {
    images.push({ url: resolveStorageUrl(house.thumbnail), alt: house.name })
  }

  const carShaped = {
    id: house.id,
    name: house.name,
    slug: house.slug,
    description: house.description || house.short_description || '',
    category: { name: typeLabel(house.type) },
    main_image_path: house.thumbnail,
    details_image_paths: (house.images || []).map((i) => i.path).filter(Boolean),
    transmission: null,
    fuel_type: null,
    units_available: house.max_guests || 1,
    price_types: [
      {
        id: 1,
        from_price_per_day: formatPriceDisplay(house),
      },
    ],
    characteristics: buildAmenities(house).map((a) => ({ name: a.name, display_text: a.name })),
    rental_options: [],
  }

  const base = mapCarToListing(carShaped, listingType, listingReviews, priceFormatter)
  const reviews = listingReviews ?? mapApiListingReviews(house.listing_reviews)
  const amenities = buildAmenities(house)
  const conditions = buildConditions(house)
  const sleeping = buildSleeping(house)

  const desc = house.description || house.short_description || ''
  const shortDesc = desc.length > 280 ? desc.slice(0, 280).trim() : desc
  const moreDesc = desc.length > 280 ? desc.slice(280).trim() : ''

  const location = {
    address: house.address || '',
    city: house.city || '',
    country: house.country || '',
    latitude: house.latitude ?? null,
    longitude: house.longitude ?? null,
    formattedLine: formatLocationLine(house),
    mapsUrl: buildGoogleMapsUrl(house),
  }

  return {
    ...base,
    car: house,
    slug: house.slug,
    metaTitle: house.meta_title || house.name,
    metaDescription: house.meta_description || house.short_description || house.description,
    ogImage: house.og_image || house.thumbnail,
    categoryName: typeLabel(house.type),
    location,
    images: images.length ? images : base.images,
    photoCount: Math.max(images.length, 1),
    detailSpecs: buildDetailSpecs(house),
    amenities: amenities.length ? amenities : base.amenities,
    conditions: conditions.length ? conditions : base.conditions,
    sleeping,
    addons: [],
    priceFrom: formatPriceDisplay(house),
    description: { short: shortDesc || 'Description coming soon.', more: moreDesc },
    rating: buildRating(house, reviews),
    reviews,
    guestPhotoUrls: [],
  }
}
