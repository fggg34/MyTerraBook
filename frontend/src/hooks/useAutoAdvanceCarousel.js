import { useCallback, useEffect, useRef, useState } from 'react'

export default function useAutoAdvanceCarousel({
  count = 0,
  interval = 4000,
  enabled = true,
} = {}) {
  const [activeIndex, setActiveIndex] = useState(0)
  const pausedRef = useRef(false)
  const timerRef = useRef(null)
  const holdTimerRef = useRef(null)

  const clearHold = useCallback(() => {
    if (holdTimerRef.current) {
      clearTimeout(holdTimerRef.current)
      holdTimerRef.current = null
    }
  }, [])

  const clearTimer = useCallback(() => {
    if (timerRef.current) {
      clearInterval(timerRef.current)
      timerRef.current = null
    }
  }, [])

  const advance = useCallback(() => {
    if (count < 2) return
    setActiveIndex((current) => (current + 1) % count)
  }, [count])

  const pause = useCallback(() => {
    pausedRef.current = true
    clearHold()
  }, [clearHold])

  const resume = useCallback(() => {
    pausedRef.current = false
    clearHold()
  }, [clearHold])

  const hold = useCallback(
    (ms = 6000) => {
      clearHold()
      pausedRef.current = true
      holdTimerRef.current = setTimeout(() => {
        pausedRef.current = false
        holdTimerRef.current = null
      }, ms)
    },
    [clearHold],
  )

  useEffect(() => {
    if (!enabled || count < 2) {
      clearTimer()
      clearHold()
      setActiveIndex(0)
      return undefined
    }

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    if (prefersReducedMotion) return undefined

    clearTimer()
    timerRef.current = setInterval(() => {
      if (!pausedRef.current) advance()
    }, interval)

    return () => {
      clearTimer()
      clearHold()
    }
  }, [enabled, count, interval, advance, clearTimer, clearHold])

  return { activeIndex, pause, resume, hold, setActiveIndex }
}
