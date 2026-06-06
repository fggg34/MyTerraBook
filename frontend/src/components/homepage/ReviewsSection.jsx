import { useMemo, useRef } from 'react'
import useSectionReveal from '../../hooks/useSectionReveal'

const AVATAR_FILLS = ['#a9d4e6', '#bcdcab', '#f1d79a', '#cdbbea', '#a4ddcd', '#f4c1a4']

function StarRow({ count = 5, className = '' }) {
  return (
    <span className={`rv2-stars ${className}`} aria-hidden="true">
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
        className="rv2-av rv2-av--photo"
        src={avatarUrl}
        alt=""
        loading="lazy"
        decoding="async"
      />
    )
  }

  return (
    <span className="rv2-av" style={{ '--fill': fill || AVATAR_FILLS[0] }} aria-hidden="true">
      {name.charAt(0)}
    </span>
  )
}

function ReviewCard({ review, duplicate = false }) {
  return (
    <article className="rv2-card" aria-hidden={duplicate || undefined}>
      <StarRow count={review.stars ?? 5} className="rv2-card-stars" />
      <blockquote className="rv2-card-quote">{review.quote}</blockquote>
      <footer className="rv2-card-by">
        <ReviewAvatar name={review.name} fill={review.fill} avatarUrl={review.avatarUrl} />
        <div className="rv2-card-meta">
          <span className="rv2-card-name">{review.name}</span>
          {review.relativeTime && <span className="rv2-card-time">{review.relativeTime}</span>}
        </div>
      </footer>
    </article>
  )
}

function splitColumns(reviews) {
  const up = []
  const down = []

  reviews.forEach((review, index) => {
    if (index % 2 === 0) up.push(review)
    else down.push(review)
  })

  return { up, down }
}

function SourceBadge({ source, isDemo }) {
  if (source === 'google' && !isDemo) {
    return (
      <span className="rv2-source rv2-source--google rv2-rise" style={{ '--d': '.14s' }}>
        <svg viewBox="0 0 24 24" aria-hidden="true">
          <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" />
          <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
          <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
          <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
        </svg>
        Live Google reviews
      </span>
    )
  }

  return (
    <span className="rv2-source rv2-source--demo rv2-rise" style={{ '--d': '.14s' }}>
      Sample reviews
    </span>
  )
}

export default function ReviewsSection({
  eyebrow,
  heading,
  rating,
  ratingCount,
  reviews = [],
  source = 'demo',
  isDemo = true,
}) {
  const sectionRef = useRef(null)
  useSectionReveal(sectionRef, { revealDoneMs: 2200 })

  const { up, down } = useMemo(() => splitColumns(reviews), [reviews])
  const ratingValue = useMemo(() => {
    const match = String(rating ?? '').match(/[\d.]+/)
    return match ? match[0] : '4.9'
  }, [rating])

  if (!reviews.length) return null

  return (
    <section className="reviews" ref={sectionRef} aria-label="Traveller reviews">
      <div className="reviews-aurora" aria-hidden="true">
        <span className="reviews-aurora-blob reviews-aurora-blob--a" />
        <span className="reviews-aurora-blob reviews-aurora-blob--b" />
        <span className="reviews-aurora-blob reviews-aurora-blob--c" />
      </div>

      <div className="wrap">
        <div className="reviews-box">
          <div className="reviews-layout">
            <div className="reviews-intro">
              {eyebrow && (
                <span className="eyebrow rv2-rise" style={{ '--d': '.04s' }}>
                  {eyebrow}
                </span>
              )}
              {heading && (
                <h2 className="rv2-rise" style={{ '--d': '.1s' }}>
                  {heading}
                </h2>
              )}

              <SourceBadge source={source} isDemo={isDemo} />

              {(rating || ratingCount) && (
                <div className="rv2-score rv2-rise" style={{ '--d': '.2s' }}>
                  <div className="rv2-score-ring" aria-hidden="true">
                    <svg viewBox="0 0 120 120">
                      <circle className="rv2-score-track" cx="60" cy="60" r="52" />
                      <circle
                        className="rv2-score-fill"
                        cx="60"
                        cy="60"
                        r="52"
                        style={{ '--pct': `${(parseFloat(ratingValue) / 5) * 100}%` }}
                      />
                    </svg>
                    <span className="rv2-score-num">{ratingValue}</span>
                  </div>
                  <div className="rv2-score-copy">
                    <StarRow />
                    {rating && <span className="rv2-score-label">{rating}</span>}
                    {ratingCount && (
                      <span className="rv2-score-count">
                        from <b>{ratingCount}</b>
                      </span>
                    )}
                  </div>
                </div>
              )}
            </div>

            <div
              className="rv2-marquee-wrap rv2-rise"
              style={{ '--d': '.26s' }}
              onMouseEnter={(e) => e.currentTarget.classList.add('rv2-marquee-wrap--paused')}
              onMouseLeave={(e) => e.currentTarget.classList.remove('rv2-marquee-wrap--paused')}
            >
              <div className="rv2-marquee">
                <div className="rv2-col rv2-col--up">
                  {up.map((review, index) => (
                    <ReviewCard key={`up-${review.name}-${index}`} review={review} />
                  ))}
                  {up.map((review, index) => (
                    <ReviewCard key={`up-${review.name}-${index}-dup`} review={review} duplicate />
                  ))}
                </div>
                <div className="rv2-col rv2-col--down">
                  {down.map((review, index) => (
                    <ReviewCard key={`down-${review.name}-${index}`} review={review} />
                  ))}
                  {down.map((review, index) => (
                    <ReviewCard key={`down-${review.name}-${index}-dup`} review={review} duplicate />
                  ))}
                </div>
              </div>
              <div className="rv2-marquee-fade rv2-marquee-fade--top" aria-hidden="true" />
              <div className="rv2-marquee-fade rv2-marquee-fade--bottom" aria-hidden="true" />
            </div>
          </div>
        </div>
      </div>
    </section>
  )
}
