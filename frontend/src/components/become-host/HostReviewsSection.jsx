import { useRef } from 'react'
import useSectionReveal from '../../hooks/useSectionReveal'
import HostPhoto from './HostPhoto'
import HostStars, { HostRatingStars } from './HostStars'

function ReviewCard({ review, duplicate = false }) {
  return (
    <div className="host-rev-card" aria-hidden={duplicate || undefined}>
      <div className="host-rev-avatar">
        <HostPhoto src={review.image ?? ''} alt={review.name} style={review.image ? undefined : { background: review.fill }} />
      </div>
      <div className="host-rev-name">{review.name}</div>
      <div className="host-rev-role">{review.role}</div>
      <div className="host-rev-divider" />
      <HostStars />
      <blockquote>{review.quote}</blockquote>
    </div>
  )
}

export default function HostReviewsSection({
  reviews = { up: [], down: [] },
  heading = 'Our hosts talk about us.',
  subheading = "Thousands of Icelanders are already turning idle vehicles and spare rooms into income. Here's what they say.",
  rating = '4.8/5',
  ratingLabel = 'Based on 14K+ host reviews',
}) {
  const sectionRef = useRef(null)
  useSectionReveal(sectionRef, { revealDoneMs: 1400, threshold: 0.1 })

  const up = reviews.up ?? []
  const down = reviews.down ?? []
  if (!up.length && !down.length) return null

  const avatarFills = up.slice(0, 3).map((r) => r.fill ?? '#a9d4e6')

  return (
    <section className="host-revs" id="revs" ref={sectionRef}>
      <div className="wrap host-revs-grid">
        <div className="host-revs-aside">
          <h2>{heading}</h2>
          <p className="host-revs-lead">{subheading}</p>
          <div className="host-revs-score">
            <div className="host-revs-avatars">
              {avatarFills.map((fill, i) => (
                <HostPhoto key={i} src="" alt="" style={{ background: fill }} />
              ))}
            </div>
            <div>
              <div className="host-revs-rating">
                {rating}
                <HostRatingStars />
              </div>
              <div className="host-revs-rating-lbl">{ratingLabel}</div>
            </div>
          </div>
        </div>
        <div className="host-revs-marquee">
          <div className="host-revs-col host-revs-col--up">
            {up.map((review) => (
              <ReviewCard key={review.name} review={review} />
            ))}
            {up.map((review) => (
              <ReviewCard key={`${review.name}-dup`} review={review} duplicate />
            ))}
          </div>
          <div className="host-revs-col host-revs-col--down">
            {down.map((review) => (
              <ReviewCard key={review.name} review={review} />
            ))}
            {down.map((review) => (
              <ReviewCard key={`${review.name}-dup`} review={review} duplicate />
            ))}
          </div>
        </div>
      </div>
    </section>
  )
}
