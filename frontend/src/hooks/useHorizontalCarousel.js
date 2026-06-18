import { useCallback, useEffect, useRef, useState } from 'react'
import useDragScroll from './useDragScroll'

function easeInOutCubic(t) {
  return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2
}

function animateScrollLeft(el, targetLeft, duration) {
  const startLeft = el.scrollLeft
  const distance = targetLeft - startLeft
  if (distance === 0) return

  const startTime = performance.now()
  let frameId = 0

  const step = (now) => {
    const progress = Math.min((now - startTime) / duration, 1)
    el.scrollLeft = startLeft + distance * easeInOutCubic(progress)
    if (progress < 1) {
      frameId = requestAnimationFrame(step)
    }
  }

  frameId = requestAnimationFrame(step)
  return () => cancelAnimationFrame(frameId)
}

export default function useHorizontalCarousel({
  gap = 24,
  cardSelector = '.pcard',
  itemCount = 0,
  enabled = true,
  scrollDurationMs = 0,
} = {}) {
  const trackRef = useRef(null)
  const [atStart, setAtStart] = useState(true)
  const [atEnd, setAtEnd] = useState(false)

  const updateNav = useCallback(() => {
    const track = trackRef.current
    if (!track) return
    const max = track.scrollWidth - track.clientWidth - 2
    setAtStart(track.scrollLeft <= 2)
    setAtEnd(track.scrollLeft >= max)
  }, [])

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
    }
  }, [enabled, itemCount, updateNav])

  const scroll = useCallback(
    (direction) => {
      const track = trackRef.current
      if (!track) return
      const card = track.querySelector(cardSelector)
      const step = card ? card.getBoundingClientRect().width + gap : 360
      const targetLeft = track.scrollLeft + direction * step
      const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches

      if (scrollDurationMs > 0 && !prefersReducedMotion) {
        animateScrollLeft(track, targetLeft, scrollDurationMs)
      } else {
        track.scrollBy({ left: direction * step, behavior: prefersReducedMotion ? 'auto' : 'smooth' })
      }
    },
    [gap, cardSelector, scrollDurationMs],
  )

  useDragScroll(trackRef, { enabled })

  return { trackRef, scroll, atStart, atEnd }
}
