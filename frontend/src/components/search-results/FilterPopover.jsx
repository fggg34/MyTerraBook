import { useEffect, useState } from 'react'

export default function FilterPopover({ open, children, className = '', onCloseComplete }) {
  const [mounted, setMounted] = useState(open)
  const [visible, setVisible] = useState(open)

  useEffect(() => {
    if (open) {
      setMounted(true)
      const frame = requestAnimationFrame(() => setVisible(true))
      return () => cancelAnimationFrame(frame)
    }

    setVisible(false)
    return undefined
  }, [open])

  if (!mounted) return null

  const handleTransitionEnd = (event) => {
    if (event.target !== event.currentTarget) return
    if (!visible) {
      setMounted(false)
      onCloseComplete?.()
    }
  }

  return (
    <div
      className={`fpop ${visible ? 'show' : 'hide'} ${className}`.trim()}
      onTransitionEnd={handleTransitionEnd}
    >
      {children}
    </div>
  )
}
