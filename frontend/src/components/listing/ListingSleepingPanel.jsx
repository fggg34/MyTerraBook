import { useEffect } from 'react'

export default function ListingSleepingPanel({ sleeping, hideKicker = false }) {
  const beds = sleeping?.beds ?? []

  useEffect(() => {
    requestAnimationFrame(() => window.dispatchEvent(new Event('resize')))
  }, [beds.length])

  if (!beds.length) {
    return <p className="listing-empty-hint">No room details listed.</p>
  }

  const firstCap = `${beds[0].title} · ${beds[0].dim}`

  return (
    <>
      {!hideKicker ? (
        <div className="panel-kicker">
          {sleeping.kicker}
          <span className="pk-line" />
        </div>
      ) : null}
      <div className="sleep-grid">
        {beds.map((bed, i) => (
          <div
            key={bed.title}
            className={`bedcard${i === 0 ? ' sel' : ''}`}
            data-bed={i}
            data-cap={`${bed.title} · ${bed.dim}`}
          >
            <span className="b-pick" aria-hidden>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3.4" strokeLinecap="round" strokeLinejoin="round">
                <path d="m5 12 4 4 10-10" />
              </svg>
            </span>
            <span className="b-ic" aria-hidden>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round">
                <path d="M3 18v-6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v6" />
                <path d="M3 18h18M3 14h18" />
                <path d="M6 10V8a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v2" />
              </svg>
            </span>
            <h4>{bed.title}</h4>
            <p>{bed.text}</p>
            <span className="b-dim">{bed.dim}</span>
          </div>
        ))}
      </div>
      <div className="sleep-preview">
        {beds.map((bed, i) => (
          <img
            key={bed.title}
            className={`sleep-shot${i === 0 ? ' active' : ''}`}
            src={bed.image}
            alt={bed.title}
            loading="lazy"
          />
        ))}
        <span className="sleep-cap" id="sleepCap">{firstCap}</span>
      </div>
    </>
  )
}
