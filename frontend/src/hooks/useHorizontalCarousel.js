import { useCallback, useEffect, useRef, useState } from 'react'
import useDragScroll from './useDragScroll'

export default function useHorizontalCarousel({
  gap = 24,
  cardSelector = '.pcard',
  itemCount = 0,
  enabled = true,
} = {}) {
  const trackRef = useRef(null)
  const [atStart, setAtStart] = useState(true)
  const [atEnd, setAtEnd] = useState(false)

  const updateNav = useCallback(() => {
    const track = trackRef.current
    if (!track) return
    const max = track.scrollWidth - track.clientWidth - 2
    setAtStart(track.scrollLeft <= 2)
    setAtEnd(track.scrollLeft >= max)
  }, [])

  useEffect(() => {
    if (!enabled) return undefined

    updateNav()
    const track = trackRef.current
    if (!track) return undefined

    track.addEventListener('scroll', updateNav, { passive: true })
    window.addEventListener('resize', updateNav)
    const t = setTimeout(updateNav, 300)

    return () => {
      clearTimeout(t)
      track.removeEventListener('scroll', updateNav)
      window.removeEventListener('resize', updateNav)
    }
  }, [enabled, itemCount, updateNav])

  const scroll = useCallback(
    (direction) => {
      const track = trackRef.current
      if (!track) return
      const card = track.querySelector(cardSelector)
      const step = card ? card.getBoundingClientRect().width + gap : 360
      track.scrollBy({ left: direction * step, behavior: 'smooth' })
    },
    [gap, cardSelector],
  )

  useDragScroll(trackRef, { enabled })

  return { trackRef, scroll, atStart, atEnd }
}
