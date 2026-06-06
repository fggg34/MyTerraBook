import { resolveStorageUrl } from '../api'

function formatPrice(house) {
  if (house.base_price_per_night_formatted) {
    return house.base_price_per_night_formatted.replace(/\s+/g, '')
  }
  const cents = Number(house.base_price_per_night_cents)
  if (Number.isNaN(cents)) return '€0'
  return `€${Math.round(cents / 100).toLocaleString('en-US')}`
}

function nightlyPrice(house) {
  const cents = Number(house.base_price_per_night_cents)
  if (!Number.isNaN(cents) && cents > 0) return cents / 100
  const formatted = house.base_price_per_night_formatted
  if (formatted) {
    const n = Number.parseFloat(String(formatted).replace(/[^\d.]/g, ''))
    if (!Number.isNaN(n)) return n
  }
  return 0
}

function typeLabel(type) {
  if (!type) return 'Stay'
  return type.charAt(0).toUpperCase() + type.slice(1)
}

export function mapGuestHouseToResultCard(house, { searchQuery = '' } = {}) {
  const href = `/guesthouses/${house.slug}${searchQuery ? `?${searchQuery}` : ''}`
  const specs = [
    { type: 'bed', label: `Guests ${house.max_guests || 1}` },
    { type: 'room', label: `${house.bedrooms || 1} bedroom${house.bedrooms === 1 ? '' : 's'}` },
    {
      type: 'bath',
      label: `${house.bathrooms || 1} bath${house.bathrooms === 1 ? '' : 's'}`,
    },
    { type: 'seat', label: house.city || typeLabel(house.type) },
  ]

  return {
    id: house.id,
    slug: house.slug,
    name: house.name,
    houseType: house.type,
    image: resolveStorageUrl(house.thumbnail) || '/images/homepage/cardhouse.jpg',
    href,
    price: formatPrice(house),
    per: 'night',
    badge: house.rating ? `${house.rating} ★` : 'Guesthouse',
    specs,
    sortPrice: nightlyPrice(house),
    sortGuests: house.max_guests || 0,
    city: house.city,
  }
}
