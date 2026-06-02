import { useRef, useState } from 'react'
import ProductCard from './ProductCard'

export default function PicksSection({ heading, tabs = [], items = {} }) {
  const trackRef = useRef(null)
  const [activeTab, setActiveTab] = useState(tabs[0]?.id || 'camper')
  const activeItems = items[activeTab] || []
  const activeTabMeta = tabs.find((t) => t.id === activeTab) || tabs[0]

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
            {tabs.map((tab) => (
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
            {activeItems.map((item) => (
              <ProductCard key={item.id || item.name} {...item} />
            ))}
          </div>
          <button className="carousel-nav next" type="button" aria-label="Next" onClick={() => scroll(1)}>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
              <path d="M9 6l6 6-6 6" />
            </svg>
          </button>
        </div>

        {activeTabMeta?.allLabel && (
          <div className="picks-foot">
            <a className="picks-all" href={activeTabMeta.allHref || '#'}>
              {activeTabMeta.allLabel}
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
                <path d="M5 12h14M13 6l6 6-6 6" />
              </svg>
            </a>
          </div>
        )}
      </div>
    </section>
  )
}
