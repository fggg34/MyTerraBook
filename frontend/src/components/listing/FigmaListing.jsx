import { useEffect, useMemo, useState } from 'react'
import { Link } from 'react-router-dom'
import { api } from '../../api'
import { usePageContent } from '../../context/SiteContentContext'
import { useShopConfig } from '../../context/ShopConfigContext'
import { useToast } from '../../context/ToastContext'
import { useFormatPrice } from '../../hooks/useFormatPrice'
import { parseDateOnly } from '../ui/DateRangePicker'
import { buildListingQuotePayload } from '../../utils/listingQuote'
import { mapGuestHouseToResultCard } from '../../utils/mapGuestHouseToResultCard'
import { sharePage } from '../../utils/sharePage'
import Container from '../site/Container'
import FigmaIcon from '../site/FigmaIcon'
import ListingWriteReview from './ListingWriteReview'
import '../../styles/listing-figma.css'

const FEATURE_ICONS = ['bed', 'kitchen', 'sun', 'snowflake', 'heating', 'tent', 'gauge', 'users']

const FRAME = {
  campervan: {
    typeLabel: 'Campervan',
    aboutTitle: 'About this campervan',
    featuresTitle: 'Features',
    includedTitle: "What's included",
    included: ['Unlimited kilometres', 'Basic insurance (CDW)', '24/7 roadside assistance', 'Bedding & kitchen kit', 'Airport pick-up and drop-off'],
    extrasTitle: 'Optional extras',
    extrasLead: 'Priced per day or one-time — added to the same booking.',
    pickupTitle: 'Pick-up & drop-off',
    knowTitle: 'Good to know',
    conditionsLink: 'Read full rental conditions',
    reviewsTitle: 'Reviews',
    writeReview: 'Write a review',
    emptyReviews: 'No reviews yet',
    emptyReviewsLead: 'Be the first to share how your trip went.',
    hostKicker: 'Meet the host',
    hostButton: 'View profile',
    bookCta: 'Request to book',
    processLink: 'View the booking process',
    insuranceTitle: 'Insurance',
    stayTitle: 'Add a night in a guesthouse',
    stayText: 'Same booking, one payment.',
    similarTitle: 'Similar rentals',
    similarButton: 'See all campervans',
    staysEyebrow: 'Complete your trip',
    staysTitle: 'Stays near the pick-up',
    staysButton: 'See stays',
    tiers: [
      { name: 'Basic', note: 'CDW · standard excess', included: true },
      { name: 'Premium', note: 'Lower excess · gravel & windscreen' },
      { name: 'Platinum', note: 'Zero excess · sand & ash' },
    ],
    know: [
      { icon: 'calendar', title: 'Cancellation', text: 'Free cancellation up to 14 days before pick-up.' },
      { icon: 'shield', title: 'Deposit', text: 'Held on your card, released after return.' },
      { icon: 'users', title: 'Driver age', text: 'Minimum 20 years, licence held 2+ years.' },
    ],
    winter: 'Winter rental: F-roads closed until June. Winter tyres included.',
    process: [
      { title: 'Request', text: 'You send a request with dates, insurance and extras. Nothing is charged yet.' },
      { title: 'Approval', text: 'The host confirms the vehicle. You pay the prepayment to lock it in.' },
      { title: 'Pick-up', text: 'The balance is due when you collect the vehicle.' },
    ],
  },
  car: {
    typeLabel: 'Car',
    aboutTitle: 'About this car',
    featuresTitle: 'Features',
    includedTitle: "What's included",
    included: ['Unlimited kilometres', 'Basic insurance (CDW)', '24/7 roadside assistance', 'Airport pick-up and drop-off'],
    extrasTitle: 'Optional extras',
    extrasLead: 'Priced per day or one-time — added to the same booking.',
    pickupTitle: 'Pick-up & drop-off',
    knowTitle: 'Good to know',
    conditionsLink: 'Read full rental conditions',
    reviewsTitle: 'Reviews',
    writeReview: 'Write a review',
    emptyReviews: 'No reviews yet',
    emptyReviewsLead: 'Be the first to share how your trip went.',
    hostKicker: 'Meet the host',
    hostButton: 'View profile',
    bookCta: 'Request to book',
    processLink: 'View the booking process',
    insuranceTitle: 'Insurance',
    stayTitle: 'Add a night in a guesthouse',
    stayText: 'Same booking, one payment.',
    similarTitle: 'Similar cars',
    similarButton: 'See all cars',
    staysEyebrow: 'Complete your trip',
    staysTitle: 'Stays near the pick-up',
    staysButton: 'See stays',
    tiers: [
      { name: 'Basic', note: 'CDW · standard excess', included: true },
      { name: 'Premium', note: 'Lower excess · gravel & windscreen' },
      { name: 'Platinum', note: 'Zero excess · sand & ash' },
    ],
    know: [
      { icon: 'calendar', title: 'Cancellation', text: 'Free cancellation up to 14 days before pick-up.' },
      { icon: 'shield', title: 'Deposit', text: 'Held on your card, released after return.' },
      { icon: 'users', title: 'Driver age', text: 'Minimum 20 years, licence held 2+ years.' },
    ],
    winter: 'Winter rental: winter tyres included. Gravel roads need extra care.',
    process: [
      { title: 'Request', text: 'You send a request with dates, insurance and extras. Nothing is charged yet.' },
      { title: 'Approval', text: 'The host confirms the car. You pay the prepayment to lock it in.' },
      { title: 'Pick-up', text: 'The balance is due when you collect the car.' },
    ],
  },
  guesthouse: {
    typeLabel: 'Guesthouse',
    aboutTitle: 'About this stay',
    featuresTitle: 'Amenities',
    includedTitle: "What's included",
    included: ['Fresh linen', 'Towels', 'Self check-in', 'Kitchen access'],
    extrasTitle: 'Optional extras',
    extrasLead: 'Added to the same booking.',
    pickupTitle: 'Where you’ll stay',
    knowTitle: 'Good to know',
    conditionsLink: 'Read the house rules',
    reviewsTitle: 'Reviews',
    writeReview: 'Write a review',
    emptyReviews: 'No reviews yet',
    emptyReviewsLead: 'Be the first to share how your stay went.',
    hostKicker: 'Meet the host',
    hostButton: 'View profile',
    bookCta: 'Request to book',
    processLink: 'View the booking process',
    insuranceTitle: '',
    stayTitle: '',
    similarTitle: 'Similar stays',
    similarButton: 'See all stays',
    staysEyebrow: '',
    staysTitle: '',
    tiers: [],
    know: [
      { icon: 'calendar', title: 'Cancellation', text: 'Free cancellation up to 14 days before check-in.' },
      { icon: 'users', title: 'Guests', text: 'The price is for the guests selected in your search.' },
      { icon: 'key', title: 'Check-in', text: 'Self check-in details arrive after the host approves.' },
    ],
    winter: '',
    process: [
      { title: 'Request', text: 'You send a request with dates. Nothing is charged yet.' },
      { title: 'Approval', text: 'The host confirms the stay. You pay the prepayment to lock it in.' },
      { title: 'Check-in', text: 'The balance is due before you arrive.' },
    ],
  },
}

function toIso(date) {
  if (!date) return ''
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${date.getFullYear()}-${month}-${day}`
}

function fromIso(value) {
  if (!value) return null
  const [year, month, day] = value.split('-').map(Number)
  if (!year || !month || !day) return null
  return new Date(year, month - 1, day)
}

function formatWhen(date, time) {
  if (!date) return 'Add date'
  const day = date.toLocaleDateString('en-GB', { weekday: 'short', day: 'numeric', month: 'short' })
  return time ? `${day} · ${time}` : day
}

function isWinter(date) {
  if (!date) return false
  const month = date.getMonth()
  return month >= 9 || month <= 3
}

function specTitle(item) {
  const text = `${item?.icon || ''} ${item?.label || ''}`.toLowerCase()
  if (text.includes('sleep')) return 'Sleeps'
  if (text.includes('seat')) return 'Seats'
  if (text.includes('auto') || text.includes('manual') || text.includes('gear') || text.includes('trans')) return 'Gearbox'
  if (text.includes('petrol') || text.includes('diesel') || text.includes('hybrid') || text.includes('fuel') || text.includes('electric')) return 'Engine'
  if (text.includes('bag')) return 'Luggage'
  if (text.includes('4×4') || text.includes('4x4') || text.includes('awd') || text.includes('drive') || text.includes('fwd')) return 'Drive'
  return 'Detail'
}

function specIcon(item) {
  const title = specTitle(item)
  if (title === 'Sleeps') return 'bed'
  if (title === 'Seats') return 'users'
  if (title === 'Luggage') return 'tent'
  return 'gauge'
}

export default function FigmaListing({
  listing,
  related = [],
  searchQuery = '',
  typeConfig,
  reviewTarget,
  onReviewsChange,
  onRequestBook,
  initialPickup,
  initialDropoff,
  bookingDatesRef,
  selectedAddonIds = [],
  onToggleAddon,
}) {
  const type = listing.listingType || 'campervan'
  const isStay = type === 'guesthouse'
  const { page } = usePageContent(`listing-${type}`)
  const copy = { ...FRAME[type], ...(page.frame || {}) }
  const { toast } = useToast()
  const fmt = useFormatPrice()
  const { prepayPercent } = useShopConfig()
  const [startDate, setStartDate] = useState(() => parseDateOnly(initialPickup))
  const [endDate, setEndDate] = useState(() => parseDateOnly(initialDropoff))
  const [tierIndex, setTierIndex] = useState(1)
  const [quoteRental, setQuoteRental] = useState(null)
  const [quoteTotal, setQuoteTotal] = useState(null)
  const [saved, setSaved] = useState(false)
  const [more, setMore] = useState(false)
  const [extraLimit, setExtraLimit] = useState(4)
  const [viewer, setViewer] = useState(null)
  const [sheet, setSheet] = useState('')
  const [stays, setStays] = useState([])

  const tiers = (listing.priceTypes || []).length
    ? listing.priceTypes.map((item, index) => ({
      id: item.id,
      name: item.name,
      note: item.description || item.short_description || (index === 0 ? 'Included with the rental' : 'Per day'),
      included: index === 0,
      daily: Number(item.from_price_per_day || item.daily_price || 0),
    }))
    : copy.tiers
  const tier = tiers[Math.min(tierIndex, Math.max(tiers.length - 1, 0))] || null

  useEffect(() => {
    if (!bookingDatesRef) return
    bookingDatesRef.current = {
      ...(bookingDatesRef.current || {}),
      pickupDate: startDate,
      dropoffDate: endDate,
      priceTypeId: tier?.id || '',
    }
  }, [bookingDatesRef, startDate, endDate, tier])

  useEffect(() => {
    if (isStay || !startDate || !endDate) {
      setQuoteRental(null)
      setQuoteTotal(null)
      return undefined
    }
    const payload = buildListingQuotePayload(listing, startDate, endDate, selectedAddonIds, '')
    if (!payload) return undefined
    if (tier?.id) payload.price_type_id = Number(tier.id)
    let cancelled = false
    const timer = window.setTimeout(() => {
      api.post('/orders/quote', payload)
        .then((res) => {
          if (cancelled) return
          const rental = Number(res.data?.base_rental_cents)
          const total = Number(res.data?.total_cents)
          setQuoteRental(Number.isFinite(rental) ? rental / 100 : null)
          setQuoteTotal(Number.isFinite(total) ? total / 100 : null)
        })
        .catch(() => {
          if (!cancelled) {
            setQuoteRental(null)
            setQuoteTotal(null)
          }
        })
    }, 300)
    return () => {
      cancelled = true
      window.clearTimeout(timer)
    }
  }, [isStay, listing, startDate, endDate, selectedAddonIds, tier])

  useEffect(() => {
    if (isStay) return undefined
    let cancelled = false
    api.get('/guest-houses', { params: { per_page: 4 } })
      .then((res) => {
        if (!cancelled) setStays((res.data?.data || []).slice(0, 4))
      })
      .catch(() => {
        if (!cancelled) setStays([])
      })
    return () => {
      cancelled = true
    }
  }, [isStay])

  const nights = startDate && endDate
    ? Math.max(1, Math.round((endDate.getTime() - startDate.getTime()) / 86400000))
    : 0
  const unit = isStay ? 'night' : 'day'
  const images = listing.images || []
  const hero = images[0]
  const thumbs = images.slice(1, 5)
  const specs = (listing.detailSpecs || []).filter((item) => !String(item.icon || '').includes('unit')).slice(0, 6)
  const units = (listing.detailSpecs || []).find((item) => String(item.icon || '').includes('unit'))
  const sleeps = (listing.detailSpecs || []).find((item) => /sleep|guest|bed/i.test(`${item.icon} ${item.label}`))
  const pickupNames = (listing.pickupLocations || []).map((item) => item.name).filter(Boolean)
  const pickupLine = pickupNames.length ? pickupNames.slice(0, 2).join(' · ') : (listing.location?.formattedLine || 'Iceland')
  const locationName = pickupNames[0] || listing.location?.city || 'Iceland'
  const features = listing.amenities || []
  const included = (listing.conditions || []).map((item) => item.title).filter(Boolean)
  const seededIncluded = (copy.included || []).map((item) => (typeof item === 'string' ? item : item.text)).filter(Boolean)
  const includedItems = included.length ? included : seededIncluded
  const extras = listing.addons || []
  const visibleExtras = extras.slice(0, extraLimit)
  const archive = type === 'car' ? '/cars' : isStay ? '/guesthouses' : '/campervans'
  const section = type === 'car' ? 'Cars & 4×4s' : isStay ? 'Guesthouses' : 'Campervans'
  const rental = quoteRental ?? (nights && listing.priceFromAmount ? listing.priceFromAmount * nights : null)
  const total = quoteTotal ?? rental
  const showWinter = !isStay && copy.winter && isWinter(startDate)

  const relatedCards = useMemo(() => related.slice(0, 3).map((item) => {
    if (isStay) return mapGuestHouseToResultCard(item, { searchQuery })
    return {
      id: item.id,
      name: item.name,
      image: item.thumbnail_url || item.main_image_path || '/figma/camper-card.png',
      href: `${archive}/${item.id}${searchQuery ? `?${searchQuery}` : ''}`,
      price: item.price_from || '',
      categoryName: item.category_name || copy.typeLabel,
    }
  }), [related, isStay, searchQuery, archive, copy.typeLabel])

  async function onShare() {
    const result = await sharePage({ title: listing.name, text: listing.name })
    if (result.ok && result.method === 'clipboard') toast('Link copied', 'success')
    else if (!result.ok && !result.aborted) toast('Could not share this page', 'error')
  }

  function book() {
    onRequestBook?.({ pickupDate: startDate, dropoffDate: endDate })
  }

  const timeLabel = (window) => (window?.from ? String(window.from).slice(0, 5) : '')

  return (
    <div className="tb-listing">
      <Container>
        <nav className="tb-crumbs" aria-label="Breadcrumb">
          <Link to="/">Home</Link>
          <span>/</span>
          <Link to={archive}>{section}</Link>
          <span>/</span>
          <span>Iceland</span>
          <span>/</span>
          <span>{listing.categoryName || copy.typeLabel}</span>
        </nav>

        <header className="tb-listing-head">
          <div>
            <div className="tb-pills">
              <span><FigmaIcon name={isStay ? 'heart' : 'tent'} size={13} />{copy.typeLabel}</span>
              {sleeps ? <span><FigmaIcon name="bed" size={13} />{sleeps.label}</span> : null}
              {units ? <span><FigmaIcon name="check" size={13} />{units.label}</span> : null}
            </div>
            <h1>{listing.name}</h1>
            <p className="tb-listing-where">
              <span><FigmaIcon name="pin" size={15} />{isStay ? 'Stay' : 'Pick-up'}: {pickupLine}</span>
              {listing.partner?.name ? <span><FigmaIcon name="key" size={15} />{listing.partner.name}</span> : null}
            </p>
          </div>
          <div className="tb-listing-actions">
            <button type="button" onClick={onShare}><FigmaIcon name="arrow" size={15} />Share</button>
            <button type="button" className={saved ? 'is-on' : ''} onClick={() => setSaved((value) => !value)}><FigmaIcon name="heart" size={15} />Save</button>
          </div>
        </header>

        <div className="tb-gallery">
          <button type="button" className="tb-gallery-hero" onClick={() => hero && setViewer(0)}>
            <img src={hero?.url || '/figma/camper-card.png'} alt={hero?.alt || listing.name} />
          </button>
          <div className="tb-gallery-side">
            {thumbs.map((image, index) => (
              <button key={image.url} type="button" onClick={() => setViewer(index + 1)}>
                <img src={image.url} alt={image.alt || ''} />
              </button>
            ))}
          </div>
          {images.length > 1 && (
            <button type="button" className="tb-gallery-all" onClick={() => setViewer(0)}>
              Show all {images.length} photos
            </button>
          )}
        </div>

        <div className="tb-listing-grid">
          <div className="tb-listing-main">
            {specs.length > 0 && (
              <div className="tb-specs">
                {specs.map((item) => (
                  <div key={`${item.icon}-${item.label}`}>
                    <FigmaIcon name={specIcon(item)} size={22} />
                    <small>{specTitle(item)}</small>
                    <strong>{item.label}</strong>
                  </div>
                ))}
              </div>
            )}

            <section>
              <h2>{copy.aboutTitle}</h2>
              <p>{more && listing.description?.more ? listing.description.more : listing.description?.short}</p>
              {listing.description?.more ? (
                <button type="button" className="tb-text-link" onClick={() => setMore((value) => !value)}>{more ? 'Show less' : 'Read more'}</button>
              ) : null}
            </section>

            {features.length > 0 && (
              <section>
                <h2>{copy.featuresTitle}</h2>
                <div className="tb-features">
                  {features.map((item, index) => (
                    <div key={item.name}>
                      <span><FigmaIcon name={FEATURE_ICONS[index % FEATURE_ICONS.length]} size={18} /></span>
                      {item.name}
                    </div>
                  ))}
                </div>
              </section>
            )}

            <section>
              <h2>{copy.includedTitle}</h2>
              <ul className="tb-included">
                {includedItems.map((item) => (
                  <li key={item}><span><FigmaIcon name="check" size={13} /></span>{item}</li>
                ))}
              </ul>
            </section>

            {extras.length > 0 && (
              <section>
                <h2>{copy.extrasTitle}</h2>
                <p className="tb-lead">{copy.extrasLead}</p>
                <div className="tb-extras">
                  {visibleExtras.map((item) => {
                    const on = selectedAddonIds.map(String).includes(String(item.id))
                    return (
                      <button key={item.id} type="button" className={on ? 'is-on' : ''} onClick={() => onToggleAddon?.(item.id)}>
                        <span><FigmaIcon name="tent" size={18} /></span>
                        <em>
                          <strong>{item.name}</strong>
                          <small>{item.price} {item.sub || ''}</small>
                        </em>
                        {on ? <FigmaIcon name="check" size={14} /> : <i />}
                      </button>
                    )
                  })}
                </div>
                {extras.length > extraLimit && (
                  <button type="button" className="tb-text-link" onClick={() => setExtraLimit(extras.length)}>Show {extras.length - extraLimit} more extras</button>
                )}
              </section>
            )}

            <section>
              <h2>{copy.pickupTitle}</h2>
              <div className="tb-pickup">
                <div className="tb-minimap" aria-hidden="true"><FigmaIcon name="pin" size={18} /></div>
                <div>
                  <p><FigmaIcon name="pin" size={18} /><span><small>Location</small><strong>{locationName}</strong></span></p>
                  <p><FigmaIcon name="calendar" size={18} /><span><small>{isStay ? 'Check-in' : 'Pick-up'}</small><strong>{timeLabel(listing.pickupTimeWindow) ? `From ${timeLabel(listing.pickupTimeWindow)}` : 'Times confirmed after approval'}</strong></span></p>
                  <p><FigmaIcon name="calendar" size={18} /><span><small>{isStay ? 'Check-out' : 'Drop-off'}</small><strong>{timeLabel(listing.dropoffTimeWindow) ? `Until ${timeLabel(listing.dropoffTimeWindow)}` : 'Same location'}</strong></span></p>
                  <p><FigmaIcon name="check" size={18} /><span><small>Bring</small><strong>{isStay ? 'The name on the booking' : 'Driving licence (2+ years), credit card for deposit'}</strong></span></p>
                </div>
              </div>
            </section>

            <section>
              <h2>{copy.knowTitle}</h2>
              <div className="tb-know">
                {(copy.know || []).map((item) => (
                  <article key={item.title}>
                    <FigmaIcon name={item.icon} size={20} />
                    <strong>{item.title}</strong>
                    <p>{item.text}</p>
                  </article>
                ))}
              </div>
              <button type="button" className="tb-text-link" onClick={() => setSheet('conditions')}>{copy.conditionsLink}</button>
            </section>

            <section className="tb-reviews">
              <div className="tb-reviews-head">
                <h2>{copy.reviewsTitle}</h2>
                <button type="button" onClick={() => setSheet('review')}>{copy.writeReview}</button>
              </div>
              {(listing.reviews || []).length === 0 ? (
                <div className="tb-reviews-empty">
                  <span><FigmaIcon name="sparkles" size={20} /></span>
                  <div>
                    <strong>{copy.emptyReviews}</strong>
                    <p>{copy.emptyReviewsLead}</p>
                  </div>
                </div>
              ) : (
                <div className="tb-review-list">
                  {listing.reviews.slice(0, 3).map((review) => (
                    <article key={review.id || review.author}>
                      <strong>{review.author || review.name || 'Guest'}</strong>
                      <p>{review.text || review.body || review.comment}</p>
                    </article>
                  ))}
                </div>
              )}
            </section>

            {(listing.owner || listing.partner) && (
              <section className="tb-host">
                <span>{(listing.owner?.initial || listing.partner?.name || 'H').slice(0, 1)}</span>
                <div>
                  <small>{copy.hostKicker}</small>
                  <strong>{listing.owner?.name || listing.partner?.name}</strong>
                  <p>{[listing.owner?.tripsLabel, listing.owner?.reviewsLabel].filter(Boolean).join(' · ') || 'Host on MyTerraBook'}</p>
                </div>
                {listing.partner?.id ? <Link to={archive}>{copy.hostButton}</Link> : null}
              </section>
            )}
          </div>

          <BookingCard
            copy={copy}
            isStay={isStay}
            fmt={fmt}
            priceFrom={listing.priceFrom}
            unit={unit}
            startDate={startDate}
            endDate={endDate}
            setStartDate={setStartDate}
            setEndDate={setEndDate}
            timeLabel={timeLabel}
            listing={listing}
            locationName={locationName}
            tiers={tiers}
            tierIndex={tierIndex}
            setTierIndex={setTierIndex}
            showWinter={showWinter}
            book={book}
            prepayPercent={prepayPercent}
            nights={nights}
            rental={rental}
            total={total}
            onProcess={() => setSheet('process')}
          />
        </div>
      </Container>

      {relatedCards.length > 0 && (
        <section className="tb-similar">
          <Container>
            <div className="tb-similar-head">
              <h2>{copy.similarTitle}</h2>
              <Link to={archive}>{copy.similarButton}</Link>
            </div>
            <div className="tb-similar-grid">
              {relatedCards.map((card) => (
                <Link key={card.id} to={card.href} className="tb-similar-card">
                  <img src={card.image || '/figma/camper-card.png'} alt="" />
                  <strong>{card.name}</strong>
                  {card.price ? <em>{card.price}</em> : null}
                </Link>
              ))}
            </div>
          </Container>
        </section>
      )}

      {!isStay && stays.length > 0 && (
        <section className="tb-near">
          <Container>
            <div className="tb-similar-head">
              <div>
                <small>{copy.staysEyebrow}</small>
                <h2>{copy.staysTitle}</h2>
              </div>
              <Link to="/guesthouses">{copy.staysButton}</Link>
            </div>
            <div className="tb-near-grid">
              {stays.map((house) => {
                const card = mapGuestHouseToResultCard(house)
                return (
                  <Link key={house.id} to={card.href} className="tb-similar-card">
                    <img src={card.image || '/figma/camper-card.png'} alt="" />
                    <strong>{card.name}</strong>
                    <em>{card.price} / night</em>
                  </Link>
                )
              })}
            </div>
          </Container>
        </section>
      )}

      <div className="tb-bookbar">
        <div>
          <strong>{listing.priceFrom || '—'}</strong>
          <small> / {unit}</small>
        </div>
        <button type="button" onClick={() => setSheet('book')}>{copy.bookCta}</button>
      </div>

      {viewer != null && (
        <div className="tb-viewer" onClick={() => setViewer(null)}>
          <button type="button" className="tb-viewer-x" onClick={() => setViewer(null)}>Close</button>
          <img src={images[viewer]?.url || '/figma/camper-card.png'} alt="" onClick={(event) => event.stopPropagation()} />
          <div className="tb-viewer-nav" onClick={(event) => event.stopPropagation()}>
            <button type="button" onClick={() => setViewer((index) => (index + images.length - 1) % images.length)}>Previous</button>
            <span>{viewer + 1} / {images.length}</span>
            <button type="button" onClick={() => setViewer((index) => (index + 1) % images.length)}>Next</button>
          </div>
        </div>
      )}

      {sheet && (
        <div className="tb-sheet-backdrop" onClick={() => setSheet('')}>
          <div className="tb-sheet tb-listing-sheet" role="dialog" onClick={(event) => event.stopPropagation()}>
            <header>
              <h2>
                {sheet === 'review' && copy.writeReview}
                {sheet === 'conditions' && copy.conditionsLink}
                {sheet === 'process' && 'Booking process'}
                {sheet === 'book' && copy.bookCta}
              </h2>
              <button type="button" onClick={() => setSheet('')}><FigmaIcon name="x" size={16} /></button>
            </header>
            {sheet === 'review' && <ListingWriteReview reviewTarget={reviewTarget} onReviewsChange={onReviewsChange} />}
            {sheet === 'conditions' && (
              listing.rentalTermsHtml
                ? <div className="tb-prose" dangerouslySetInnerHTML={{ __html: listing.rentalTermsHtml }} />
                : <ul className="tb-included">{includedItems.map((item) => <li key={item}><span><FigmaIcon name="check" size={13} /></span>{item}</li>)}</ul>
            )}
            {sheet === 'process' && (
              <ol className="tb-process">
                {(page.bookingSteps?.length ? page.bookingSteps : copy.process).map((step, index) => (
                  <li key={step.title || index}><strong>{step.title}</strong><p>{step.text}</p></li>
                ))}
              </ol>
            )}
            {sheet === 'book' && (
              <BookingCard
                copy={copy}
                isStay={isStay}
                fmt={fmt}
                priceFrom={listing.priceFrom}
                unit={unit}
                startDate={startDate}
                endDate={endDate}
                setStartDate={setStartDate}
                setEndDate={setEndDate}
                timeLabel={timeLabel}
                listing={listing}
                locationName={locationName}
                tiers={tiers}
                tierIndex={tierIndex}
                setTierIndex={setTierIndex}
                showWinter={showWinter}
                book={() => { setSheet(''); book() }}
                prepayPercent={prepayPercent}
                nights={nights}
                rental={rental}
                total={total}
                onProcess={() => setSheet('process')}
              />
            )}
          </div>
        </div>
      )}
    </div>
  )
}

function BookingCard({
  copy, isStay, fmt, priceFrom, unit, startDate, endDate, setStartDate, setEndDate,
  timeLabel, listing, locationName, tiers, tierIndex, setTierIndex, showWinter, book,
  prepayPercent, nights, rental, total, onProcess,
}) {
  const tier = tiers[tierIndex]
  const money = (amount) => (amount == null ? '—' : fmt.format(amount))
  return (
    <aside className="tb-book">
      <p className="tb-book-price"><small>from</small><strong>{priceFrom || '—'}</strong><em>/ {unit}</em></p>
      <div className="tb-book-dates">
        <label>
          <small>{isStay ? 'CHECK-IN' : 'PICK-UP'}</small>
          <strong>{formatWhen(startDate, timeLabel(listing.pickupTimeWindow))}</strong>
          <input type="date" value={toIso(startDate)} onChange={(event) => setStartDate(fromIso(event.target.value))} />
        </label>
        <label>
          <small>{isStay ? 'CHECK-OUT' : 'DROP-OFF'}</small>
          <strong>{formatWhen(endDate, timeLabel(listing.dropoffTimeWindow))}</strong>
          <input type="date" value={toIso(endDate)} onChange={(event) => setEndDate(fromIso(event.target.value))} />
        </label>
        <div>
          <small>{isStay ? 'WHERE' : 'PICK-UP / DROP-OFF'}</small>
          <strong>{locationName}{!isStay && locationName ? ` → ${locationName}` : ''}</strong>
        </div>
      </div>
      {!isStay && tiers.length > 0 && (
        <div className="tb-insure">
          <p><FigmaIcon name="shield" size={15} />{copy.insuranceTitle}</p>
          {tiers.map((item, index) => (
            <button key={item.name} type="button" className={index === tierIndex ? 'is-on' : ''} onClick={() => setTierIndex(index)}>
              <i />
              <span><strong>{item.name}</strong><small>{item.note}</small></span>
              <em>{item.included || !item.daily ? 'Included' : `${fmt.format(item.daily)} / day`}</em>
            </button>
          ))}
        </div>
      )}
      {showWinter && <p className="tb-winter"><FigmaIcon name="snowflake" size={16} />{copy.winter}</p>}
      <button type="button" className="tb-book-cta" onClick={book}>{copy.bookCta} <FigmaIcon name="arrow" size={16} /></button>
      <p className="tb-book-note">{prepayPercent}% prepayment on approval · balance due on {isStay ? 'arrival' : 'pick-up'}</p>
      <button type="button" className="tb-text-link" onClick={onProcess}>{copy.processLink}</button>
      <div className="tb-lines">
        <p><span>{isStay ? 'Nightly rate' : 'Daily rate'}{nights ? ` × ${nights} ${nights === 1 ? unit : `${unit}s`}` : ''}</span><strong>{money(rental)}</strong></p>
        {!isStay && <p><span>Optional extras</span><strong>{nights ? money(0) : '—'}</strong></p>}
        {!isStay && tier && !tier.included && <p><span>{tier.name} insurance{nights ? ` × ${nights}` : ''}</span><strong>{nights && tier.daily ? money(tier.daily * nights) : '—'}</strong></p>}
        <p><span>Service fee</span><strong>{total != null && rental != null ? money(Math.max(0, total - rental)) : '—'}</strong></p>
      </div>
      <div className="tb-total"><span>Total</span><strong>{money(total)}</strong></div>
      {!isStay && (
        <Link to="/guesthouses" className="tb-add-stay">
          <span><FigmaIcon name="bed" size={17} /></span>
          <em><strong>{copy.stayTitle}</strong><small>{copy.stayText}</small></em>
          <FigmaIcon name="arrow" size={16} />
        </Link>
      )}
    </aside>
  )
}
