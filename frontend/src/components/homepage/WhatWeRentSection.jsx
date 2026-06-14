import { useMemo } from 'react'
import { Link } from 'react-router-dom'
import { resolveCmsImage } from '../../api'
import { useFormatPrice } from '../../hooks/useFormatPrice'
import { formatRentListingStats } from '../../utils/formatRentListingStats'

function CardLink({ href, className, children }) {
  if (href?.startsWith('/') && !href.startsWith('//')) {
    return (
      <Link to={href} className={className}>
        {children}
      </Link>
    )
  }
  return (
    <a href={href || '#'} className={className}>
      {children}
    </a>
  )
}

const CARD_IMAGE_FALLBACKS = [
  '/images/homepage/cardcamper.jpg',
  '/images/homepage/cardcar.jpg',
  '/images/homepage/cardhouse.jpg',
]

export default function WhatWeRentSection({ heading, subtitle, cards = [] }) {
  const priceFormatter = useFormatPrice()
  const enrichedCards = useMemo(
    () =>
      cards.map((card) => ({
        ...card,
        listingLabel: card.listingStats ? formatRentListingStats(card.listingStats, priceFormatter) : null,
      })),
    [cards, priceFormatter],
  )

  return (
    <section className="rent">
      <div className="wrap">
        <div className="rent-head">
          <div>{heading && <h2>{heading}</h2>}</div>
          {subtitle && <p className="sub">{subtitle}</p>}
        </div>
        <div className="cards">
          {enrichedCards.map((card, index) => (
            <CardLink key={card.name} href={card.href} className="rcard">
              <img
                src={resolveCmsImage(card.image, CARD_IMAGE_FALLBACKS[index])}
                alt={card.alt || card.name}
              />
              <div className="meta">
                {card.listingLabel && <span className="listings">{card.listingLabel}</span>}
                <h3>{card.name}</h3>
                {card.tagline && <span className="tag">{card.tagline}</span>}
              </div>
              <span className="go">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M7 17 17 7M9 7h8v8" />
                </svg>
              </span>
            </CardLink>
          ))}
        </div>
      </div>
    </section>
  )
}
