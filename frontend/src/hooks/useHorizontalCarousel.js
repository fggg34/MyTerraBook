import { useCallback, useEffect, useRef, useState } from 'react'
import useDragScroll from './useDragScroll'

function easeOutCubic(t) {
  return 1 - Math.pow(1 - t, 3)
}

function animateScrollLeft(el, targetLeft, duration) {
  const previousBehavior = el.style.scrollBehavior
  el.style.scrollBehavior = 'auto'

  const startLeft = el.scrollLeft
  const distance = targetLeft - startLeft
  if (distance === 0) {
    el.style.scrollBehavior = previousBehavior
    return () => {}
  }

  const startTime = performance.now()
  let frameId = 0
  let cancelled = false

  const finish = () => {
    if (cancelled) return
    el.style.scrollBehavior = previousBehavior
  }

  const step = (now) => {
    if (cancelled) return
    const progress = Math.min((now - startTime) / duration, 1)
    el.scrollLeft = startLeft + distance * easeOutCubic(progress)
    if (progress < 1) {
      frameId = requestAnimationFrame(step)
    } else {
      finish()
    }
  }

  frameId = requestAnimationFrame(step)

  return () => {
    cancelled = true
    cancelAnimationFrame(frameId)
    finish()
  }
}

export default function useHorizontalCarousel({
  trackRef: externalTrackRef,
  gap = 24,
  cardSelector = '.pcard',
  itemCount = 0,
  enabled = true,
  scrollDurationMs = 260,
  dragScroll = true,
} = {}) {
  const internalTrackRef = useRef(null)
  const trackRef = externalTrackRef ?? internalTrackRef
  const cancelAnimRef = useRef(null)
  const [atStart, setAtStart] = useState(true)
  const [atEnd, setAtEnd] = useState(false)

  const updateNav = useCallback(() => {
    const track = trackRef.current
    if (!track) return
    const max = track.scrollWidth - track.clientWidth - 2
    setAtStart(track.scrollLeft <= 2)
    setAtEnd(track.scrollLeft >= max)
  }, [trackRef])

  useEffect(() => {
    if (!enabled) return undefined

    updateNav()
    const track = trackRef.current
    if (!track) return undefined

    track.addEventListener('scroll', updateNav, { passive: true })
    window.addEventListener('resize', updateNav)
    const t = setTimeout(updateNav, 300)

    let resizeObserver
    if ('ResizeObserver' in window) {
      resizeObserver = new ResizeObserver(updateNav)
      resizeObserver.observe(track)
    }

    return () => {
      clearTimeout(t)
      track.removeEventListener('scroll', updateNav)
      window.removeEventListener('resize', updateNav)
      resizeObserver?.disconnect()
      cancelAnimRef.current?.()
    }
  }, [enabled, itemCount, updateNav, trackRef])

  const scroll = useCallback(
    (direction) => {
      const track = trackRef.current
      if (!track) return
      const card = track.querySelector(cardSelector)
      const step = card ? card.getBoundingClientRect().width + gap : 360
      const targetLeft = track.scrollLeft + direction * step
      const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches

      cancelAnimRef.current?.()

      if (scrollDurationMs > 0 && !prefersReducedMotion) {
        cancelAnimRef.current = animateScrollLeft(track, targetLeft, scrollDurationMs)
        window.setTimeout(updateNav, scrollDurationMs + 32)
      } else {
        track.scrollTo({ left: targetLeft, behavior: 'auto' })
        updateNav()
      }
    },
    [gap, cardSelector, scrollDurationMs, trackRef, updateNav],
  )

  useDragScroll(trackRef, { enabled: enabled && dragScroll })

  return { trackRef, scroll, atStart, atEnd }
}
