import { ArrowUp } from 'lucide-react'
import { useEffect, useRef, useState } from 'react'

const BTN_SIZE = 44
const VIEWPORT_EDGE = 24
const MOBILE_QUERY = '(max-width: 768px)'
const FOOTER_TRACK_LEAD = 320

function readMetaDocCenter() {
  const meta = document.querySelector('.ftr-meta')
  if (!meta) return null

  const rect = meta.getBoundingClientRect()
  return window.scrollY + rect.top + rect.height / 2
}

function applyMobileLift(button, mq, metaDocCenter) {
  if (!button || !mq.matches) {
    button?.style.removeProperty('transform')
    return
  }

  const scrollBottom = window.scrollY + window.innerHeight
  const docHeight = document.documentElement.scrollHeight

  if (scrollBottom < docHeight - FOOTER_TRACK_LEAD || metaDocCenter == null) {
    if (button.style.transform) button.style.removeProperty('transform')
    return
  }

  const btnCenterAtDefault = window.innerHeight - VIEWPORT_EDGE - BTN_SIZE / 2
  const metaViewportCenter = metaDocCenter - window.scrollY
  const lift = Math.max(0, Math.round(btnCenterAtDefault - metaViewportCenter))
  const transform = lift > 0 ? `translate3d(0, -${lift}px, 0)` : ''

  if (button.style.transform !== transform) {
    if (transform) button.style.transform = transform
    else button.style.removeProperty('transform')
  }
}

export default function BackToTop() {
  const buttonRef = useRef(null)
  const [visible, setVisible] = useState(false)
  const visibleRef = useRef(false)
  const rafRef = useRef(0)
  const metaDocCenterRef = useRef(null)
  const inFooterZoneRef = useRef(false)

  useEffect(() => {
    const mq = window.matchMedia(MOBILE_QUERY)

    const refreshMetaCenter = () => {
      metaDocCenterRef.current = readMetaDocCenter()
    }

    const update = () => {
      const shouldShow = window.scrollY > 400

      if (shouldShow !== visibleRef.current) {
        visibleRef.current = shouldShow
        setVisible(shouldShow)
      }

      if (!shouldShow || !mq.matches || !buttonRef.current) return

      const scrollBottom = window.scrollY + window.innerHeight
      const docHeight = document.documentElement.scrollHeight
      const nearFooter = scrollBottom >= docHeight - FOOTER_TRACK_LEAD

      if (nearFooter && !inFooterZoneRef.current) {
        refreshMetaCenter()
        inFooterZoneRef.current = true
      } else if (!nearFooter) {
        inFooterZoneRef.current = false
      }

      applyMobileLift(buttonRef.current, mq, metaDocCenterRef.current)
    }

    const onScroll = () => {
      if (!mq.matches) {
        if (visibleRef.current !== window.scrollY > 400) {
          const shouldShow = window.scrollY > 400
          visibleRef.current = shouldShow
          setVisible(shouldShow)
        }
        return
      }

      cancelAnimationFrame(rafRef.current)
      rafRef.current = requestAnimationFrame(update)
    }

    const onResize = () => {
      refreshMetaCenter()
      update()
    }

    const onMqChange = () => {
      if (!mq.matches && buttonRef.current) {
        buttonRef.current.style.removeProperty('transform')
        inFooterZoneRef.current = false
      }
      update()
    }

    let resizeObserver
    const meta = document.querySelector('.ftr-meta')
    if (meta && 'ResizeObserver' in window) {
      resizeObserver = new ResizeObserver(() => {
        if (!mq.matches) return
        refreshMetaCenter()
        if (inFooterZoneRef.current && buttonRef.current) {
          applyMobileLift(buttonRef.current, mq, metaDocCenterRef.current)
        }
      })
      resizeObserver.observe(meta)
    }

    update()
    window.addEventListener('scroll', onScroll, { passive: true })
    window.addEventListener('resize', onResize)
    mq.addEventListener('change', onMqChange)

    return () => {
      cancelAnimationFrame(rafRef.current)
      resizeObserver?.disconnect()
      window.removeEventListener('scroll', onScroll)
      window.removeEventListener('resize', onResize)
      mq.removeEventListener('change', onMqChange)
    }
  }, [])

  useEffect(() => {
    if (!visible || !buttonRef.current) return undefined

    const mq = window.matchMedia(MOBILE_QUERY)
    if (!mq.matches) {
      buttonRef.current.style.removeProperty('transform')
      return undefined
    }

    metaDocCenterRef.current = readMetaDocCenter()
    applyMobileLift(buttonRef.current, mq, metaDocCenterRef.current)

    return undefined
  }, [visible])

  if (!visible) return null

  return (
    <button
      ref={buttonRef}
      type="button"
      className="back-to-top"
      onClick={() => window.scrollTo({ top: 0, behavior: 'smooth' })}
      aria-label="Back to top"
    >
      <ArrowUp aria-hidden />
    </button>
  )
}
