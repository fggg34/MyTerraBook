import { useCallback, useMemo, useRef } from 'react'
import { createPortal } from 'react-dom'
import { Link } from 'react-router-dom'
import ProductCard from '../components/homepage/ProductCard'
import { SearchResultsChromeProvider } from '../context/SearchResultsChromeContext'
import SearchResultsChrome, { SearchResultsHeaderPill } from '../components/search-results/SearchResultsChrome'
import PageHead from '../components/seo/PageHead'
import usePageSeo from '../hooks/usePageSeo'
import useSearchResultsPage from '../hooks/useSearchResultsPage'
import useGuesthouseSearchPage from '../hooks/useGuesthouseSearchPage'
import useSearchResultsEffects from '../hooks/useSearchResultsEffects'
import useSearchResultsIntroEffects from '../hooks/useSearchResultsIntroEffects'
import { PAGE_SIZE } from '../data/searchResultsConfig'
import '../styles/search-results.css'

export default function SearchResultsPage({ vehicleType = 'campervan' }) {
  const rootRef = useRef(null)
  const isGuesthouse = vehicleType === 'guesthouse'
  const searchPageKey = useMemo(() => {
    if (isGuesthouse) return 'search-guesthouse'
    if (vehicleType === 'car') return 'search-car'
    return 'search-campervan'
  }, [isGuesthouse, vehicleType])
  const vehicleState = useSearchResultsPage(vehicleType)
  const guesthouseState = useGuesthouseSearchPage(isGuesthouse)
  const state = isGuesthouse ? guesthouseState : vehicleState
  const {
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
    attributeQuickFilters,
    categoryFilterOptions,
    guestsLabel,
    priceBounds,
    transmissionOptions,
  } = state

  const pillText = useMemo(() => {
    if (isGuesthouse) {
      const city = query.city || 'Iceland'
      const dates =
        query.check_in && query.check_out ? formatShortRange(query.check_in, query.check_out) : 'Dates'
      const guestPart = guestsLabel || query.guests
        ? `${guestsLabel || query.guests} guests`
        : 'Guests'
      return `${city} · ${dates} · ${guestPart}`
    }
    const loc = pickupLabel.includes('(') ? pickupLabel.match(/\(([^)]+)\)/)?.[1] || 'KEF' : 'KEF'
    const dates = query.pickup_at && query.dropoff_at ? formatShortRange(query.pickup_at, query.dropoff_at) : 'Dates'
    return `${loc} · ${dates}`
  }, [isGuesthouse, pickupLabel, query, guestsLabel])

  const onLoadMore = useCallback(() => {
    setVisibleCount((n) => Math.min(n + PAGE_SIZE, totalCount))
  }, [setVisibleCount, totalCount])

  useSearchResultsEffects({
    rootRef,
    totalCount,
    visibleCount,
    onLoadMore,
  })

  useSearchResultsIntroEffects(rootRef, { ready: !loading })

  const locationShort = isGuesthouse
    ? config.introLocationDefault || pickupLabel || 'Iceland'
    : pickupLabel.split('(').pop()?.replace(')', '').trim() || 'Keflavík'
  const locationWord = locationShort.split(' ')[0]
  const unitPlural = config.unitPlural || 'results'

  const seo = usePageSeo(searchPageKey, {
    source: {
      titleLead: config?.titleLead,
      subtitle: config?.subtitle,
    },
  })

  return (
    <SearchResultsChromeProvider pillText={pillText}>
      <PageHead {...seo} />
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
              loading={loading}
              loadFailed={Boolean(error)}
              config={config}
              sort={sort}
              setSort={setSort}
              sortLabel={sortLabel}
              sortOptions={sortOptions}
              quickFilterOptions={quickFilterOptions}
              attributeQuickFilters={attributeQuickFilters}
              categoryFilterOptions={categoryFilterOptions}
              guestsLabel={guestsLabel}
              quickFilters={quickFilters}
              toggleQuick={toggleQuick}
              clearFilters={clearFilters}
              hasActiveFilters={hasActiveFilters}
              filters={filters}
              setFilters={setFilters}
              priceBounds={priceBounds}
              transmissionOptions={transmissionOptions}
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
                    {/* Never print a count before the results are in: on a slow
                        connection "0 ready" reads as an empty fleet. */}
                    {loading ? (
                      <span className="ri-status">Finding {unitPlural} near {locationWord}…</span>
                    ) : error ? (
                      <span className="ri-status">{`Couldn't load ${unitPlural} right now.`}</span>
                    ) : (
                      <>
                        <span className="ri-count" id="introCount">
                          {totalCount}
                        </span>{' '}
                        ready near {locationWord}.
                      </>
                    )}
                  </h1>
                  <p className="ri-sub reveal-desc" data-reveal-now="1">
                    {config.subtitle}
                  </p>
                </div>
              </div>
            </section>
          </div>

          <div className="wrap">
            <section className="results-wrap">
              {loading && (
                <div className="results-grid results-grid--loading" aria-busy="true" aria-label={`Loading ${unitPlural}`}>
                  {Array.from({ length: 6 }, (_, index) => (
                    <div key={index} className="cell">
                      <ResultCardSkeleton />
                    </div>
                  ))}
                </div>
              )}

              {!loading && error && (
                <div className="results-state" role="alert">
                  <p className="results-state-title">{`We couldn't load the ${unitPlural}.`}</p>
                  <p className="results-state-text">Check your connection and try again.</p>
                  <button className="loadmore" type="button" onClick={retry}>
                    Try again
                  </button>
                </div>
              )}

              {!loading && !error && totalCount === 0 && (
                <div className="results-state">
                  <p className="results-state-title">No {unitPlural} match this search.</p>
                  <p className="results-state-text">
                    {hasActiveFilters ? 'Loosen a filter or clear them all.' : 'Try different dates or a different location.'}
                  </p>
                  {hasActiveFilters && (
                    <button className="loadmore" type="button" onClick={clearFilters}>
                      Clear filters
                    </button>
                  )}
                </div>
              )}

              {!loading && !error && totalCount > 0 && (
                <div className="results-grid" id="resultsGrid">
                  {visibleCards.map((card, index) => (
                    <div key={card.id} className="cell reveal" style={{ '--d': `${(index % 9) * 0.05}s` }}>
                      <ProductCard {...card} />
                    </div>
                  ))}
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

function ResultCardSkeleton() {
  return (
    <div className="pcard-skeleton" aria-hidden="true">
      <div className="skeleton pcard-skeleton__media" />
      <div className="skeleton pcard-skeleton__line pcard-skeleton__line--title" />
      <div className="skeleton pcard-skeleton__line pcard-skeleton__line--short" />
      <div className="pcard-skeleton__chips">
        <div className="skeleton pcard-skeleton__chip" />
        <div className="skeleton pcard-skeleton__chip" />
        <div className="skeleton pcard-skeleton__chip" />
      </div>
      <div className="skeleton pcard-skeleton__price" />
    </div>
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
