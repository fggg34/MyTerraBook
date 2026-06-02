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

export function mapCarsToPickCards(cars = []) {
  return cars.map((car) => ({
    id: car.id,
    name: car.name,
    image: resolveStorageUrl(car.thumbnail_url) || '/images/homepage/cardcar.jpg',
    href: `/cars/${car.id}`,
    price: formatPrice(car.base_daily_price),
    per: 'day',
    badge: 'Extras included',
    specs: [
      { type: 'gearbox', label: gearboxLabel(car.transmission) },
      { type: 'drive', label: fuelLabel(car.fuel_type) },
      {
        type: 'seat',
        label: car.units_available > 1 ? `${car.units_available} units` : 'Available',
      },
    ],
  }))
}
