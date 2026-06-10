import { useCallback, useEffect, useId, useMemo, useRef, useState } from 'react'
import { createPortal } from 'react-dom'

const CHEVRON = (
  <svg className="host-multi-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round" aria-hidden>
    <path d="m6 9 6 6 6-6" />
  </svg>
)

export default function HostMultiSelect({
  value = [],
  onChange,
  options = [],
  placeholder = 'Select options',
  disabled = false,
  searchable = true,
  ariaLabel,
}) {
  const listId = useId()
  const rootRef = useRef(null)
  const triggerRef = useRef(null)
  const menuRef = useRef(null)
  const searchRef = useRef(null)
  const [open, setOpen] = useState(false)
  const [query, setQuery] = useState('')
  const [highlight, setHighlight] = useState(0)
  const [menuStyle, setMenuStyle] = useState(null)

  const selectedSet = useMemo(() => new Set(value.map(String)), [value])

  const selectedOptions = useMemo(
    () => options.filter((opt) => selectedSet.has(String(opt.value))),
    [options, selectedSet],
  )

  const filteredOptions = useMemo(() => {
    const needle = query.trim().toLowerCase()
    if (!needle) return options
    return options.filter((opt) => {
      const label = String(opt.label || '').toLowerCase()
      const subtitle = String(opt.subtitle || '').toLowerCase()
      return label.includes(needle) || subtitle.includes(needle)
    })
  }, [options, query])

  const updateMenuPosition = useCallback(() => {
    const el = triggerRef.current
    if (!el) return
    const rect = el.getBoundingClientRect()
    setMenuStyle({
      position: 'fixed',
      top: rect.bottom + 6,
      left: rect.left,
      width: Math.max(rect.width, 280),
      zIndex: 1200,
    })
  }, [])

  const closeMenu = useCallback(() => {
    setOpen(false)
    setQuery('')
    setHighlight(0)
  }, [])

  const openMenu = useCallback(() => {
    if (disabled) return
    updateMenuPosition()
    setOpen(true)
    setHighlight(0)
    requestAnimationFrame(() => searchRef.current?.focus())
  }, [disabled, updateMenuPosition])

  const toggleValue = (optValue) => {
    const key = String(optValue)
    const next = selectedSet.has(key)
      ? value.filter((v) => String(v) !== key)
      : [...value, optValue]
    onChange?.(next)
  }

  const removeValue = (optValue, event) => {
    event?.stopPropagation()
    onChange?.(value.filter((v) => String(v) !== String(optValue)))
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
  }, [open, updateMenuPosition, filteredOptions.length])

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

  useEffect(() => {
    if (highlight >= filteredOptions.length) {
      setHighlight(filteredOptions.length ? filteredOptions.length - 1 : 0)
    }
  }, [filteredOptions.length, highlight])

  const onKeyDown = (event) => {
    if (disabled) return
    if (!open && (event.key === 'ArrowDown' || event.key === 'Enter' || event.key === ' ')) {
      event.preventDefault()
      openMenu()
      return
    }
    if (event.key === 'Escape') {
      closeMenu()
      return
    }
    if (!open || !filteredOptions.length) return
    if (event.key === 'ArrowDown') {
      event.preventDefault()
      setHighlight((prev) => (prev + 1) % filteredOptions.length)
    } else if (event.key === 'ArrowUp') {
      event.preventDefault()
      setHighlight((prev) => (prev <= 0 ? filteredOptions.length - 1 : prev - 1))
    } else if (event.key === 'Enter' && highlight >= 0) {
      event.preventDefault()
      toggleValue(filteredOptions[highlight].value)
    }
  }

  const menu = open && menuStyle && (
    <div ref={menuRef} className="host-multi-menu" style={menuStyle}>
      {searchable && (
        <div className="host-multi-search">
          <input
            ref={searchRef}
            type="search"
            value={query}
            placeholder="Search…"
            onChange={(e) => {
              setQuery(e.target.value)
              setHighlight(0)
            }}
            onKeyDown={onKeyDown}
          />
        </div>
      )}
      <ul id={listId} className="host-multi-options" role="listbox" aria-multiselectable="true">
        {filteredOptions.length === 0 ? (
          <li className="host-multi-status" role="presentation">No matches</li>
        ) : (
          filteredOptions.map((opt, index) => {
            const checked = selectedSet.has(String(opt.value))
            return (
              <li key={opt.value} role="presentation">
                <button
                  type="button"
                  className={`host-multi-option ${highlight === index ? 'highlighted' : ''} ${checked ? 'selected' : ''}`}
                  role="option"
                  aria-selected={checked}
                  onMouseEnter={() => setHighlight(index)}
                  onMouseDown={(e) => e.preventDefault()}
                  onClick={() => toggleValue(opt.value)}
                >
                  <span className={`host-multi-check ${checked ? 'on' : ''}`} aria-hidden />
                  <span className="host-multi-option-text">
                    <span className="host-multi-option-label">{opt.label}</span>
                    {opt.subtitle && <span className="host-multi-option-sub">{opt.subtitle}</span>}
                  </span>
                </button>
              </li>
            )
          })
        )}
      </ul>
    </div>
  )

  return (
    <div className={`host-multi-select ${open ? 'is-open' : ''} ${disabled ? 'disabled' : ''}`} ref={rootRef}>
      <button
        ref={triggerRef}
        type="button"
        className={`host-multi-trigger ${selectedOptions.length ? 'filled' : ''}`}
        onClick={() => (open ? closeMenu() : openMenu())}
        onKeyDown={onKeyDown}
        disabled={disabled}
        aria-label={ariaLabel}
        aria-haspopup="listbox"
        aria-expanded={open}
        aria-controls={open ? listId : undefined}
      >
        <span className="host-multi-tags">
          {selectedOptions.length === 0 ? (
            <span className="host-multi-placeholder">{placeholder}</span>
          ) : (
            selectedOptions.map((opt) => (
              <span key={opt.value} className="host-multi-tag">
                {opt.label}
                <button
                  type="button"
                  className="host-multi-tag-remove"
                  aria-label={`Remove ${opt.label}`}
                  onClick={(e) => removeValue(opt.value, e)}
                >
                  ×
                </button>
              </span>
            ))
          )}
        </span>
        {CHEVRON}
      </button>
      {menu && createPortal(menu, document.body)}
    </div>
  )
}
