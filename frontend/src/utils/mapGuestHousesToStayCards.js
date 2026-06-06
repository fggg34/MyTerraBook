import { resolveStorageUrl } from '../api'

function formatPrice(house) {
  if (house.base_price_per_night_formatted) {
    return house.base_price_per_night_formatted.replace(/\s+/g, '')
  }
  const cents = Number(house.base_price_per_night_cents)
  if (Number.isNaN(cents)) return '€0'
  return `€${Math.round(cents / 100).toLocaleString('en-US')}`
}

function typeLabel(type) {
  if (!type) return 'Stay'
  return type.charAt(0).toUpperCase() + type.slice(1)
}

export function mapGuestHousesToStayCards(houses = []) {
  return houses.map((house) => {
    const specs = [
      { type: 'bed', label: `Sleeps ${house.max_guests || 1}` },
      { type: 'room', label: `${house.bedrooms || 1} ${house.bedrooms === 1 ? 'room' : 'rooms'}` },
      { type: 'seat', label: house.city || typeLabel(house.type) },
    ]

    return {
      id: house.id,
      slug: house.slug,
      name: house.name,
      image: resolveStorageUrl(house.thumbnail) || '/images/homepage/cardhouse.jpg',
      href: `/guesthouses/${house.slug}`,
      price: formatPrice(house),
      per: 'night',
      badge: house.rating ? `${house.rating} ★` : 'Guesthouse',
      specs,
    }
  })
}
