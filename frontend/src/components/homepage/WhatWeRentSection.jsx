import { useMemo } from 'react'
import { useFormatPrice } from '../../hooks/useFormatPrice'
import useHorizontalCarousel from '../../hooks/useHorizontalCarousel'
import useMediaQuery from '../../hooks/useMediaQuery'
import { formatRentListingStats } from '../../utils/formatRentListingStats'
import RentCategoryCard from './RentCategoryCard'

export default function WhatWeRentSection({ heading, subtitle, cards = [] }) {
  const priceFormatter = useFormatPrice()
  const isMobile = useMediaQuery('(max-width: 768px)')
  const enrichedCards = useMemo(
    () =>
      cards.map((card) => ({
        ...card,
        listingLabel: card.listingStats ? formatRentListingStats(card.listingStats, priceFormatter) : null,
      })),
    [cards, priceFormatter],
  )
  const showCarousel = isMobile && enrichedCards.length > 1
  const { trackRef, scroll, atStart, atEnd } = useHorizontalCarousel({
    itemCount: enrichedCards.length,
    cardSelector: '.rcard',
    gap: 12,
    enabled: showCarousel,
  })

  return (
    <section className="rent">
      <div className="wrap">
        <div className="rent-head">
          <div>{heading && <h2>{heading}</h2>}</div>
          {subtitle && <p className="sub">{subtitle}</p>}
        </div>
        <div className="rent-panel">
          <div className={`cards${showCarousel ? ' cards--carousel' : ''}`} ref={showCarousel ? trackRef : undefined}>
            {enrichedCards.map((card) => (
              <RentCategoryCard key={card.name} card={card} />
            ))}
          </div>
        </div>
        {showCarousel && (
          <div className="rent-carousel-controls">
            <button
              className="carousel-nav prev"
              type="button"
              aria-label="Previous category"
              disabled={atStart}
              onClick={() => scroll(-1)}
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
                <path d="M15 6l-6 6 6 6" />
              </svg>
            </button>
            <button
              className="carousel-nav next"
              type="button"
              aria-label="Next category"
              disabled={atEnd}
              onClick={() => scroll(1)}
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
                <path d="M9 6l6 6-6 6" />
              </svg>
            </button>
          </div>
        )}
      </div>
    </section>
  )
}
