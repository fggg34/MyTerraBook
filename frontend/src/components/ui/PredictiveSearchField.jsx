import { useEffect, useId, useMemo, useRef, useState } from 'react'
import { createPortal } from 'react-dom'
import useFixedMenuPosition from '../../hooks/useFixedMenuPosition'
import useSearchSuggestions from '../../hooks/useSearchSuggestions'

export default function PredictiveSearchField({
  scope,
  role,
  pickupLocationId,
  value,
  displayValue,
  onChange,
  placeholder,
  icon,
  ariaLabel,
  className = '',
  limit = 8,
  enabled = true,
  allowFreeText = false,
  menuMode = 'fixed',
  openOnFocus = true,
  maxMenuHeight = 260,
  closeOnViewportResize = false,
  suggestionTypes = null,
  onFocus,
  onActivate,
}) {
  const listId = useId()
  const rootRef = useRef(null)
  const inputRef = useRef(null)
  const menuRef = useRef(null)
  const [pendingFocus, setPendingFocus] = useState(false)
  const [open, setOpen] = useState(false)
  const [query, setQuery] = useState(displayValue || '')
  const [highlight, setHighlight] = useState(-1)

  const { suggestions, loading } = useSearchSuggestions({
    scope,
    query: open ? query : '',
    role,
    pickupLocationId,
    limit,
    enabled: enabled && open && (openOnFocus || !!query.trim()),
  })

  const isInlineMenu = menuMode === 'inline'

  const visibleSuggestions = useMemo(() => {
    if (!suggestionTypes?.length) return suggestions
    return suggestions.filter((item) => suggestionTypes.includes(item.type))
  }, [suggestions, suggestionTypes])

  const menuStyle = useFixedMenuPosition(inputRef, open && !isInlineMenu, {
    maxHeight: maxMenuHeight,
    deps: [visibleSuggestions.length, query],
  })

  useEffect(() => {
    setQuery(displayValue || '')
  }, [displayValue])

  useEffect(() => {
    if (!pendingFocus) return undefined
    setPendingFocus(false)
    const id = window.requestAnimationFrame(() => {
      inputRef.current?.focus()
    })
    return () => window.cancelAnimationFrame(id)
  }, [pendingFocus])

  useEffect(() => {
    if (!open || !closeOnViewportResize) return undefined

    const closeMenu = () => {
      setOpen(false)
      setHighlight(-1)
    }

    window.visualViewport?.addEventListener('resize', closeMenu)
    return () => window.visualViewport?.removeEventListener('resize', closeMenu)
  }, [open, closeOnViewportResize])

  useEffect(() => {
    if (!open) return undefined
    const onPointerDown = (event) => {
      const inRoot = rootRef.current?.contains(event.target)
      const inMenu = menuRef.current?.contains(event.target)
      if (!inRoot && !inMenu) {
        setOpen(false)
        setHighlight(-1)
        setQuery(displayValue || '')
      }
    }
    document.addEventListener('mousedown', onPointerDown)
    return () => document.removeEventListener('mousedown', onPointerDown)
  }, [open, displayValue])

  const selectSuggestion = (item) => {
    onChange?.({
      value: item.value,
      label: item.label,
      type: item.type,
      item,
    })
    setQuery(item.label)
    setOpen(false)
    setHighlight(-1)
  }

  const onInputChange = (event) => {
    const next = event.target.value
    setQuery(next)
    if (next.trim() || openOnFocus) {
      setOpen(true)
    } else {
      setOpen(false)
    }
    setHighlight(-1)
    if (allowFreeText) {
      onChange?.({
        value: next,
        label: next,
        type: next.trim() ? 'text' : null,
        item: null,
      })
    } else if (!next.trim()) {
      onChange?.({ value: '', label: '', type: null, item: null })
    }
  }

  const onKeyDown = (event) => {
    if (!open && (event.key === 'ArrowDown' || event.key === 'ArrowUp')) {
      setOpen(true)
      return
    }
    if (event.key === 'Escape') {
      setOpen(false)
      setHighlight(-1)
      setQuery(displayValue || '')
      return
    }
    if (!visibleSuggestions.length) return
    if (event.key === 'ArrowDown') {
      event.preventDefault()
      setHighlight((prev) => (prev + 1) % visibleSuggestions.length)
    } else if (event.key === 'ArrowUp') {
      event.preventDefault()
      setHighlight((prev) => (prev <= 0 ? visibleSuggestions.length - 1 : prev - 1))
    } else if (event.key === 'Enter' && highlight >= 0) {
      event.preventDefault()
      selectSuggestion(visibleSuggestions[highlight])
    }
  }

  const handleActivate = (event) => {
    const shouldDeferFocus = onActivate?.(event) === true
    if (shouldDeferFocus) {
      setPendingFocus(true)
      event.preventDefault()
    }
  }

  const handleFocus = () => {
    onFocus?.()
    if (openOnFocus) setOpen(true)
  }

  const showMenu = open && (
    loading
    || visibleSuggestions.length > 0
    || query.trim()
  )

  const menuContent = (
    <>
      {loading && !visibleSuggestions.length && (
        <li className="predictive-search-status" role="presentation">
          Searching…
        </li>
      )}
      {!loading && !visibleSuggestions.length && query.trim() && (
        <li className="predictive-search-status" role="presentation">
          No matches found
        </li>
      )}
      {visibleSuggestions.map((item, index) => (
        <li key={item.id} role="presentation">
          <button
            type="button"
            className={`predictive-search-item ${highlight === index ? 'highlighted' : ''}`}
            role="option"
            aria-selected={value === item.value && displayValue === item.label}
            onMouseEnter={() => setHighlight(index)}
            onMouseDown={(event) => event.preventDefault()}
            onClick={() => selectSuggestion(item)}
          >
            <span className="predictive-search-label">{item.label}</span>
            {item.subtitle && <span className="predictive-search-sub">{item.subtitle}</span>}
          </button>
        </li>
      ))}
    </>
  )

  const menu = showMenu && (isInlineMenu || menuStyle) && (
    <ul
      ref={menuRef}
      id={listId}
      className={`predictive-search-menu${isInlineMenu ? ' predictive-search-menu--inline' : ''}`}
      style={isInlineMenu
        ? { maxHeight: maxMenuHeight }
        : menuStyle}
      role="listbox"
    >
      {menuContent}
    </ul>
  )

  return (
    <div
      className={`predictive-search${isInlineMenu ? ' predictive-search--inline' : ''}${showMenu ? ' is-open' : ''} ${className}`.trim()}
      ref={rootRef}
    >
      <div className="field-control-wrap">
        {icon}
        <input
          ref={inputRef}
          type="text"
          className="field-control"
          value={query}
          placeholder={placeholder}
          onChange={onInputChange}
          onPointerDown={handleActivate}
          onFocus={handleFocus}
          onKeyDown={onKeyDown}
          aria-label={ariaLabel}
          aria-expanded={showMenu}
          aria-controls={showMenu ? listId : undefined}
          aria-autocomplete="list"
          role="combobox"
          autoComplete="off"
        />
      </div>

      {menu && (isInlineMenu ? menu : createPortal(menu, document.body))}
    </div>
  )
}
