import { useMemo, useRef, useState } from 'react'
import useCarouselScrollGuard from '../../hooks/useCarouselScrollGuard'
import useHorizontalCarousel from '../../hooks/useHorizontalCarousel'
import useMediaQuery from '../../hooks/useMediaQuery'
import useReviewsMarquee from '../../hooks/useReviewsMarquee'
import useSectionReveal from '../../hooks/useSectionReveal'
import ReviewDetailDialog from './ReviewDetailDialog'

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

function CarouselNav({ direction, disabled, onClick, label }) {
  return (
    <button
      className={`carousel-nav ${direction}`}
      type="button"
      aria-label={label}
      disabled={disabled}
      onClick={onClick}
    >
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
        {direction === 'prev' ? <path d="M15 6l-6 6 6 6" /> : <path d="M9 6l6 6-6 6" />}
      </svg>
    </button>
  )
}

function ReviewCard({
  review,
  reviewerLabel,
  duplicate = false,
  style,
  onOpen,
  scrollGuardRef,
}) {
  const handleOpen = () => {
    if (duplicate || !onOpen) return
    if (scrollGuardRef?.current) return
    onOpen(review)
  }

  return (
    <article className="rv-card" aria-hidden={duplicate || undefined} style={style}>
      {!duplicate && onOpen && (
        <button
          type="button"
          className="rv-card-open"
          onClick={handleOpen}
          aria-label={`Read full review from ${review.name}`}
        />
      )}
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
  const trackWrapRef = useRef(null)
  const [activeReview, setActiveReview] = useState(null)
  const isMobile = useMediaQuery('(max-width: 768px)')
  const showMobileCarousel = isMobile && reviews.length > 1
  const showMarquee = reviews.length > 1
  const scrollGuardRef = useCarouselScrollGuard(trackWrapRef, { enabled: showMobileCarousel })

  useSectionReveal(sectionRef, { revealDoneMs: 1800 })
  const { pause, resume } = useReviewsMarquee(trackWrapRef, { enabled: showMarquee })
  const { scroll } = useHorizontalCarousel({
    trackRef: trackWrapRef,
    itemCount: reviews.length,
    cardSelector: '.rv-card:not([aria-hidden="true"])',
    gap: 12,
    enabled: showMobileCarousel,
    dragScroll: false,
  })

  const ratingValue = useMemo(() => {
    const match = String(rating ?? '').match(/[\d.]+/)
    return match ? match[0] : '4.9'
  }, [rating])

  const reviewerLabel = source === 'google' && !isDemo ? 'Google reviewer' : 'Traveller'
  const ratingSourceLabel = source === 'google' && !isDemo ? 'Google' : 'MyTerraBook'
  const resolvedCtaLabel = ctaLabel || (source === 'google' && !isDemo ? 'Leave a Google Review' : null)

  const trackReviews = useMemo(() => {
    if (showMarquee) return [...reviews, ...reviews]
    return reviews
  }, [reviews, showMarquee])

  const openReview = (review) => {
    pause()
    setActiveReview(review)
  }

  const closeReview = () => {
    setActiveReview(null)
    resume()
  }

  const stepCarousel = (direction) => {
    pause()
    scroll(direction)
    window.setTimeout(resume, 280)
  }

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
      </div>

      {reviews.length > 1 ? (
        <div className="rv-carousel-panel">
          <div
            className={`rv-track-wrap${showMobileCarousel ? ' rv-track-wrap--carousel' : ''}`}
            ref={trackWrapRef}
          >
            <div className={`rv-track rv-track--marquee${showMobileCarousel ? ' rv-track--carousel' : ''}`}>
              {trackReviews.map((review, index) => (
                <ReviewCard
                  key={`${review.name}-${index}${index >= reviews.length ? '-dup' : ''}`}
                  review={review}
                  reviewerLabel={reviewerLabel}
                  duplicate={showMarquee && index >= reviews.length}
                  style={{ '--i': index % reviews.length }}
                  onOpen={openReview}
                  scrollGuardRef={scrollGuardRef}
                />
              ))}
            </div>
          </div>
          {showMobileCarousel && (
            <div className="reviews-carousel-controls product-carousel-controls">
              <CarouselNav direction="prev" label="Previous review" onClick={() => stepCarousel(-1)} />
              <CarouselNav direction="next" label="Next review" onClick={() => stepCarousel(1)} />
            </div>
          )}
        </div>
      ) : (
        <div className="wrap">
          <div className="rv-track rv-track--single">
            <ReviewCard
              review={reviews[0]}
              reviewerLabel={reviewerLabel}
              style={{ '--i': 0 }}
              onOpen={openReview}
            />
          </div>
        </div>
      )}

      <ReviewDetailDialog
        open={Boolean(activeReview)}
        review={activeReview}
        reviewerLabel={reviewerLabel}
        onClose={closeReview}
      />
    </section>
  )
}
