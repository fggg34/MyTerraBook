import { useEffect } from 'react'

const AUTO_MS = 4500

export default function useReviewsDeckEffects({
  deckRef,
  reviewCount,
  activeIndex,
  setActiveIndex,
  paused,
  setPaused,
  revealed,
}) {
  useEffect(() => {
    if (!revealed || paused || reviewCount < 2) return undefined

    const timer = window.setInterval(() => {
      setActiveIndex((current) => (current + 1) % reviewCount)
    }, AUTO_MS)

    return () => window.clearInterval(timer)
  }, [revealed, paused, reviewCount, setActiveIndex])

  useEffect(() => {
    const deck = deckRef.current
    if (!deck) return undefined

    const mobile = window.matchMedia('(max-width: 900px)').matches
    if (!mobile) return undefined

    const card = deck.children[activeIndex]
    if (!card) return undefined

    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    card.scrollIntoView({
      behavior: reduce ? 'auto' : 'smooth',
      inline: 'center',
      block: 'nearest',
    })
  }, [activeIndex, deckRef])

  useEffect(() => {
    const panel = deckRef.current?.closest('.r-panel')
    if (!panel) return undefined

    const onEnter = () => setPaused(true)
    const onLeave = () => setPaused(false)

    panel.addEventListener('mouseenter', onEnter)
    panel.addEventListener('mouseleave', onLeave)

    return () => {
      panel.removeEventListener('mouseenter', onEnter)
      panel.removeEventListener('mouseleave', onLeave)
    }
  }, [deckRef, setPaused])
}
