import { useEffect, useRef, useState } from 'react'
import useSectionReveal from '../../hooks/useSectionReveal'

const HOW_DUR = 6000

export default function HostHowItWorks({
  howTabs = [],
  heading = 'From idle to earning, in four simple steps.',
  headingAccent = '',
}) {
  const sectionRef = useRef(null)
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
        <div className="host-how-tabs" role="tablist">
          {howTabs.map((tab, index) => (
            <button
              key={tab.title}
              type="button"
              role="tab"
              aria-selected={index === active}
              className={`host-how-tab ${index === active ? 'active' : ''}`}
              onClick={() => goTo(index)}
            >
              <span className="host-how-tab-num">{index + 1}.</span>
              {tab.title}
              <span className="host-how-tab-bar" key={index === active ? `bar-${active}` : 'bar-idle'} />
            </button>
          ))}
        </div>
        <div
          className="host-how-stage"
          onMouseEnter={() => {
            pausedRef.current = true
            clearTimeout(timerRef.current)
          }}
          onMouseLeave={() => {
            pausedRef.current = false
            schedule()
          }}
        >
          {howTabs.map((tab, index) => (
            <div key={tab.title} className={`host-how-slide ${index === active ? 'active' : ''}`}>
              <img src={tab.image} alt={tab.imageAlt ?? tab.title} />
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
