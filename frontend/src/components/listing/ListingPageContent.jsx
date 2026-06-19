import { Link } from 'react-router-dom'
import { useState } from 'react'
import useHorizontalCarousel from '../../hooks/useHorizontalCarousel'
import useMediaQuery from '../../hooks/useMediaQuery'
import { useToast } from '../../context/ToastContext'
import { useFormatPrice } from '../../hooks/useFormatPrice'
import { sharePage } from '../../utils/sharePage'
import ProductCard from '../homepage/ProductCard'
import { mapCarToResultCard } from '../../utils/mapCarToResultCard'
import { mapGuestHouseToResultCard } from '../../utils/mapGuestHouseToResultCard'
import ListingGallery from './ListingGallery'
import ListingReviewsSection from './ListingReviewsSection'
import ListingTabPanels from './ListingTabPanels'

function CarouselNav({ direction, disabled, onClick, label }) {
  return (
    <button
      className={`carousel-nav ${direction}`}
      type="button"
      aria-label={label}
      disabled={disabled}
      onClick={onClick}
    >
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
        {direction === 'prev' ? <path d="M15 6l-6 6 6 6" /> : <path d="M9 6l6 6-6 6" />}
      </svg>
    </button>
  )
}

export default function ListingPageContent({
  listing,
  related,
  searchQuery,
  typeConfig,
  reviewTarget,
  onReviewsChange,
  onRequestBook,
  initialPickup,
  initialDropoff,
  bookingDatesRef,
  openCalendarRef,
  selectedAddonIds = [],
  onToggleAddon,
}) {
  const { toast } = useToast()
  const price = useFormatPrice()
  const [bookingBlocked, setBookingBlocked] = useState(false)
  const isMobile = useMediaQuery('(max-width: 768px)')

  const handleShare = async () => {
    const result = await sharePage({ title: listing.name, text: listing.name })
    if (result.ok && result.method === 'clipboard') {
      toast('Link copied to clipboard', 'success')
    } else if (!result.ok && !result.aborted) {
      toast('Could not share this page', 'error')
    }
  }

  const detailBase =
    listing.listingType === 'car' ? '/cars' : listing.listingType === 'guesthouse' ? '/guesthouses' : '/campervans'
  const relatedCards = related.map((item) => {
    if (listing.listingType === 'guesthouse') {
      return mapGuestHouseToResultCard(item, { searchQuery })
    }
    const card = mapCarToResultCard(
      { ...item, categoryName: item.category_name },
      {
        searchQuery,
        categoryName: item.category_name,
        vehicleType: listing.listingType,
        priceFormatter: price,
      },
    )
    return { ...card, href: `${detailBase}/${item.id}${searchQuery ? `?${searchQuery}` : ''}` }
  })

  const showSimilarCarousel = isMobile && relatedCards.length > 1
  const { trackRef: simTrackRef, scroll: scrollSimilar, atStart: simAtStart, atEnd: simAtEnd } = useHorizontalCarousel({
    itemCount: relatedCards.length,
    gap: 12,
    enabled: showSimilarCarousel || relatedCards.length > 0,
  })

  return (
    <>
      <div className="listing-subbar">
        <div className="wrap listing-subbar__row">
          <Link className="listing-subbar__back" to={typeConfig.archiveRoute}>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
              <path d="m15 18-6-6 6-6" />
            </svg>
            <span>{typeConfig.archiveLabel}</span>
          </Link>
          <button className="listing-subbar__share" type="button" onClick={handleShare}>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.9" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
              <path d="M4 12v7a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-7" />
              <path d="M12 3v13M8 7l4-4 4 4" />
            </svg>
            <span>Share</span>
          </button>
        </div>
      </div>

      <ListingGallery images={listing.images} photoCount={listing.photoCount} />

      <div className="layout">
        <div className="wrap">
          <ListingTabPanels
            listing={listing}
            reviews={listing.reviews}
            reviewTarget={reviewTarget}
            onReviewsChange={onReviewsChange}
            onRequestBook={onRequestBook}
            initialPickup={initialPickup}
            initialDropoff={initialDropoff}
            bookingDatesRef={bookingDatesRef}
            openCalendarRef={openCalendarRef}
            selectedAddonIds={selectedAddonIds}
            onToggleAddon={onToggleAddon}
            onAvailabilityChange={({ datesUnavailable }) => setBookingBlocked(datesUnavailable)}
          />
        </div>
      </div>

      <ListingReviewsSection
        listing={listing}
        typeConfig={typeConfig}
        reviewTarget={reviewTarget}
        onReviewsChange={onReviewsChange}
      />

      {relatedCards.length > 0 && (
        <section className="similar">
          <div className="wrap">
            <div className="similar-head">
              <h2>{typeConfig.similarTitle}</h2>
            </div>
            <div className="similar-panel">
              {!showSimilarCarousel && (
                <CarouselNav direction="prev" label="Previous" disabled={simAtStart} onClick={() => scrollSimilar(-1)} />
              )}
              <div className={`track${showSimilarCarousel ? ' track--carousel' : ''}`} ref={simTrackRef}>
                {relatedCards.map((card) => (
                  <ProductCard key={card.id} {...card} />
                ))}
              </div>
              {!showSimilarCarousel && (
                <CarouselNav direction="next" label="Next" disabled={simAtEnd} onClick={() => scrollSimilar(1)} />
              )}
            </div>
            {showSimilarCarousel && (
              <div className="product-carousel-controls">
                <CarouselNav direction="prev" label="Previous" disabled={simAtStart} onClick={() => scrollSimilar(-1)} />
                <CarouselNav direction="next" label="Next" disabled={simAtEnd} onClick={() => scrollSimilar(1)} />
              </div>
            )}
          </div>
        </section>
      )}

      <div className="bp-overlay" id="bpOverlay" aria-hidden="true">
        <div className="bp-modal" role="dialog" aria-modal="true" aria-labelledby="bpTitle">
          <div className="bp-head">
            <h3 id="bpTitle">{typeConfig.bookingModalTitle}</h3>
            <p>{typeConfig.bookingModalLead}</p>
            <button className="bp-close" id="bpClose" type="button" aria-label="Close">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
                <path d="M6 6l12 12M18 6 6 18" />
              </svg>
            </button>
          </div>
          <div className="bp-steps">
            {typeConfig.bookingSteps.map((step, i) => (
              <div key={step.title} className="bp-step">
                <span className="bp-num">{i + 1}</span>
                <div className="bp-tx">
                  <h4>{step.title}</h4>
                  <p>{step.text}</p>
                  {step.tag ? <span className="bp-tag">{step.tag}</span> : null}
                </div>
              </div>
            ))}
          </div>
          <div className="bp-foot">
            <button
              className="bp-cta"
              id="bpCta"
              type="button"
              disabled={bookingBlocked}
              onClick={(e) => {
                e.stopPropagation()
                if (bookingBlocked) {
                  toast('Selected dates are not available for booking', 'error')
                  return
                }
                const overlay = document.getElementById('bpOverlay')
                overlay?.classList.remove('open')
                overlay?.setAttribute('aria-hidden', 'true')
                document.body.style.overflow = ''
                onRequestBook?.({
                  pickupDate: bookingDatesRef?.current?.pickupDate,
                  dropoffDate: bookingDatesRef?.current?.dropoffDate,
                })
              }}
            >
              {typeConfig.bookCta}
            </button>
            {typeConfig.bookingModalFootnote ? (
              <span className="bp-note">{typeConfig.bookingModalFootnote}</span>
            ) : null}
          </div>
        </div>
      </div>
    </>
  )
}
