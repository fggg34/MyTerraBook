import { useEffect, useState } from 'react'

const AMENITY_ROWS = 3
const AMENITY_COLS = 3
const AMENITY_INITIAL = AMENITY_ROWS * AMENITY_COLS

export default function ListingAmenities({ amenities = [] }) {
  const [expanded, setExpanded] = useState(false)
  const hasMore = amenities.length > AMENITY_INITIAL
  const visible = expanded ? amenities : amenities.slice(0, AMENITY_INITIAL)

  useEffect(() => {
    requestAnimationFrame(() => window.dispatchEvent(new Event('resize')))
  }, [expanded, amenities.length])

  if (!amenities.length) {
    return <p className="listing-empty-hint">No amenities listed for this vehicle.</p>
  }

  return (
    <div className="amen-panel">
      <div className="amen-grid">
        {visible.map((a) => (
          <div key={a.name} className={`amen ${a.featured ? 'feat' : ''}`}>
            <span className="a-ic" aria-hidden>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                <path d="m5 12 4 4 10-10" />
              </svg>
            </span>
            {a.name}
          </div>
        ))}
      </div>
      {hasMore && (
        <div className="amen-more-wrap">
          <button
            type="button"
            className="amen-more"
            onClick={() => setExpanded((v) => !v)}
            aria-expanded={expanded}
          >
            {expanded ? 'Show less' : 'Show more'}
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round" aria-hidden>
              <path d="m6 9 6 6 6-6" />
            </svg>
          </button>
        </div>
      )}
    </div>
  )
}
