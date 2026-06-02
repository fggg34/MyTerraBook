import { useEffect, useLayoutEffect } from 'react'

export default function useSectionReveal(sectionRef, { revealDoneMs = 1700, threshold = 0.12 } = {}) {
  const runReveal = (sec) => {
    if (sec.classList.contains('revealed')) return
    sec.classList.add('revealed')
    window.setTimeout(() => sec.classList.add('reveal-done'), revealDoneMs)
  }

  const maybeReveal = (sec) => {
    const rect = sec.getBoundingClientRect()
    const vh = window.innerHeight || document.documentElement.clientHeight
    if (rect.top < vh * 0.92 && rect.bottom > vh * 0.04) {
      runReveal(sec)
      return true
    }
    return false
  }

  useLayoutEffect(() => {
    const sec = sectionRef.current
    if (!sec) return undefined

    if (maybeReveal(sec)) return undefined

    const raf = window.requestAnimationFrame(() => {
      maybeReveal(sec)
    })

    return () => window.cancelAnimationFrame(raf)
  }, [sectionRef, revealDoneMs])

  useEffect(() => {
    const sec = sectionRef.current
    if (!sec || sec.classList.contains('revealed')) return undefined

    let io

    const onReveal = () => {
      if (maybeReveal(sec) && io) io.disconnect()
    }

    if ('IntersectionObserver' in window) {
      io = new IntersectionObserver(
        (entries) => {
          entries.forEach((entry) => {
            if (entry.isIntersecting) onReveal()
          })
        },
        { threshold, rootMargin: '0px 0px -40px 0px' },
      )
      io.observe(sec)
    }

    window.addEventListener('scroll', onReveal, { passive: true })
    window.addEventListener('resize', onReveal)
    onReveal()
    const t1 = window.setTimeout(onReveal, 120)
    const t2 = window.setTimeout(onReveal, 500)

    return () => {
      if (io) io.disconnect()
      window.removeEventListener('scroll', onReveal)
      window.removeEventListener('resize', onReveal)
      window.clearTimeout(t1)
      window.clearTimeout(t2)
    }
  }, [sectionRef, revealDoneMs, threshold])
}
