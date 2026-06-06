import { Link } from 'react-router-dom'

const ARROW = (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
    <path d="M5 12h14M13 6l6 6-6 6" />
  </svg>
)

function PromoCta({ promo }) {
  if (!promo.cta) return null
  if (promo.href?.startsWith('/')) {
    return (
      <Link className="promo-cta" to={promo.href}>
        {promo.cta}
        {ARROW}
      </Link>
    )
  }
  return (
    <a className="promo-cta" href={promo.href || '#'}>
      {promo.cta}
      {ARROW}
    </a>
  )
}

export default function PromoTile({ promo, layout = 'card', style }) {
  const isLandscape = layout === 'landscape'

  return (
    <div
      className={`cell reveal promo-cell ${isLandscape ? 'promo-cell--landscape' : 'promo-cell--card'}`}
      style={style}
    >
      <div className={`promo ${isLandscape ? 'promo--landscape' : 'promo--card'}`}>
        <div className="promo-aurora" aria-hidden="true" />
        {isLandscape && (
          <div className="promo-landscape-media" aria-hidden="true">
            {promo.image ? (
              <img src={promo.image} alt={promo.image_alt || ''} />
            ) : (
              <div className="promo-landscape-fallback" />
            )}
          </div>
        )}
        <div className="promo-body">
          <h3>{promo.title}</h3>
          {promo.text && <p>{promo.text}</p>}
          <PromoCta promo={promo} />
        </div>
      </div>
    </div>
  )
}
