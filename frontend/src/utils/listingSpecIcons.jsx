import LucideIcon, { hasIcon } from './iconCatalog'

const SPEC_LUCIDE_MAP = {
  seat: 'users',
  seats: 'users',
  bed: 'bed',
  sleeps: 'bed',
  bag: 'luggage',
  bags: 'luggage',
  room: 'bed',
  bedroom: 'bed',
  bath: 'bath',
  bathroom: 'bath',
  wifi: 'wifi',
  city: 'map',
  type: 'tent',
  stay: 'tent',
  units: 'package',
  drive: 'mountain',
  fwd: 'car',
  rwd: 'car',
  awd: 'mountain',
  '4wd': 'mountain',
  gearbox: 'cog',
  fuel: 'fuel',
}

function gearboxLucideName(label) {
  const lower = String(label || '').toLowerCase()
  if (lower.includes('auto')) return 'settings'
  return 'cog'
}

function gearboxDisplayLabel(label) {
  const lower = String(label || '').toLowerCase()
  if (lower.includes('auto')) return 'Auto'
  if (lower.includes('manual')) return 'Manual'
  return String(label || '').trim()
}

export function normalizeSpec(spec) {
  if (typeof spec === 'string') {
    const lower = spec.toLowerCase()
    let type = 'seat'
    if (lower.includes('sleep') || lower.includes('guest')) type = 'bed'
    if (lower.includes('room')) type = 'room'
    if (lower.includes('bath')) type = 'bath'
    if (lower.includes('wi-fi') || lower.includes('wifi')) type = 'wifi'
    return { type, label: spec }
  }
  return {
    type: spec.icon || spec.type || 'seat',
    label: spec.label,
  }
}

export function resolveListingSpecIconName(icon) {
  if (!icon) return 'users'
  if (hasIcon(icon)) return icon
  return SPEC_LUCIDE_MAP[icon] || icon
}

export function SpecIcon({ icon, className = 'detail-specs-char-ic', size = 16, strokeWidth = 1.8 }) {
  const lucideName = resolveListingSpecIconName(icon)
  return (
    <span className={className} aria-hidden="true">
      <LucideIcon name={lucideName} size={size} strokeWidth={strokeWidth} />
    </span>
  )
}

function driveLucideName(label) {
  const lower = String(label || '').toLowerCase()
  if (lower.includes('4') || lower.includes('awd')) return 'mountain'
  return 'car'
}

export function renderProductCardSpec(type, label, key) {
  if (type === 'gearbox') {
    return (
      <span className="spec spec-gearbox" key={key}>
        <span className="spec-ic" aria-hidden="true">
          <LucideIcon name={gearboxLucideName(label)} size={20} strokeWidth={1.8} />
        </span>
        <span className="spec-lbl">{gearboxDisplayLabel(label)}</span>
      </span>
    )
  }
  if (type === 'fuel') {
    return (
      <span className="spec spec-fuel" key={key}>
        <span className="spec-ic" aria-hidden="true">
          <LucideIcon name="fuel" size={20} strokeWidth={1.8} />
        </span>
        <span className="spec-lbl">{label}</span>
      </span>
    )
  }
  if (type === 'drive') {
    return (
      <span className="spec spec-drive" key={key}>
        <span className="spec-ic" aria-hidden="true">
          <LucideIcon name={driveLucideName(label)} size={20} strokeWidth={1.8} />
        </span>
        <span className="spec-lbl">{label}</span>
      </span>
    )
  }
  const lucideName = SPEC_LUCIDE_MAP[type] || 'users'
  return (
    <span className="spec" key={key}>
      <span className="spec-ic" aria-hidden="true">
        <LucideIcon name={lucideName} size={20} strokeWidth={1.8} />
      </span>
      <span className="spec-lbl">{label}</span>
    </span>
  )
}
