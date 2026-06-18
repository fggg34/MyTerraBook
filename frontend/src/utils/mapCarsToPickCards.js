import { resolveStorageUrl } from '../api'
import { buildVehicleCardSpecs } from './buildVehicleCardSpecs'

export function mapCarsToPickCards(cars = [], { detailBase = '/cars', priceFormatter } = {}) {
  const formatPrice = (car) => {
    const cents = Number(car.base_daily_price_cents)
    if (priceFormatter?.formatCents && Number.isFinite(cents) && cents > 0) {
      return priceFormatter.formatCents(cents)
    }
    if (priceFormatter?.format) {
      return priceFormatter.format(car.base_daily_price)
    }
    const n = Number.parseFloat(car.base_daily_price)
    if (Number.isNaN(n)) return '€0'
    return `€${Math.round(n).toLocaleString('en-US')}`
  }
  const base = detailBase.replace(/\/$/, '')
  const isCampervan = base.includes('campervan')

  return cars.map((car) => {
    const specs = buildVehicleCardSpecs(car, { isCampervan })

    return {
      id: car.id,
      name: car.name,
      image: resolveStorageUrl(car.thumbnail_url || car.main_image_path) || '/images/homepage/cardcar.jpg',
      href: `${base}/${car.id}`,
      price: formatPrice(car),
      per: 'day',
      badge: 'Extras included',
      specs,
    }
  })
}
