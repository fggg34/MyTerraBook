import { useRef } from 'react'

export default function PartnerLogoStrip({
  partners,
  totalCount,
  unitLabel,
  selectedId,
  onSelect,
}) {
  const scrollerRef = useRef(null)
  const selected = selectedId ? String(selectedId) : ''

  const scrollNext = () => {
    scrollerRef.current?.scrollBy({ left: 280, behavior: 'smooth' })
  }

  return (
    <section className="partner-strip" aria-label="Filter by partner">
      <div className="partner-strip__row">
        <button
          type="button"
          className={`partner-tile partner-tile--all${!selected ? ' is-active' : ''}`}
          aria-pressed={!selected}
          onClick={() => onSelect('')}
        >
          <span className="partner-tile__all-title">Show all</span>
          <span className="partner-tile__all-count">{totalCount} {unitLabel}</span>
        </button>

        <div className="partner-strip__scroller" ref={scrollerRef}>
          {partners.map((partner) => {
            const active = selected === String(partner.id)
            return (
              <button
                key={partner.id}
                type="button"
                className={`partner-tile${active ? ' is-active' : ''}`}
                aria-pressed={active}
                onClick={() => onSelect(String(partner.id))}
              >
                {partner.logo ? (
                  <img src={partner.logo} alt={partner.name} />
                ) : (
                  <span className="partner-tile__name">{partner.name}</span>
                )}
                <span className="partner-tile__count">{partner.count}</span>
              </button>
            )
          })}
        </div>

        {partners.length > 4 && (
          <button type="button" className="partner-strip__next" aria-label="More partners" onClick={scrollNext}>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round" aria-hidden>
              <path d="m9 6 6 6-6 6" />
            </svg>
          </button>
        )}
      </div>
    </section>
  )
}
