import { useMemo, useRef, useState } from 'react'
import useDragScroll from '../../hooks/useDragScroll'
import {
  buildMarqueePhotos,
  computeRating,
  guestPhotosFromReviews,
  pickFeatureImage,
} from '../../utils/listingReviews'
import ListingWriteReview from './ListingWriteReview'

function ReviewCard({ review }) {
  const [expanded, setExpanded] = useState(false)
  const clamped = review.clamp && !expanded

  return (
    <div className="rcard-rev">
      <div className="rcard-head">
        <div className="rcard-av">
          {review.photoUrl ? <img src={review.photoUrl} alt="" /> : review.initial}
        </div>
        <div className="rcard-id">
          <span className="rcard-name">{review.name}</span>
          <span className="rcard-sub">
            {review.date}{' '}
            <span className="rc-star">
              <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2.5l2.9 6.1 6.6.8-4.9 4.6 1.3 6.6L12 18.9 6.1 21.2l1.3-6.6L2.5 9.9l6.6-.8L12 2.5z" />
              </svg>
              {review.score}
            </span>
          </span>
        </div>
      </div>
      <p className={`rcard-text ${clamped ? 'clamp' : ''}`}>{review.text}</p>
      {review.clamp ? (
        <button className="rcard-more" type="button" onClick={() => setExpanded((v) => !v)}>
          {expanded ? 'Show less' : 'Show more'}
        </button>
      ) : null}
    </div>
  )
}

export default function ListingReviewsSection({ listing, typeConfig, reviewTarget, onReviewsChange }) {
  const allReviews = listing.reviews || []
  const hasReviews = allReviews.length > 0
  const guestPhotos = useMemo(() => guestPhotosFromReviews(allReviews), [allReviews])
  const rating = useMemo(() => computeRating(listing.rating, allReviews), [listing.rating, allReviews])
  const featureImage = useMemo(() => pickFeatureImage(allReviews), [allReviews])
  const marqueePhotos = useMemo(() => buildMarqueePhotos(guestPhotos), [guestPhotos])
  const gpMarqueeRef = useRef(null)
  const revTrackRef = useRef(null)

  useDragScroll(gpMarqueeRef, {
    enabled: guestPhotos.length > 0,
    convertAnimationFrom: '.gp-track',
  })

  useDragScroll(revTrackRef, { enabled: hasReviews })

  if (!hasReviews) {
    return null
  }

  return (
    <>
      <section className="reviews-sec" id="reviews">
        <div className="wrap">
          <h2>{typeConfig.reviewsTitle}</h2>

          <div className="rev-summary">
            <div className="rev-summary-main">
              <div className="rev-summary-stats">
                <div className="rev-score">
                  <svg className="rs-star" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2.5l2.9 6.1 6.6.8-4.9 4.6 1.3 6.6L12 18.9 6.1 21.2l1.3-6.6L2.5 9.9l6.6-.8L12 2.5z" />
                  </svg>
                  <div className="rs-meta">
                    <span className="rs-num">{rating.score}</span>
                    <span className="rs-excellent">{rating.label}</span>
                    <a href="#reviews">{rating.reviewLinkLabel}</a>
                  </div>
                </div>
                <div className="rev-overall">
                  <div className="ro-label">Overall rating</div>
                  <div className="ro-bars">
                    {[5, 4, 3, 2, 1].map((n) => (
                      <div key={n} className="ro-bar">
                        <span className="ro-n">{n}</span>
                        <span className="ro-track">
                          <span className="ro-fill" data-w={n === 5 ? 100 : 0} />
                        </span>
                      </div>
                    ))}
                  </div>
                </div>
                {(listing.reviewCategories?.length ?? 0) > 0 ? (
                  <div className="rev-cats">
                    {listing.reviewCategories.map((rc) => (
                      <div key={rc.label} className="rev-cat">
                        <span className="rc-label">{rc.label}</span>
                        <span className="rc-val">
                          {rc.value}{' '}
                          <span className="rc-bar">
                            <i style={{ width: rc.width }} />
                          </span>
                        </span>
                      </div>
                    ))}
                  </div>
                ) : null}
              </div>

              <ListingWriteReview reviewTarget={reviewTarget} onReviewsChange={onReviewsChange} />
            </div>

            <div className="rev-carousel">
              <div className="rev-track" ref={revTrackRef}>
                {featureImage ? (
                  <div className="rev-feature">
                    <img src={featureImage} alt="" />
                  </div>
                ) : null}
                {allReviews.map((rev) => (
                  <ReviewCard key={rev.id} review={rev} />
                ))}
              </div>
            </div>
          </div>
        </div>
      </section>

      {guestPhotos.length > 0 ? (
        <section className="gphotos">
          <div className="wrap">
            <h2>{typeConfig.guestPhotosTitle}</h2>
          </div>
          <div className="gp-marquee" id="gpMarquee" ref={gpMarqueeRef}>
            <div className="gp-track">
              {marqueePhotos.map((url, i) => (
                <div
                  key={`${url}-${i}`}
                  className="gp-tile"
                  aria-hidden={i >= marqueePhotos.length / 2 ? true : undefined}
                >
                  <img src={url} alt="" />
                </div>
              ))}
            </div>
          </div>
        </section>
      ) : null}
    </>
  )
}
