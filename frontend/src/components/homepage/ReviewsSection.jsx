import { useMemo, useRef } from 'react'
import useSectionReveal from '../../hooks/useSectionReveal'

const AVATAR_FILLS = ['#a9d4e6', '#bcdcab', '#f1d79a', '#cdbbea', '#a4ddcd', '#f4c1a4']

function StarRow({ count = 5, className = '' }) {
  return (
    <span className={`rv-stars ${className}`} aria-hidden="true">
      {Array.from({ length: 5 }).map((_, i) => (
        <svg key={i} viewBox="0 0 24 24" fill={i < count ? 'currentColor' : 'none'} stroke="currentColor" strokeWidth="1.5">
          <path d="m12 2 2.9 6.3 6.9.8-5.1 4.7 1.4 6.8L12 17.6 5.9 20.6l1.4-6.8L2.2 9.1l6.9-.8L12 2Z" />
        </svg>
      ))}
    </span>
  )
}

function ReviewAvatar({ name, fill, avatarUrl }) {
  if (avatarUrl) {
    return (
      <img
        className="rv-av rv-av--photo"
        src={avatarUrl}
        alt=""
        loading="lazy"
        decoding="async"
      />
    )
  }

  return (
    <span className="rv-av" style={{ '--fill': fill || AVATAR_FILLS[0] }} aria-hidden="true">
      {name.charAt(0)}
    </span>
  )
}

function ReviewCard({ review, reviewerLabel, duplicate = false, style }) {
  return (
    <article className="rv-card" aria-hidden={duplicate || undefined} style={style}>
      <div className="rv-card-top">
        <StarRow count={review.stars ?? 5} className="rv-card-stars" />
        {review.relativeTime && <time className="rv-card-time">{review.relativeTime}</time>}
      </div>
      <blockquote className="rv-card-quote">{review.quote}</blockquote>
      <footer className="rv-card-by">
        <ReviewAvatar name={review.name} fill={review.fill} avatarUrl={review.avatarUrl} />
        <div className="rv-card-meta">
          <span className="rv-card-name">{review.name}</span>
          <span className="rv-card-role">{reviewerLabel}</span>
        </div>
      </footer>
    </article>
  )
}

function AvatarStack({ reviews }) {
  const faces = reviews.slice(0, 4)

  return (
    <div className="rv-avatar-stack" aria-hidden="true">
      {faces.map((review, index) => (
        <span key={`${review.name}-${index}`} className="rv-avatar-stack-item" style={{ zIndex: faces.length - index }}>
          <ReviewAvatar name={review.name} fill={review.fill} avatarUrl={review.avatarUrl} />
        </span>
      ))}
    </div>
  )
}

export default function ReviewsSection({
  eyebrow,
  heading,
  lead,
  rating,
  ratingCount,
  reviews = [],
  source = 'demo',
  isDemo = true,
  ctaLabel,
  ctaHref,
  trustLine = 'Trusted by travellers worldwide',
}) {
  const sectionRef = useRef(null)
  useSectionReveal(sectionRef, { revealDoneMs: 1800 })

  const ratingValue = useMemo(() => {
    const match = String(rating ?? '').match(/[\d.]+/)
    return match ? match[0] : '4.9'
  }, [rating])

  const reviewerLabel = source === 'google' && !isDemo ? 'Google reviewer' : 'Traveller'
  const ratingSourceLabel = source === 'google' && !isDemo ? 'Google' : 'MyTerraBook'
  const resolvedCtaLabel = ctaLabel || (source === 'google' && !isDemo ? 'Leave a Google Review' : null)
  const useMarquee = reviews.length > 3

  const trackReviews = useMemo(() => {
    if (!useMarquee) return reviews
    return [...reviews, ...reviews]
  }, [reviews, useMarquee])

  if (!reviews.length) return null

  return (
    <section className="reviews" ref={sectionRef} aria-label="Traveller reviews">
      <div className="wrap">
        <div className="rv-head">
          <div className="rv-head-copy">
            {eyebrow && (
              <span className="eyebrow rv-rise" style={{ '--d': '.04s' }}>
                {eyebrow}
              </span>
            )}
            {heading && (
              <h2 className="rv-rise" style={{ '--d': '.1s' }}>
                {heading}
              </h2>
            )}
            {lead && (
              <p className="rv-lead rv-rise" style={{ '--d': '.16s' }}>
                {lead}
              </p>
            )}
            {resolvedCtaLabel && ctaHref && (
              <a
                className="rv-cta rv-rise"
                style={{ '--d': '.22s' }}
                href={ctaHref}
                target="_blank"
                rel="noopener noreferrer"
              >
                {resolvedCtaLabel}
              </a>
            )}
          </div>

          {(rating || ratingCount) && (
            <div className="rv-head-stats rv-rise" style={{ '--d': '.14s' }}>
              <div className="rv-score-row">
                <span className="rv-score-num">{ratingValue}</span>
                <StarRow />
              </div>
              {ratingCount && (
                <p className="rv-score-meta">
                  Average rating across <strong>{ratingCount}</strong> on{' '}
                  <span className="rv-score-source">{ratingSourceLabel}</span>
                </p>
              )}
              <div className="rv-trust">
                <AvatarStack reviews={reviews} />
                <span className="rv-trust-line">{trustLine}</span>
              </div>
            </div>
          )}
        </div>

        <div
          className={`rv-track-wrap${useMarquee ? ' rv-track-wrap--marquee' : ''}`}
          onMouseEnter={(e) => e.currentTarget.classList.add('rv-track-wrap--paused')}
          onMouseLeave={(e) => e.currentTarget.classList.remove('rv-track-wrap--paused')}
        >
          <div className={`rv-track${useMarquee ? ' rv-track--scroll' : ' rv-track--grid'}`}>
            {trackReviews.map((review, index) => (
              <ReviewCard
                key={`${review.name}-${index}${index >= reviews.length ? '-dup' : ''}`}
                review={review}
                reviewerLabel={reviewerLabel}
                duplicate={index >= reviews.length}
                style={useMarquee ? undefined : { '--i': index }}
              />
            ))}
          </div>
          {useMarquee && (
            <>
              <div className="rv-track-fade rv-track-fade--left" aria-hidden="true" />
              <div className="rv-track-fade rv-track-fade--right" aria-hidden="true" />
            </>
          )}
        </div>
      </div>
    </section>
  )
}
