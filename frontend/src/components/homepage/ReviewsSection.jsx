import { useRef } from 'react'
import useSectionReveal from '../../hooks/useSectionReveal'

function ReviewAvatar({ name, fill }) {
  return (
    <span
      className="r-av"
      style={{
        width: 52,
        height: 52,
        background: `linear-gradient(145deg, ${fill}, rgba(255,255,255,.65))`,
        display: 'grid',
        placeItems: 'center',
        fontFamily: 'var(--serif)',
        fontWeight: 700,
        fontSize: 15,
        color: 'var(--navy)',
      }}
      aria-hidden="true"
    >
      {name.charAt(0)}
    </span>
  )
}

export default function ReviewsSection({ eyebrow, heading, rating, ratingCount, reviews = [] }) {
  const sectionRef = useRef(null)
  useSectionReveal(sectionRef, { revealDoneMs: 1700 })

  return (
    <section className="reviews" ref={sectionRef}>
      <div className="wrap">
        <div className="r-head">
          {eyebrow && <span className="eyebrow">{eyebrow}</span>}
          {heading && <h2>{heading}</h2>}
          {(rating || ratingCount) && (
            <div className="r-rating">
              <span className="r-stars">
                {Array.from({ length: 5 }).map((_, i) => (
                  <svg key={i} viewBox="0 0 24 24" fill="currentColor">
                    <path d="m12 2 2.9 6.3 6.9.8-5.1 4.7 1.4 6.8L12 17.6 5.9 20.6l1.4-6.8L2.2 9.1l6.9-.8L12 2Z" />
                  </svg>
                ))}
              </span>
              <span>
                <b>{rating}</b> from {ratingCount}
              </span>
            </div>
          )}
        </div>

        <div className="r-deck">
          {reviews.map((review) => (
            <figure
              key={review.name}
              className="r-card"
              style={{
                '--rot': review.rot,
                '--ty': review.ty,
                '--fill': review.fill,
              }}
            >
              <blockquote className="r-quote">{review.quote}</blockquote>
              <figcaption className="r-by">
                <ReviewAvatar name={review.name} fill={review.fill} />
                <span className="r-name">{review.name}</span>
              </figcaption>
            </figure>
          ))}
        </div>
      </div>
    </section>
  )
}
