import { useCallback, useEffect, useRef, useState } from 'react'
import useAutoAdvanceCarousel from '../../hooks/useAutoAdvanceCarousel'
import useMediaQuery from '../../hooks/useMediaQuery'
import useTrustStripSwipe from '../../hooks/useTrustStripSwipe'

const AUTO_INTERVAL_MS = 7000
const HOLD_AFTER_TAP_MS = 6000
const HOLD_AFTER_SWIPE_MS = 7000
const HOLD_AFTER_READ_MS = 9000

function TrustIcon({ type, image }) {
  if (image) {
    return <img src={image} alt="" className="trust-ic-img" aria-hidden="true" />
  }
  const icons = {
    star: (
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
        <path d="m12 3 2.7 5.5 6 .9-4.3 4.2 1 6-5.4-2.8L6.6 19.6l1-6L3.3 9.4l6-.9L12 3Z" />
      </svg>
    ),
    check: (
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
        <path d="M3 12a9 9 0 1 0 18 0 9 9 0 0 0-18 0Z" />
        <path d="m8.5 12 2.5 2.5L16 9" />
      </svg>
    ),
    shield: (
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
        <path d="M12 22s8-4.5 8-11V5l-8-3-8 3v6c0 6.5 8 11 8 11Z" />
      </svg>
    ),
    phone: (
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
        <path d="M4 5.5C4 4.7 4.7 4 5.5 4h2.8c.6 0 1.2.4 1.4 1l1.2 3.2c.2.5 0 1.1-.4 1.5L8.8 11.2a13 13 0 0 0 5.6 5.6l1.5-1.7c.4-.4 1-.6 1.5-.4l3.2 1.2c.6.2 1 .8 1 1.4v2.8c0 .8-.7 1.5-1.5 1.5C10.3 21.6 4 15.3 4 5.5Z" />
      </svg>
    ),
  }
  return icons[type] || icons.check
}

export default function TrustStrip({ items = [] }) {
  const isMobile = useMediaQuery('(max-width: 768px)')
  const trackRef = useRef(null)
  const dragGuardRef = useRef(false)
  const descOpenRef = useRef(false)
  const hadDescOpenRef = useRef(false)
  const [descOpen, setDescOpen] = useState(false)
  const swipeEnabled = isMobile && items.length > 1
  const { activeIndex, pause, hold, setActiveIndex } = useAutoAdvanceCarousel({
    count: items.length,
    interval: AUTO_INTERVAL_MS,
    enabled: swipeEnabled,
  })

  descOpenRef.current = descOpen

  const holdUnlessReading = useCallback(
    (ms) => {
      if (descOpenRef.current) return
      hold(ms)
    },
    [hold],
  )

  const handleSwipe = useCallback(() => {
    dragGuardRef.current = true
    setDescOpen(false)
    holdUnlessReading(HOLD_AFTER_SWIPE_MS)
    window.setTimeout(() => {
      dragGuardRef.current = false
    }, 0)
  }, [holdUnlessReading])

  const handleTap = useCallback(() => {
    holdUnlessReading(HOLD_AFTER_TAP_MS)
  }, [holdUnlessReading])

  const handleDragStart = useCallback(() => {
    pause()
  }, [pause])

  const handleDragEnd = useCallback(() => {
    holdUnlessReading(HOLD_AFTER_SWIPE_MS)
  }, [holdUnlessReading])

  const { dragOffset, isDragging } = useTrustStripSwipe(trackRef, {
    count: items.length,
    setActiveIndex,
    enabled: swipeEnabled,
    onSwipe: handleSwipe,
    onTap: handleTap,
    onDragStart: handleDragStart,
    onDragEnd: handleDragEnd,
  })

  useEffect(() => {
    setDescOpen(false)
  }, [activeIndex])

  useEffect(() => {
    if (!swipeEnabled) return undefined
    if (descOpen) {
      pause()
      hadDescOpenRef.current = true
      return undefined
    }
    if (hadDescOpenRef.current) {
      hold(HOLD_AFTER_READ_MS)
    }
    hadDescOpenRef.current = false
    return undefined
  }, [descOpen, hold, pause, swipeEnabled])

  if (!items.length) return null

  return (
    <section className="trust" aria-label="Trust highlights">
      <div className="wrap">
        <div
          ref={trackRef}
          className={`trust-track${isMobile ? ' trust-track--mobile' : ''}${isDragging ? ' is-dragging' : ''}`}
          style={
            isMobile
              ? {
                  '--trust-index': activeIndex,
                  '--trust-offset': `${dragOffset}px`,
                }
              : undefined
          }
        >
          {items.map((item, index) => {
            const isActive = !isMobile || index === activeIndex
            const showSubtitle = !isMobile || (isActive && descOpen)

            return (
              <div
                className="trust-item"
                key={`${item.title}-${index}`}
                aria-hidden={isMobile && index !== activeIndex ? true : undefined}
              >
                <span className="trust-ic">
                  <TrustIcon type={item.icon} image={item.iconImage} />
                </span>
                <div className="trust-text">
                  <div className="tt-top">
                    {item.title}
                    {item.icon === 'star' && item.stars ? (
                      <span className="trust-stars" aria-hidden="true">
                        {Array.from({ length: item.stars }).map((_, i) => (
                          <svg key={i} viewBox="0 0 24 24" fill="currentColor">
                            <path d="m12 2 2.9 6.3 6.9.8-5.1 4.7 1.4 6.8L12 17.6 5.9 20.6l1.4-6.8L2.2 9.1l6.9-.8L12 2Z" />
                          </svg>
                        ))}
                      </span>
                    ) : null}
                    {isMobile && item.subtitle ? (
                      <button
                        type="button"
                        className="trust-info-btn"
                        aria-expanded={isActive && descOpen}
                        aria-label={`More about ${item.title}`}
                        onClick={() => {
                          if (dragGuardRef.current || index !== activeIndex) return
                          setDescOpen((open) => !open)
                        }}
                      >
                        ?
                      </button>
                    ) : null}
                  </div>
                  {showSubtitle && item.subtitle ? (
                    <div className="tt-sub">{item.subtitle}</div>
                  ) : null}
                </div>
              </div>
            )
          })}
        </div>
      </div>
    </section>
  )
}
