import { buildVehicleCardSpecs, gearboxLabel } from './buildVehicleCardSpecs'
import { resolveStorageUrl } from '../api'

export function partnerInitials(name) {
  const parts = String(name || '').trim().split(/\s+/).filter(Boolean).slice(0, 2)
  if (!parts.length) return ''
  return parts.map((part) => part[0]).join('').toUpperCase()
}

export function mapPartner(car) {
  const partner = car?.partner
  if (!partner?.id || !partner?.name) return null
  return {
    id: partner.id,
    name: partner.name,
    logo: resolveStorageUrl(partner.logo_url) || '',
  }
}

function dailyPrice(car) {
  const pricing = car.search_pricing
  if (pricing?.rental_subtotal && pricing?.rental_days) {
    return Number(pricing.rental_subtotal) / Number(pricing.rental_days)
  }
  return Number.parseFloat(car.base_daily_price) || 0
}

export function mapCarToResultCard(car, { searchQuery = '', config, categoryName, priceFormatter, vehicleType } = {}) {
  const formatPrice = priceFormatter?.format ?? ((amount) => `€${Math.round(amount).toLocaleString('en-US')}`)
  const isCampervan = vehicleType === 'campervan' || car.main_category_slug === 'campervan'
  const specs = buildVehicleCardSpecs(car, { isCampervan })

  const detailBase = config?.route || '/cars'
  const href = `${detailBase}/${car.id}${searchQuery ? `?${searchQuery}` : ''}`

  return {
    id: car.id,
    name: car.name,
    partner: mapPartner(car),
    image: resolveStorageUrl(car.thumbnail_url || car.main_image_path) || '/images/homepage/cardcar.jpg',
    href,
    price: formatPrice(dailyPrice(car)),
    per: 'day',
    badge: car.search_pricing?.has_special_discount ? 'Special price' : 'Extras included',
    specs,
    transmission: car.transmission,
    fuel_type: car.fuel_type,
    drive_type: car.drive_type,
    categoryName: categoryName || car.categoryName,
    sortPrice: dailyPrice(car),
    sortSeats: car.seats ?? 0,
    sortSleeps: car.sleeps ?? 0,
  }
}

export { gearboxLabel }
