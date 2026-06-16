export default function ListingRatingSummary({ rating, variant = 'default' }) {
  if (!rating) return null

  const block = (
    <div className="rblock">
      <div className="rscore">
        <svg className="star" viewBox="0 0 24 24" fill="currentColor" aria-hidden>
          <path d="M12 2.5l2.9 6.1 6.6.8-4.9 4.6 1.3 6.6L12 18.9 6.1 21.2l1.3-6.6L2.5 9.9l6.6-.8L12 2.5z" />
        </svg>
        <span className="num">{rating.score}</span>
      </div>
      <div className="rmeta">
        <span className="excellent">{rating.label}</span>
        <span className="ministars" aria-hidden>
          {[1, 2, 3, 4, 5].map((n) => (
            <svg key={n} viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 2.5l2.9 6.1 6.6.8-4.9 4.6 1.3 6.6L12 18.9 6.1 21.2l1.3-6.6L2.5 9.9l6.6-.8L12 2.5z" />
            </svg>
          ))}
        </span>
        <a href="#reviews">{rating.reviewLinkLabel}</a>
      </div>
    </div>
  )

  if (variant === 'details') {
    return <div className="listing-rating listing-rating--header">{block}</div>
  }

  return (
    <div className={`listing-rating listing-rating--${variant}`}>
      {block}
    </div>
  )
}
