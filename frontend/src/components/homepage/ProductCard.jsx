import { Link } from 'react-router-dom'
import { usePageContent } from '../../context/SiteContentContext'
import { cmsText } from '../../data/searchFormCopy'
import { normalizeSpec, renderProductCardSpec } from '../../utils/listingSpecIcons'
import { partnerInitials } from '../../utils/mapCarToResultCard'

function BookButton({ label }) {
  return (
    <span className="pcard-book" aria-hidden="true">
      {label}
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
  partner,
}) {
  const { page: globalPage } = usePageContent('global')
  const priceFromLabel = cmsText(globalPage.cards?.priceFromLabel, 'From')
  const bookLabel = cmsText(globalPage.cards?.bookLabel, 'Book')

  return (
    <article className="pcard">
      {href && (
        <Link className="pcard-stretch-link" to={href} aria-label={`View ${name}`} />
      )}
      <div className="pcard-media">
        {badge && <span className="pbadge">{badge}</span>}
        <img src={image} alt={imageAlt || name} draggable={false} />
      </div>
      <div className="pcard-foot">
        {partner?.name && (
          <div className="pcard-partner">
            {partner.logo ? (
              <img src={partner.logo} alt={partner.name} />
            ) : (
              <span className="pcard-partner__mark" title={partner.name}>{partnerInitials(partner.name)}</span>
            )}
          </div>
        )}
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
                <span className="ps-label">{priceFromLabel}</span>
                <span className="pill">{price}</span>
                <span className="ps-per">/ {per}</span>
              </div>
              <BookButton label={bookLabel} />
            </div>
          )}
        </div>
      </div>
    </article>
  )
}
