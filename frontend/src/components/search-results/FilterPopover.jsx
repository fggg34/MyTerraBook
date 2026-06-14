import { useEffect, useLayoutEffect, useRef, useState } from 'react'
import { createPortal } from 'react-dom'

function computePortalStyle(anchorEl, popEl, align = 'left') {
  if (!anchorEl) return null

  const rect = anchorEl.getBoundingClientRect()
  const popWidth = popEl?.offsetWidth || 320
  const margin = 12
  let left = rect.left

  if (align === 'right') {
    left = rect.right - popWidth
  }

  left = Math.max(margin, Math.min(left, window.innerWidth - popWidth - margin))

  return {
    position: 'fixed',
    top: rect.bottom + 10,
    left,
    zIndex: 200,
  }
}

export default function FilterPopover({
  open,
  children,
  className = '',
  onCloseComplete,
  anchorRef,
  menuRef,
  portal = false,
  align = 'left',
}) {
  const [mounted, setMounted] = useState(open)
  const [visible, setVisible] = useState(open)
  const [portalStyle, setPortalStyle] = useState(null)
  const internalPopRef = useRef(null)

  const setPopRef = (node) => {
    internalPopRef.current = node
    if (menuRef) {
      if (typeof menuRef === 'function') menuRef(node)
      else menuRef.current = node
    }
  }

  useEffect(() => {
    if (open) {
      setMounted(true)
      const frame = requestAnimationFrame(() => setVisible(true))
      return () => cancelAnimationFrame(frame)
    }

    setVisible(false)
    return undefined
  }, [open])

  useLayoutEffect(() => {
    if (!portal || !open || !mounted) {
      setPortalStyle(null)
      return undefined
    }

    const updatePosition = () => {
      const anchorEl = anchorRef?.current
      const popEl = internalPopRef.current
      if (!anchorEl || !popEl) return
      setPortalStyle(computePortalStyle(anchorEl, popEl, align))
    }

    updatePosition()
    const frame = requestAnimationFrame(updatePosition)
    window.addEventListener('resize', updatePosition)
    window.addEventListener('scroll', updatePosition, true)

    return () => {
      cancelAnimationFrame(frame)
      window.removeEventListener('resize', updatePosition)
      window.removeEventListener('scroll', updatePosition, true)
    }
  }, [portal, open, mounted, visible, anchorRef, align])

  if (!mounted) return null

  const handleTransitionEnd = (event) => {
    if (event.target !== event.currentTarget) return
    if (!visible) {
      setMounted(false)
      onCloseComplete?.()
    }
  }

  const pop = (
    <div
      ref={setPopRef}
      className={`fpop ${visible ? 'show' : 'hide'} ${portal ? 'fpop--portal' : ''} ${className}`.trim()}
      style={portal ? portalStyle || undefined : undefined}
      onTransitionEnd={handleTransitionEnd}
    >
      {children}
    </div>
  )

  if (portal && typeof document !== 'undefined') {
    return createPortal(pop, document.body)
  }

  return pop
}
