import { Link } from 'react-router-dom'
import useHorizontalCarousel from '../../hooks/useHorizontalCarousel'
import { useStayListings } from '../../hooks/useHomepageListings'
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

export default function StaySection({ heading, subtitle, allLabel, allHref }) {
  const { cards, loading } = useStayListings()
  const { trackRef, scroll, atStart, atEnd } = useHorizontalCarousel({ itemCount: cards.length })

  return (
    <section className="stay">
      <div className="wrap">
        <div className="stay-head">
          <div>
            {heading && <h2>{heading}</h2>}
            {subtitle && <p className="stay-sub">{subtitle}</p>}
          </div>
          {allLabel && (
            <SectionLink className="section-all" href={allHref}>
              {allLabel}
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
                <path d="M5 12h14M13 6l6 6-6 6" />
              </svg>
            </SectionLink>
          )}
        </div>
        <div className="stay-panel">
          <button className="carousel-nav prev" type="button" aria-label="Previous" disabled={atStart} onClick={() => scroll(-1)}>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
              <path d="M15 6l-6 6 6 6" />
            </svg>
          </button>
          <div className="track" ref={trackRef}>
            {cards.length ? (
              cards.map((card) => (
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
              ))
            ) : !loading ? (
              <p className="stay-empty" role="status">No guesthouses available yet.</p>
            ) : null}
          </div>
          <button className="carousel-nav next" type="button" aria-label="Next" disabled={atEnd} onClick={() => scroll(1)}>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
              <path d="M9 6l6 6-6 6" />
            </svg>
          </button>
        </div>
      </div>
    </section>
  )
}
