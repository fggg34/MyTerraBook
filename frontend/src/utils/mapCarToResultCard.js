import { resolveStorageUrl } from '../api'

function formatPrice(amount) {
  const n = Number.parseFloat(amount)
  if (Number.isNaN(n)) return '€0'
  return `€${Math.round(n).toLocaleString('en-US')}`
}

function gearboxLabel(transmission) {
  const value = String(transmission || '').toLowerCase()
  if (value.includes('manual')) return 'MANUAL'
  if (value.includes('auto')) return 'AUTO'
  return String(transmission || 'AUTO').toUpperCase()
}

function fuelLabel(fuelType) {
  const value = String(fuelType || '').toLowerCase()
  if (!value || value === '—') return 'Petrol'
  return value.charAt(0).toUpperCase() + value.slice(1)
}

function dailyPrice(car) {
  const pricing = car.search_pricing
  if (pricing?.rental_subtotal && pricing?.rental_days) {
    return Number(pricing.rental_subtotal) / Number(pricing.rental_days)
  }
  return Number.parseFloat(car.base_daily_price) || 0
}

export function mapCarToResultCard(car, { searchQuery = '', config, categoryName } = {}) {
  const seats = config?.defaultSeats ?? 5
  const sleeps = config?.defaultSleeps ?? 2
  const bags = config?.defaultBags ?? 2
  const specs = [
    { type: 'gearbox', label: gearboxLabel(car.transmission) },
    { type: 'seat', label: `Seats ${seats}` },
  ]
  if (sleeps > 0) specs.push({ type: 'bed', label: `Sleeps ${sleeps}` })
  specs.push({ type: 'bag', label: `${bags} Bag${bags === 1 ? '' : 's'}` })

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
    sortSeats: seats,
    sortSleeps: sleeps,
  }
}
