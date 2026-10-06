import { useEffect, useMemo, useRef, useState } from 'react'
import DateRangePicker from '../ui/DateRangePicker'
import DestinationSelect from '../ui/DestinationSelect'
import FieldSelect from '../ui/FieldSelect'
import PredictiveSearchField from '../ui/PredictiveSearchField'
import { GUESTHOUSE_CITY_SEARCH_PROPS } from '../../data/guesthouseSearchField'
import FilterPopover from './FilterPopover'
import FilterSidePanel from './FilterSidePanel'
import PriceRangeFilter from './PriceRangeFilter'
import useSearchChromeDraft from '../../hooks/useSearchChromeDraft'
import { toFieldSelectOptions } from '../../hooks/useLocationOptions'
import useMediaQuery from '../../hooks/useMediaQuery'
import { useFormatPrice } from '../../hooks/useFormatPrice'
import { SORT_OPTIONS } from '../../data/searchResultsConfig'
import { SEARCH_FORM_COPY, cmsText, fillTemplate } from '../../data/searchFormCopy'
import { usePageContent } from '../../context/SiteContentContext'
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

function formatTransmissionLabel(value, copy) {
  if (!value) return copy.anyLabel
  const lower = String(value).toLowerCase()
  if (lower.includes('auto')) return copy.automaticLabel
  if (lower.includes('manual')) return copy.manualLabel
  return value.charAt(0).toUpperCase() + value.slice(1)
}

export default function SearchResultsChrome({
  vehicleType,
  query,
  updateSearch,
  totalCount,
  loading = false,
  loadFailed = false,
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
  const { page: globalPage } = usePageContent('global')
  const searchCopy = { ...SEARCH_FORM_COPY, ...(globalPage.searchForm || {}) }
  const label = (key) => cmsText(searchCopy[key], SEARCH_FORM_COPY[key])
  const [sortOpen, setSortOpen] = useState(false)
  const [openPop, setOpenPop] = useState(null)
  const [mobileDetailsOpen, setMobileDetailsOpen] = useState(false)
  const isMobileCompact = useMediaQuery('(max-width: 768px)')
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

  const hasSearchDetails = useMemo(() => {
    if (isGuesthouse) {
      return Boolean(query.city || query.check_in || query.check_out)
    }
    return Boolean(
      query.pickup_location_id ||
      query.dropoff_location_id ||
      query.pickup_at ||
      query.dropoff_at,
    )
  }, [isGuesthouse, query])

  const showMobileDetails = !isMobileCompact || mobileDetailsOpen || hasSearchDetails

  useEffect(() => {
    setMobileDetailsOpen(false)
  }, [vehicleType, isGuesthouse])

  useEffect(() => {
    if (isMobileCompact && hasSearchDetails) {
      setMobileDetailsOpen(true)
    }
  }, [isMobileCompact, hasSearchDetails])

  const expandMobileDetails = () => {
    if (isMobileCompact) setMobileDetailsOpen(true)
  }

  const pickupOptions = useMemo(() => toFieldSelectOptions(pickupLocations), [pickupLocations])

  const dropoffOptions = useMemo(() => toFieldSelectOptions(dropoffLocations), [dropoffLocations])

  const priceActive = isPriceFilterActive(filters, priceBounds)
  const perLabel = isGuesthouse ? 'night' : 'day'

  // An untouched slider has no value of its own, so it sits at the full range.
  const sliderMin = filters.minPrice ?? priceBounds.min
  const sliderMax = filters.maxPrice ?? priceBounds.max

  const priceChipLabel = useMemo(() => {
    if (!priceActive) return label('priceChipLabel')
    return `${priceFormatter.format(sliderMin)} – ${priceFormatter.format(sliderMax)}`
  }, [priceActive, sliderMin, sliderMax, priceFormatter, searchCopy])

  const transmissionChipLabel = useMemo(() => {
    if (!filters.transmission) return label('transmissionLabel')
    return formatTransmissionLabel(filters.transmission, searchCopy)
  }, [filters.transmission, searchCopy])

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
    setFilters((prev) => ({ ...prev, ...defaultPriceFilters() }))
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
          {label('clearAllLabel')}
        </button>
      )}
      <button className="filter-side-apply" type="button" onClick={closeAllFilters}>
        {label('showPrefix')} {totalCount} {totalCount === 1 ? config.unitSingular : config.unitPlural}
      </button>
    </>
  )

  return (
    <>
      <div className="hsearch" id="hsearch">
        <div className="hsearch-inner">
          {!isGuesthouse && pickupEmpty && showMobileDetails && (
            <p className="location-empty-hint" role="status">
              {label('emptyLocationsHintShort')}
            </p>
          )}
          <div
            className={`hsearch-bar${showMobileDetails ? ' hsearch-bar--expanded' : ' hsearch-bar--compact'}`}
          >
            {isGuesthouse ? (
              <>
                <div className="hfield hfield--control hfield--primary">
                  <span className="hf-label">{label('cityLabel')}</span>
                  <PredictiveSearchField
                    {...GUESTHOUSE_CITY_SEARCH_PROPS}
                    value={guestDraft.city}
                    displayValue={guestCityLabel}
                    placeholder={label('cityPlaceholderShort')}
                    icon={PIN_ICON}
                    ariaLabel={label('cityLabel')}
                    onFocus={expandMobileDetails}
                    onChange={({ value, label }) => {
                      setGuestCityLabel(label)
                      setGuestDraft((prev) => ({ ...prev, city: value }))
                      expandMobileDetails()
                    }}
                  />
                </div>
                <div className="hfield hfield--control hfield--dates hfield--detail">
                  <span className="hf-label">{label('stayDatesLabel')}</span>
                  <DateRangePicker
                    variant="embedded compact"
                    fixedPopper
                    startLabel={label('checkInLabel')}
                    endLabel={label('checkOutLabel')}
                    startDate={guestStartDate}
                    endDate={guestEndDate}
                    minNights={1}
                    rateUnit="night"
                    onChange={handleGuestDates}
                  />
                </div>
                <div className="hfield hfield--control hfield--guests hfield--detail">
                  <span className="hf-label">{label('guestsLabel')}</span>
                  <FieldSelect
                    value={guestDraft.guests}
                    onChange={(value) => setGuestDraft((prev) => ({ ...prev, guests: value }))}
                    options={guestPeopleOptions.map((n) => ({
                      value: String(n),
                      label: `${n} ${n === 1 ? label('guestSingular') : label('guestPlural')}`,
                    }))}
                    placeholder={label('guestsLabel')}
                    icon={PERSON_ICON}
                    ariaLabel={label('guestsAria')}
                  />
                </div>
              </>
            ) : (
              <>
                <DestinationSelect value={vehicleDraft.country_code} mainCategory={vehicleType}
                  className="hfield hfield--control hfield--primary" labelClassName="hf-label"
                  onOpen={expandMobileDetails}
                  onChange={(country_code) => {
                    setVehicleDraft((prev) => ({ ...prev, country_code, pickup_location_id: '', dropoff_location_id: '' }))
                    expandMobileDetails()
                  }} />
                <div className="hfield hfield--control hfield--primary">
                  <span className="hf-label">{label('pickupLabel')}</span>
                  <FieldSelect
                    value={vehicleDraft.pickup_location_id}
                    onChange={(value) => {
                      setVehicleDraft((prev) => ({
                        ...prev,
                        pickup_location_id: value,
                        dropoff_location_id: '',
                      }))
                      expandMobileDetails()
                    }}
                    options={pickupOptions}
                    searchable
                    placeholder={label('locationPlaceholder')}
                    icon={PIN_ICON}
                    ariaLabel={label('pickupLabel')}
                    disabled={pickupEmpty}
                    onOpen={expandMobileDetails}
                  />
                </div>
                <div className="hfield hfield--control hfield--detail">
                  <span className="hf-label">{label('dropoffLabel')}</span>
                  <FieldSelect
                    value={vehicleDraft.dropoff_location_id}
                    onChange={(value) => setVehicleDraft((prev) => ({ ...prev, dropoff_location_id: value }))}
                    options={dropoffOptions}
                    searchable
                    placeholder={label('locationPlaceholder')}
                    icon={PIN_ICON}
                    ariaLabel={label('dropoffLabel')}
                    disabled={!vehicleDraft.pickup_location_id || pickupEmpty}
                    onOpen={expandMobileDetails}
                  />
                </div>
                <div className="hfield hfield--control hfield--dates hfield--detail">
                  <span className="hf-label">{label('vehicleDatesLabel')}</span>
                  <DateRangePicker
                    variant="embedded compact"
                    fixedPopper
                    startLabel={label('pickupDateLabel')}
                    endLabel={label('dropoffDateLabel')}
                    startDate={vehicleStartDate}
                    endDate={vehicleEndDate}
                    minNights={minRentalDays}
                    maxNights={maxRentalDays}
                    rateUnit="day"
                    onChange={handleVehicleDates}
                  />
                </div>
              </>
            )}
            <button
              className="hsearch-btn hsearch-btn--detail"
              type="button"
              onClick={(event) => {
                event.stopPropagation()
                applyDraft()
              }}
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
                <circle cx="11" cy="11" r="7" />
                <path d="m20 20-3.2-3.2" />
              </svg>
              {label('updateLabel')}
            </button>
          </div>
        </div>
      </div>

      <div className="filterbar" id="filterbar">
        <div className="filterbar-inner">
          <div className="chips-scroll">
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
                <h5>{fillTemplate(label('priceHeading'), { unit: perLabel })}</h5>
                <PriceRangeFilter
                  min={priceBounds.min}
                  max={priceBounds.max}
                  step={priceBounds.step}
                  valueMin={sliderMin}
                  valueMax={sliderMax}
                  onChange={handlePriceChange}
                  formatPrice={priceFormatter.format}
                  perLabel={perLabel}
                />
                {priceActive && (
                  <div className="fpop-foot fpop-foot--start">
                    <button className="fpop-reset" type="button" onClick={resetPriceFilter}>
                      {label('resetRangeLabel')}
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
                  <h5>{label('transmissionLabel')}</h5>
                  <div className="fopts">
                    <button
                      type="button"
                      className={`fopt ${!filters.transmission ? 'sel' : ''}`}
                      onClick={() => handleTransmissionSelect('')}
                    >
                      {label('anyLabel')}
                    </button>
                    {transmissionOptions.map((t) => (
                      <button
                        key={t}
                        type="button"
                        className={`fopt ${filters.transmission === t ? 'sel' : ''}`}
                        onClick={() => handleTransmissionSelect(t)}
                      >
                        {formatTransmissionLabel(t, searchCopy)}
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
                  <span className="chip-label">{label('allFiltersLabel')}</span>
                  {panelActiveCount > 0 && <span className="chip-count">{panelActiveCount}</span>}
                </button>
              </div>
            )}

            {showAllFilters && (
              <FilterSidePanel
                open={openPop === 'all'}
                onClose={closeAllFilters}
                title={label('filtersTitle')}
                side="right"
                panelRef={allFiltersMenuRef}
                footer={allFiltersFooter}
              >
                {categoryFilterOptions.length > 0 && (
                  <div className="fpop-section">
                    <h5>{label('vehicleTypeLabel')}</h5>
                    <div className="fopts">
                      <button
                        type="button"
                        className={`fopt ${!activeCategory ? 'sel' : ''}`}
                        onClick={() => { if (activeCategory) toggleQuick(activeCategory.id) }}
                      >
                        {label('anyLabel')}
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
                    <h5>{label('featuresLabel')}</h5>
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
                  <h5>{label('transmissionLabel')}</h5>
                  <div className="fopts">
                    <button
                      type="button"
                      className={`fopt ${!filters.transmission ? 'sel' : ''}`}
                      onClick={() => setFilters((prev) => ({ ...prev, transmission: '' }))}
                    >
                      {label('anyLabel')}
                    </button>
                    {transmissionOptions.map((t) => (
                      <button
                        key={t}
                        type="button"
                        className={`fopt ${filters.transmission === t ? 'sel' : ''}`}
                        onClick={() => setFilters((prev) => ({ ...prev, transmission: t }))}
                      >
                        {formatTransmissionLabel(t, searchCopy)}
                      </button>
                    ))}
                  </div>
                </div>

                <div className="fpop-section">
                  <h5>{label('minSeatsLabel')}</h5>
                  <div className="fopts">
                    {SEAT_OPTIONS.map((value) => (
                      <button
                        key={value}
                        type="button"
                        className={`fopt ${filters.minSeats === value ? 'sel' : ''}`}
                        onClick={() => handleSeatsSelect(value)}
                      >
                        {value ? `${value}+` : label('anyLabel')}
                      </button>
                    ))}
                  </div>
                </div>

                <div className="fpop-section">
                  <h5>{label('minSleepsLabel')}</h5>
                  <div className="fopts">
                    {SLEEP_OPTIONS.map((value) => (
                      <button
                        key={value}
                        type="button"
                        className={`fopt ${filters.minSleeps === value ? 'sel' : ''}`}
                        onClick={() => handleSleepsSelect(value)}
                      >
                        {value ? `${value}+` : label('anyLabel')}
                      </button>
                    ))}
                  </div>
                </div>

                <div className="fpop-section">
                  <h5>{fillTemplate(label('priceHeading'), { unit: perLabel })}</h5>
                  <PriceRangeFilter
                    min={priceBounds.min}
                    max={priceBounds.max}
                    step={priceBounds.step}
                    valueMin={sliderMin}
                    valueMax={sliderMax}
                    onChange={handlePriceChange}
                    formatPrice={priceFormatter.format}
                    perLabel={perLabel}
                  />
                  {priceActive && (
                    <button className="fpop-reset fpop-reset--block" type="button" onClick={resetPriceFilter}>
                      {label('resetRangeLabel')}
                    </button>
                  )}
                </div>
              </FilterSidePanel>
            )}

            {hasActiveFilters && (
              <button className="chip clear" type="button" onClick={clearFilters}>
                {label('clearAllChipLabel')}
              </button>
            )}
            </div>
          </div>

          <div className="fb-right">
            <span className="result-count" id="resultCount">
              {loading ? (
                label('loadingLabel')
              ) : loadFailed ? (
                label('unavailableLabel')
              ) : (
                <>
                  <b>{totalCount}</b> {totalCount === 1 ? config.unitSingular : config.unitPlural}
                </>
              )}
            </span>
            <div className="sortwrap">
              <button className={`sortbtn ${sortOpen ? 'open' : ''}`} type="button" id="sortBtn" onClick={() => setSortOpen(!sortOpen)}>
                {label('sortPrefix')} <b id="sortLabel">{sortLabel}</b>
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
        <div className="filterbar-mobile-count" aria-live="polite">
          {loading ? (
            fillTemplate(label('loadingPlural'), { plural: config.unitPlural })
          ) : loadFailed ? (
            fillTemplate(label('loadFailedPlural'), { plural: config.unitPlural })
          ) : (
            <>
              <b>{totalCount}</b> {totalCount === 1 ? config.unitSingular : config.unitPlural} {label('foundSuffix')}
            </>
          )}
        </div>
      </div>
    </>
  )
}

export function SearchResultsHeaderPill({ pillText }) {
  const { page: globalPage } = usePageContent('global')
  const editLabel = cmsText(globalPage.searchForm?.editLabel, SEARCH_FORM_COPY.editLabel)

  return (
    <button className="hsearch-pill" id="hsearchPill" type="button">
      <span className="hsp-ic">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
          <circle cx="11" cy="11" r="7" />
          <path d="m20 20-3.2-3.2" />
        </svg>
      </span>
      <span className="hsp-text">{pillText}</span>
      <span className="hsp-edit">{editLabel}</span>
    </button>
  )
}
