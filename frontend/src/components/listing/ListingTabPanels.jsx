import { MapPin } from 'lucide-react'
import { useEffect, useState } from 'react'
import DateRangePicker, { parseDateOnly } from '../ui/DateRangePicker'
import { useMapsConfig } from '../../hooks/useMapsConfig'
import { useFormatPrice } from '../../hooks/useFormatPrice'
import { buildStaticMapUrl } from '../../utils/parseGooglePlace'

export default function ListingTabPanels({
  listing,
  onRequestBook,
  initialPickup,
  initialDropoff,
  bookingDatesRef,
  openCalendarRef,
}) {
  const { typeConfig, rating, detailSpecs, description, amenities, conditions, sleeping, addons, location, pickupLocations, owner } = listing
  const { mapsApiKey } = useMapsConfig()
  const price = useFormatPrice()
  const [startDate, setStartDate] = useState(() => parseDateOnly(initialPickup))
  const [endDate, setEndDate] = useState(() => parseDateOnly(initialDropoff))
  const priceFrom = Number(listing.priceFrom) || 0

  const syncBookingDates = (start, end) => {
    if (bookingDatesRef) {
      bookingDatesRef.current = { pickupDate: start, dropoffDate: end }
    }
  }

  useEffect(() => {
    const start = parseDateOnly(initialPickup)
    const end = parseDateOnly(initialDropoff)
    setStartDate(start)
    setEndDate(end)
    syncBookingDates(start, end)
  }, [initialPickup, initialDropoff])

  const handleDateChange = ({ start, end }) => {
    setStartDate(start)
    setEndDate(end)
    syncBookingDates(start, end)
  }

  const nights =
    startDate && endDate
      ? Math.max(1, Math.round((endDate.getTime() - startDate.getTime()) / 86400000))
      : 0
  const total = nights * priceFrom
  const staticMapUrl = location
    ? buildStaticMapUrl({
        latitude: location.latitude,
        longitude: location.longitude,
        mapsApiKey,
      })
    : null

  return (
    <>
      <div className="tabbar" id="tabbar">
        <div className="tabs" id="tabs">
          {typeConfig.tabs.map((tab, i) => (
            <button key={tab.id} className={`tab ${i === 0 ? 'active' : ''}`} data-i={i} type="button">
              {tab.label}
              <span className="underline" />
            </button>
          ))}
        </div>
      </div>

      <div className="split">
        <div className="maincol">
          {owner ? (
            <div className="owner">
              <div className="owner-av">{owner.initial}</div>
              <div className="owner-meta">
                <div className="owner-name">
                  {owner.name}
                  {owner.badge && <span className="owner-badge">{owner.badge}</span>}
                </div>
                <div className="owner-sub">
                  Host · <b>{owner.tripsLabel}</b> · {owner.reviewsLabel}
                </div>
              </div>
            </div>
          ) : null}

          <h1 className="listing-title">{listing.title}</h1>

          {location?.formattedLine ? (
            <div className="listing-location">
              <p className="listing-location-line">
                <MapPin className="listing-location-icon" aria-hidden />
                <span>{location.formattedLine}</span>
              </p>
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
            </div>
          ) : null}

          <div className="tabcard" id="tabcard">
            <div className="tpanel active" data-panel="0">
              {rating ? (
              <div className="rating-strip">
                <div className="rblock">
                  <div className="rscore">
                    <svg className="star" viewBox="0 0 24 24" fill="currentColor">
                      <path d="M12 2.5l2.9 6.1 6.6.8-4.9 4.6 1.3 6.6L12 18.9 6.1 21.2l1.3-6.6L2.5 9.9l6.6-.8L12 2.5z" />
                    </svg>
                    <span className="num">{rating.score}</span>
                  </div>
                  <div className="rmeta">
                    <span className="excellent">{rating.label}</span>
                    <span className="ministars" aria-hidden>
                      {[1, 2, 3, 4, 5].map((n) => (
                        <svg key={n} viewBox="0 0 24 24" fill="currentColor">
                          <path d="M12 2.5l2.9 6.1 6.6.8-4.9 4.6 1.3 6.6L12 18.9 6.1 21.2l1.3-6.6L2.5 9.9l6.6-.8L12 2.5z" />
                        </svg>
                      ))}
                    </span>
                    <a href="#reviews">{rating.reviewLinkLabel}</a>
                  </div>
                </div>
                <div className="spec-grid">
                  {detailSpecs.map((spec) => (
                    <div key={spec.label} className="spec">
                      <span className="spec-lbl">{spec.label}</span>
                    </div>
                  ))}
                  {(pickupLocations || []).map((loc) => (
                    <div key={`pickup-${loc.id}`} className="spec">
                      <span className="spec-lbl">Pick-up: {loc.name}</span>
                    </div>
                  ))}
                </div>
              </div>
              ) : (
                <div className="spec-grid">
                  {detailSpecs.map((spec) => (
                    <div key={spec.label} className="spec">
                      <span className="spec-lbl">{spec.label}</span>
                    </div>
                  ))}
                  {(pickupLocations || []).map((loc) => (
                    <div key={`pickup-${loc.id}`} className="spec">
                      <span className="spec-lbl">Pick-up: {loc.name}</span>
                    </div>
                  ))}
                </div>
              )}
              <div className="descwrap">
                <p className="desc" id="desc">
                  {description.short}
                  {description.more ? <span className="more-text"> {description.more}</span> : null}
                </p>
                {description.more ? (
                  <button className="showmore" id="showMore" type="button">
                    <span data-label>Show more </span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
                      <path d="m6 9 6 6 6-6" />
                    </svg>
                  </button>
                ) : null}
              </div>
            </div>

            <div className="tpanel" data-panel="1">
              <div className="amen-grid">
                {amenities.length ? amenities.map((a) => (
                  <div key={a.name} className={`amen ${a.featured ? 'feat' : ''}`}>
                    <span className="a-ic" aria-hidden>
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                        <path d="m5 12 4 4 10-10" />
                      </svg>
                    </span>
                    {a.name}
                  </div>
                )) : (
                  <p className="listing-empty-hint">No amenities listed for this vehicle.</p>
                )}
              </div>
            </div>

            <div className="tpanel" data-panel="2">
              <div className="cond-list">
                {conditions.length ? conditions.map((c) => (
                  <div key={c.title} className="cond">
                    <span className="c-ic" aria-hidden>
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.9" strokeLinecap="round" strokeLinejoin="round">
                        <path d="m5 12 4 4 10-10" />
                      </svg>
                    </span>
                    <span>
                      <span className="c-t">{c.title}</span>
                      <span className="c-d">{c.desc}</span>
                    </span>
                  </div>
                )) : (
                  <p className="listing-empty-hint">No rental conditions listed.</p>
                )}
              </div>
            </div>

            {sleeping && (
              <div className="tpanel" data-panel="3">
                <div className="sleep-grid">
                  {sleeping.beds.map((bed, i) => (
                    <div
                      key={bed.title}
                      className={`bedcard ${i === 0 ? 'sel' : ''}`}
                      data-bed={i}
                      data-cap={`${bed.title} · ${bed.dim}`}
                    >
                      <span className="b-pick" aria-hidden>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3.4" strokeLinecap="round" strokeLinejoin="round">
                          <path d="m5 12 4 4 10-10" />
                        </svg>
                      </span>
                      <h4>{bed.title}</h4>
                      <p>{bed.text}</p>
                      <span className="b-dim">{bed.dim}</span>
                    </div>
                  ))}
                </div>
                <div className="sleep-preview">
                  {sleeping.beds.map((bed, i) => (
                    <img
                      key={bed.title}
                      className={`sleep-shot ${i === 0 ? 'active' : ''}`}
                      src={bed.image}
                      alt={bed.title}
                    />
                  ))}
                  <span className="sleep-cap" id="sleepCap">
                    {sleeping.beds[0] ? `${sleeping.beds[0].title} · ${sleeping.beds[0].dim}` : ''}
                  </span>
                </div>
              </div>
            )}

            <div className="tpanel" data-panel={sleeping ? 4 : 3}>
              <div className="addon-list">
                {addons.length ? addons.map((a) => (
                  <div key={a.name} className="addon">
                    <span className="ad-ic" aria-hidden>
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                        <path d="M12 5v14M5 12h14" />
                      </svg>
                    </span>
                    <span className="ad-tx">
                      <span className="ad-name">{a.name}</span>
                      <span className="ad-sub">{a.sub}</span>
                    </span>
                    <span className={`ad-price ${a.free ? 'free' : ''}`}>{a.price}</span>
                  </div>
                )) : (
                  <p className="listing-empty-hint">No add-ons available for this vehicle.</p>
                )}
              </div>
            </div>
          </div>
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
                pricePerDay={priceFrom}
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
                  <b>{price.format(total)}</b>
                ) : listing.priceFrom ? (
                  <>
                    From <b>{listing.priceFrom}</b>
                  </>
                ) : (
                  <b>-</b>
                )}
              </span>
            </div>
            <button
              className="book-btn"
              id="listingBookBtn"
              type="button"
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
    </>
  )
}
