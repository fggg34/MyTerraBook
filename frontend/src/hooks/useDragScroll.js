import { useEffect } from 'react'

const INTERACTIVE = 'a, button, input, select, textarea, label, [role="button"]'
const DRAG_THRESHOLD = 5

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
    let pendingDrag = false
    let startX = 0
    let scrollLeftStart = 0
    let moved = false

    const prepareScroll = () => {
      const animatedEl = resolveAnimationSource(el, convertAnimationFrom)
      if (animatedEl) convertAnimatedTrackToScroll(el, animatedEl)
    }

    const removeDragListeners = () => {
      document.removeEventListener('pointermove', onPointerMove)
      document.removeEventListener('pointerup', endDrag)
      document.removeEventListener('pointercancel', endDrag)
    }

    const beginDrag = (e) => {
      prepareScroll()
      isDragging = true
      lockScrollPhysics(el)
      el.classList.add('is-dragging')
      el.setPointerCapture?.(e.pointerId)
    }

    const onPointerMove = (e) => {
      if (!isDragging && !pendingDrag) return

      const dx = e.pageX - startX

      if (!isDragging && pendingDrag) {
        if (Math.abs(dx) <= DRAG_THRESHOLD) return
        pendingDrag = false
        startX = e.pageX
        scrollLeftStart = el.scrollLeft
        beginDrag(e)
      }

      if (!isDragging) return

      e.preventDefault()
      const dragDx = e.pageX - startX
      if (Math.abs(dragDx) > 2) moved = true
      el.scrollLeft = scrollLeftStart - dragDx
    }

    const endDrag = (e) => {
      removeDragListeners()

      if (isDragging) {
        el.classList.remove('is-dragging')
        unlockScrollPhysics(el)
        if (e?.pointerId != null) el.releasePointerCapture?.(e.pointerId)
      }

      isDragging = false
      pendingDrag = false
    }

    const onPointerDown = (e) => {
      if (e.pointerType === 'mouse' && e.button !== 0) return
      if (e.target.closest(INTERACTIVE) && !e.target.closest('.pcard-stretch-link, a.rcard')) return

      moved = false
      startX = e.pageX
      scrollLeftStart = el.scrollLeft
      pendingDrag = true

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
      removeDragListeners()
      el.classList.remove('is-dragging')
      unlockScrollPhysics(el)
    }
  }, [ref, enabled, convertAnimationFrom])
}
