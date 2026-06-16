const svgProps = {
  viewBox: '0 0 24 24',
  fill: 'none',
  stroke: 'currentColor',
  strokeWidth: 1.8,
  strokeLinecap: 'round',
  strokeLinejoin: 'round',
}

export const SPEC_ICONS = {
  seat: (
    <svg {...svgProps}>
      <path d="M5 19v-2a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v2" />
      <path d="M7 15V9a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v6" />
      <path d="M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
    </svg>
  ),
  seats: (
    <svg {...svgProps}>
      <path d="M5 19v-2a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v2" />
      <path d="M7 15V9a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v6" />
      <path d="M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
    </svg>
  ),
  bed: (
    <svg {...svgProps}>
      <path d="M3 18V8M3 13h18v5M21 13v-2a3 3 0 0 0-3-3h-7v5" />
      <circle cx="7" cy="11" r="1.6" />
    </svg>
  ),
  sleeps: (
    <svg {...svgProps}>
      <path d="M3 18V8M3 13h18v5M21 13v-2a3 3 0 0 0-3-3h-7v5" />
      <circle cx="7" cy="11" r="1.6" />
    </svg>
  ),
  bag: (
    <svg {...svgProps}>
      <path d="M8 7V5a4 4 0 1 1 8 0v2M5 9h14l-1 12H6L5 9Z" />
    </svg>
  ),
  bags: (
    <svg {...svgProps}>
      <path d="M8 7V5a4 4 0 1 1 8 0v2M5 9h14l-1 12H6L5 9Z" />
    </svg>
  ),
  drive: (
    <svg {...svgProps}>
      <circle cx="12" cy="12" r="3" />
      <path d="M12 2v3M12 19v3M2 12h3M19 12h3" />
    </svg>
  ),
  wifi: (
    <svg {...svgProps}>
      <path d="M5 12.5a10 10 0 0 1 14 0M8 15.5a6 6 0 0 1 8 0" />
      <circle cx="12" cy="19" r="1" />
    </svg>
  ),
  room: (
    <svg {...svgProps}>
      <path d="M5 21V4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v17M3 21h18" />
      <circle cx="15" cy="12" r="1.6" fill="currentColor" stroke="none" />
    </svg>
  ),
  bedroom: (
    <svg {...svgProps}>
      <path d="M5 21V4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v17M3 21h18" />
      <circle cx="15" cy="12" r="1.6" fill="currentColor" stroke="none" />
    </svg>
  ),
  bath: (
    <svg {...svgProps}>
      <path d="M4 12h16a4 4 0 0 1 0 8H4a4 4 0 0 1 0-8Z" />
      <path d="M6 12V7a2 2 0 0 1 4 0v1M10 8h4" />
    </svg>
  ),
  bathroom: (
    <svg {...svgProps}>
      <path d="M4 12h16a4 4 0 0 1 0 8H4a4 4 0 0 1 0-8Z" />
      <path d="M6 12V7a2 2 0 0 1 4 0v1M10 8h4" />
    </svg>
  ),
  gearbox: (
    <svg {...svgProps}>
      <circle cx="12" cy="12" r="3" />
      <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41" />
    </svg>
  ),
  fuel: (
    <svg {...svgProps}>
      <path d="M3 22V8a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v14" />
      <path d="M3 14h10M13 10h2l3 4v8h-5v-8" />
    </svg>
  ),
  units: (
    <svg {...svgProps}>
      <path d="M12 3 3 7v10l9 4 9-4V7l-9-4Z" />
      <path d="M3 7l9 4 9-4M12 11v10" />
    </svg>
  ),
  type: (
    <svg {...svgProps}>
      <path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-10.5Z" />
    </svg>
  ),
  city: (
    <svg {...svgProps}>
      <path d="M12 21s-6-4.35-6-10a6 6 0 1 1 12 0c0 5.65-6 10-6 10Z" />
      <circle cx="12" cy="11" r="2" />
    </svg>
  ),
  stay: (
    <svg {...svgProps}>
      <path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-10.5Z" />
    </svg>
  ),
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

export function SpecIcon({ icon, className = 'detail-specs-char-ic' }) {
  const svg = SPEC_ICONS[icon] || SPEC_ICONS.seat
  return <span className={className} aria-hidden="true">{svg}</span>
}

export function renderProductCardSpec(type, label, key) {
  if (type === 'gearbox' || type === 'fuel') {
    const badgeClass = type === 'fuel' ? 'fbox' : 'gbox'
    return (
      <span className={`spec spec-${type}`} key={key}>
        <span className={badgeClass}>{label}</span>
      </span>
    )
  }
  const icon = SPEC_ICONS[type] || SPEC_ICONS.seat
  return (
    <span className="spec" key={key}>
      {icon}
      <span className="spec-lbl">{label}</span>
    </span>
  )
}
