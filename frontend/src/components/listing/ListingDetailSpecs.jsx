import { Fragment, useEffect, useMemo, useState } from 'react'
import { SpecIcon } from '../../utils/listingSpecIcons'

const LOCATION_PREVIEW_COUNT = 3

function formatTimeWindow(window) {
  if (!window?.from || !window?.to) return null
  return `${window.from} – ${window.to}`
}

function sameLocationSets(pickups, dropoffs) {
  if (!pickups.length && !dropoffs.length) return true
  if (pickups.length !== dropoffs.length) return false
  const pickupIds = pickups.map((loc) => String(loc.id)).sort().join(',')
  const dropoffIds = dropoffs.map((loc) => String(loc.id)).sort().join(',')
  return pickupIds === dropoffIds
}

function LocationGroup({ title, locations, expanded, showToggle, onToggle, hiddenCount }) {
  if (!locations.length) return null

  const visible = expanded ? locations : locations.slice(0, LOCATION_PREVIEW_COUNT)

  return (
    <div className="detail-specs-loc-group">
      <h4 className="detail-specs-loc-label">{title}</h4>
      <ul className="detail-specs-loc-list">
        {visible.map((loc) => (
          <li key={loc.id} className="detail-specs-loc-chip">
            {loc.name}
          </li>
        ))}
      </ul>
      {showToggle && (
        <button
          type="button"
          className="detail-specs-loc-more"
          onClick={onToggle}
          aria-expanded={expanded}
        >
          {expanded ? 'Show fewer locations' : `Show ${hiddenCount} more location${hiddenCount === 1 ? '' : 's'}`}
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round" aria-hidden>
            <path d="m6 9 6 6 6-6" />
          </svg>
        </button>
      )}
    </div>
  )
}

export default function ListingDetailSpecs({
  detailSpecs = [],
  pickupLocations = [],
  dropoffLocations = [],
  pickupTimeWindow = null,
  dropoffTimeWindow = null,
}) {
  const [pickupExpanded, setPickupExpanded] = useState(false)

  const pickups = pickupLocations || []
  const dropoffs = dropoffLocations || []
  const showDropoffGroup = dropoffs.length > 0 && !sameLocationSets(pickups, dropoffs)

  const pickupTimes = formatTimeWindow(pickupTimeWindow)
  const dropoffTimes = formatTimeWindow(dropoffTimeWindow)

  const hasLocationInfo = pickups.length > 0
    || showDropoffGroup
    || pickupTimes
    || dropoffTimes

  const pickupHiddenCount = Math.max(0, pickups.length - LOCATION_PREVIEW_COUNT)
  const showPickupToggle = pickupHiddenCount > 0

  const resizeKey = useMemo(
    () => `${detailSpecs.length}-${pickups.length}-${dropoffs.length}-${pickupExpanded}`,
    [detailSpecs.length, pickups.length, dropoffs.length, pickupExpanded],
  )

  useEffect(() => {
    requestAnimationFrame(() => window.dispatchEvent(new Event('resize')))
  }, [resizeKey])

  if (!detailSpecs.length && !hasLocationInfo) return null

  return (
    <div className="detail-specs">
      {detailSpecs.length > 0 && (
        <div className="detail-specs-chars" role="list">
          {detailSpecs.map((spec, index) => (
            <Fragment key={`${spec.icon}-${spec.label}`}>
              {index > 0 && <span className="detail-specs-sep" aria-hidden="true">|</span>}
              <span className="detail-specs-char" role="listitem">
                <SpecIcon icon={spec.icon} />
                <span>{spec.label}</span>
              </span>
            </Fragment>
          ))}
        </div>
      )}

      {hasLocationInfo && (
        <div className="detail-specs-locations">
          <h3 className="detail-specs-locations-title">Pick-up &amp; drop-off</h3>

          {pickups.length > 0 && (
            <LocationGroup
              title="Pick-up locations"
              locations={pickups}
              expanded={pickupExpanded}
              showToggle={showPickupToggle}
              onToggle={() => setPickupExpanded((v) => !v)}
              hiddenCount={pickupHiddenCount}
            />
          )}

          {showDropoffGroup && (
            <LocationGroup
              title="Drop-off locations"
              locations={dropoffs}
              expanded
              showToggle={false}
              onToggle={() => {}}
              hiddenCount={0}
            />
          )}

          {(pickupTimes || dropoffTimes) && (
            <div className="detail-specs-times">
              {pickupTimes && (
                <p className="detail-specs-time">
                  <span className="detail-specs-time-label">Pick-up hours</span>
                  <span>{pickupTimes}</span>
                </p>
              )}
              {dropoffTimes && (
                <p className="detail-specs-time">
                  <span className="detail-specs-time-label">Drop-off hours</span>
                  <span>{dropoffTimes}</span>
                </p>
              )}
            </div>
          )}
        </div>
      )}
    </div>
  )
}
