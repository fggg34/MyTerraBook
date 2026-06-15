import { useEffect } from 'react'

const INTERACTIVE = 'a, button, input, select, textarea, label, [role="button"]'

function resolveAnimationSource(root, convertAnimationFrom) {
  if (!convertAnimationFrom) return null
  if (typeof convertAnimationFrom === 'string') {
    return root.querySelector(convertAnimationFrom)
  }
  return convertAnimationFrom.current
}

function convertAnimatedTrackToScroll(root, animatedEl) {
  if (!root || !animatedEl || root.dataset.dragScrollReady === '1') return

  const style = window.getComputedStyle(animatedEl)
  if (style.transform && style.transform !== 'none') {
    const matrix = new DOMMatrixReadOnly(style.transform)
    root.scrollLeft = Math.abs(matrix.m41)
  }

  animatedEl.style.animation = 'none'
  animatedEl.style.transform = 'none'
  animatedEl.style.willChange = 'auto'
  root.classList.add('is-manual-scroll')
  root.dataset.dragScrollReady = '1'
}

function lockScrollPhysics(el) {
  el.style.scrollSnapType = 'none'
  el.style.scrollBehavior = 'auto'
}

function unlockScrollPhysics(el) {
  el.style.scrollSnapType = ''
  el.style.scrollBehavior = ''
}

export default function useDragScroll(ref, { enabled = true, convertAnimationFrom = null } = {}) {
  useEffect(() => {
    if (!enabled) return undefined

    const el = ref.current
    if (!el) return undefined

    let isDragging = false
    let startX = 0
    let scrollLeftStart = 0
    let moved = false

    const prepareScroll = () => {
      const animatedEl = resolveAnimationSource(el, convertAnimationFrom)
      if (animatedEl) convertAnimatedTrackToScroll(el, animatedEl)
    }

    const canScroll = () => el.scrollWidth > el.clientWidth + 1

    const onPointerMove = (e) => {
      if (!isDragging) return
      e.preventDefault()
      const dx = e.pageX - startX
      if (Math.abs(dx) > 2) moved = true
      el.scrollLeft = scrollLeftStart - dx
    }

    const endDrag = (e) => {
      if (!isDragging) return
      isDragging = false
      el.classList.remove('is-dragging')
      unlockScrollPhysics(el)
      document.removeEventListener('pointermove', onPointerMove)
      document.removeEventListener('pointerup', endDrag)
      document.removeEventListener('pointercancel', endDrag)
      if (e?.pointerId != null) el.releasePointerCapture?.(e.pointerId)
    }

    const onPointerDown = (e) => {
      if (e.pointerType !== 'mouse' || e.button !== 0) return
      if (e.target.closest(INTERACTIVE)) return

      prepareScroll()
      if (!canScroll()) return

      e.preventDefault()

      isDragging = true
      moved = false
      startX = e.pageX
      scrollLeftStart = el.scrollLeft
      lockScrollPhysics(el)
      el.classList.add('is-dragging')
      el.setPointerCapture?.(e.pointerId)

      document.addEventListener('pointermove', onPointerMove, { passive: false })
      document.addEventListener('pointerup', endDrag)
      document.addEventListener('pointercancel', endDrag)
    }

    const onClick = (e) => {
      if (moved) {
        e.preventDefault()
        e.stopPropagation()
      }
    }

    const onDragStart = (e) => e.preventDefault()

    el.addEventListener('pointerdown', onPointerDown)
    el.addEventListener('click', onClick, true)
    el.addEventListener('dragstart', onDragStart)

    return () => {
      el.removeEventListener('pointerdown', onPointerDown)
      el.removeEventListener('click', onClick, true)
      el.removeEventListener('dragstart', onDragStart)
      document.removeEventListener('pointermove', onPointerMove)
      document.removeEventListener('pointerup', endDrag)
      document.removeEventListener('pointercancel', endDrag)
      el.classList.remove('is-dragging')
      unlockScrollPhysics(el)
    }
  }, [ref, enabled, convertAnimationFrom])
}
