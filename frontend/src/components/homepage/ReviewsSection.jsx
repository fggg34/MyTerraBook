import { useCallback, useEffect, useRef, useState } from 'react'
import useSectionReveal from '../../hooks/useSectionReveal'

const AUTOPLAY_MS = 5500

function StarRow({ className = '' }) {
  return (
    <span className={`rv-stars ${className}`} aria-hidden="true">
      {Array.from({ length: 5 }).map((_, i) => (
        <svg key={i} viewBox="0 0 24 24" fill="currentColor">
          <path d="m12 2 2.9 6.3 6.9.8-5.1 4.7 1.4 6.8L12 17.6 5.9 20.6l1.4-6.8L2.2 9.1l6.9-.8L12 2Z" />
        </svg>
      ))}
    </span>
  )
}

function ReviewAvatar({ name, fill }) {
  return (
    <span
      className="rv-av"
      style={{ '--fill': fill }}
      aria-hidden="true"
    >
      {name.charAt(0)}
    </span>
  )
}

export default function ReviewsSection({ eyebrow, heading, rating, ratingCount, reviews = [] }) {
  const sectionRef = useRef(null)
  const routeRef = useRef(null)
  const pausedRef = useRef(false)
  const [active, setActive] = useState(0)

  useSectionReveal(sectionRef, { revealDoneMs: 1900 })

  const count = reviews.length
  const safeActive = count ? Math.min(active, count - 1) : 0
  const featured = reviews[safeActive]
  const routeReviews = reviews
    .map((review, index) => ({ review, index }))
    .filter(({ index }) => index !== safeActive)

  const scrollTo = useCallback((index) => {
    const route = routeRef.current
    if (!route) return
    const stop = route.querySelector(`[data-index="${index}"]`)
    if (!stop) return
    stop.scrollIntoView({ behavior: 'smooth', block: 'nearest' })
  }, [])

  const go = useCallback(
    (direction) => {
      if (!count) return
      setActive((current) => {
        const safe = count ? Math.min(current, count - 1) : 0
        return (safe + direction + count) % count
      })
    },
    [count],
  )

  const selectReview = (index) => {
    setActive(index)
    scrollTo(index)
  }

  const pauseAutoplay = useCallback(() => {
    pausedRef.current = true
  }, [])

  const resumeAutoplay = useCallback(() => {
    pausedRef.current = false
  }, [])

  const handleBlur = useCallback((event) => {
    if (!sectionRef.current?.contains(event.relatedTarget)) {
      resumeAutoplay()
    }
  }, [resumeAutoplay])

  useEffect(() => {
    if (count <= 1) return undefined
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return undefined

    const tick = () => {
      if (pausedRef.current || document.hidden) return
      setActive((current) => {
        const safe = Math.min(current, count - 1)
        return (safe + 1) % count
      })
    }

    const id = window.setInterval(tick, AUTOPLAY_MS)
    return () => window.clearInterval(id)
  }, [count])

  return (
    <section
      className="reviews"
      ref={sectionRef}
      aria-label="Traveller reviews"
      aria-roledescription="carousel"
      onMouseEnter={pauseAutoplay}
      onMouseLeave={resumeAutoplay}
      onFocusCapture={pauseAutoplay}
      onBlurCapture={handleBlur}
    >
      <div className="reviews-glow" aria-hidden="true" />
      <div className="wrap">
        <div className="reviews-box">
          <span className="reviews-watermark" aria-hidden="true">
            4.9
          </span>

          <div className="reviews-grid">
            <div className="reviews-aside">
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

              {(rating || ratingCount) && (
                <div className="reviews-score rv-rise" style={{ '--d': '.16s' }}>
                  <StarRow />
                  <div className="reviews-score-tx">
                    {rating && <span className="reviews-score-num">{rating}</span>}
                    {ratingCount && (
                      <span className="reviews-score-lbl">
                        from <b>{ratingCount}</b>
                      </span>
                    )}
                  </div>
                </div>
              )}

              {featured && (
                <figure
                  className="reviews-spotlight rv-rise"
                  style={{ '--d': '.22s' }}
                  key={safeActive}
                  aria-live="polite"
                >
                  <svg className="rv-quote-mark" viewBox="0 0 48 40" fill="currentColor" aria-hidden="true">
                    <path d="M18 0C8 0 0 8 0 18c0 10 8 18 18 18v-8c-5 0-10-5-10-10S13 8 18 8V0zm30 0c-10 0-18 8-18 18 0 10 8 18 18 18v-8c-5 0-10-5-10-10S43 8 48 8V0z" />
                  </svg>
                  <blockquote className="rv-spotlight-quote">{featured.quote}</blockquote>
                  <figcaption className="rv-spotlight-by">
                    <ReviewAvatar name={featured.name} fill={featured.fill} />
                    <span className="rv-spotlight-name">{featured.name}</span>
                  </figcaption>
                </figure>
              )}

              {count > 1 && (
                <div className="reviews-controls rv-rise" style={{ '--d': '.28s' }}>
                  <button className="rv-ctrl" type="button" aria-label="Previous review" onClick={() => go(-1)}>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
                      <path d="M15 6l-6 6 6 6" />
                    </svg>
                  </button>
                  <div className="rv-dots" role="tablist" aria-label="Choose a review">
                    {reviews.map((review, index) => (
                      <button
                        key={review.name}
                        type="button"
                        role="tab"
                        className={`rv-dot ${index === safeActive ? 'active' : ''}`}
                        aria-selected={index === safeActive}
                        aria-label={`Review by ${review.name}`}
                        onClick={() => selectReview(index)}
                      />
                    ))}
                  </div>
                  <button className="rv-ctrl" type="button" aria-label="Next review" onClick={() => go(1)}>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
                      <path d="M9 6l6 6-6 6" />
                    </svg>
                  </button>
                </div>
              )}
            </div>

            <div className="reviews-route-wrap rv-rise" style={{ '--d': '.18s' }}>
              <p className="reviews-route-label">More traveller stories</p>
              <div className="reviews-route" ref={routeRef}>
                {routeReviews.map(({ review, index }, listIndex) => (
                  <article
                    key={review.name}
                    className="rv-stop"
                    data-index={index}
                    style={{ '--accent': review.fill, '--i': listIndex }}
                  >
                    <button
                      type="button"
                      className="rv-stop-btn"
                      aria-pressed={false}
                      onClick={() => selectReview(index)}
                    >
                      <span className="rv-pin" aria-hidden="true">
                        <span className="rv-pin-dot" />
                      </span>
                      <div className="rv-card rv-card--picker">
                        <footer className="rv-card-by">
                          <ReviewAvatar name={review.name} fill={review.fill} />
                          <span className="rv-card-name">{review.name}</span>
                        </footer>
                        <p className="rv-card-teaser">{review.quote}</p>
                      </div>
                    </button>
                  </article>
                ))}
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  )
}
