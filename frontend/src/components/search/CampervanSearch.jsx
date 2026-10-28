import { useEffect, useMemo, useState } from 'react'
import { Link } from 'react-router-dom'
import Container from '../site/Container'
import FigmaIcon from '../site/FigmaIcon'
import PageHead from '../seo/PageHead'
import { usePageContent } from '../../context/SiteContentContext'
import { useFormatPrice } from '../../hooks/useFormatPrice'
import { isPriceFilterActive } from '../../utils/searchPriceBounds'
import '../../styles/campervan-search.css'

const TABS = [
  { id: 'campervan', label: 'Campervans', href: '/campervans', icon: 'tent' },
  { id: 'car', label: 'Cars', href: '/cars', icon: 'gauge' },
  { id: 'guesthouse', label: 'Guesthouses', href: '/guesthouses', icon: 'heart' },
]

const SEAT_STEPS = [0, 2, 4, 5, 7, 9]
const SLEEP_STEPS = [0, 2, 3, 4, 5, 6, 7]

function fill(template, values) {
  return String(template || '').replace(/\{(\w+)\}/g, (_, key) => values[key] ?? '')
}

function formatRange(start, end, empty) {
  if (!start || !end) return empty || ''
  const from = new Date(start)
  const to = new Date(end)
  if (Number.isNaN(from.getTime()) || Number.isNaN(to.getTime())) return empty || ''
  const month = from.toLocaleDateString('en', { month: 'short' })
  const days = Math.max(1, Math.round((to - from) / 86400000))
  return `${from.getDate()} – ${to.getDate()} ${month} · ${days} days`
}

function gearLabel(value) {
  const text = String(value || '').toLowerCase()
  if (text.includes('auto')) return 'Automatic'
  if (text) return 'Manual'
  return ''
}

export default function CampervanSearch({
  variant = 'campervan',
  pageKey = 'search-campervan',
  pageSize = 6,
  seo,
  config,
  loading,
  error,
  retry,
  visibleCards,
  visibleCount,
  setVisibleCount,
  totalCount,
  pickupLabel,
  dropoffLabel,
  guestsLabel,
  query,
  updateSearch,
  sort,
  setSort,
  sortOptions = [],
  quickFilters = [],
  toggleQuick = () => {},
  clearFilters,
  filters = {},
  setFilters = () => {},
  priceBounds,
  categoryFilterOptions = [],
  partners = [],
  partnerTotal = 0,
  selectedPartnerId = '',
  selectPartner = () => {},
  locations = [],
  quickFilterOptions = [],
}) {
  const { page } = usePageContent(pageKey)
  const copy = page.presentation || {}
  const fmt = useFormatPrice()
  const isStay = variant === 'guesthouse'
  const startValue = isStay ? query.check_in : query.pickup_at
  const endValue = isStay ? query.check_out : query.dropoff_at
  const [sheet, setSheet] = useState('')
  const [draft, setDraft] = useState({
    pickup: isStay ? (query.city || '') : (query.pickup_location_id || ''),
    dropoff: query.dropoff_location_id || '',
    start: startValue ? String(startValue).slice(0, 10) : '',
    end: endValue ? String(endValue).slice(0, 10) : '',
    guests: query.guests || '2',
  })

  useEffect(() => {
    document.body.classList.add('tb-camper-search')
    setVisibleCount(pageSize)
    return () => document.body.classList.remove('tb-camper-search')
  }, [pageSize, setVisibleCount])

  useEffect(() => {
    document.body.classList.toggle('tb-sheet-open', Boolean(sheet))
  }, [sheet])

  const place = !pickupLabel || pickupLabel === 'All destinations' ? (copy.breadcrumbPlace || pickupLabel) : pickupLabel
  const datesText = formatRange(startValue, endValue, copy.datesEmpty)
  const perWord = isStay ? 'night' : 'day'
  const priceText = priceBounds?.max > 0 ? `from ${fmt.format(priceBounds.min)} to ${fmt.format(priceBounds.max)} per ${perWord}` : ''
  const resultsLine = loading
    ? ''
    : isStay
      ? `${totalCount} stays in ${place}${copy.insuranceNote ? ` · ${copy.insuranceNote}` : ''}`
      : `${totalCount} ready near ${place}${priceText ? ` · ${priceText}` : ''}${copy.insuranceNote ? ` · ${copy.insuranceNote}` : ''}`
  const selectedFeatures = ['4x4', 'auto', 'winter'].filter((id) => quickFilters.includes(id)).length
  const hintFor = (id) => (copy.sortHints || []).find((item) => item.id === id)?.hint || ''

  const chips = isStay
    ? ['Price', 'Type', 'Guests', 'Bedrooms', 'Amenities']
    : variant === 'car'
      ? ['Price', 'Transmission', 'Vehicle type', 'Seats', '4×4', 'Automatic', 'Winter-ready']
      : ['Price', 'Transmission', 'Vehicle type', 'Seats', 'Sleeps', '4×4', 'Automatic', 'Winter-ready']
  const cards = visibleCards || []
  const firstRow = cards.slice(0, isStay ? 4 : 3)
  const rest = cards.slice(isStay ? 4 : 3)

  const board = (card) => {
    const items = []
    if (card.sortSleeps) items.push({ icon: 'bed-navy', label: 'Bed' })
    items.push({ icon: 'kitchen', label: 'Kitchen' })
    items.push({ icon: 'heating', label: 'Heater' })
    const gear = gearLabel(card.transmission)
    if (gear) items.push({ icon: 'gauge', label: gear })
    return items.slice(0, 4)
  }

  function applySearch() {
    if (isStay) {
      updateSearch({
        city: draft.pickup,
        check_in: draft.start,
        check_out: draft.end,
        guests: draft.guests,
      })
    } else {
      updateSearch({
        pickup_location_id: draft.pickup,
        dropoff_location_id: draft.dropoff,
        pickup_at: draft.start,
        dropoff_at: draft.end,
      })
    }
    setSheet('')
  }

  const summary = `${place}${datesText ? ` · ${datesText}` : ''}`

  const filterCount = useMemo(() => {
    let count = selectedFeatures
    if (filters.transmission) count += 1
    if (filters.minSeats) count += 1
    if (filters.minSleeps) count += 1
    if (isPriceFilterActive(filters, priceBounds)) count += 1
    if (selectedPartnerId) count += 1
    return count
  }, [filters, priceBounds, selectedFeatures, selectedPartnerId])

  return (
    <div className="tb-camper-page">
      <PageHead {...seo} />
      <section className="tb-strip">
        <Container className="tb-strip-bar">
          <div className="tb-type-tabs">
            {TABS.map((tab) => (
              <Link key={tab.id} to={tab.href} className={tab.id === variant ? 'is-on' : ''}>
                <FigmaIcon name={tab.icon} size={14} />
                {tab.label}
              </Link>
            ))}
          </div>
          <div className="tb-strip-search">
            <button type="button" onClick={() => setSheet('edit')}>
              <FigmaIcon name="pin" size={16} />
              <span><small>{copy.pickupLabel}</small><strong>{pickupLabel || copy.breadcrumbPlace}</strong></span>
            </button>
            <button type="button" onClick={() => setSheet('edit')}>
              <FigmaIcon name="calendar" size={16} />
              <span><small>{copy.datesLabel}</small><strong>{datesText || copy.datesEmpty}</strong></span>
            </button>
            <button type="button" onClick={() => setSheet('edit')}>
              <FigmaIcon name="users" size={16} />
              <span><small>{isStay ? copy.whoLabel : copy.dropoffLabel}</small><strong>{isStay ? (guestsLabel || query.guests || copy.whoValue) : (dropoffLabel || copy.dropoffSame)}</strong></span>
            </button>
            <button type="button" className="tb-update" onClick={applySearch}>
              <FigmaIcon name="search" size={16} />
              {copy.updateLabel}
            </button>
          </div>
          <button type="button" className="tb-summary" onClick={() => setSheet('edit')}>
            <FigmaIcon name="search" size={16} />
            <span>
              <strong>{summary}</strong>
              <small>{isStay ? `${startValue && endValue ? datesText : copy.datesEmpty} · ${guestsLabel || copy.whoValue || ''}` : (datesText ? `${datesText} · ${copy.dropoffSame}` : copy.dropoffSame)}</small>
            </span>
            <FigmaIcon name="pencil" size={16} />
          </button>
        </Container>
      </section>

      <Container as="main" className="tb-results">
        <div className="tb-results-head">
          <div>
            <nav className="tb-crumbs" aria-label="Breadcrumb">
              <Link to="/">{copy.breadcrumbHome}</Link>
              <span>/</span>
              <span>{copy.breadcrumbSection}</span>
              <span>/</span>
              <span>{place}</span>
            </nav>
            <h1>{copy.title || config.titleLead}</h1>
            <p>{resultsLine}</p>
          </div>
          <button type="button" className="tb-sort-btn" onClick={() => setSheet('sort')}>
            <FigmaIcon name="chevron" size={14} />
            <span>{copy.sortPrefix}</span>
            <strong>{sortOptions.find((item) => item.id === sort)?.label}</strong>
          </button>
        </div>

        <div className="tb-filter-row">
          <button type="button" className="tb-chip" onClick={() => setSheet('filters')}>
            <FigmaIcon name="sliders" size={14} />
            {copy.filtersLabel}{filterCount ? ` (${filterCount})` : ''}
          </button>
          <button type="button" className="tb-chip tb-mobile-sort" onClick={() => setSheet('sort')}>
            {copy.sortLabel}
            <FigmaIcon name="chevron" size={12} />
          </button>
          {chips.map((label) => (
            <button
              key={label}
              type="button"
              className={['4×4', 'Automatic', 'Winter-ready'].includes(label) && quickFilters.includes(label === '4×4' ? '4x4' : label === 'Automatic' ? 'auto' : 'winter') ? 'tb-chip is-on' : 'tb-chip'}
              onClick={() => {
                if (label === '4×4') toggleQuick('4x4')
                else if (label === 'Automatic') toggleQuick('auto')
                else if (label === 'Winter-ready') toggleQuick('winter')
                else setSheet('filters')
              }}
            >
              {label}
              {!['4×4', 'Automatic', 'Winter-ready'].includes(label) ? <FigmaIcon name="chevron" size={12} /> : null}
            </button>
          ))}
        </div>

        {isStay && quickFilterOptions.length > 0 && (
          <div className="tb-partners">
            <span>Type</span>
            <button type="button" className={!quickFilterOptions.some((item) => quickFilters.includes(item.id)) ? 'is-on' : ''} onClick={() => quickFilterOptions.forEach((item) => { if (quickFilters.includes(item.id)) toggleQuick(item.id) })}>All types</button>
            {quickFilterOptions.map((item) => (
              <button key={item.id} type="button" className={quickFilters.includes(item.id) ? 'is-on' : ''} onClick={() => toggleQuick(item.id)}>{item.label}</button>
            ))}
          </div>
        )}

        {!isStay && (
        <div className="tb-partners">
          <span><FigmaIcon name="key" size={14} />{copy.partnerLabel}</span>
          <button type="button" className={!selectedPartnerId ? 'is-on' : ''} onClick={() => selectPartner('')}>
            {copy.showAllLabel}<em>{partnerTotal || totalCount}</em>
          </button>
          {partners.map((partner) => (
            <button key={partner.id} type="button" className={selectedPartnerId === partner.id ? 'is-on' : ''} onClick={() => selectPartner(partner.id)}>
              {partner.name}<em>{partner.count ?? ''}</em>
            </button>
          ))}
        </div>
        )}

        {loading && <p className="tb-status">Loading {copy.breadcrumbSection || 'results'}…</p>}
        {!loading && error && (
          <p className="tb-status">
            Couldn’t load {(copy.breadcrumbSection || 'results').toLowerCase()}. <button type="button" onClick={retry}>Try again</button>
          </p>
        )}
        {!loading && !error && totalCount === 0 && <p className="tb-status">No {(copy.breadcrumbSection || 'results').toLowerCase()} match this search.</p>}

        {!loading && !error && totalCount > 0 && (
          <>
            <div className={isStay ? 'tb-van-grid is-stay' : 'tb-van-grid'}>
              {firstRow.map((card) => <VanCard key={card.id} card={card} copy={copy} board={board(card)} stay={isStay} />)}
            </div>
            <aside className="tb-bundle">
              <span><FigmaIcon name="bed" size={22} /></span>
              <div>
                <strong>{copy.bundleTitle}</strong>
                <p>{copy.bundleText}</p>
              </div>
              <Link to={copy.bundleHref || '/guesthouses'}>{copy.bundleButton}</Link>
            </aside>
            {rest.length > 0 && (
              <div className={isStay ? 'tb-van-grid is-stay' : 'tb-van-grid'}>
                {rest.map((card) => <VanCard key={card.id} card={card} copy={copy} board={board(card)} stay={isStay} />)}
              </div>
            )}
            <div className="tb-more">
              <p>{fill(copy.showingLabel, { shown: Math.min(visibleCount, totalCount), total: totalCount })}</p>
              {visibleCount < totalCount && (
                <button type="button" onClick={() => setVisibleCount((count) => count + pageSize)}>{copy.loadMoreLabel}</button>
              )}
            </div>
          </>
        )}
      </Container>

      {sheet && (
        <div className="tb-sheet-backdrop" onClick={() => setSheet('')}>
          <div className="tb-sheet" role="dialog" onClick={(event) => event.stopPropagation()}>
            <span className="tb-sheet-grab" />
            <header>
              <div>
                <h2>{sheet === 'sort' ? copy.sortTitle : sheet === 'edit' ? copy.updateLabel : copy.filtersTitle}</h2>
                {sheet === 'filters' ? <p>{fill(copy.matchLabel, { count: totalCount })}</p> : null}
              </div>
              <button type="button" aria-label="Close" onClick={() => setSheet('')}><FigmaIcon name="x" size={18} /></button>
            </header>
            {sheet === 'sort' && (
              <div className="tb-sort-list">
                {sortOptions.map((option) => (
                  <button key={option.id} type="button" className={sort === option.id ? 'is-on' : ''} onClick={() => { setSort(option.id); setSheet('') }}>
                    <span><strong>{option.label}</strong><small>{hintFor(option.id)}</small></span>
                    <i />
                  </button>
                ))}
              </div>
            )}
            {sheet === 'edit' && (
              <div className="tb-edit">
                <label>{copy.pickupLabel}
                  <select value={draft.pickup} onChange={(event) => setDraft((prev) => ({ ...prev, pickup: event.target.value }))}>
                    <option value="">{copy.breadcrumbPlace}</option>
                    {locations.map((location) => <option key={location.id} value={location.id}>{location.name}</option>)}
                  </select>
                </label>
                <label>{copy.datesLabel}
                  <input type="date" value={draft.start} onChange={(event) => setDraft((prev) => ({ ...prev, start: event.target.value }))} />
                  <input type="date" value={draft.end} onChange={(event) => setDraft((prev) => ({ ...prev, end: event.target.value }))} />
                </label>
                <label>{copy.dropoffLabel}
                  <select value={draft.dropoff} onChange={(event) => setDraft((prev) => ({ ...prev, dropoff: event.target.value }))}>
                    <option value="">{copy.dropoffSame}</option>
                    {locations.map((location) => <option key={location.id} value={location.id}>{location.name}</option>)}
                  </select>
                </label>
                <button type="button" className="tb-show" onClick={applySearch}>{copy.updateLabel}</button>
              </div>
            )}
            {sheet === 'filters' && (
              <div className="tb-filter-sheet">
                <h3>{copy.vehicleTypeLabel}</h3>
                <div className="tb-pills">
                  <button type="button" className={!categoryFilterOptions.some((item) => quickFilters.includes(item.id)) ? 'is-on' : ''} onClick={() => categoryFilterOptions.forEach((item) => { if (quickFilters.includes(item.id)) toggleQuick(item.id) })}>{copy.anyLabel}</button>
                  {categoryFilterOptions.map((item) => (
                    <button key={item.id} type="button" className={quickFilters.includes(item.id) ? 'is-on' : ''} onClick={() => toggleQuick(item.id)}>{item.label}</button>
                  ))}
                </div>
                <h3>{copy.featuresLabel} <em>{fill(copy.selectedLabel, { count: selectedFeatures })}</em></h3>
                <div className="tb-pills">
                  <button type="button" className={quickFilters.includes('4x4') ? 'is-on' : ''} onClick={() => toggleQuick('4x4')}>4×4</button>
                  <button type="button" className={quickFilters.includes('auto') ? 'is-on' : ''} onClick={() => toggleQuick('auto')}>Automatic</button>
                  <button type="button" className={quickFilters.includes('winter') ? 'is-on' : ''} onClick={() => toggleQuick('winter')}>Winter-ready</button>
                </div>
                <h3>{copy.transmissionLabel}</h3>
                <div className="tb-segment">
                  {['', 'manual', 'automatic'].map((value) => (
                    <button key={value || 'any'} type="button" className={(filters.transmission || '') === value ? 'is-on' : ''} onClick={() => setFilters({ ...filters, transmission: value })}>
                      {value ? gearLabel(value) : copy.anyLabel}
                    </button>
                  ))}
                </div>
                <h3>{copy.seatsLabel}</h3>
                <div className="tb-pills">
                  {SEAT_STEPS.map((step) => (
                    <button key={step} type="button" className={Number(filters.minSeats || 0) === step ? 'is-on' : ''} onClick={() => setFilters({ ...filters, minSeats: step })}>{step ? `${step}+` : copy.anyLabel}</button>
                  ))}
                </div>
                <h3>{copy.sleepsLabel}</h3>
                <div className="tb-pills">
                  {SLEEP_STEPS.map((step) => (
                    <button key={step} type="button" className={Number(filters.minSleeps || 0) === step ? 'is-on' : ''} onClick={() => setFilters({ ...filters, minSleeps: step })}>{step ? `${step}+` : copy.anyLabel}</button>
                  ))}
                </div>
                <h3>{copy.priceLabel}</h3>
                <div className="tb-price-fields">
                  <label>Min<input type="number" value={filters.minPrice ?? priceBounds.min} onChange={(event) => setFilters({ ...filters, minPrice: Number(event.target.value) })} /></label>
                  <label>Max<input type="number" value={filters.maxPrice ?? priceBounds.max} onChange={(event) => setFilters({ ...filters, maxPrice: Number(event.target.value) })} /></label>
                </div>
                <h3>{copy.partnerLabel}</h3>
                <div className="tb-pills">
                  <button type="button" className={!selectedPartnerId ? 'is-on' : ''} onClick={() => selectPartner('')}>{copy.showAllLabel}</button>
                  {partners.map((partner) => (
                    <button key={partner.id} type="button" className={selectedPartnerId === partner.id ? 'is-on' : ''} onClick={() => selectPartner(partner.id)}>{partner.name}</button>
                  ))}
                </div>
                <footer>
                  <button type="button" onClick={() => { clearFilters(); selectPartner(''); setSheet('') }}>{copy.clearLabel}</button>
                  <button type="button" className="tb-show" onClick={() => setSheet('')}>{fill(copy.showLabel, { count: totalCount })}</button>
                </footer>
              </div>
            )}
          </div>
        </div>
      )}
    </div>
  )
}

function VanCard({ card, copy, board, stay }) {
  return (
    <article className="tb-van">
      <Link to={card.href} className="tb-van-media">
        <img
          src={card.image || '/figma/camper-card.png'}
          alt={card.name || ''}
          onError={(event) => {
            event.currentTarget.onerror = null
            event.currentTarget.src = '/figma/camper-card.png'
          }}
        />
        <span className="tb-sleeps"><FigmaIcon name={stay ? 'users' : 'bed'} size={15} />{stay ? (card.houseType ? card.houseType.charAt(0).toUpperCase() + card.houseType.slice(1) : (copy.typeLabel || 'Stay')) : (card.sortSleeps ? `Sleeps ${card.sortSleeps}` : (card.sortSeats ? `Seats ${card.sortSeats}` : copy.typeLabel))}</span>
        <span className="tb-save" aria-hidden="true"><FigmaIcon name="heart" size={16} /></span>
      </Link>
      <div className="tb-van-body">
        {stay ? (
          <div className="tb-stay-where"><FigmaIcon name="pin" size={13} />{card.city || copy.breadcrumbPlace}</div>
        ) : (
          <div className="tb-van-meta">
            <span><FigmaIcon name="tent" size={13} />{card.categoryName || card.badge || copy.typeLabel}</span>
            <em>{card.partner?.name}</em>
          </div>
        )}
        <h2>{card.name}</h2>
        {stay && card.specs?.length > 0 && (
          <div className="tb-stay-specs">
            {card.specs.slice(0, 3).map((item) => <span key={item.label}>{item.label}</span>)}
          </div>
        )}
        {!stay && (
          <div className="tb-board">
            <p>{copy.onBoardLabel}</p>
            <div>
              {board.map((item) => (
                <span key={item.label}><FigmaIcon name={item.icon} size={18} />{item.label}</span>
              ))}
            </div>
          </div>
        )}
        <div className="tb-van-price">
          <div>
            <small>{copy.fromLabel}</small>
            <strong>{card.price} <em>{copy.perLabel}</em></strong>
          </div>
          <Link to={card.href}>{copy.bookLabel} <FigmaIcon name="arrow" size={16} /></Link>
        </div>
      </div>
    </article>
  )
}
