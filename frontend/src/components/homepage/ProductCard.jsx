import { Link } from 'react-router-dom'

const SPEC_ICONS = {
  seat: (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
      <path d="M5 19v-2a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v2" />
      <path d="M7 15V9a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v6" />
      <path d="M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
    </svg>
  ),
  bed: (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
      <path d="M3 18V8M3 13h18v5M21 13v-2a3 3 0 0 0-3-3h-7v5" />
      <circle cx="7" cy="11" r="1.6" />
    </svg>
  ),
  bag: (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
      <path d="M8 7V5a4 4 0 1 1 8 0v2M5 9h14l-1 12H6L5 9Z" />
    </svg>
  ),
  drive: (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
      <circle cx="12" cy="12" r="3" />
      <path d="M12 2v3M12 19v3M2 12h3M19 12h3" />
    </svg>
  ),
  wifi: (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
      <path d="M5 12.5a10 10 0 0 1 14 0M8 15.5a6 6 0 0 1 8 0" />
      <circle cx="12" cy="19" r="1" />
    </svg>
  ),
  room: (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
      <path d="M5 21V4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v17M3 21h18" />
      <circle cx="15" cy="12" r="1.6" fill="currentColor" stroke="none" />
    </svg>
  ),
  bath: (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
      <path d="M4 12h16a4 4 0 0 1 0 8H4a4 4 0 0 1 0-8Z" />
      <path d="M6 12V7a2 2 0 0 1 4 0v1M10 8h4" />
    </svg>
  ),
}

function normalizeSpec(spec) {
  if (typeof spec === 'string') {
    const lower = spec.toLowerCase()
    let type = 'seat'
    if (lower.includes('sleep') || lower.includes('guest')) type = 'bed'
    if (lower.includes('room')) type = 'room'
    if (lower.includes('bath')) type = 'bath'
    if (lower.includes('wi-fi') || lower.includes('wifi')) type = 'wifi'
    return { type, label: spec }
  }
  return spec
}

function specIcon(type, label, key) {
  if (type === 'gearbox') {
    return (
      <span className="spec spec-gearbox" key={key}>
        <span className="gbox">{label}</span>
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

export default function ProductCard({
  name,
  image,
  imageAlt,
  badge = 'Extras included',
  specs = [],
  price,
  per = 'night',
  href,
}) {
  return (
    <article className="pcard">
      <div className="pcard-media">
        {badge && <span className="pbadge">{badge}</span>}
        <img src={image} alt={imageAlt || name} />
      </div>
      <div className="pcard-foot">
        <h3>{name}</h3>
        <div className="specs">
          {specs.map((spec) => {
            const { type, label } = normalizeSpec(spec)
            return specIcon(type, label, `${type}-${label}`)
          })}
        </div>
        {price && (
          <div className="pcard-cta">
            <div className="pcard-price">
              <span className="ps-label">From</span>
              <span className="pill">{price}</span>
              <span className="ps-per">/ {per}</span>
            </div>
            {href ? (
              <Link className="pcard-book" to={href}>
                Book
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M5 12h14M13 6l6 6-6 6" />
                </svg>
              </Link>
            ) : (
              <button className="pcard-book" type="button">
                Book
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M5 12h14M13 6l6 6-6 6" />
                </svg>
              </button>
            )}
          </div>
        )}
      </div>
    </article>
  )
}
