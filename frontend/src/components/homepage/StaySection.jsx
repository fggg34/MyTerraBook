import ProductCard from './ProductCard'

export default function StaySection({ heading, subtitle, allLabel, allHref, cards = [] }) {
  return (
    <section className="stay">
      <div className="wrap">
        <div className="stay-head">
          <div>
            {heading && <h2>{heading}</h2>}
            {subtitle && <p className="stay-sub">{subtitle}</p>}
          </div>
          {allLabel && (
            <a className="stay-all" href={allHref || '#'}>
              {allLabel}
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
                <path d="M5 12h14M13 6l6 6-6 6" />
              </svg>
            </a>
          )}
        </div>
        <div className="stay-grid">
          {cards.map((card) => (
            <ProductCard
              key={card.name}
              name={card.name}
              image={card.image}
              badge={card.badge}
              specs={card.specs}
              price={card.price}
              per="night"
              simpleSpecs
            />
          ))}
        </div>
      </div>
    </section>
  )
}
