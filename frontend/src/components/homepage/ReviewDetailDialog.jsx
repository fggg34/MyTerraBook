import { useEffect } from 'react'

function StarRow({ count = 5 }) {
  return (
    <span className="rv-stars" aria-hidden="true">
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
    return <img className="rv-av rv-av--photo" src={avatarUrl} alt="" />
  }

  return (
    <span className="rv-av" style={{ '--fill': fill || '#e9edf2' }} aria-hidden="true">
      {name.charAt(0)}
    </span>
  )
}

export default function ReviewDetailDialog({ open, review, reviewerLabel, onClose }) {
  useEffect(() => {
    if (!open) return undefined

    const onKeyDown = (event) => {
      if (event.key === 'Escape') onClose()
    }

    const previousOverflow = document.body.style.overflow
    document.body.style.overflow = 'hidden'
    document.addEventListener('keydown', onKeyDown)

    return () => {
      document.body.style.overflow = previousOverflow
      document.removeEventListener('keydown', onKeyDown)
    }
  }, [open, onClose])

  if (!open || !review) return null

  return (
    <div className="rv-dialog" role="dialog" aria-modal="true" aria-labelledby="rv-dialog-title">
      <button type="button" className="rv-dialog-backdrop" onClick={onClose} aria-label="Close review" />
      <div className="rv-dialog-panel">
        <button type="button" className="rv-dialog-close" onClick={onClose} aria-label="Close review">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" aria-hidden="true">
            <path d="M6 6l12 12M18 6 6 18" />
          </svg>
        </button>
        <div className="rv-dialog-top">
          <StarRow count={review.stars ?? 5} />
          {review.relativeTime && <time className="rv-card-time">{review.relativeTime}</time>}
        </div>
        <blockquote className="rv-dialog-quote" id="rv-dialog-title">
          {review.quote}
        </blockquote>
        <footer className="rv-card-by">
          <ReviewAvatar name={review.name} fill={review.fill} avatarUrl={review.avatarUrl} />
          <div className="rv-card-meta">
            <span className="rv-card-name">{review.name}</span>
            <span className="rv-card-role">{reviewerLabel}</span>
          </div>
        </footer>
      </div>
    </div>
  )
}
