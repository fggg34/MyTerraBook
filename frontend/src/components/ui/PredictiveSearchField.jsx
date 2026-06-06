import { useCallback, useEffect, useId, useRef, useState } from 'react'
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
}) {
  const listId = useId()
  const rootRef = useRef(null)
  const inputRef = useRef(null)
  const [open, setOpen] = useState(false)
  const [query, setQuery] = useState(displayValue || '')
  const [highlight, setHighlight] = useState(-1)
  const [menuStyle, setMenuStyle] = useState(null)

  const { suggestions, loading } = useSearchSuggestions({
    scope,
    query: open ? query : '',
    role,
    pickupLocationId,
    limit,
    enabled: enabled && open,
  })

  useEffect(() => {
    setQuery(displayValue || '')
  }, [displayValue])

  const updateMenuPosition = useCallback(() => {
    const el = inputRef.current
    if (!el) return
    const rect = el.getBoundingClientRect()
    setMenuStyle({
      position: 'fixed',
      top: rect.bottom + 6,
      left: rect.left,
      width: rect.width,
      zIndex: 1200,
    })
  }, [])

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
  }, [open, updateMenuPosition, suggestions.length])

  useEffect(() => {
    if (!open) return undefined
    const onPointerDown = (event) => {
      if (!rootRef.current?.contains(event.target)) {
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
    setOpen(true)
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
    if (!suggestions.length) return
    if (event.key === 'ArrowDown') {
      event.preventDefault()
      setHighlight((prev) => (prev + 1) % suggestions.length)
    } else if (event.key === 'ArrowUp') {
      event.preventDefault()
      setHighlight((prev) => (prev <= 0 ? suggestions.length - 1 : prev - 1))
    } else if (event.key === 'Enter' && highlight >= 0) {
      event.preventDefault()
      selectSuggestion(suggestions[highlight])
    }
  }

  const showMenu = open && (loading || suggestions.length > 0 || query.trim())

  return (
    <div className={`predictive-search ${className}`} ref={rootRef}>
      <div className="field-control-wrap">
        {icon}
        <input
          ref={inputRef}
          type="text"
          className="field-control"
          value={query}
          placeholder={placeholder}
          onChange={onInputChange}
          onFocus={() => {
            setOpen(true)
            updateMenuPosition()
          }}
          onKeyDown={onKeyDown}
          aria-label={ariaLabel}
          aria-expanded={showMenu}
          aria-controls={showMenu ? listId : undefined}
          aria-autocomplete="list"
          role="combobox"
          autoComplete="off"
        />
      </div>

      {showMenu && menuStyle && (
        <ul
          id={listId}
          className="predictive-search-menu"
          style={menuStyle}
          role="listbox"
        >
          {loading && !suggestions.length && (
            <li className="predictive-search-status" role="presentation">
              Searching…
            </li>
          )}
          {!loading && !suggestions.length && query.trim() && (
            <li className="predictive-search-status" role="presentation">
              No matches found
            </li>
          )}
          {suggestions.map((item, index) => (
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
        </ul>
      )}
    </div>
  )
}
