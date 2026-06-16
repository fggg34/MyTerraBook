import { useState } from 'react'
import CatalogIcon from '../../utils/CatalogIcon'
import ListingShowMore from './ListingShowMore'

const ADDON_INITIAL_COUNT = 4

export default function ListingOptionalExtras({
  addons = [],
  selectedAddonIds = [],
  onToggleAddon,
}) {
  const [expanded, setExpanded] = useState(false)

  if (!addons.length) return null

  const hiddenCount = Math.max(0, addons.length - ADDON_INITIAL_COUNT)
  const visible = expanded ? addons : addons.slice(0, ADDON_INITIAL_COUNT)

  return (
    <div className="addon-panel">
      <div className="addon-list">
        {visible.map((a) => {
          const selected = selectedAddonIds.includes(Number(a.id))
          return (
            <button
              key={a.id || a.name}
              type="button"
              className={`addon${selected ? ' on' : ''}`}
              aria-pressed={selected}
              onClick={() => onToggleAddon?.(a.id)}
            >
              <span className="ad-ic" aria-hidden>
                {selected ? (
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
                    <path d="m5 12 4 4 10-10" />
                  </svg>
                ) : (
                  <CatalogIcon name={a.icon} iconUrl={a.iconUrl} size={24} imgClassName="ad-ic-img" fallback="plus" />
                )}
              </span>
              <span className="ad-tx">
                <span className="ad-name">{a.name}</span>
                <span className="ad-sub">{a.sub}</span>
              </span>
              <span className={`ad-price ${a.free ? 'free' : ''}`}>{a.price}</span>
            </button>
          )
        })}
      </div>
      <ListingShowMore
        expanded={expanded}
        hiddenCount={hiddenCount}
        onToggle={() => setExpanded((v) => !v)}
        itemLabel="extra"
        align="start"
      />
    </div>
  )
}
