import { useRef } from 'react'
import { Link } from 'react-router-dom'
import ProductCard from './ProductCard'

function SectionLink({ href, className, children }) {
  if (href?.startsWith('/') && !href.startsWith('//')) {
    return (
      <Link to={href} className={className}>
        {children}
      </Link>
    )
  }
  return (
    <a className={className} href={href || '#'}>
      {children}
    </a>
  )
}

export default function StaySection({ heading, subtitle, allLabel, allHref, cards = [] }) {
  const trackRef = useRef(null)

  const scroll = (direction) => {
    const track = trackRef.current
    if (!track) return
    const card = track.querySelector('.pcard')
    const step = card ? card.getBoundingClientRect().width + 24 : 360
    track.scrollBy({ left: direction * step, behavior: 'smooth' })
  }

  return (
    <section className="stay">
      <div className="wrap">
        <div className="stay-head">
          <div>
            {heading && <h2>{heading}</h2>}
            {subtitle && <p className="stay-sub">{subtitle}</p>}
          </div>
          {allLabel && (
            <SectionLink className="stay-all" href={allHref}>
              {allLabel}
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
                <path d="M5 12h14M13 6l6 6-6 6" />
              </svg>
            </SectionLink>
          )}
        </div>
        <div className="stay-panel">
          <button className="carousel-nav prev" type="button" aria-label="Previous" onClick={() => scroll(-1)}>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
              <path d="M15 6l-6 6 6 6" />
            </svg>
          </button>
          <div className="track" ref={trackRef}>
            {cards.map((card) => (
              <ProductCard
                key={card.slug || card.id || card.name}
                name={card.name}
                image={card.image}
                badge={card.badge}
                specs={card.specs}
                price={card.price}
                per={card.per || 'night'}
                href={card.href}
              />
            ))}
          </div>
          <button className="carousel-nav next" type="button" aria-label="Next" onClick={() => scroll(1)}>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
              <path d="M9 6l6 6-6 6" />
            </svg>
          </button>
        </div>
      </div>
    </section>
  )
}
