import { ChevronRight, MapPin } from 'lucide-react'
import { useEffect, useMemo, useState } from 'react'
import { api } from '../../api'
import DateRangePicker, { parseDateOnly } from '../ui/DateRangePicker'
import { useMapsConfig } from '../../hooks/useMapsConfig'
import { useFormatPrice } from '../../hooks/useFormatPrice'
import { buildStaticMapUrl } from '../../utils/parseGooglePlace'
import { expandBlockedWindows, rangeIncludesBlockedDate } from '../../utils/bookingRestrictions'
import { buildListingQuotePayload } from '../../utils/listingQuote'
import ListingSpecGrid from './ListingSpecGrid'
import ListingPickupDropoff from './ListingPickupDropoff'
import ListingAmenities from './ListingAmenities'
import ListingOptionalExtras from './ListingOptionalExtras'
import ListingSleepingPanel from './ListingSleepingPanel'
import ListingSection from './ListingSection'
import CatalogIcon from '../../utils/CatalogIcon'

export default function ListingTabPanels({
  listing,
  onRequestBook,
  initialPickup,
  initialDropoff,
  bookingDatesRef,
  openCalendarRef,
  selectedAddonIds = [],
  onToggleAddon,
  onAvailabilityChange,
}) {
  const {
    typeConfig,
    detailSpecs,
    description,
    amenities,
    conditions,
    addons,
    sleeping,
    location,
    pickupLocations,
    dropoffLocations,
    pickupTimeWindow,
    dropoffTimeWindow,
    owner,
    listingType,
  } = listing
  const isVehicle = listingType === 'car' || listingType === 'campervan'
  const hasPickupInfo =
    (pickupLocations?.length ?? 0) > 0
    || (dropoffLocations?.length ?? 0) > 0
    || pickupTimeWindow
    || dropoffTimeWindow
  const { mapsApiKey } = useMapsConfig()
  const price = useFormatPrice()
  const [startDate, setStartDate] = useState(() => parseDateOnly(initialPickup))
  const [endDate, setEndDate] = useState(() => parseDateOnly(initialDropoff))
  const [descExpanded, setDescExpanded] = useState(false)
  const [blockedDates, setBlockedDates] = useState([])
  const [blockedDatesLoaded, setBlockedDatesLoaded] = useState(!isVehicle)
  const [quoteTotal, setQuoteTotal] = useState(null)
  const [quoteLoading, setQuoteLoading] = useState(false)
  const priceFromAmount = Number(listing.priceFromAmount) || 0

  useEffect(() => {
    if (!isVehicle || !listing.id) {
      setBlockedDates([])
      setBlockedDatesLoaded(true)
      return undefined
    }

    setBlockedDatesLoaded(false)
    let cancelled = false
    api
      .get(`/cars/${listing.id}/availability-calendar`)
      .then((res) => {
        if (cancelled) return
        const windows = [...(res.data?.booked ?? []), ...(res.data?.blocked ?? [])]
        setBlockedDates(expandBlockedWindows(windows))
      })
      .catch(() => {
        if (!cancelled) setBlockedDates([])
      })
      .finally(() => {
        if (!cancelled) setBlockedDatesLoaded(true)
      })

    return () => {
      cancelled = true
    }
  }, [isVehicle, listing.id])

  const syncBookingDates = (start, end) => {
    if (bookingDatesRef) {
      bookingDatesRef.current = { pickupDate: start, dropoffDate: end }
    }
  }

  const datesUnavailable = useMemo(
    () => rangeIncludesBlockedDate(startDate, endDate, blockedDates),
    [startDate, endDate, blockedDates],
  )
  const bookingBlocked = isVehicle && (!blockedDatesLoaded || datesUnavailable)

  useEffect(() => {
    onAvailabilityChange?.({
      datesUnavailable: bookingBlocked,
      blockedDatesLoaded,
    })
    if (bookingDatesRef) {
      bookingDatesRef.current = {
        pickupDate: startDate,
        dropoffDate: endDate,
        datesUnavailable: bookingBlocked,
        blockedDatesLoaded,
      }
    }
  }, [startDate, endDate, bookingBlocked, blockedDatesLoaded, onAvailabilityChange, bookingDatesRef])

  useEffect(() => {
    const start = parseDateOnly(initialPickup)
    const end = parseDateOnly(initialDropoff)
    setStartDate(start)
    setEndDate(end)
    syncBookingDates(start, end)
  }, [initialPickup, initialDropoff])

  useEffect(() => {
    if (!isVehicle || !startDate || !endDate || bookingBlocked) {
      setQuoteTotal(null)
      setQuoteLoading(false)
      return undefined
    }

    const payload = buildListingQuotePayload(listing, startDate, endDate, selectedAddonIds)
    if (!payload) {
      setQuoteTotal(null)
      return undefined
    }

    let cancelled = false
    setQuoteLoading(true)
    const timer = window.setTimeout(() => {
      api
        .post('/orders/quote', payload)
        .then((res) => {
          if (cancelled) return
          const amount = Number(res.data?.total)
          setQuoteTotal(Number.isFinite(amount) ? amount : null)
        })
        .catch(() => {
          if (!cancelled) setQuoteTotal(null)
        })
        .finally(() => {
          if (!cancelled) setQuoteLoading(false)
        })
    }, 300)

    return () => {
      cancelled = true
      window.clearTimeout(timer)
    }
  }, [isVehicle, listing, startDate, endDate, selectedAddonIds, bookingBlocked])

  const handleDateChange = ({ start, end }) => {
    setStartDate(start)
    setEndDate(end)
    syncBookingDates(start, end)
  }

  const nights =
    startDate && endDate
      ? Math.max(1, Math.round((endDate.getTime() - startDate.getTime()) / 86400000))
      : 0
  const fallbackTotal = nights * priceFromAmount
  const displayTotal = quoteTotal ?? (quoteLoading ? null : fallbackTotal)
  const staticMapUrl = location
    ? buildStaticMapUrl({
        latitude: location.latitude,
        longitude: location.longitude,
        mapsApiKey,
      })
    : null

  const conditionsTitle = listingType === 'guesthouse' ? 'House rules' : 'Rental conditions'
  const amenitiesTitle =
    listingType === 'car' ? 'Vehicle features' : listingType === 'guesthouse' ? 'Amenities' : 'Features'
  const sectionDesc = typeConfig.sectionDescriptions || {}

  return (
    <div className="split">
      <div className="maincol">
        <header className="listing-header">
          <h1 className="listing-title">{listing.title}</h1>
          {location?.formattedLine ? (
            <div className="listing-header__meta">
              <p className="listing-location-line">
                <MapPin className="listing-location-icon" aria-hidden />
                {location.mapsUrl ? (
                  <a
                    className="listing-location-link listing-location-link--inline"
                    href={location.mapsUrl}
                    target="_blank"
                    rel="noopener noreferrer"
                  >
                    {location.formattedLine}
                  </a>
                ) : (
                  <span>{location.formattedLine}</span>
                )}
              </p>
            </div>
          ) : null}
        </header>

        {owner ? (
          <div className="owner-trust">
            <div className="owner-trust__head">
              <div className="owner-av">{owner.initial}</div>
              <div className="owner-meta">
                <div className="owner-name">
                  Meet the host, {owner.name}
                  {owner.badge ? <span className="owner-badge">{owner.badge}</span> : null}
                </div>
                <div className="owner-sub">
                  {owner.tripsLabel} · {owner.reviewsLabel}
                </div>
              </div>
            </div>
          </div>
        ) : null}

        {detailSpecs.length > 0 ? (
          <div className="listing-basics">
            <ListingSpecGrid detailSpecs={detailSpecs} />
          </div>
        ) : null}

        {description?.short ? (
          <div className="listing-description">
            <p className={`desc${descExpanded ? ' open' : ''}`}>
              {description.short}
              {description.more ? <span className="more-text"> {description.more}</span> : null}
            </p>
            {description.more ? (
              <button
                className="showmore"
                type="button"
                onClick={() => setDescExpanded((v) => !v)}
              >
                <span>{descExpanded ? 'Show less' : 'Read more'}</span>
                <ChevronRight className={`showmore-chevron${descExpanded ? ' showmore-chevron--up' : ''}`} aria-hidden />
              </button>
            ) : null}
          </div>
        ) : null}

        {amenities.length > 0 ? (
          <ListingSection title={amenitiesTitle} description={sectionDesc.amenities}>
            <ListingAmenities amenities={amenities} />
          </ListingSection>
        ) : null}

        {listingType === 'guesthouse' && sleeping?.beds?.length ? (
          <ListingSection title="Room details" description={sectionDesc.roomDetails}>
            <ListingSleepingPanel sleeping={sleeping} hideKicker />
          </ListingSection>
        ) : null}

        {isVehicle && addons.length > 0 ? (
          <ListingSection title="Optional extras" description={sectionDesc.optionalExtras}>
            <ListingOptionalExtras
              addons={addons}
              selectedAddonIds={selectedAddonIds}
              onToggleAddon={onToggleAddon}
            />
          </ListingSection>
        ) : null}

        {conditions.length > 0 ? (
          <ListingSection title={conditionsTitle} description={sectionDesc.conditions}>
            <div className="cond-list">
              {conditions.map((c) => (
                <div key={c.title} className="cond">
                  <span className="c-ic" aria-hidden>
                    {c.icon ? (
                      <CatalogIcon name={c.icon} size={24} strokeWidth={1.9} />
                    ) : (
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.9" strokeLinecap="round" strokeLinejoin="round">
                        <path d="m5 12 4 4 10-10" />
                      </svg>
                    )}
                  </span>
                  <span>
                    <span className="c-t">{c.title}</span>
                    <span className="c-d">{c.desc}</span>
                  </span>
                </div>
              ))}
            </div>
          </ListingSection>
        ) : null}

        {isVehicle && hasPickupInfo ? (
          <ListingSection title="Pick-up & drop-off" description={sectionDesc.pickupDropoff}>
            <ListingPickupDropoff
              pickupLocations={pickupLocations}
              dropoffLocations={dropoffLocations}
              pickupTimeWindow={pickupTimeWindow}
              dropoffTimeWindow={dropoffTimeWindow}
            />
          </ListingSection>
        ) : null}

        {isVehicle && !conditions.length && !hasPickupInfo ? (
          <ListingSection title={conditionsTitle}>
            <p className="listing-empty-hint">No rental conditions listed.</p>
          </ListingSection>
        ) : null}

        {listingType === 'guesthouse' && !conditions.length ? (
          <ListingSection title={conditionsTitle}>
            <p className="listing-empty-hint">No house rules listed.</p>
          </ListingSection>
        ) : null}

        {location?.formattedLine ? (
          <ListingSection title="Location" description={sectionDesc.location}>
            {location.mapsUrl ? (
              <a className="listing-location-link" href={location.mapsUrl} target="_blank" rel="noopener noreferrer">
                Open in Google Maps
              </a>
            ) : null}
            {staticMapUrl ? (
              <a className="listing-location-map" href={location.mapsUrl || staticMapUrl} target="_blank" rel="noopener noreferrer">
                <img src={staticMapUrl} alt={`Map showing ${location.formattedLine}`} loading="lazy" />
              </a>
            ) : null}
          </ListingSection>
        ) : null}
      </div>

      <aside className="listing-book">
        <div className="listing-bcard">
          <div className="date-wrap" id="dateWrap">
            <DateRangePicker
              ref={openCalendarRef}
              startLabel={typeConfig.dateStartLabel || 'Pick-up'}
              endLabel={typeConfig.dateEndLabel || 'Drop-off'}
              startDate={startDate}
              endDate={endDate}
              pricePerDay={priceFromAmount}
              blockedDates={blockedDates}
              onChange={handleDateChange}
            />
          </div>
          <div className="rate-row">
            <span className="rl" id="rateL">
              {startDate && endDate
                ? `Total · ${nights} night${nights > 1 ? 's' : ''}`
                : typeConfig.rateLabelDefault}
            </span>
            <span className="rr" id="rateR">
              {startDate && endDate ? (
                quoteLoading ? (
                  <b>…</b>
                ) : displayTotal != null && displayTotal > 0 ? (
                  <b>{price.format(displayTotal)}</b>
                ) : (
                  <b>-</b>
                )
              ) : listing.priceFrom ? (
                <>
                  From <b>{listing.priceFrom}</b>
                </>
              ) : (
                <b>-</b>
              )}
            </span>
          </div>
          {listingType !== 'guesthouse' && selectedAddonIds.length > 0 && (
            <p className="listing-extras-note">
              {selectedAddonIds.length} extra{selectedAddonIds.length !== 1 ? 's' : ''} selected
            </p>
          )}
          {bookingBlocked && blockedDatesLoaded && (
            <p className="listing-unavailable-note">Selected dates are not available for booking.</p>
          )}
          {!blockedDatesLoaded && isVehicle && (
            <p className="listing-availability-loading">Checking availability…</p>
          )}
          <button
            className="book-btn"
            id="listingBookBtn"
            type="button"
            disabled={bookingBlocked}
            onClick={(e) => {
              e.stopPropagation()
              onRequestBook?.({ pickupDate: startDate, dropoffDate: endDate })
            }}
          >
            {typeConfig.bookCta}
          </button>
          <button className="book-link" id="bookProcessLink" type="button">
            View the booking process
          </button>
          <div className="listing-bcard-div" />
          <div className="trust-points">
            {typeConfig.trustPoints.map((tp) => (
              <div key={tp.html} className="tp">
                <span className="tp-ic" aria-hidden>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round">
                    <path d="m5 12 4 4 10-10" />
                  </svg>
                </span>
                <span dangerouslySetInnerHTML={{ __html: tp.html }} />
              </div>
            ))}
          </div>
        </div>
      </aside>
    </div>
  )
}
