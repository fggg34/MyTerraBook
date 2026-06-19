import { useEffect, useLayoutEffect } from 'react'

export default function useSectionReveal(
  sectionRef,
  { revealDoneMs = 1700, threshold = 0.12, onReveal, watch = true } = {},
) {
  const runReveal = (sec) => {
    if (sec.classList.contains('revealed')) return false
    sec.classList.add('revealed')
    onReveal?.()
    window.setTimeout(() => sec.classList.add('reveal-done'), revealDoneMs)
    return true
  }

  const maybeReveal = (sec) => {
    if (sec.classList.contains('revealed')) return true

    const rect = sec.getBoundingClientRect()
    const vh = window.innerHeight || document.documentElement.clientHeight
    if (rect.top < vh * 0.92 && rect.bottom > vh * 0.04) {
      return runReveal(sec)
    }
    return false
  }

  useLayoutEffect(() => {
    if (!watch) return undefined

    const sec = sectionRef.current
    if (!sec) return undefined

    if (maybeReveal(sec)) return undefined

    const raf = window.requestAnimationFrame(() => {
      maybeReveal(sec)
    })

    return () => window.cancelAnimationFrame(raf)
  }, [sectionRef, revealDoneMs, onReveal, watch])

  useEffect(() => {
    if (!watch) return undefined

    const sec = sectionRef.current
    if (!sec || sec.classList.contains('revealed')) return undefined

    let io
    let t1
    let t2

    const stopWatching = () => {
      if (io) io.disconnect()
      window.removeEventListener('scroll', checkReveal)
      window.removeEventListener('resize', checkReveal)
      window.clearTimeout(t1)
      window.clearTimeout(t2)
    }

    const checkReveal = () => {
      if (maybeReveal(sec)) {
        stopWatching()
      }
    }

    if ('IntersectionObserver' in window) {
      io = new IntersectionObserver(
        (entries) => {
          entries.forEach((entry) => {
            if (entry.isIntersecting) checkReveal()
          })
        },
        { threshold, rootMargin: '0px 0px -40px 0px' },
      )
      io.observe(sec)
    }

    window.addEventListener('scroll', checkReveal, { passive: true })
    window.addEventListener('resize', checkReveal)
    checkReveal()
    t1 = window.setTimeout(checkReveal, 120)
    t2 = window.setTimeout(checkReveal, 500)

    return stopWatching
  }, [sectionRef, revealDoneMs, threshold, onReveal, watch])
}
