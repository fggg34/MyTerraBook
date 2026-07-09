import { useEffect, useRef, useState } from 'react'
import CmsImage from '../cms/CmsImage'
import useSectionReveal from '../../hooks/useSectionReveal'

const HOW_DUR = 6000

export default function HostHowItWorks({
  howTabs = [],
  heading = 'From idle to earning, in four simple steps.',
  headingAccent = '',
}) {
  const sectionRef = useRef(null)
  const tabsRef = useRef(null)
  const [active, setActive] = useState(0)
  const timerRef = useRef(null)
  const pausedRef = useRef(false)

  useSectionReveal(sectionRef, { revealDoneMs: 1400, threshold: 0.1 })

  const schedule = () => {
    clearTimeout(timerRef.current)
    if (pausedRef.current) return
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    if (reduce) return
    timerRef.current = setTimeout(() => {
      setActive((prev) => (prev + 1) % Math.max(howTabs.length, 1))
      schedule()
    }, HOW_DUR)
  }

  useEffect(() => {
    schedule()
    return () => clearTimeout(timerRef.current)
  }, [howTabs.length, active])

  // Keep the active step card in view on the mobile horizontal tab scroller.
  useEffect(() => {
    const tabs = tabsRef.current
    if (!tabs) return

    const activeTab = tabs.querySelector('.host-how-tab.active')
    if (!activeTab) return

    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    const tabLeft = activeTab.offsetLeft
    const tabWidth = activeTab.offsetWidth
    const target = tabLeft - (tabs.clientWidth - tabWidth) / 2
    const maxScroll = tabs.scrollWidth - tabs.clientWidth
    const nextLeft = Math.max(0, Math.min(target, maxScroll))

    tabs.scrollTo({
      left: nextLeft,
      behavior: reduce ? 'auto' : 'smooth',
    })
  }, [active])

  const goTo = (index) => {
    setActive(index)
    schedule()
  }

  if (!howTabs.length) return null

  return (
    <section className="host-how" id="how" ref={sectionRef}>
      <div className="wrap">
        <div className="host-how-head">
          <h2>
            {heading}
            {headingAccent && <span className="host-accent"> {headingAccent}</span>}
          </h2>
        </div>
        <div
          className="host-how-tabs"
          role="tablist"
          aria-label="How it works steps"
          ref={tabsRef}
        >
          {howTabs.map((tab, index) => (
            <button
              key={tab.title}
              type="button"
              role="tab"
              aria-selected={index === active}
              aria-controls="host-how-stage"
              id={`host-how-tab-${index}`}
              className={`host-how-tab ${index === active ? 'active' : ''}`}
              onClick={() => goTo(index)}
            >
              <span className="host-how-tab-num">{String(index + 1).padStart(2, '0')}</span>
              <span className="host-how-tab-label">{tab.title}</span>
              <span className="host-how-tab-bar" key={index === active ? `bar-${active}` : 'bar-idle'} />
            </button>
          ))}
        </div>
        <div
          id="host-how-stage"
          className="host-how-stage"
          role="tabpanel"
          aria-labelledby={`host-how-tab-${active}`}
          onMouseEnter={() => {
            pausedRef.current = true
            clearTimeout(timerRef.current)
          }}
          onMouseLeave={() => {
            pausedRef.current = false
            schedule()
          }}
          onTouchStart={() => {
            pausedRef.current = true
            clearTimeout(timerRef.current)
          }}
          onTouchEnd={() => {
            pausedRef.current = false
            schedule()
          }}
        >
          {howTabs.map((tab, index) => (
            <div key={tab.title} className={`host-how-slide ${index === active ? 'active' : ''}`}>
              <CmsImage src={tab.image} alt={tab.imageAlt ?? tab.title} />
              <div className="host-how-cap">
                {tab.caption}
                <span className="host-how-cap-muted">{tab.muted}</span>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  )
}
