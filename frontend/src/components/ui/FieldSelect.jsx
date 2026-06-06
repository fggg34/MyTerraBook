import { useCallback, useEffect, useId, useRef, useState } from 'react'
import { createPortal } from 'react-dom'

const CHEVRON_ICON = (
  <svg className="field-select-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round" aria-hidden>
    <path d="m6 9 6 6 6-6" />
  </svg>
)

export default function FieldSelect({
  value,
  onChange,
  options = [],
  placeholder = 'Select',
  icon,
  ariaLabel,
  disabled = false,
  className = '',
}) {
  const listId = useId()
  const rootRef = useRef(null)
  const triggerRef = useRef(null)
  const menuRef = useRef(null)
  const [open, setOpen] = useState(false)
  const [highlight, setHighlight] = useState(-1)
  const [menuStyle, setMenuStyle] = useState(null)

  const selected = options.find((opt) => opt.value === value)
  const hasValue = Boolean(value && selected)

  const updateMenuPosition = useCallback(() => {
    const el = triggerRef.current
    if (!el) return
    const rect = el.getBoundingClientRect()
    setMenuStyle({
      position: 'fixed',
      top: rect.bottom + 6,
      left: rect.left,
      width: Math.max(rect.width, 220),
      zIndex: 1200,
    })
  }, [])

  const closeMenu = useCallback(() => {
    setOpen(false)
    setHighlight(-1)
  }, [])

  const openMenu = useCallback(() => {
    if (disabled) return
    updateMenuPosition()
    setOpen(true)
    const idx = options.findIndex((opt) => opt.value === value)
    setHighlight(idx >= 0 ? idx : 0)
  }, [disabled, options, updateMenuPosition, value])

  const selectOption = (opt) => {
    onChange?.(opt.value)
    closeMenu()
  }

  useEffect(() => {
    if (!open) return undefined
    updateMenuPosition()
    const onScrollOrResize = () => updateMenuPosition()
    window.addEventListener('scroll', onScrollOrResize, true)
    window.addEventListener('resize', onScrollOrResize)
    return () => {
      window.removeEventListener('scroll', onScrollOrResize, true)
      window.removeEventListener('resize', onScrollOrResize)
    }
  }, [open, updateMenuPosition, options.length])

  useEffect(() => {
    if (!open) return undefined
    const onPointerDown = (event) => {
      const inRoot = rootRef.current?.contains(event.target)
      const inMenu = menuRef.current?.contains(event.target)
      if (!inRoot && !inMenu) closeMenu()
    }
    document.addEventListener('mousedown', onPointerDown)
    return () => document.removeEventListener('mousedown', onPointerDown)
  }, [open, closeMenu])

  const onKeyDown = (event) => {
    if (disabled) return
    if (!open && (event.key === 'ArrowDown' || event.key === 'ArrowUp' || event.key === 'Enter' || event.key === ' ')) {
      event.preventDefault()
      openMenu()
      return
    }
    if (event.key === 'Escape') {
      closeMenu()
      return
    }
    if (!open || !options.length) return
    if (event.key === 'ArrowDown') {
      event.preventDefault()
      setHighlight((prev) => (prev + 1) % options.length)
    } else if (event.key === 'ArrowUp') {
      event.preventDefault()
      setHighlight((prev) => (prev <= 0 ? options.length - 1 : prev - 1))
    } else if ((event.key === 'Enter' || event.key === ' ') && highlight >= 0) {
      event.preventDefault()
      selectOption(options[highlight])
    }
  }

  const menu = open && menuStyle && (
    <ul ref={menuRef} id={listId} className="field-select-menu" style={menuStyle} role="listbox">
      {options.length === 0 ? (
        <li className="field-select-status" role="presentation">
          No options available
        </li>
      ) : (
        options.map((opt, index) => (
          <li key={opt.value} role="presentation">
            <button
              type="button"
              className={`field-select-item ${highlight === index ? 'highlighted' : ''} ${value === opt.value ? 'selected' : ''}`}
              role="option"
              aria-selected={value === opt.value}
              onMouseEnter={() => setHighlight(index)}
              onMouseDown={(event) => event.preventDefault()}
              onClick={() => selectOption(opt)}
            >
              <span className="field-select-label">{opt.label}</span>
              {opt.subtitle && <span className="field-select-sub">{opt.subtitle}</span>}
            </button>
          </li>
        ))
      )}
    </ul>
  )

  return (
    <div className={`field-select ${open ? 'is-open' : ''} ${disabled ? 'disabled' : ''} ${className}`.trim()} ref={rootRef}>
      <div className={`field-control-wrap ${hasValue ? 'filled' : ''}`}>
        {icon}
        <button
          ref={triggerRef}
          type="button"
          className={`field-select-trigger ${hasValue ? 'filled' : ''}`}
          onClick={() => (open ? closeMenu() : openMenu())}
          onKeyDown={onKeyDown}
          disabled={disabled}
          aria-label={ariaLabel}
          aria-haspopup="listbox"
          aria-expanded={open}
          aria-controls={open ? listId : undefined}
        >
          <span className="field-select-value">{selected?.label || placeholder}</span>
        </button>
        {CHEVRON_ICON}
      </div>

      {menu && createPortal(menu, document.body)}
    </div>
  )
}
