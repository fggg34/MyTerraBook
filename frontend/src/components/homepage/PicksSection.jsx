import { useEffect, useMemo, useRef, useState } from 'react'
import { Link } from 'react-router-dom'
import { usePicksListings } from '../../hooks/useHomepageListings'
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

export default function PicksSection({ heading, tabs = [] }) {
  const trackRef = useRef(null)
  const { items, loading } = usePicksListings()
  const tabList = useMemo(() => {
    const availableTabs = tabs.filter((tab) => (items[tab.id] || []).length > 0)
    return availableTabs.length ? availableTabs : tabs
  }, [tabs, items])
  const [activeTab, setActiveTab] = useState(tabList[0]?.id || 'camper')
  const activeItems = items[activeTab] || []
  const activeTabMeta = tabList.find((t) => t.id === activeTab) || tabList[0]

  useEffect(() => {
    if (!tabList.some((tab) => tab.id === activeTab)) {
      setActiveTab(tabList[0]?.id || 'camper')
    }
  }, [activeTab, tabList])

  const scroll = (direction) => {
    const track = trackRef.current
    if (!track) return
    const card = track.querySelector('.pcard')
    const step = card ? card.getBoundingClientRect().width + 24 : 360
    track.scrollBy({ left: direction * step, behavior: 'smooth' })
  }

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
          <button className="carousel-nav prev" type="button" aria-label="Previous" onClick={() => scroll(-1)}>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
              <path d="M15 6l-6 6 6 6" />
            </svg>
          </button>
          <div className="track" ref={trackRef}>
            {loading ? (
              <p className="picks-empty" role="status">Loading listings…</p>
            ) : activeItems.length ? (
              activeItems.map((item) => (
                <ProductCard key={item.id || item.name} {...item} />
              ))
            ) : (
              <p className="picks-empty" role="status">No listings available yet.</p>
            )}
          </div>
          <button className="carousel-nav next" type="button" aria-label="Next" onClick={() => scroll(1)}>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
              <path d="M9 6l6 6-6 6" />
            </svg>
          </button>
        </div>

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
