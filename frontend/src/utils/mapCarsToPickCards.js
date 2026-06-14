import { resolveStorageUrl } from '../api'

function gearboxLabel(transmission) {
  const value = String(transmission || '').toLowerCase()
  if (value.includes('manual')) return 'MANUAL'
  if (value.includes('auto')) return 'AUTO'
  return String(transmission || 'AUTO').toUpperCase()
}

function fuelLabel(fuelType) {
  const value = String(fuelType || '').toLowerCase()
  if (!value || value === '-') return 'Petrol'
  return value.charAt(0).toUpperCase() + value.slice(1)
}

export function mapCarsToPickCards(cars = [], { detailBase = '/cars', priceFormatter } = {}) {
  const formatPrice = priceFormatter?.format ?? ((amount) => {
    const n = Number.parseFloat(amount)
    if (Number.isNaN(n)) return '€0'
    return `€${Math.round(n).toLocaleString('en-US')}`
  })
  const base = detailBase.replace(/\/$/, '')
  return cars.map((car) => {
    const specs = [{ type: 'gearbox', label: gearboxLabel(car.transmission) }, { type: 'drive', label: fuelLabel(car.fuel_type) }]
    if (car.seats != null) specs.push({ type: 'seat', label: `Seats ${car.seats}` })
    else if (car.units_available > 1) specs.push({ type: 'seat', label: `${car.units_available} units` })
    if (car.sleeps != null && car.sleeps > 0) specs.push({ type: 'bed', label: `Sleeps ${car.sleeps}` })

    return {
      id: car.id,
      name: car.name,
      image: resolveStorageUrl(car.thumbnail_url || car.main_image_path) || '/images/homepage/cardcar.jpg',
      href: `${base}/${car.id}`,
      price: formatPrice(car.base_daily_price),
      per: 'day',
      badge: 'Extras included',
      specs,
    }
  })
}
