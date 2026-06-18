import { ArrowUp } from 'lucide-react'
import { useEffect, useState } from 'react'

const BTN_SIZE = 44
const VIEWPORT_EDGE = 24
const CARD_INSET = 16

function getDefaultPosition() {
  return {
    bottom: VIEWPORT_EDGE,
    top: null,
    right: VIEWPORT_EDGE,
  }
}

export default function BackToTop() {
  const [visible, setVisible] = useState(false)
  const [pos, setPos] = useState(getDefaultPosition)

  useEffect(() => {
    const update = () => {
      setVisible(window.scrollY > 400)

      const meta = document.querySelector('.ftr-meta')
      if (!meta) {
        setPos(getDefaultPosition())
        return
      }

      const rect = meta.getBoundingClientRect()
      const vh = window.innerHeight
      const inView = rect.top < vh - VIEWPORT_EDGE && rect.bottom > VIEWPORT_EDGE

      if (!inView) {
        setPos(getDefaultPosition())
        return
      }

      const card = meta.closest('.ftr-card')
      const cardRect = card?.getBoundingClientRect()
      const right = cardRect
        ? Math.max(VIEWPORT_EDGE, window.innerWidth - cardRect.right + CARD_INSET)
        : VIEWPORT_EDGE
      const centerY = rect.top + rect.height / 2 - BTN_SIZE / 2
      const top = Math.max(VIEWPORT_EDGE, Math.min(centerY, vh - BTN_SIZE - VIEWPORT_EDGE))

      setPos({ bottom: null, top, right })
    }

    update()
    window.addEventListener('scroll', update, { passive: true })
    window.addEventListener('resize', update)
    return () => {
      window.removeEventListener('scroll', update)
      window.removeEventListener('resize', update)
    }
  }, [])

  if (!visible) return null

  const style = {
    right: pos.right,
    ...(pos.top != null ? { top: pos.top } : { bottom: pos.bottom }),
  }

  return (
    <button
      type="button"
      className="back-to-top"
      style={style}
      onClick={() => window.scrollTo({ top: 0, behavior: 'smooth' })}
      aria-label="Back to top"
    >
      <ArrowUp aria-hidden />
    </button>
  )
}
