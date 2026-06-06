import {
  Armchair,
  BedDouble,
  ChefHat,
  Sparkles,
  Wifi,
} from 'lucide-react'
import { formatCurrencyFromCents } from '../../utils/format'

function addonIcon(name = '') {
  const n = name.toLowerCase()
  if (n.includes('chair') || n.includes('table') || n.includes('camping')) return Armchair
  if (n.includes('bed') || n.includes('bedding') || n.includes('linen')) return BedDouble
  if (n.includes('kitchen') || n.includes('gps')) return ChefHat
  if (n.includes('wifi') || n.includes('wi-fi') || n.includes('hotspot')) return Wifi
  if (n.includes('clean')) return Sparkles
  return Armchair
}

export default function AddonRow({ option, selected, nights, onToggle }) {
  const unitCents = option.cost_cents || 0
  const totalCents = option.is_daily_cost ? unitCents * nights : unitCents
  const price = formatCurrencyFromCents(totalCents)
  const unitLabel = option.is_daily_cost ? `for trip` : 'per trip'
  const Icon = addonIcon(option.name)

  const description = option.is_daily_cost && unitCents
    ? `${option.description || ''}${option.description ? ' · ' : ''}${formatCurrencyFromCents(unitCents)} / day × ${nights}`
    : option.description

  return (
    <div className={`addon-row${selected ? ' on' : ''}`}>
      <span className="aic"><Icon aria-hidden /></span>
      <span className="atx">
        <span className="an">{option.name}</span>
        {description && <span className="ad">{description}</span>}
      </span>
      <span className="apr">
        {price}
        <small>{unitLabel}</small>
      </span>
      <button type="button" className="add-toggle" onClick={() => onToggle(option.id)}>
        {selected ? 'Added ✓' : 'Add'}
      </button>
    </div>
  )
}
