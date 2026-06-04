import { useCallback, useEffect, useMemo, useState } from 'react'
import {
  buildUserReview,
  computeRating,
  mergeGuestPhotos,
  mergeReviews,
  pickFeatureImage,
} from '../../utils/listingReviews'

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

function ListingReviewForm({ onSubmit }) {
  const [name, setName] = useState('')
  const [score, setScore] = useState(5)
  const [text, setText] = useState('')
  const [photoPreview, setPhotoPreview] = useState(null)
  const [error, setError] = useState('')
  const [submitted, setSubmitted] = useState(false)

  useEffect(() => {
    return () => {
      if (photoPreview?.startsWith('blob:')) URL.revokeObjectURL(photoPreview)
    }
  }, [photoPreview])

  const onPhotoChange = (e) => {
    const file = e.target.files?.[0]
    if (!file) return
    if (!file.type.startsWith('image/')) {
      setError('Please choose an image file (JPG, PNG, or WebP).')
      return
    }
    if (photoPreview?.startsWith('blob:')) URL.revokeObjectURL(photoPreview)
    setPhotoPreview(URL.createObjectURL(file))
    setError('')
  }

  const clearPhoto = () => {
    if (photoPreview?.startsWith('blob:')) URL.revokeObjectURL(photoPreview)
    setPhotoPreview(null)
  }

  const handleSubmit = (e) => {
    e.preventDefault()
    if (!text.trim()) {
      setError('Please write a few words about your stay.')
      return
    }
    onSubmit({
      name,
      score,
      text,
      photoUrl: photoPreview,
    })
    setName('')
    setScore(5)
    setText('')
    clearPhoto()
    setError('')
    setSubmitted(true)
    setTimeout(() => setSubmitted(false), 4000)
  }

  return (
    <form className="rev-write" onSubmit={handleSubmit}>
      <div className="rev-write-head">
        <h3>Share your experience</h3>
        <p>Add a review and optional photo — it appears in guest reviews and guest photos.</p>
      </div>
      <div className="rev-write-grid">
        <label className="rev-field">
          <span className="rev-field-lab">Your name</span>
          <input
            type="text"
            value={name}
            onChange={(ev) => setName(ev.target.value)}
            placeholder="e.g. Alex K."
            maxLength={80}
          />
        </label>
        <div className="rev-field">
          <span className="rev-field-lab">Rating</span>
          <div className="rev-stars-pick" role="group" aria-label="Rating">
            {[1, 2, 3, 4, 5].map((n) => (
              <button
                key={n}
                type="button"
                className={`rev-star-btn ${n <= score ? 'on' : ''}`}
                aria-label={`${n} star${n > 1 ? 's' : ''}`}
                aria-pressed={n <= score}
                onClick={() => setScore(n)}
              >
                <svg viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 2.5l2.9 6.1 6.6.8-4.9 4.6 1.3 6.6L12 18.9 6.1 21.2l1.3-6.6L2.5 9.9l6.6-.8L12 2.5z" />
                </svg>
              </button>
            ))}
          </div>
        </div>
        <label className="rev-field rev-field-wide">
          <span className="rev-field-lab">Your review</span>
          <textarea
            value={text}
            onChange={(ev) => setText(ev.target.value)}
            placeholder="What was your trip like?"
            rows={4}
            maxLength={2000}
          />
        </label>
        <div className="rev-field rev-photo-field">
          <span className="rev-field-lab">Photo (optional)</span>
          <div className="rev-photo-row">
            <label className="rev-photo-upload">
              <input type="file" accept="image/*" onChange={onPhotoChange} />
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                <rect x="3" y="5" width="18" height="14" rx="2" />
                <circle cx="12" cy="12" r="3" />
                <path d="M8 5l1.5-2h5L16 5" />
              </svg>
              {photoPreview ? 'Change photo' : 'Upload photo'}
            </label>
            {photoPreview ? (
              <div className="rev-photo-preview">
                <img src={photoPreview} alt="Your upload preview" />
                <button type="button" className="rev-photo-remove" onClick={clearPhoto} aria-label="Remove photo">
                  ×
                </button>
              </div>
            ) : null}
          </div>
        </div>
      </div>
      {error ? <p className="rev-write-error">{error}</p> : null}
      {submitted ? <p className="rev-write-success">Thanks — your review is now visible below.</p> : null}
      <button className="rev-submit" type="submit">
        Post review
      </button>
    </form>
  )
}

export default function ListingReviewsSection({ listing, typeConfig }) {
  const [userReviews, setUserReviews] = useState([])

  const allReviews = useMemo(
    () => mergeReviews(listing.reviews, userReviews),
    [listing.reviews, userReviews],
  )
  const guestPhotos = useMemo(
    () => mergeGuestPhotos(userReviews, listing.guestPhotoUrls),
    [userReviews, listing.guestPhotoUrls],
  )
  const rating = useMemo(() => computeRating(listing.rating, allReviews), [listing.rating, allReviews])
  const featureImage = useMemo(
    () => pickFeatureImage(userReviews, guestPhotos, listing.images),
    [userReviews, guestPhotos, listing.images],
  )

  const onSubmitReview = useCallback(({ name, score, text, photoUrl }) => {
    setUserReviews((prev) => [buildUserReview({ name, score, text, photoUrl }), ...prev])
  }, [])

  const marqueePhotos = guestPhotos.length ? [...guestPhotos, ...guestPhotos] : []

  return (
    <>
      <section className="reviews-sec" id="reviews">
        <div className="wrap">
          <h2>{typeConfig.reviewsTitle}</h2>

          <div className="rev-summary">
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
          </div>

          <ListingReviewForm onSubmit={onSubmitReview} />

          <div className="rev-grid">
            <div className="rev-feature">
              <img src={featureImage} alt="" />
            </div>
            {allReviews.map((rev) => (
              <ReviewCard key={rev.id} review={rev} />
            ))}
          </div>
        </div>
      </section>

      <section className="gphotos">
        <div className="wrap">
          <h2>{typeConfig.guestPhotosTitle}</h2>
        </div>
        {marqueePhotos.length > 0 ? (
          <div className="gp-marquee" id="gpMarquee">
            <div className="gp-track">
              {marqueePhotos.map((url, i) => (
                <div
                  key={`${url}-${i}`}
                  className="gp-tile"
                  aria-hidden={i >= guestPhotos.length ? true : undefined}
                >
                  <img src={url} alt="" />
                </div>
              ))}
            </div>
          </div>
        ) : (
          <div className="wrap">
            <p className="gp-empty">Guest photos will appear here when travellers share them.</p>
          </div>
        )}
      </section>
    </>
  )
}
