import { useEffect, useMemo, useState } from 'react'
import { Link } from 'react-router-dom'
import useHorizontalCarousel from '../../hooks/useHorizontalCarousel'
import { usePicksListings } from '../../hooks/useHomepageListings'
import useMediaQuery from '../../hooks/useMediaQuery'
import ProductCard from './ProductCard'

function SectionLink({ href, className, children }) {
  if (href?.startsWith('/') && !href.startsWith('//')) {
    return (
      <Link to={href} className={className}>
        {children}
      </Link>
    )
  }
  return (
    <a className={className} href={href || '#'}>
      {children}
    </a>
  )
}

function CarouselNav({ direction, disabled, onClick, label }) {
  return (
    <button
      className={`carousel-nav ${direction}`}
      type="button"
      aria-label={label}
      disabled={disabled}
      onClick={onClick}
    >
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
        {direction === 'prev' ? <path d="M15 6l-6 6 6 6" /> : <path d="M9 6l6 6-6 6" />}
      </svg>
    </button>
  )
}

export default function PicksSection({ heading, tabs = [] }) {
  const { items, loading } = usePicksListings()
  const isMobile = useMediaQuery('(max-width: 768px)')
  const tabList = useMemo(() => {
    const availableTabs = tabs.filter((tab) => (items[tab.id] || []).length > 0)
    return availableTabs.length ? availableTabs : tabs
  }, [tabs, items])
  const [activeTab, setActiveTab] = useState(tabList[0]?.id || 'camper')
  const activeItems = items[activeTab] || []
  const activeTabMeta = tabList.find((t) => t.id === activeTab) || tabList[0]
  const showCarousel = isMobile && activeItems.length > 1
  const { trackRef, scroll, atStart, atEnd } = useHorizontalCarousel({
    itemCount: activeItems.length,
    gap: 12,
    enabled: showCarousel || activeItems.length > 0,
    scrollDurationMs: 700,
  })

  useEffect(() => {
    if (!tabList.some((tab) => tab.id === activeTab)) {
      setActiveTab(tabList[0]?.id || 'camper')
    }
  }, [activeTab, tabList])

  return (
    <section className="picks">
      <div className="wrap">
        <div className="picks-head">
          <div className="picks-head-l">{heading && <h2>{heading}</h2>}</div>
          <div className="picks-tabs">
            {tabList.map((tab) => (
              <button
                key={tab.id}
                type="button"
                className={`ptab ${activeTab === tab.id ? 'active' : ''}`}
                onClick={() => setActiveTab(tab.id)}
              >
                {tab.label}
              </button>
            ))}
          </div>
        </div>

        <div className="picks-panel">
          {!showCarousel && (
            <CarouselNav direction="prev" label="Previous" disabled={atStart} onClick={() => scroll(-1)} />
          )}
          <div className={`track${showCarousel ? ' track--carousel' : ''}`} ref={trackRef}>
            {activeItems.length ? (
              activeItems.map((item) => (
                <ProductCard key={item.id || item.name} {...item} />
              ))
            ) : !loading ? (
              <p className="picks-empty" role="status">No listings available yet.</p>
            ) : null}
          </div>
          {!showCarousel && (
            <CarouselNav direction="next" label="Next" disabled={atEnd} onClick={() => scroll(1)} />
          )}
        </div>

        {showCarousel && (
          <div className="product-carousel-controls">
            <CarouselNav direction="prev" label="Previous" disabled={atStart} onClick={() => scroll(-1)} />
            <CarouselNav direction="next" label="Next" disabled={atEnd} onClick={() => scroll(1)} />
          </div>
        )}

        {activeTabMeta?.allLabel && (
          <div className="picks-foot">
            <SectionLink className="picks-all" href={activeTabMeta.allHref}>
              {activeTabMeta.allLabel}
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
                <path d="M5 12h14M13 6l6 6-6 6" />
              </svg>
            </SectionLink>
          </div>
        )}
      </div>
    </section>
  )
}
