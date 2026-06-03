import { useMemo, useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { QUICK_FILTERS, SORT_OPTIONS, VEHICLE_TYPES } from '../../data/searchResultsConfig'

const CAT_ICONS = {
  camper: (
    <svg viewBox="0 0 28 18" fill="none" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round">
      <path d="M1 13V6.5C1 5.7 1.7 5 2.5 5H16l5 4h4.5C26.3 9 27 9.7 27 10.5V13" />
      <path d="M1 13h3M11 13h6M24 13h3" />
      <circle cx="7.5" cy="13.5" r="2.5" />
      <circle cx="20.5" cy="13.5" r="2.5" />
      <path d="M5 5V2.5h7V5" />
    </svg>
  ),
  car: (
    <svg viewBox="0 0 28 18" fill="none" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round">
      <path d="M2 12V9.2c0-.5.3-.9.8-1L6 7l2.8-3.4c.4-.5 1-.8 1.6-.8h7.5c.7 0 1.3.3 1.7.9L22 7l3.2.9c.5.1.8.5.8 1V12" />
      <path d="M2 12h3M11 12h6M23 12h3M6 7h16" />
      <circle cx="8" cy="12.5" r="2.4" />
      <circle cx="20" cy="12.5" r="2.4" />
    </svg>
  ),
}

function formatDateRange(pickupAt, dropoffAt) {
  if (!pickupAt || !dropoffAt) return 'Select dates'
  const p = new Date(pickupAt)
  const d = new Date(dropoffAt)
  if (Number.isNaN(p.getTime()) || Number.isNaN(d.getTime())) return 'Select dates'
  const fmt = (dt) => dt.toLocaleDateString('en-GB', { day: 'numeric', month: 'short' })
  return `${fmt(p)} – ${fmt(d)}`
}

export default function SearchResultsChrome({
  vehicleType,
  pickupLabel,
  dropoffLabel,
  query,
  updateSearch,
  totalCount,
  config,
  sort,
  setSort,
  sortLabel,
  quickFilters,
  toggleQuick,
  clearFilters,
  hasActiveFilters,
  filters,
  setFilters,
}) {
  const navigate = useNavigate()
  const [sortOpen, setSortOpen] = useState(false)
  const [openPop, setOpenPop] = useState(null)

  const dateLabel = useMemo(() => formatDateRange(query.pickup_at, query.dropoff_at), [query.pickup_at, query.dropoff_at])
  const drivers = query.drivers || '2'

  const switchVehicle = (type) => {
    const target = VEHICLE_TYPES[type]?.route || '/campervans'
    const params = new URLSearchParams()
    Object.entries(query).forEach(([k, v]) => {
      if (v) params.set(k, v)
    })
    navigate(`${target}?${params.toString()}`)
  }

  const onUpdate = () => {
    const params = new URLSearchParams()
    Object.entries(query).forEach(([k, v]) => {
      if (v) params.set(k, v)
    })
    navigate(`${config.route}?${params.toString()}`)
  }

  return (
    <div className="search-chrome chrome" id="searchChrome">
      <div className="hsearch" id="hsearch">
        <div className="hsearch-inner">
          <div className="hcats" id="hcats">
            <button
              className={`hcat ${vehicleType === 'campervan' ? 'active' : ''}`}
              type="button"
              data-cat="camper"
              onClick={() => switchVehicle('campervan')}
            >
              {CAT_ICONS.camper}
              Campervan
            </button>
            <button
              className={`hcat ${vehicleType === 'car' ? 'active' : ''}`}
              type="button"
              data-cat="car"
              onClick={() => switchVehicle('car')}
            >
              {CAT_ICONS.car}
              Cars
            </button>
            <button className="hcat" type="button" data-cat="house" onClick={() => navigate('/#guesthouse')}>
              <svg viewBox="0 0 24 22" fill="none" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round">
                <path d="M4 20V9l8-6 8 6v11" />
                <path d="M2 20h20" />
                <rect x="9.5" y="12" width="5" height="8" />
              </svg>
              Guesthouses
            </button>
          </div>

          <div className="hsearch-bar">
            <div className="hfield" data-mode="vehicle">
              <span className="hf-label">Pick-up location</span>
              <span className="hf-val">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.9" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M12 21s7-6.3 7-11a7 7 0 1 0-14 0c0 4.7 7 11 7 11Z" />
                  <circle cx="12" cy="10" r="2.5" />
                </svg>
                {pickupLabel}
              </span>
            </div>
            <div className="hfield" data-mode="vehicle">
              <span className="hf-label">Drop-off location</span>
              <span className={`hf-val ${dropoffLabel === 'Same as pick-up' ? 'muted' : ''}`}>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.9" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M12 21s7-6.3 7-11a7 7 0 1 0-14 0c0 4.7 7 11 7 11Z" />
                  <circle cx="12" cy="10" r="2.5" />
                </svg>
                {dropoffLabel}
              </span>
            </div>
            <div className="hfield">
              <span className="hf-label">Pick-up → Drop-off</span>
              <span className="hf-val">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.9" strokeLinecap="round" strokeLinejoin="round">
                  <rect x="3" y="4.5" width="18" height="16" rx="2.5" />
                  <path d="M3 9h18M8 2.5v4M16 2.5v4" />
                </svg>
                {dateLabel}
              </span>
            </div>
            <div className="hfield ppl">
              <span className="hf-label">Drivers</span>
              <span className="hf-val">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.9" strokeLinecap="round" strokeLinejoin="round">
                  <circle cx="12" cy="8" r="4" />
                  <path d="M4 21c0-4 3.6-6.5 8-6.5s8 2.5 8 6.5" />
                </svg>
                {drivers}
              </span>
            </div>
            <button className="hsearch-btn" type="button" onClick={onUpdate}>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
                <circle cx="11" cy="11" r="7" />
                <path d="m20 20-3.2-3.2" />
              </svg>
              Update
            </button>
          </div>
        </div>
      </div>

      <div className="filterbar" id="filterbar">
        <div className="filterbar-inner">
          <div className="chips" id="chips">
            <button
              className={`chip ${openPop === 'price' ? 'open' : ''} ${filters.maxPrice < 500 ? 'active' : ''}`}
              type="button"
              onClick={() => setOpenPop(openPop === 'price' ? null : 'price')}
            >
              Price
              <svg className="caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
                <path d="m6 9 6 6 6-6" />
              </svg>
            </button>
            <button
              className={`chip ${openPop === 'trans' ? 'open' : ''} ${filters.transmission ? 'active' : ''}`}
              type="button"
              onClick={() => setOpenPop(openPop === 'trans' ? null : 'trans')}
            >
              Transmission
              <svg className="caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
                <path d="m6 9 6 6 6-6" />
              </svg>
            </button>
            <span className="chip-div" />
            {QUICK_FILTERS.map((qf) => (
              <button
                key={qf.id}
                className={`chip quick ${quickFilters.includes(qf.id) ? 'active' : ''}`}
                type="button"
                onClick={() => toggleQuick(qf.id)}
              >
                {qf.label}
              </button>
            ))}
            {hasActiveFilters && (
              <button className="chip clear" type="button" onClick={clearFilters}>
                Clear all ✕
              </button>
            )}
          </div>
          <div className="fb-right">
            <span className="result-count" id="resultCount">
              <b>{totalCount}</b> {totalCount === 1 ? config.unitSingular : config.unitPlural}
            </span>
            <div className="sortwrap">
              <button className={`sortbtn ${sortOpen ? 'open' : ''}`} type="button" id="sortBtn" onClick={() => setSortOpen(!sortOpen)}>
                Sort: <b id="sortLabel">{sortLabel}</b>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
                  <path d="m6 9 6 6 6-6" />
                </svg>
              </button>
              <div className={`sortmenu ${sortOpen ? 'show' : ''}`} id="sortMenu">
                {SORT_OPTIONS.map((opt) => (
                  <button
                    key={opt.id}
                    type="button"
                    data-sort={opt.id}
                    className={sort === opt.id ? 'sel' : ''}
                    onClick={() => {
                      setSort(opt.id)
                      setSortOpen(false)
                    }}
                  >
                    {opt.label}
                  </button>
                ))}
              </div>
            </div>
          </div>
        </div>
        <div className="scroll-progress" id="scrollProgress" />
      </div>

      {openPop === 'price' && (
        <div className="fpop show" style={{ position: 'absolute', zIndex: 70, left: 40, top: '100%' }}>
          <h5>Max price per day</h5>
          <input
            type="range"
            min={20}
            max={500}
            step={10}
            value={filters.maxPrice}
            onChange={(e) => setFilters({ ...filters, maxPrice: Number(e.target.value) })}
          />
          <div className="fpop-foot">
            <button className="fpop-apply" type="button" onClick={() => setOpenPop(null)}>
              Apply
            </button>
          </div>
        </div>
      )}
      {openPop === 'trans' && (
        <div className="fpop show" style={{ position: 'absolute', zIndex: 70, left: 140, top: '100%' }}>
          <h5>Transmission</h5>
          {['', 'automatic', 'manual'].map((t) => (
            <button
              key={t || 'any'}
              type="button"
              className={filters.transmission === t ? 'sel' : ''}
              onClick={() => setFilters({ ...filters, transmission: t })}
              style={{ display: 'block', width: '100%', textAlign: 'left', padding: '8px 12px', background: 'none', border: 'none', cursor: 'pointer' }}
            >
              {t ? t.charAt(0).toUpperCase() + t.slice(1) : 'Any'}
            </button>
          ))}
          <div className="fpop-foot">
            <button className="fpop-apply" type="button" onClick={() => setOpenPop(null)}>
              Apply
            </button>
          </div>
        </div>
      )}
    </div>
  )
}

export function SearchResultsHeaderPill({ pillText }) {
  return (
    <button className="hsearch-pill" id="hsearchPill" type="button">
      <span className="hsp-ic">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
          <circle cx="11" cy="11" r="7" />
          <path d="m20 20-3.2-3.2" />
        </svg>
      </span>
      <span className="hsp-text">{pillText}</span>
      <span className="hsp-edit">Edit</span>
    </button>
  )
}
