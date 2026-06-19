import { useEffect, useRef } from 'react'
import useDragScroll from './useDragScroll'

const LOOP_DURATION_MS = 52000

export default function useReviewsMarquee(trackRef, { enabled = true, durationMs = LOOP_DURATION_MS } = {}) {
  const hoverPausedRef = useRef(false)
  const dragPausedRef = useRef(false)
  const rafRef = useRef(0)

  const isPaused = () => hoverPausedRef.current || dragPausedRef.current

  useDragScroll(trackRef, { enabled })

  useEffect(() => {
    if (!enabled) return undefined

    const el = trackRef.current
    if (!el) return undefined

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    if (prefersReducedMotion) return undefined

    const halfWidth = () => el.scrollWidth / 2

    let lastTime = performance.now()

    const tick = (now) => {
      const half = halfWidth()
      const section = el.closest('.reviews')
      const revealed = section?.classList.contains('revealed') ?? true

      if (!isPaused() && revealed && half > 0) {
        const dt = Math.min(now - lastTime, 48)
        el.scrollLeft += (half / durationMs) * dt
        if (el.scrollLeft >= half) {
          el.scrollLeft -= half
        }
      }

      lastTime = now
      rafRef.current = requestAnimationFrame(tick)
    }

    const onEnter = () => {
      hoverPausedRef.current = true
    }
    const onLeave = () => {
      hoverPausedRef.current = false
    }
    const onPointerDown = () => {
      dragPausedRef.current = true
    }
    const onPointerUp = () => {
      dragPausedRef.current = false
    }

    rafRef.current = requestAnimationFrame(tick)
    el.addEventListener('mouseenter', onEnter)
    el.addEventListener('mouseleave', onLeave)
    el.addEventListener('pointerdown', onPointerDown)
    el.addEventListener('pointerup', onPointerUp)
    el.addEventListener('pointercancel', onPointerUp)
    el.addEventListener('touchstart', onPointerDown, { passive: true })
    el.addEventListener('touchend', onPointerUp, { passive: true })

    return () => {
      cancelAnimationFrame(rafRef.current)
      el.removeEventListener('mouseenter', onEnter)
      el.removeEventListener('mouseleave', onLeave)
      el.removeEventListener('pointerdown', onPointerDown)
      el.removeEventListener('pointerup', onPointerUp)
      el.removeEventListener('pointercancel', onPointerUp)
      el.removeEventListener('touchstart', onPointerDown)
      el.removeEventListener('touchend', onPointerUp)
    }
  }, [trackRef, enabled, durationMs])

  return {
    pause: () => {
      hoverPausedRef.current = true
    },
    resume: () => {
      hoverPausedRef.current = false
    },
  }
}
