import { Link } from 'react-router-dom'
import { normalizeSpec, renderProductCardSpec } from '../../utils/listingSpecIcons'

export default function ProductCard({
  name,
  image,
  imageAlt,
  badge = 'Extras included',
  specs = [],
  price,
  per = 'night',
  href,
}) {
  return (
    <article className="pcard">
      <div className="pcard-media">
        {badge && <span className="pbadge">{badge}</span>}
        <img src={image} alt={imageAlt || name} />
      </div>
      <div className="pcard-foot">
        <h3>{name}</h3>
        <div className="specs">
          {specs.map((spec) => {
            const { type, label } = normalizeSpec(spec)
            return renderProductCardSpec(type, label, `${type}-${label}`)
          })}
        </div>
        {price && (
          <div className="pcard-cta">
            <div className="pcard-price">
              <span className="ps-label">From</span>
              <span className="pill">{price}</span>
              <span className="ps-per">/ {per}</span>
            </div>
            {href ? (
              <Link className="pcard-book" to={href}>
                Book
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M5 12h14M13 6l6 6-6 6" />
                </svg>
              </Link>
            ) : (
              <button className="pcard-book" type="button">
                Book
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M5 12h14M13 6l6 6-6 6" />
                </svg>
              </button>
            )}
          </div>
        )}
      </div>
    </article>
  )
}
