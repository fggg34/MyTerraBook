import { resolveStorageUrl } from '../api'
import { buildVehicleCardSpecs } from './buildVehicleCardSpecs'

export function mapCarsToPickCards(cars = [], { detailBase = '/cars', priceFormatter } = {}) {
  const formatPrice = priceFormatter?.format ?? ((amount) => {
    const n = Number.parseFloat(amount)
    if (Number.isNaN(n)) return '€0'
    return `€${Math.round(n).toLocaleString('en-US')}`
  })
  const base = detailBase.replace(/\/$/, '')
  const isCampervan = base.includes('campervan')

  return cars.map((car) => {
    const specs = buildVehicleCardSpecs(car, { isCampervan })

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
