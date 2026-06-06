import { Building2, Car, MapPin } from 'lucide-react'

const ICONS = {
  default: MapPin,
  airport: Car,
  city: Building2,
}

function pickIcon(name = '') {
  const n = name.toLowerCase()
  if (n.includes('airport')) return ICONS.airport
  if (n.includes('city') || n.includes('center') || n.includes('centre')) return ICONS.city
  return ICONS.default
}

export default function RadioOptionCard({ location, selected, onSelect, priceLabel = 'Free' }) {
  const Icon = pickIcon(location.name)
  const isFree = priceLabel === 'Free' || priceLabel === '€0' || priceLabel === '€0.00'
  return (
    <label className={`opt${selected ? ' sel' : ''}`} onClick={() => onSelect(location.id)}>
      <span className="radio" />
      <span className="oic"><Icon aria-hidden /></span>
      <span className="otx">
        <span className="on">{location.name}</span>
        {location.address && <span className="od">{location.address}</span>}
      </span>
      <span className={`op${isFree ? ' free' : ''}`}>{priceLabel}</span>
    </label>
  )
}
