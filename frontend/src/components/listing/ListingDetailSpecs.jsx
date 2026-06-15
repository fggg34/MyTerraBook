import { useEffect, useState } from 'react'

export default function ListingDetailSpecs({ detailSpecs = [], pickupLocations = [] }) {
  const [expanded, setExpanded] = useState(false)
  const pickups = pickupLocations || []
  const firstPickup = pickups[0]
  const extraPickups = pickups.length > 1 ? pickups.slice(1) : []
  const showMore = extraPickups.length > 0

  const primaryItems = detailSpecs.map((spec) => ({
    key: `spec-${spec.label}`,
    label: spec.label,
  }))

  if (firstPickup) {
    primaryItems.push({
      key: `pickup-${firstPickup.id}`,
      label: `Pick-up: ${firstPickup.name}`,
    })
  }

  useEffect(() => {
    requestAnimationFrame(() => window.dispatchEvent(new Event('resize')))
  }, [expanded, primaryItems.length, extraPickups.length])

  if (!primaryItems.length) return null

  return (
    <div className="detail-specs">
      <div className="detail-specs-primary">
        <ul className="detail-specs-list">
          {primaryItems.map((item) => (
            <li key={item.key}>{item.label}</li>
          ))}
        </ul>
        {showMore && (
          <button
            type="button"
            className="detail-specs-more"
            onClick={() => setExpanded((v) => !v)}
            aria-expanded={expanded}
          >
            {expanded ? 'Show less' : 'Show more'}
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round" aria-hidden>
              <path d="m6 9 6 6 6-6" />
            </svg>
          </button>
        )}
      </div>

      {expanded && extraPickups.length > 0 && (
        <ul className="detail-specs-pickups-list detail-specs-pickups-list--extra">
          {extraPickups.map((loc) => (
            <li key={loc.id}>{loc.name}</li>
          ))}
        </ul>
      )}
    </div>
  )
}
