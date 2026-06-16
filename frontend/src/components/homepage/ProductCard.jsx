import { Link } from 'react-router-dom'
import { normalizeSpec, renderProductCardSpec } from '../../utils/listingSpecIcons'

function BookButton() {
  return (
    <span className="pcard-book" aria-hidden="true">
      Book
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
        <path d="M5 12h14M13 6l6 6-6 6" />
      </svg>
    </span>
  )
}

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
      {href && (
        <Link className="pcard-stretch-link" to={href} aria-label={`View ${name}`} />
      )}
      <div className="pcard-media">
        {badge && <span className="pbadge">{badge}</span>}
        <img src={image} alt={imageAlt || name} />
      </div>
      <div className="pcard-foot">
        <h3>{name}</h3>
        <div className="pcard-details">
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
              <BookButton />
            </div>
          )}
        </div>
      </div>
    </article>
  )
}
