import { Check, ChevronRight, X } from 'lucide-react'

/**
 * Shows the host exactly what's required before a listing can be submitted for
 * review. Each item: { label, done, focusId?, step? }.
 * Pass onGoTo to let hosts jump to the related field from the checklist.
 */
export default function HostReadinessChecklist({ items = [], title = 'Before you submit', onGoTo }) {
  const doneCount = items.filter((i) => i.done).length
  const allDone = items.length > 0 && doneCount === items.length

  return (
    <div className={`host-readiness${allDone ? ' is-ready' : ''}`}>
      <div className="host-readiness__head">
        <strong>{title}</strong>
        <span className="host-readiness__count">
          {allDone ? 'All set, ready to submit' : `${doneCount}/${items.length} complete`}
        </span>
      </div>
      <ul className="host-readiness__list">
        {items.map((item) => (
          <li key={item.label} className={item.done ? 'is-done' : 'is-todo'}>
            <span className="host-readiness__icon" aria-hidden>
              {item.done ? <Check size={14} strokeWidth={3} /> : <X size={14} strokeWidth={3} />}
            </span>
            <span className="host-readiness__label">{item.label}</span>
            {onGoTo && (item.focusId || item.step != null) && (
              <button
                type="button"
                className="host-readiness__go"
                onClick={() => onGoTo(item)}
              >
                {item.done ? 'Review' : 'Complete setup'}
                <ChevronRight size={14} aria-hidden />
              </button>
            )}
          </li>
        ))}
      </ul>
    </div>
  )
}
