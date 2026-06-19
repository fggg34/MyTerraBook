import { useEffect, useRef } from 'react'

const MOVE_THRESHOLD = 8

export default function useCarouselScrollGuard(ref, { enabled = true } = {}) {
  const blockedRef = useRef(false)

  useEffect(() => {
    if (!enabled) return undefined

    const el = ref.current
    if (!el) return undefined

    let startX = 0
    let startY = 0

    const onTouchStart = (e) => {
      blockedRef.current = false
      startX = e.touches[0]?.clientX ?? 0
      startY = e.touches[0]?.clientY ?? 0
    }

    const onTouchMove = (e) => {
      const touch = e.touches[0]
      if (!touch) return
      const dx = Math.abs(touch.clientX - startX)
      const dy = Math.abs(touch.clientY - startY)
      if (dx > MOVE_THRESHOLD || dy > MOVE_THRESHOLD) {
        blockedRef.current = true
      }
    }

    el.addEventListener('touchstart', onTouchStart, { passive: true })
    el.addEventListener('touchmove', onTouchMove, { passive: true })

    return () => {
      el.removeEventListener('touchstart', onTouchStart)
      el.removeEventListener('touchmove', onTouchMove)
    }
  }, [ref, enabled])

  return blockedRef
}
