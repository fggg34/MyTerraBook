import { useState } from 'react'
import CatalogIcon from '../../utils/CatalogIcon'
import ListingShowMore from './ListingShowMore'

const AMENITY_ROWS = 3
const AMENITY_COLS = 3
const AMENITY_INITIAL = AMENITY_ROWS * AMENITY_COLS

export default function ListingAmenities({ amenities = [] }) {
  const [expanded, setExpanded] = useState(false)
  const hiddenCount = Math.max(0, amenities.length - AMENITY_INITIAL)
  const visible = expanded ? amenities : amenities.slice(0, AMENITY_INITIAL)

  if (!amenities.length) {
    return <p className="listing-empty-hint">No amenities listed for this vehicle.</p>
  }

  return (
    <div className="amen-panel">
      <div className="amen-grid">
        {visible.map((a) => (
          <div key={a.name} className={`amen ${a.featured ? 'feat' : ''}`}>
            <span className="a-ic" aria-hidden>
              <CatalogIcon name={a.icon} iconUrl={a.iconUrl} size={24} imgClassName="a-ic-img" />
            </span>
            {a.name}
          </div>
        ))}
      </div>
      <ListingShowMore
        expanded={expanded}
        hiddenCount={hiddenCount}
        onToggle={() => setExpanded((v) => !v)}
        itemLabel="feature"
        align="center"
      />
    </div>
  )
}
