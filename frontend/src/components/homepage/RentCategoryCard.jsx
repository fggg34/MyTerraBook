import { Link } from 'react-router-dom'
import CmsImage from '../cms/CmsImage'

function CardLink({ href, className, style, children }) {
  if (href?.startsWith('/') && !href.startsWith('//')) {
    return (
      <Link to={href} className={className} style={style}>
        {children}
      </Link>
    )
  }

  return (
    <a href={href || '#'} className={className} style={style}>
      {children}
    </a>
  )
}

export default function RentCategoryCard({ card, className = 'rcard', style }) {
  return (
    <CardLink href={card.href} className={className} style={style}>
      <CmsImage src={card.image} alt={card.alt || card.name} />
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
  )
}
