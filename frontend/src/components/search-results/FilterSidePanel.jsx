import { useEffect, useRef, useState } from 'react'
import { createPortal } from 'react-dom'

export default function FilterSidePanel({
  open,
  onClose,
  children,
  title = 'Filters',
  side = 'right',
  panelRef,
  footer,
}) {
  const [mounted, setMounted] = useState(open)
  const [visible, setVisible] = useState(open)
  const internalPanelRef = useRef(null)

  const setPanelRef = (node) => {
    internalPanelRef.current = node
    if (panelRef) {
      if (typeof panelRef === 'function') panelRef(node)
      else panelRef.current = node
    }
  }

  useEffect(() => {
    if (open) {
      setMounted(true)
      const frame = requestAnimationFrame(() => setVisible(true))
      return () => cancelAnimationFrame(frame)
    }

    setVisible(false)
    return undefined
  }, [open])

  useEffect(() => {
    if (!open) return undefined

    const prevOverflow = document.body.style.overflow
    document.body.style.overflow = 'hidden'

    return () => {
      document.body.style.overflow = prevOverflow
    }
  }, [open])

  if (!mounted || typeof document === 'undefined') return null

  const handleTransitionEnd = (event) => {
    if (event.target !== event.currentTarget) return
    if (!visible) {
      setMounted(false)
    }
  }

  const panel = (
    <div
      className={`filter-side-root ${visible ? 'show' : 'hide'} filter-side-root--${side}`}
      aria-hidden={!visible}
    >
      <button
        type="button"
        className="filter-side-backdrop"
        aria-label="Close filters"
        onClick={onClose}
      />
      <aside
        ref={setPanelRef}
        className="filter-side-panel"
        role="dialog"
        aria-modal="true"
        aria-label={title}
        onTransitionEnd={handleTransitionEnd}
      >
        <header className="filter-side-head">
          <h2 className="filter-side-title">{title}</h2>
          <button type="button" className="filter-side-close" aria-label="Close filters" onClick={onClose}>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" aria-hidden>
              <path d="M6 6l12 12M18 6 6 18" />
            </svg>
          </button>
        </header>
        <div className="filter-side-body">{children}</div>
        {footer && <footer className="filter-side-foot">{footer}</footer>}
      </aside>
    </div>
  )

  return createPortal(panel, document.body)
}
