import { useCallback, useEffect, useRef, useState } from 'react'

export default function useAutoAdvanceCarousel({
  count = 0,
  interval = 4000,
  enabled = true,
} = {}) {
  const [activeIndex, setActiveIndex] = useState(0)
  const pausedRef = useRef(false)
  const timerRef = useRef(null)

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
    clearTimer()
  }, [clearTimer])

  const resume = useCallback(() => {
    pausedRef.current = false
  }, [])

  useEffect(() => {
    if (!enabled || count < 2) {
      clearTimer()
      setActiveIndex(0)
      return undefined
    }

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    if (prefersReducedMotion) return undefined

    clearTimer()
    timerRef.current = setInterval(() => {
      if (!pausedRef.current) advance()
    }, interval)

    return clearTimer
  }, [enabled, count, interval, advance, clearTimer])

  return { activeIndex, pause, resume, setActiveIndex }
}
