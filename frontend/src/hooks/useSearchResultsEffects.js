import { useEffect } from 'react'
import { useSearchResultsChrome } from '../context/SearchResultsChromeContext'

export default function useSearchResultsEffects({ rootRef, totalCount, visibleCount, onLoadMore }) {
  const chrome = useSearchResultsChrome()

  useEffect(() => {
    document.body.classList.add('search-results-active')
    document.documentElement.classList.add('reveal-on')

    const root = rootRef.current
    if (!root) return undefined

    const chromeEl = root.querySelector('#searchChrome')
    const progressEl = root.querySelector('#scrollProgress')
    const resfloat = root.querySelector('#resfloat')
    const rfBar = root.querySelector('#rfBar')
    const intro = root.querySelector('.results-intro')

    const onScroll = () => {
      const y = window.scrollY
      const introBottom = intro ? intro.getBoundingClientRect().bottom + y : 400
      const condensed = y > introBottom - 120
      chrome?.setCondensed?.(condensed)
      chromeEl?.classList.toggle('condensed', condensed)
      document.body.classList.toggle('search-chrome-condensed', condensed)

      if (chromeEl) {
        const rect = chromeEl.getBoundingClientRect()
        const stuck = rect.top <= 74 && y > 80
        chromeEl.classList.toggle('stuck', stuck)
        chrome?.setStuck?.(stuck)
        document.body.classList.toggle('search-chrome-stuck', stuck)
      }

      const docH = document.documentElement.scrollHeight - window.innerHeight
      const pct = docH > 0 ? Math.min(100, (y / docH) * 100) : 0
      if (progressEl) progressEl.style.width = `${pct}%`

      const showFloat = y > 500 && visibleCount < totalCount
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
      chromeEl?.classList.remove('condensed')
      document.body.classList.remove('search-chrome-condensed')
      intro?.scrollIntoView({ behavior: 'smooth', block: 'start' })
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
