import { starPathIcon } from '../../data/becomeHostData'

export default function HostStars() {
  return (
    <div className="stars">
      {Array.from({ length: 5 }).map((_, i) => (
        <svg key={i} viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path d={starPathIcon} />
        </svg>
      ))}
    </div>
  )
}

export function HostRatingStars() {
  return (
    <span className="ag-stars">
      {Array.from({ length: 5 }).map((_, i) => (
        <svg key={i} viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path d={starPathIcon} />
        </svg>
      ))}
    </span>
  )
}
