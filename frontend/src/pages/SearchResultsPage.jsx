import { useCallback, useMemo, useRef } from 'react'
import { createPortal } from 'react-dom'
import { Link } from 'react-router-dom'
import ProductCard from '../components/homepage/ProductCard'
import { SearchResultsChromeProvider } from '../context/SearchResultsChromeContext'
import PromoTile from '../components/search-results/PromoTile'
import SearchResultsChrome, { SearchResultsHeaderPill } from '../components/search-results/SearchResultsChrome'
import useSearchResultsPage from '../hooks/useSearchResultsPage'
import useGuesthouseSearchPage from '../hooks/useGuesthouseSearchPage'
import useSearchPromotions from '../hooks/useSearchPromotions'
import useSearchResultsEffects from '../hooks/useSearchResultsEffects'
import useSearchResultsIntroEffects from '../hooks/useSearchResultsIntroEffects'
import { buildSearchGridItems } from '../utils/buildSearchGridItems'
import { PAGE_SIZE } from '../data/searchResultsConfig'
import '../styles/search-results.css'

function MidbannerTile({ banner }) {
  return (
    <div className="cell banner reveal">
      <div className="midbanner">
        <div className="mb-bg">
          <img src={banner.image} alt="" />
        </div>
        <div className="mb-overlay" aria-hidden="true" />
        <div className="mb-content" style={{ '--mb-op': 1, '--mb-slide': '0px' }}>
          <h3>{banner.title}</h3>
          <p>{banner.text}</p>
          <a className="mb-cta" href={banner.href || '#faq'}>
            {banner.cta}
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
              <path d="M5 12h14M13 6l6 6-6 6" />
            </svg>
          </a>
        </div>
      </div>
    </div>
  )
}

export default function SearchResultsPage({ vehicleType = 'campervan' }) {
  const rootRef = useRef(null)
  const isGuesthouse = vehicleType === 'guesthouse'
  const vehicleState = useSearchResultsPage(vehicleType)
  const guesthouseState = useGuesthouseSearchPage(isGuesthouse)
  const state = isGuesthouse ? guesthouseState : vehicleState
  const {
    config,
    loading,
    visibleCards,
    visibleCount,
    setVisibleCount,
    totalCount,
    pickupLabel,
    dropoffLabel,
    query,
    updateSearch,
    sort,
    setSort,
    sortLabel,
    quickFilters,
    toggleQuick,
    clearFilters,
    hasActiveFilters,
    filters,
    setFilters,
    sortOptions,
    quickFilterOptions,
    guestsLabel,
  } = state

  const pillText = useMemo(() => {
    if (isGuesthouse) {
      const city = query.city || 'Iceland'
      const dates =
        query.check_in && query.check_out ? formatShortRange(query.check_in, query.check_out) : 'Dates'
      return `${city} · ${dates} · ${guestsLabel || query.guests || 2} guests`
    }
    const loc = pickupLabel.includes('(') ? pickupLabel.match(/\(([^)]+)\)/)?.[1] || 'KEF' : 'KEF'
    const dates = query.pickup_at && query.dropoff_at ? formatShortRange(query.pickup_at, query.dropoff_at) : 'Dates'
    return `${loc} · ${dates} · ${query.drivers || 2} drivers`
  }, [isGuesthouse, pickupLabel, query, guestsLabel])

  const { promotions } = useSearchPromotions(vehicleType)
  const gridItems = useMemo(
    () => buildSearchGridItems(visibleCards, promotions, config.promo),
    [visibleCards, promotions, config.promo],
  )

  const onLoadMore = useCallback(() => {
    setVisibleCount((n) => Math.min(n + PAGE_SIZE, totalCount))
  }, [setVisibleCount, totalCount])

  useSearchResultsEffects({
    rootRef,
    totalCount,
    visibleCount,
    onLoadMore,
  })

  useSearchResultsIntroEffects(rootRef)

  const locationShort = isGuesthouse
    ? config.introLocationDefault || pickupLabel || 'Iceland'
    : pickupLabel.split('(').pop()?.replace(')', '').trim() || 'Keflavík'

  return (
    <SearchResultsChromeProvider pillText={pillText}>
      <div className="search-results-page" ref={rootRef}>
        {typeof document !== 'undefined' &&
          createPortal(<SearchResultsHeaderPill pillText={pillText} />, document.getElementById('headerSearchSlot') || document.body)}

        {typeof document !== 'undefined' &&
          createPortal(
            <SearchResultsChrome
              vehicleType={vehicleType}
              pickupLabel={pickupLabel}
              dropoffLabel={dropoffLabel}
              query={query}
              updateSearch={updateSearch}
              totalCount={totalCount}
              config={config}
              sort={sort}
              setSort={setSort}
              sortLabel={sortLabel}
              sortOptions={sortOptions}
              quickFilterOptions={quickFilterOptions}
              guestsLabel={guestsLabel}
              quickFilters={quickFilters}
              toggleQuick={toggleQuick}
              clearFilters={clearFilters}
              hasActiveFilters={hasActiveFilters}
              filters={filters}
              setFilters={setFilters}
            />,
            document.getElementById('searchChromeBar') || document.body,
          )}

        <main className="results">
          <div className="wrap">
            <section className="results-intro">
              <nav className="crumb" aria-label="Breadcrumb">
                <Link to="/">Home</Link>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
                  <path d="m9 6 6 6-6 6" />
                </svg>
                <span>{config.breadcrumb}</span>
              </nav>
              <div className="results-intro-row">
                <div>
                  <h1 className="reveal-title" data-reveal-now="1">
                    {config.titleLead}
                    <br />
                    <span className="ri-count" id="introCount">
                      {totalCount}
                    </span>{' '}
                    ready near {locationShort.split(' ')[0]}.
                  </h1>
                  <p className="ri-sub reveal-desc" data-reveal-now="1">
                    {config.subtitle}
                  </p>
                </div>
                <div className="ri-tags reveal-tags">
                  {isGuesthouse ? (
                    <>
                      <span className="ri-tag">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.9" strokeLinecap="round" strokeLinejoin="round">
                          <path d="M4 20V9l8-6 8 6v11" />
                          <path d="M2 20h20" />
                        </svg>
                        Verified hosts
                      </span>
                      <span className="ri-tag">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.9" strokeLinecap="round" strokeLinejoin="round">
                          <path d="M3 12a9 9 0 1 0 18 0 9 9 0 0 0-18 0Z" />
                          <path d="M12 7v5l3 2" />
                        </svg>
                        Flexible cancellation
                      </span>
                    </>
                  ) : (
                    <>
                      <span className="ri-tag">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.9" strokeLinecap="round" strokeLinejoin="round">
                          <path d="M12 22s8-4.5 8-11V5l-8-3-8 3v6c0 6.5 8 11 8 11Z" />
                          <path d="m8.5 11.5 2.5 2.5L16 8.5" />
                        </svg>
                        Insurance included
                      </span>
                      <span className="ri-tag">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.9" strokeLinecap="round" strokeLinejoin="round">
                          <path d="M3 12a9 9 0 1 0 18 0 9 9 0 0 0-18 0Z" />
                          <path d="M12 7v5l3 2" />
                        </svg>
                        Free cancellation
                      </span>
                    </>
                  )}
                </div>
              </div>
            </section>
          </div>

          <div className="wrap">
            <section className="results-wrap">
              {loading ? (
                <p className="results-progress-text">Loading {config.unitPlural}…</p>
              ) : (
                <div className="results-grid" id="resultsGrid">
                  {gridItems.map((item, index) => {
                    if (item.type === 'promo') {
                      return (
                        <PromoTile
                          key={item.key}
                          promo={item.promo}
                          layout={item.layout}
                          style={{ '--d': `${(index % 9) * 0.05}s` }}
                        />
                      )
                    }
                    if (item.type === 'banner') {
                      return <MidbannerTile key={item.key} banner={config.midbanner} />
                    }
                    return (
                      <div key={item.key} className="cell reveal" style={{ '--d': `${(index % 9) * 0.05}s` }}>
                        <ProductCard {...item.card} />
                      </div>
                    )
                  })}
                </div>
              )}

              {!loading && totalCount > 0 && (
                <div className="results-foot">
                  <p className="results-progress-text" id="progressText">
                    Showing <b>{Math.min(visibleCount, totalCount)}</b> of <b>{totalCount}</b> {config.unitPlural}
                  </p>
                  {visibleCount < totalCount && (
                    <button className="loadmore" type="button" id="loadMore">
                      {config.loadMoreLabel}
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
                        <path d="M12 5v14M6 13l6 6 6-6" />
                      </svg>
                    </button>
                  )}
                </div>
              )}
            </section>
          </div>
        </main>

        <div className="resfloat" id="resfloat" aria-live="polite">
          <span className="rf-ring">
            <svg viewBox="0 0 30 30">
              <circle className="rf-track" cx="15" cy="15" r="12.5" />
              <circle className="rf-bar" id="rfBar" cx="15" cy="15" r="12.5" />
            </svg>
          </span>
          <span>
            Showing <b id="rfShown">{Math.min(visibleCount, totalCount)}</b> of <span id="rfTotal">{totalCount}</span>
          </span>
          <button className="rf-up" id="rfUp" type="button" aria-label="Back to top">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
              <path d="M12 19V5M6 11l6-6 6 6" />
            </svg>
          </button>
        </div>
      </div>
    </SearchResultsChromeProvider>
  )
}

function formatShortRange(pickupAt, dropoffAt) {
  const p = new Date(pickupAt)
  const d = new Date(dropoffAt)
  if (Number.isNaN(p.getTime()) || Number.isNaN(d.getTime())) return 'Dates'
  const day = (dt) => dt.getDate()
  const mon = (dt) => dt.toLocaleDateString('en-GB', { month: 'short' })
  return `${day(p)}–${day(d)} ${mon(d)}`
}
