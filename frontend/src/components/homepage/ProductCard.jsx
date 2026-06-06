import { Link } from 'react-router-dom'

const SPEC_ICONS = {
  seat: (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
      <path d="M5 17a2 2 0 1 0 4 0 2 2 0 0 0-4 0Zm10 0a2 2 0 1 0 4 0 2 2 0 0 0-4 0Z" />
      <path d="M5 17H3v-4l2-5h11l3 5v4h-2M9 17h6M3 11h17" />
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
}

function normalizeSpec(spec) {
  if (typeof spec === 'string') {
    const lower = spec.toLowerCase()
    let type = 'seat'
    if (lower.includes('sleep') || lower.includes('guest')) type = 'bed'
    if (lower.includes('room')) type = 'room'
    if (lower.includes('wi-fi') || lower.includes('wifi')) type = 'wifi'
    return { type, label: spec }
  }
  return spec
}

function specIcon(type, label, key) {
  if (type === 'gearbox') {
    return (
      <span className="spec" key={key}>
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
