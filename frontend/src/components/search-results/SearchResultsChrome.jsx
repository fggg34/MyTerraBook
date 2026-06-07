import { useMemo, useState } from 'react'
import DateRangePicker from '../ui/DateRangePicker'
import FieldSelect from '../ui/FieldSelect'
import PredictiveSearchField from '../ui/PredictiveSearchField'
import useSearchChromeDraft from '../../hooks/useSearchChromeDraft'
import { SORT_OPTIONS } from '../../data/searchResultsConfig'

const PIN_ICON = (
  <svg className="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.9" strokeLinecap="round" strokeLinejoin="round" aria-hidden>
    <path d="M12 21s7-6.3 7-11a7 7 0 1 0-14 0c0 4.7 7 11 7 11Z" />
    <circle cx="12" cy="10" r="2.5" />
  </svg>
)

const PERSON_ICON = (
  <svg className="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.9" strokeLinecap="round" strokeLinejoin="round" aria-hidden>
    <circle cx="12" cy="8" r="4" />
    <path d="M4 21c0-4 3.6-6.5 8-6.5s8 2.5 8 6.5" />
  </svg>
)

export default function SearchResultsChrome({
  vehicleType,
  query,
  updateSearch,
  totalCount,
  config,
  sort,
  setSort,
  sortLabel,
  sortOptions = SORT_OPTIONS,
  quickFilterOptions = [],
  quickFilters,
  toggleQuick,
  clearFilters,
  hasActiveFilters,
  filters,
  setFilters,
  guestsLabel,
}) {
  const [sortOpen, setSortOpen] = useState(false)
  const [openPop, setOpenPop] = useState(null)

  const {
    isGuesthouse,
    vehicleDraft,
    setVehicleDraft,
    guestDraft,
    setGuestDraft,
    guestCityLabel,
    setGuestCityLabel,
    pickupLocations,
    dropoffLocations,
    handleVehicleDates,
    handleGuestDates,
    applyDraft,
    guestPeopleOptions,
    minRentalDays,
    maxRentalDays,
    vehicleStartDate,
    vehicleEndDate,
    guestStartDate,
    guestEndDate,
  } = useSearchChromeDraft({ vehicleType, query, updateSearch })

  const pickupOptions = useMemo(
    () => pickupLocations.map((loc) => ({ value: loc.value, label: loc.label, subtitle: loc.subtitle })),
    [pickupLocations],
  )

  const dropoffOptions = useMemo(
    () => dropoffLocations.map((loc) => ({ value: loc.value, label: loc.label, subtitle: loc.subtitle })),
    [dropoffLocations],
  )

  return (
    <>
      <div className="hsearch" id="hsearch">
        <div className="hsearch-inner">
          <div className="hsearch-bar">
            {isGuesthouse ? (
              <>
                <div className="hfield hfield--control">
                  <span className="hf-label">City or area</span>
                  <PredictiveSearchField
                    scope="guesthouse"
                    allowFreeText
                    value={guestDraft.city}
                    displayValue={guestCityLabel}
                    placeholder="e.g. Reykjavík"
                    icon={PIN_ICON}
                    ariaLabel="City or area"
                    onChange={({ value, label }) => {
                      setGuestCityLabel(label)
                      setGuestDraft((prev) => ({ ...prev, city: value }))
                    }}
                  />
                </div>
                <div className="hfield hfield--control hfield--dates">
                  <span className="hf-label">Check-in → Check-out</span>
                  <DateRangePicker
                    variant="embedded compact"
                    fixedPopper
                    startLabel="Check-in"
                    endLabel="Check-out"
                    startDate={guestStartDate}
                    endDate={guestEndDate}
                    minNights={1}
                    onChange={handleGuestDates}
                  />
                </div>
                <div className="hfield hfield--control hfield--guests">
                  <span className="hf-label">Guests</span>
                  <FieldSelect
                    value={guestDraft.guests}
                    onChange={(value) => setGuestDraft((prev) => ({ ...prev, guests: value }))}
                    options={guestPeopleOptions.map((n) => ({
                      value: String(n),
                      label: `${n} ${n === 1 ? 'guest' : 'guests'}`,
                    }))}
                    icon={PERSON_ICON}
                    ariaLabel="Number of guests"
                  />
                </div>
              </>
            ) : (
              <>
                <div className="hfield hfield--control">
                  <span className="hf-label">Pick-up location</span>
                  <FieldSelect
                    value={vehicleDraft.pickup_location_id}
                    onChange={(value) => {
                      const sameAsPickup = vehicleDraft.dropoff_location_id === vehicleDraft.pickup_location_id
                      setVehicleDraft((prev) => ({
                        ...prev,
                        pickup_location_id: value,
                        dropoff_location_id: sameAsPickup ? value : prev.dropoff_location_id,
                      }))
                    }}
                    options={pickupOptions}
                    placeholder="Select location"
                    icon={PIN_ICON}
                    ariaLabel="Pick-up location"
                  />
                </div>
                <div className="hfield hfield--control">
                  <span className="hf-label">Drop-off location</span>
                  <FieldSelect
                    value={vehicleDraft.dropoff_location_id}
                    onChange={(value) => setVehicleDraft((prev) => ({ ...prev, dropoff_location_id: value }))}
                    options={dropoffOptions}
                    placeholder="Select location"
                    icon={PIN_ICON}
                    ariaLabel="Drop-off location"
                    disabled={!vehicleDraft.pickup_location_id}
                  />
                </div>
                <div className="hfield hfield--control hfield--dates">
                  <span className="hf-label">Pick-up → Drop-off</span>
                  <DateRangePicker
                    variant="embedded compact"
                    fixedPopper
                    startLabel="Pick-up"
                    endLabel="Drop-off"
                    startDate={vehicleStartDate}
                    endDate={vehicleEndDate}
                    minNights={minRentalDays}
                    maxNights={maxRentalDays}
                    onChange={handleVehicleDates}
                  />
                </div>
              </>
            )}
            <button className="hsearch-btn" type="button" onClick={applyDraft}>
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
            {!isGuesthouse && (
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
            )}
            {quickFilterOptions.length > 0 && <span className="chip-div" />}
            {quickFilterOptions.map((qf) => (
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
                {sortOptions.map((opt) => (
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
      </div>

      {openPop === 'price' && (
        <div className="fpop show" style={{ position: 'absolute', zIndex: 70, left: 40, top: '100%' }}>
          <h5>Max price per {isGuesthouse ? 'night' : 'day'}</h5>
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
      {!isGuesthouse && openPop === 'trans' && (
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
    </>
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
