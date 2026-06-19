import { useCallback, useLayoutEffect, useState } from 'react'

const GAP = 6
const MIN_MENU_HEIGHT = 120
const DEFAULT_MAX_HEIGHT = 260

function computeMenuStyle(anchorEl, { minWidth = 0, maxHeight = DEFAULT_MAX_HEIGHT } = {}) {
  if (!anchorEl) return null

  const rect = anchorEl.getBoundingClientRect()
  const vv = window.visualViewport
  const viewportTop = vv?.offsetTop ?? 0
  const viewportHeight = vv?.height ?? window.innerHeight
  const viewportBottom = viewportTop + viewportHeight

  const spaceBelow = viewportBottom - rect.bottom - GAP
  const spaceAbove = rect.top - viewportTop - GAP
  const openBelow = spaceBelow >= MIN_MENU_HEIGHT || spaceBelow >= spaceAbove

  const available = openBelow ? spaceBelow : spaceAbove
  const clampedMaxHeight = Math.max(80, Math.min(maxHeight, available))

  if (openBelow) {
    return {
      position: 'fixed',
      top: rect.bottom + GAP,
      left: rect.left,
      width: Math.max(rect.width, minWidth),
      maxHeight: clampedMaxHeight,
      overflowY: 'auto',
      zIndex: 1200,
    }
  }

  return {
    position: 'fixed',
    top: rect.top - GAP,
    left: rect.left,
    width: Math.max(rect.width, minWidth),
    maxHeight: clampedMaxHeight,
    overflowY: 'auto',
    transform: 'translateY(-100%)',
    zIndex: 1200,
  }
}

export default function useFixedMenuPosition(anchorRef, open, options = {}) {
  const { minWidth = 0, maxHeight = DEFAULT_MAX_HEIGHT, deps = [] } = options
  const [menuStyle, setMenuStyle] = useState(null)

  const updatePosition = useCallback(() => {
    const el = anchorRef.current
    if (!el) return
    setMenuStyle(computeMenuStyle(el, { minWidth, maxHeight }))
  }, [anchorRef, minWidth, maxHeight])

  useLayoutEffect(() => {
    if (!open) {
      setMenuStyle(null)
      return undefined
    }

    updatePosition()

    const onReposition = () => updatePosition()
    window.addEventListener('scroll', onReposition, true)
    window.addEventListener('resize', onReposition)
    window.visualViewport?.addEventListener('resize', onReposition)
    window.visualViewport?.addEventListener('scroll', onReposition)

    return () => {
      window.removeEventListener('scroll', onReposition, true)
      window.removeEventListener('resize', onReposition)
      window.visualViewport?.removeEventListener('resize', onReposition)
      window.visualViewport?.removeEventListener('scroll', onReposition)
    }
  }, [open, updatePosition, ...deps])

  return menuStyle
}
