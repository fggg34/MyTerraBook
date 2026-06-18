import { useEffect, useRef, useState } from 'react'

const AXIS_THRESHOLD = 8
const SWIPE_THRESHOLD = 48

export default function useTrustStripSwipe(
  trackRef,
  { count = 0, setActiveIndex, enabled = true, onSwipe, onTap, onDragStart, onDragEnd } = {},
) {
  const [dragOffset, setDragOffset] = useState(0)
  const [isDragging, setIsDragging] = useState(false)
  const dragRef = useRef({
    active: false,
    axis: null,
    startX: 0,
    startY: 0,
    moved: false,
  })

  useEffect(() => {
    if (!enabled || count < 2) return undefined

    const track = trackRef.current
    if (!track) return undefined

    const resetDrag = () => {
      dragRef.current = {
        active: false,
        axis: null,
        startX: 0,
        startY: 0,
        moved: false,
      }
      setDragOffset(0)
      setIsDragging(false)
    }

    const onPointerDown = (e) => {
      if (e.pointerType === 'mouse' && e.button !== 0) return

      dragRef.current = {
        active: true,
        axis: null,
        startX: e.clientX,
        startY: e.clientY,
        moved: false,
      }
      track.setPointerCapture?.(e.pointerId)
    }

    const onPointerMove = (e) => {
      if (!dragRef.current.active) return

      const dx = e.clientX - dragRef.current.startX
      const dy = e.clientY - dragRef.current.startY

      if (!dragRef.current.axis) {
        if (Math.abs(dx) < AXIS_THRESHOLD && Math.abs(dy) < AXIS_THRESHOLD) return
        if (Math.abs(dy) > Math.abs(dx)) {
          dragRef.current.active = false
          dragRef.current.axis = 'y'
          track.releasePointerCapture?.(e.pointerId)
          resetDrag()
          return
        }
        dragRef.current.axis = 'x'
        setIsDragging(true)
        onDragStart?.()
      }

      if (dragRef.current.axis !== 'x') return

      e.preventDefault()
      if (Math.abs(dx) > 4) dragRef.current.moved = true
      setDragOffset(dx)
    }

    const finishDrag = (e) => {
      const { active, axis, startX, moved } = dragRef.current
      if (!active) return

      let didSwipe = false

      if (axis === 'x' && moved) {
        const dx = e.clientX - startX
        if (Math.abs(dx) >= SWIPE_THRESHOLD) {
          if (dx < 0) {
            setActiveIndex((current) => (current + 1) % count)
          } else {
            setActiveIndex((current) => (current - 1 + count) % count)
          }
          onSwipe?.()
          didSwipe = true
        }
      } else if (!moved && axis !== 'y') {
        onTap?.()
      }

      track.releasePointerCapture?.(e.pointerId)
      resetDrag()

      if (axis === 'x' && moved) {
        onDragEnd?.({ didSwipe })
      }
    }

    const onPointerUp = (e) => finishDrag(e)
    const onPointerCancel = (e) => finishDrag(e)

    track.addEventListener('pointerdown', onPointerDown)
    track.addEventListener('pointermove', onPointerMove)
    track.addEventListener('pointerup', onPointerUp)
    track.addEventListener('pointercancel', onPointerCancel)

    return () => {
      track.removeEventListener('pointerdown', onPointerDown)
      track.removeEventListener('pointermove', onPointerMove)
      track.removeEventListener('pointerup', onPointerUp)
      track.removeEventListener('pointercancel', onPointerCancel)
      resetDrag()
    }
  }, [count, enabled, onDragEnd, onDragStart, onSwipe, onTap, setActiveIndex, trackRef])

  return { dragOffset, isDragging }
}
