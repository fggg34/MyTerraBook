import { useEffect, useMemo, useRef, useState } from 'react'
import DateRangePicker from '../ui/DateRangePicker'
import FieldSelect from '../ui/FieldSelect'
import PredictiveSearchField from '../ui/PredictiveSearchField'
import FilterPopover from './FilterPopover'
import FilterSidePanel from './FilterSidePanel'
import PriceRangeFilter from './PriceRangeFilter'
import useSearchChromeDraft from '../../hooks/useSearchChromeDraft'
import { useFormatPrice } from '../../hooks/useFormatPrice'
import { SORT_OPTIONS } from '../../data/searchResultsConfig'
import { defaultPriceFilters, isPriceFilterActive } from '../../utils/searchPriceBounds'

const SEAT_OPTIONS = [0, 2, 4, 5, 7, 9]
const SLEEP_OPTIONS = [0, 2, 3, 4, 5, 6, 7]

function countPanelFilters({ quickFilters, filters }) {
  return quickFilters.length + (filters.minSeats > 0 ? 1 : 0) + (filters.minSleeps > 0 ? 1 : 0)
}

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

const CARET_ICON = (
  <svg className="caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
    <path d="m6 9 6 6 6-6" />
  </svg>
)

function formatTransmissionLabel(value) {
  if (!value) return 'Any'
  const lower = String(value).toLowerCase()
  if (lower.includes('auto')) return 'Automatic'
  if (lower.includes('manual')) return 'Manual'
  return value.charAt(0).toUpperCase() + value.slice(1)
}

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
  attributeQuickFilters = [],
  categoryFilterOptions = [],
  quickFilters,
  toggleQuick,
  clearFilters,
  hasActiveFilters,
  filters,
  setFilters,
  guestsLabel,
  priceBounds = { min: 0, max: 500, step: 10 },
  transmissionOptions = ['automatic', 'manual'],
}) {
  const [sortOpen, setSortOpen] = useState(false)
  const [openPop, setOpenPop] = useState(null)
  const priceWrapRef = useRef(null)
  const transWrapRef = useRef(null)
  const allFiltersWrapRef = useRef(null)
  const allFiltersMenuRef = useRef(null)
  const priceFormatter = useFormatPrice()

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
    pickupEmpty,
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

  const priceActive = isPriceFilterActive(filters, priceBounds)
  const perLabel = isGuesthouse ? 'night' : 'day'

  const priceChipLabel = useMemo(() => {
    if (!priceActive) return 'Price'
    return `${priceFormatter.format(filters.minPrice)} – ${priceFormatter.format(filters.maxPrice)}`
  }, [priceActive, filters.minPrice, filters.maxPrice, priceFormatter])

  const transmissionChipLabel = useMemo(() => {
    if (!filters.transmission) return 'Transmission'
    return formatTransmissionLabel(filters.transmission)
  }, [filters.transmission])

  const activeCategory = useMemo(
    () => categoryFilterOptions.find((option) => quickFilters.includes(option.id)),
    [categoryFilterOptions, quickFilters],
  )

  const inlineQuickFilters = isGuesthouse ? quickFilterOptions : attributeQuickFilters

  const panelActiveCount = useMemo(
    () => countPanelFilters({ quickFilters, filters }),
    [quickFilters, filters],
  )

  const showAllFilters = !isGuesthouse

  useEffect(() => {
    if (!openPop) return undefined

    const onPointerDown = (event) => {
      const inPrice = priceWrapRef.current?.contains(event.target)
      const inTrans = transWrapRef.current?.contains(event.target)
      const inAllFilters = allFiltersWrapRef.current?.contains(event.target)
        || allFiltersMenuRef.current?.contains(event.target)
      if (openPop === 'price' && !inPrice) setOpenPop(null)
      if (openPop === 'trans' && !inTrans) setOpenPop(null)
      if (openPop === 'all' && !inAllFilters) setOpenPop(null)
    }

    const onKeyDown = (event) => {
      if (event.key === 'Escape') setOpenPop(null)
    }

    // Defer so the same click that opened a popover does not instantly close it.
    const attachTimer = window.setTimeout(() => {
      document.addEventListener('mousedown', onPointerDown)
    }, 0)

    document.addEventListener('keydown', onKeyDown)

    return () => {
      window.clearTimeout(attachTimer)
      document.removeEventListener('mousedown', onPointerDown)
      document.removeEventListener('keydown', onKeyDown)
    }
  }, [openPop])

  const handlePriceChange = ({ minPrice, maxPrice }) => {
    setFilters((prev) => ({ ...prev, minPrice, maxPrice }))
  }

  const resetPriceFilter = () => {
    setFilters((prev) => ({ ...prev, ...defaultPriceFilters(priceBounds) }))
  }

  const handleTransmissionSelect = (transmission) => {
    setFilters((prev) => ({ ...prev, transmission }))
    setOpenPop(null)
  }

  const handleSeatsSelect = (minSeats) => {
    setFilters((prev) => ({ ...prev, minSeats }))
  }

  const handleSleepsSelect = (minSleeps) => {
    setFilters((prev) => ({ ...prev, minSleeps }))
  }

  const togglePop = (id) => {
    setSortOpen(false)
    setOpenPop((prev) => (prev === id ? null : id))
  }

  const closeAllFilters = () => setOpenPop(null)

  const allFiltersFooter = (
    <>
      {hasActiveFilters && (
        <button className="filter-side-clear" type="button" onClick={clearFilters}>
          Clear all
        </button>
      )}
      <button className="filter-side-apply" type="button" onClick={closeAllFilters}>
        Show {totalCount} {totalCount === 1 ? config.unitSingular : config.unitPlural}
      </button>
    </>
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
                {pickupEmpty && (
                  <p className="location-empty-hint" role="status" style={{ marginBottom: 12 }}>
                    Pickup locations are being configured. Assign locations to vehicles in admin.
                  </p>
                )}
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
                    disabled={pickupEmpty}
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
                    disabled={!vehicleDraft.pickup_location_id || pickupEmpty}
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
            <div className="chip-wrap" ref={priceWrapRef}>
              <button
                className={`chip chip--filter ${openPop === 'price' ? 'open' : ''} ${priceActive ? 'active' : ''}`}
                type="button"
                aria-expanded={openPop === 'price'}
                onClick={() => togglePop('price')}
              >
                <span className="chip-label">{priceChipLabel}</span>
                {CARET_ICON}
              </button>
              <FilterPopover open={openPop === 'price'}>
                <h5>Price per {perLabel}</h5>
                <PriceRangeFilter
                  min={priceBounds.min}
                  max={priceBounds.max}
                  step={priceBounds.step}
                  valueMin={filters.minPrice}
                  valueMax={filters.maxPrice}
                  onChange={handlePriceChange}
                  formatPrice={priceFormatter.format}
                  perLabel={perLabel}
                />
                {priceActive && (
                  <div className="fpop-foot fpop-foot--start">
                    <button className="fpop-reset" type="button" onClick={resetPriceFilter}>
                      Reset range
                    </button>
                  </div>
                )}
              </FilterPopover>
            </div>

            {!isGuesthouse && (
              <div className="chip-wrap" ref={transWrapRef}>
                <button
                  className={`chip chip--filter ${openPop === 'trans' ? 'open' : ''} ${filters.transmission ? 'active' : ''}`}
                  type="button"
                  aria-expanded={openPop === 'trans'}
                  onClick={() => togglePop('trans')}
                >
                  <span className="chip-label">{transmissionChipLabel}</span>
                  {CARET_ICON}
                </button>
                <FilterPopover open={openPop === 'trans'}>
                  <h5>Transmission</h5>
                  <div className="fopts">
                    <button
                      type="button"
                      className={`fopt ${!filters.transmission ? 'sel' : ''}`}
                      onClick={() => handleTransmissionSelect('')}
                    >
                      Any
                    </button>
                    {transmissionOptions.map((t) => (
                      <button
                        key={t}
                        type="button"
                        className={`fopt ${filters.transmission === t ? 'sel' : ''}`}
                        onClick={() => handleTransmissionSelect(t)}
                      >
                        {formatTransmissionLabel(t)}
                      </button>
                    ))}
                  </div>
                </FilterPopover>
              </div>
            )}

            {inlineQuickFilters.length > 0 && <span className="chip-div" />}
            {inlineQuickFilters.map((qf) => (
              <button
                key={qf.id}
                className={`chip quick ${quickFilters.includes(qf.id) ? 'active' : ''}`}
                type="button"
                onClick={() => toggleQuick(qf.id)}
              >
                {qf.label}
              </button>
            ))}

            {showAllFilters && (
              <div className="chip-wrap chip-wrap--all-filters" ref={allFiltersWrapRef}>
                <button
                  className={`chip chip--filter chip--all-filters ${openPop === 'all' ? 'open' : ''} ${panelActiveCount > 0 ? 'active' : ''}`}
                  type="button"
                  aria-expanded={openPop === 'all'}
                  onClick={() => togglePop('all')}
                >
                  <span className="chip-label">All filters</span>
                  {panelActiveCount > 0 && <span className="chip-count">{panelActiveCount}</span>}
                </button>
              </div>
            )}

            {showAllFilters && (
              <FilterSidePanel
                open={openPop === 'all'}
                onClose={closeAllFilters}
                title="Filters"
                side="right"
                panelRef={allFiltersMenuRef}
                footer={allFiltersFooter}
              >
                {categoryFilterOptions.length > 0 && (
                  <div className="fpop-section">
                    <h5>Vehicle type</h5>
                    <div className="fopts">
                      <button
                        type="button"
                        className={`fopt ${!activeCategory ? 'sel' : ''}`}
                        onClick={() => { if (activeCategory) toggleQuick(activeCategory.id) }}
                      >
                        Any
                      </button>
                      {categoryFilterOptions.map((option) => (
                        <button
                          key={option.id}
                          type="button"
                          className={`fopt ${quickFilters.includes(option.id) ? 'sel' : ''}`}
                          onClick={() => toggleQuick(option.id)}
                        >
                          {option.label}
                        </button>
                      ))}
                    </div>
                  </div>
                )}

                {attributeQuickFilters.length > 0 && (
                  <div className="fpop-section">
                    <h5>Features</h5>
                    <div className="fopts">
                      {attributeQuickFilters.map((qf) => (
                        <button
                          key={qf.id}
                          type="button"
                          className={`fopt ${quickFilters.includes(qf.id) ? 'sel' : ''}`}
                          onClick={() => toggleQuick(qf.id)}
                        >
                          {qf.label}
                        </button>
                      ))}
                    </div>
                  </div>
                )}

                <div className="fpop-section">
                  <h5>Transmission</h5>
                  <div className="fopts">
                    <button
                      type="button"
                      className={`fopt ${!filters.transmission ? 'sel' : ''}`}
                      onClick={() => setFilters((prev) => ({ ...prev, transmission: '' }))}
                    >
                      Any
                    </button>
                    {transmissionOptions.map((t) => (
                      <button
                        key={t}
                        type="button"
                        className={`fopt ${filters.transmission === t ? 'sel' : ''}`}
                        onClick={() => setFilters((prev) => ({ ...prev, transmission: t }))}
                      >
                        {formatTransmissionLabel(t)}
                      </button>
                    ))}
                  </div>
                </div>

                <div className="fpop-section">
                  <h5>Minimum seats</h5>
                  <div className="fopts">
                    {SEAT_OPTIONS.map((value) => (
                      <button
                        key={value}
                        type="button"
                        className={`fopt ${filters.minSeats === value ? 'sel' : ''}`}
                        onClick={() => handleSeatsSelect(value)}
                      >
                        {value ? `${value}+` : 'Any'}
                      </button>
                    ))}
                  </div>
                </div>

                <div className="fpop-section">
                  <h5>Minimum sleeps</h5>
                  <div className="fopts">
                    {SLEEP_OPTIONS.map((value) => (
                      <button
                        key={value}
                        type="button"
                        className={`fopt ${filters.minSleeps === value ? 'sel' : ''}`}
                        onClick={() => handleSleepsSelect(value)}
                      >
                        {value ? `${value}+` : 'Any'}
                      </button>
                    ))}
                  </div>
                </div>

                <div className="fpop-section">
                  <h5>Price per {perLabel}</h5>
                  <PriceRangeFilter
                    min={priceBounds.min}
                    max={priceBounds.max}
                    step={priceBounds.step}
                    valueMin={filters.minPrice}
                    valueMax={filters.maxPrice}
                    onChange={handlePriceChange}
                    formatPrice={priceFormatter.format}
                    perLabel={perLabel}
                  />
                  {priceActive && (
                    <button className="fpop-reset fpop-reset--block" type="button" onClick={resetPriceFilter}>
                      Reset range
                    </button>
                  )}
                </div>
              </FilterSidePanel>
            )}

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
