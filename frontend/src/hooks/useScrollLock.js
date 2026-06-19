import { useEffect, useRef } from 'react'

/**
 * Prevent background scroll while a modal/menu is open.
 * Avoids overflow:hidden on body — that breaks position:sticky on the header.
 */
export default function useScrollLock(active, { allowSelector = '.mobile-menu-scroll, .mobile-menu, .lang-cur-panel--mobile' } = {}) {
  const scrollYRef = useRef(0)

  useEffect(() => {
    if (!active) return undefined

    scrollYRef.current = window.scrollY

    const preventTouchMove = (event) => {
      if (allowSelector && event.target?.closest?.(allowSelector)) return
      event.preventDefault()
    }

    const preventWheel = (event) => {
      if (allowSelector && event.target?.closest?.(allowSelector)) return
      event.preventDefault()
    }

    document.addEventListener('touchmove', preventTouchMove, { passive: false })
    document.addEventListener('wheel', preventWheel, { passive: false })

    return () => {
      document.removeEventListener('touchmove', preventTouchMove)
      document.removeEventListener('wheel', preventWheel)
      window.scrollTo(0, scrollYRef.current)
    }
  }, [active, allowSelector])
}
