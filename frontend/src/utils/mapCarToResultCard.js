import { resolveStorageUrl } from '../api'

function gearboxLabel(transmission) {
  const value = String(transmission || '').toLowerCase()
  if (value.includes('manual')) return 'MANUAL'
  if (value.includes('auto')) return 'AUTO'
  return String(transmission || 'AUTO').toUpperCase()
}

function dailyPrice(car) {
  const pricing = car.search_pricing
  if (pricing?.rental_subtotal && pricing?.rental_days) {
    return Number(pricing.rental_subtotal) / Number(pricing.rental_days)
  }
  return Number.parseFloat(car.base_daily_price) || 0
}

export function mapCarToResultCard(car, { searchQuery = '', config, categoryName, priceFormatter } = {}) {
  const formatPrice = priceFormatter?.format ?? ((amount) => `€${Math.round(amount).toLocaleString('en-US')}`)
  const seats = car.seats ?? config?.defaultSeats ?? null
  const sleeps = car.sleeps ?? config?.defaultSleeps ?? null
  const bags = car.bags ?? config?.defaultBags ?? null
  const specs = [{ type: 'gearbox', label: gearboxLabel(car.transmission) }]
  if (seats != null) specs.push({ type: 'seat', label: `Seats ${seats}` })
  if (sleeps != null && sleeps > 0) specs.push({ type: 'bed', label: `Sleeps ${sleeps}` })
  if (bags != null) specs.push({ type: 'bag', label: `${bags} Bag${bags === 1 ? '' : 's'}` })

  const detailBase = config?.route || '/cars'
  const href = `${detailBase}/${car.id}${searchQuery ? `?${searchQuery}` : ''}`

  return {
    id: car.id,
    name: car.name,
    image: resolveStorageUrl(car.thumbnail_url || car.main_image_path) || '/images/homepage/cardcar.jpg',
    href,
    price: formatPrice(dailyPrice(car)),
    per: 'day',
    badge: car.search_pricing?.has_special_discount ? 'Special price' : 'Extras included',
    specs,
    transmission: car.transmission,
    fuel_type: car.fuel_type,
    categoryName: categoryName || car.categoryName,
    sortPrice: dailyPrice(car),
    sortSeats: seats ?? 0,
    sortSleeps: sleeps ?? 0,
  }
}
