import { MapPin } from 'lucide-react'
import { useEffect, useMemo, useState } from 'react'
import ListingShowMore from './ListingShowMore'

const LOCATION_PREVIEW_COUNT = 4

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

function LocationList({ locations, expanded, showToggle, onToggle, hiddenCount }) {
  if (!locations.length) return null

  const visible = expanded ? locations : locations.slice(0, LOCATION_PREVIEW_COUNT)

  return (
    <>
      <ul className="pickup-loc-list">
        {visible.map((loc) => (
          <li key={loc.id} className="pickup-loc-item">
            <span className="pickup-loc-ic" aria-hidden>
              <MapPin strokeWidth={2} size={24} />
            </span>
            <span className="pickup-loc-copy">
              <span className="pickup-loc-name">{loc.name}</span>
              {loc.address ? <span className="pickup-loc-sub">{loc.address}</span> : null}
            </span>
          </li>
        ))}
      </ul>
      {showToggle ? (
        <ListingShowMore
          expanded={expanded}
          hiddenCount={hiddenCount}
          onToggle={onToggle}
          itemLabel="location"
          align="start"
        />
      ) : null}
    </>
  )
}

function LocationGroup({ title, locations, expanded, showToggle, onToggle, hiddenCount, note }) {
  if (!locations.length && !note) return null

  return (
    <div className="pickup-loc-group">
      {title ? <h3 className="pickup-loc-label">{title}</h3> : null}
      {note ? <p className="pickup-loc-note">{note}</p> : null}
      {locations.length > 0 ? (
        <LocationList
          locations={locations}
          expanded={expanded}
          showToggle={showToggle}
          onToggle={onToggle}
          hiddenCount={hiddenCount}
        />
      ) : null}
    </div>
  )
}

export default function ListingPickupDropoff({
  pickupLocations = [],
  dropoffLocations = [],
  pickupTimeWindow = null,
  dropoffTimeWindow = null,
}) {
  const [pickupExpanded, setPickupExpanded] = useState(false)
  const [dropoffExpanded, setDropoffExpanded] = useState(false)

  const pickups = pickupLocations || []
  const dropoffs = dropoffLocations || []
  const sharedLocations = sameLocationSets(pickups, dropoffs)
  const hasDropoffLocations = dropoffs.length > 0

  const pickupTimes = formatTimeWindow(pickupTimeWindow)
  const dropoffTimes = formatTimeWindow(dropoffTimeWindow)

  const hasLocationInfo = pickups.length > 0 || hasDropoffLocations || pickupTimes || dropoffTimes

  const pickupHiddenCount = Math.max(0, pickups.length - LOCATION_PREVIEW_COUNT)
  const dropoffHiddenCount = Math.max(0, dropoffs.length - LOCATION_PREVIEW_COUNT)

  const resizeKey = useMemo(
    () => `${pickups.length}-${dropoffs.length}-${pickupExpanded}-${dropoffExpanded}`,
    [pickups.length, dropoffs.length, pickupExpanded, dropoffExpanded],
  )

  useEffect(() => {
    requestAnimationFrame(() => window.dispatchEvent(new Event('resize')))
  }, [resizeKey])

  if (!hasLocationInfo) return null

  return (
    <div className="pickup-dropoff">
      {pickups.length > 0 ? (
        <LocationGroup
          title="Pick-up locations"
          locations={pickups}
          expanded={pickupExpanded}
          showToggle={pickupHiddenCount > 0}
          onToggle={() => setPickupExpanded((v) => !v)}
          hiddenCount={pickupHiddenCount}
        />
      ) : null}

      {hasDropoffLocations ? (
        <LocationGroup
          title="Drop-off locations"
          note={sharedLocations ? 'Same locations as pick-up.' : null}
          locations={sharedLocations ? [] : dropoffs}
          expanded={dropoffExpanded}
          showToggle={!sharedLocations && dropoffHiddenCount > 0}
          onToggle={() => setDropoffExpanded((v) => !v)}
          hiddenCount={dropoffHiddenCount}
        />
      ) : null}

      {(pickupTimes || dropoffTimes) ? (
        <div className="pickup-times">
          {pickupTimes ? (
            <div className="pickup-time-card">
              <span className="pickup-time-label">Pick-up hours</span>
              <span className="pickup-time-value">{pickupTimes}</span>
            </div>
          ) : null}
          {dropoffTimes ? (
            <div className="pickup-time-card">
              <span className="pickup-time-label">Drop-off hours</span>
              <span className="pickup-time-value">{dropoffTimes}</span>
            </div>
          ) : null}
        </div>
      ) : null}
    </div>
  )
}
