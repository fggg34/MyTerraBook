import { useCallback, useLayoutEffect, useRef, useState } from 'react'

function getStickyOffset() {
  const raw = getComputedStyle(document.documentElement).getPropertyValue('--nav-h').trim()
  const parsed = parseFloat(raw)
  return Number.isFinite(parsed) ? parsed : 64
}

function computeStepState(track, stepCount) {
  if (!track || stepCount < 1) {
    return { activeIndex: 0, barProgress: 0 }
  }

  const stickyTop = getStickyOffset()
  const scrollRange = track.offsetHeight - window.innerHeight + stickyTop

  if (scrollRange <= 0) {
    return { activeIndex: 0, barProgress: stepCount <= 1 ? 1 : 0 }
  }

  const rect = track.getBoundingClientRect()
  const progress = Math.max(0, Math.min(1, (stickyTop - rect.top) / scrollRange))
  const activeIndex = Math.min(stepCount - 1, Math.floor(progress * stepCount))

  if (stepCount <= 1) {
    return { activeIndex: 0, barProgress: 1 }
  }

  const scaled = progress * stepCount
  const withinStep = scaled - activeIndex
  const barProgress = Math.max(0, Math.min(1, (activeIndex + withinStep) / (stepCount - 1)))

  return { activeIndex, barProgress }
}

export default function useScrollPinnedSteps(track, { stepCount = 1, enabled = true } = {}) {
  const [activeIndex, setActiveIndex] = useState(0)
  const [barProgress, setBarProgress] = useState(0)
  const trackRef = useRef(track)
  trackRef.current = track

  const applyState = useCallback(({ activeIndex: index, barProgress: progress }) => {
    setActiveIndex(index)
    setBarProgress(progress)
  }, [])

  useLayoutEffect(() => {
    if (!enabled || stepCount < 1 || !track) return undefined

    const update = () => {
      const el = trackRef.current
      if (!el) return
      applyState(computeStepState(el, stepCount))
    }

    update()
    window.addEventListener('scroll', update, { passive: true })
    window.addEventListener('resize', update)
    window.visualViewport?.addEventListener('resize', update)
    window.visualViewport?.addEventListener('scroll', update)

    return () => {
      window.removeEventListener('scroll', update)
      window.removeEventListener('resize', update)
      window.visualViewport?.removeEventListener('resize', update)
      window.visualViewport?.removeEventListener('scroll', update)
    }
  }, [applyState, enabled, stepCount, track])

  const scrollToStep = useCallback(
    (index) => {
      const el = trackRef.current
      if (!el || stepCount < 1) return

      const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches
      const stickyTop = getStickyOffset()
      const scrollRange = el.offsetHeight - window.innerHeight + stickyTop
      if (scrollRange <= 0) return

      const clamped = Math.max(0, Math.min(stepCount - 1, index))
      const trackTop = el.getBoundingClientRect().top + window.scrollY
      const progress = stepCount <= 1 ? 1 : clamped / (stepCount - 1)
      const target = trackTop - stickyTop + progress * scrollRange

      window.scrollTo({ top: target, behavior: prefersReducedMotion ? 'auto' : 'smooth' })
    },
    [stepCount],
  )

  return { activeIndex, barProgress, scrollToStep }
}
