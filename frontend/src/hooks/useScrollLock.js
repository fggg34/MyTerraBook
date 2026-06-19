import { useEffect, useRef } from 'react'

/**
 * Lock document scroll without position:fixed on body (that shifts the whole page
 * and hides the sticky header while scrolled).
 */
export default function useScrollLock(active, { allowSelector = '.mobile-menu-scroll, .mobile-menu, .lang-cur-panel--mobile' } = {}) {
  const scrollYRef = useRef(0)

  useEffect(() => {
    if (!active) return undefined

    scrollYRef.current = window.scrollY
    document.documentElement.classList.add('scroll-lock')
    document.body.classList.add('scroll-lock')

    const preventTouchMove = (event) => {
      if (allowSelector && event.target?.closest?.(allowSelector)) return
      event.preventDefault()
    }

    document.addEventListener('touchmove', preventTouchMove, { passive: false })

    return () => {
      document.documentElement.classList.remove('scroll-lock')
      document.body.classList.remove('scroll-lock')
      document.removeEventListener('touchmove', preventTouchMove)
      window.scrollTo(0, scrollYRef.current)
    }
  }, [active, allowSelector])

  useEffect(
    () => () => {
      document.documentElement.classList.remove('scroll-lock')
      document.body.classList.remove('scroll-lock')
      document.body.style.removeProperty('overflow')
      document.body.style.removeProperty('position')
      document.body.style.removeProperty('top')
      document.body.style.removeProperty('width')
      document.body.style.removeProperty('touch-action')
      document.documentElement.style.removeProperty('overflow')
    },
    [],
  )
}
