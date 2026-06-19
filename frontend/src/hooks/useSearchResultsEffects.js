import { useEffect } from 'react'
import { useSearchResultsChrome } from '../context/SearchResultsChromeContext'

export default function useSearchResultsEffects({ rootRef, totalCount, visibleCount, onLoadMore }) {
  const chrome = useSearchResultsChrome()

  useEffect(() => {
    document.body.classList.add('search-results-active')
    document.documentElement.classList.add('reveal-on')

    const root = rootRef.current
    const chromeEl = document.getElementById('searchChrome')
    if (!root || !chromeEl) return undefined

    const progressEl = document.getElementById('scrollProgress')
    const resfloat = root.querySelector('#resfloat')
    const rfBar = root.querySelector('#rfBar')
    const progressTextEl = root.querySelector('#progressText')
    let condensed = false

    const mobileCollapseAt = () => {
      if (window.innerWidth > 768) return 120
      const inner = document.getElementById('hsearch')?.querySelector('.hsearch-inner')
      const height = inner?.scrollHeight ?? 72
      return Math.min(Math.max(height - 16, 72), 220)
    }

    const onScroll = () => {
      const y = window.scrollY
      const isMobile = window.innerWidth <= 768
      const collapseAt = isMobile ? mobileCollapseAt() : 120
      const expandAt = isMobile ? 0 : 32

      if (!condensed && y > collapseAt) condensed = true
      else if (condensed && y <= expandAt) condensed = false

      const isCondensed = chromeEl.classList.contains('condensed')
      if (isCondensed !== condensed) {
        chrome?.setCondensed?.(condensed)
        chromeEl.classList.toggle('condensed', condensed)
        document.body.classList.toggle('search-chrome-condensed', condensed)
      }

      const stuck = y > 80
      chromeEl.classList.toggle('stuck', stuck)
      chrome?.setStuck?.(stuck)
      document.body.classList.toggle('search-chrome-stuck', stuck)

      const docH = document.documentElement.scrollHeight - window.innerHeight
      const pct = docH > 0 ? Math.min(100, (y / docH) * 100) : 0
      if (progressEl) progressEl.style.width = `${pct}%`

      const progressTextVisible = progressTextEl
        ? progressTextEl.getBoundingClientRect().top <= window.innerHeight - 80
        : false
      const showFloat =
        window.innerWidth > 768 &&
        y > 500 &&
        visibleCount < totalCount &&
        !progressTextVisible
      resfloat?.classList.toggle('show', showFloat)

      const progress = totalCount > 0 ? visibleCount / totalCount : 0
      if (rfBar) {
        const circumference = 2 * Math.PI * 12.5
        rfBar.style.strokeDasharray = `${circumference}`
        rfBar.style.strokeDashoffset = `${circumference * (1 - progress)}`
      }

      root.querySelectorAll('.cell.reveal:not(.in)').forEach((cell) => {
        const r = cell.getBoundingClientRect()
        if (r.top < window.innerHeight * 0.92) cell.classList.add('in')
      })
    }

    window.addEventListener('scroll', onScroll, { passive: true })
    onScroll()

    const loadBtn = root.querySelector('#loadMore')
    const onLoad = () => onLoadMore?.()
    loadBtn?.addEventListener('click', onLoad)

    const rfUp = root.querySelector('#rfUp')
    const onUp = () => window.scrollTo({ top: 0, behavior: 'smooth' })
    rfUp?.addEventListener('click', onUp)

    const pill = document.getElementById('hsearchPill')
    const onPill = () => {
      condensed = false
      chrome?.setCondensed?.(false)
      chrome?.setStuck?.(false)
      chromeEl.classList.remove('condensed', 'stuck')
      document.body.classList.remove('search-chrome-condensed', 'search-chrome-stuck')
      window.scrollTo({ top: 0, behavior: 'smooth' })
    }
    pill?.addEventListener('click', onPill)

    return () => {
      document.body.classList.remove('search-results-active', 'search-chrome-condensed', 'search-chrome-stuck')
      document.documentElement.classList.remove('reveal-on')
      window.removeEventListener('scroll', onScroll)
      loadBtn?.removeEventListener('click', onLoad)
      rfUp?.removeEventListener('click', onUp)
      pill?.removeEventListener('click', onPill)
    }
  }, [rootRef, totalCount, visibleCount, onLoadMore, chrome])
}
