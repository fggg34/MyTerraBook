import { Link } from 'react-router-dom'
import ImageSlot from './ImageSlot'

function CardLink({ href, className, children }) {
  const isInternal = href?.startsWith('/') && !href.startsWith('//')
  if (isInternal) {
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

export default function WhatWeRentSection({ heading, subtitle, cards = [] }) {
  return (
    <section className="hp-rent">
      <div className="homepage-wrap">
        <div className="hp-rent-header">
          {heading && <h2>{heading}</h2>}
          {subtitle && <p>{subtitle}</p>}
        </div>

        <div className="hp-rent-grid">
          {cards.map((card) => (
            <CardLink key={card.name} href={card.href} className="hp-rcard">
              <div className="hp-rcard-image">
                {card.image ? (
                  <img src={card.image} alt={card.alt || card.name} />
                ) : (
                  <ImageSlot label={card.name} />
                )}
              </div>
              <div className="hp-rcard-overlay" />
              <div className="hp-rcard-content">
                {card.listingCount && <div className="hp-rcard-meta">{card.listingCount}</div>}
                <div className="hp-rcard-bottom">
                  <div>
                    <h3>{card.name}</h3>
                    {card.tagline && <p>{card.tagline}</p>}
                  </div>
                  <span className="hp-rcard-arrow" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                      <path d="M7 17L17 7M17 7H9M17 7v8" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
                    </svg>
                  </span>
                </div>
              </div>
            </CardLink>
          ))}
        </div>
      </div>
    </section>
  )
}
