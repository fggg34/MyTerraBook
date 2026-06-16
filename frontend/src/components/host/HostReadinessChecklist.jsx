import { Check, X } from 'lucide-react'

/**
 * Shows the host exactly what's required before a listing can be submitted for
 * review. `items` is a list of { label, done } so the same component works for
 * cars and guesthouses (and the dashboard).
 */
export default function HostReadinessChecklist({ items = [], title = 'Before you submit' }) {
  const doneCount = items.filter((i) => i.done).length
  const allDone = items.length > 0 && doneCount === items.length

  return (
    <div className={`host-readiness${allDone ? ' is-ready' : ''}`}>
      <div className="host-readiness__head">
        <strong>{title}</strong>
        <span className="host-readiness__count">
          {allDone ? 'All set — ready to submit' : `${doneCount}/${items.length} complete`}
        </span>
      </div>
      <ul className="host-readiness__list">
        {items.map((item) => (
          <li key={item.label} className={item.done ? 'is-done' : 'is-todo'}>
            <span className="host-readiness__icon" aria-hidden>
              {item.done ? <Check size={14} strokeWidth={3} /> : <X size={14} strokeWidth={3} />}
            </span>
            {item.label}
          </li>
        ))}
      </ul>
    </div>
  )
}
