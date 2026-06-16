import { useMemo, useState } from 'react'
import { Check, Search } from 'lucide-react'
import LucideIcon from '../../utils/iconCatalog'

/**
 * Searchable, icon-rich multi-select for picking catalog items (characteristics,
 * extras, amenities). Each option shows its Lucide icon and name; clicking
 * toggles selection.
 */
export default function HostIconMultiSelect({
  items = [],
  selectedIds = [],
  onToggle,
  placeholder = 'Search…',
  emptyLabel = 'No matches found.',
}) {
  const [query, setQuery] = useState('')

  const isSelected = (id) => selectedIds.some((x) => String(x) === String(id))

  const filtered = useMemo(() => {
    const q = query.trim().toLowerCase()
    if (!q) return items
    return items.filter((item) => {
      const haystack = `${item.name || ''} ${item.group || ''} ${item.icon || ''}`.toLowerCase()
      return haystack.includes(q)
    })
  }, [items, query])

  const selectedCount = selectedIds.length

  return (
    <div className="host-icon-select">
      <div className="host-icon-select__search">
        <Search size={16} aria-hidden />
        <input
          type="text"
          value={query}
          onChange={(e) => setQuery(e.target.value)}
          placeholder={placeholder}
          aria-label={placeholder}
        />
        {selectedCount > 0 && (
          <span className="host-icon-select__count">{selectedCount} selected</span>
        )}
      </div>

      <div className="host-icon-select__list" role="listbox" aria-multiselectable="true">
        {filtered.length === 0 ? (
          <p className="host-icon-select__empty">{emptyLabel}</p>
        ) : (
          filtered.map((item) => {
            const selected = isSelected(item.id)
            return (
              <button
                key={item.id}
                type="button"
                role="option"
                aria-selected={selected}
                className={`host-icon-option${selected ? ' is-selected' : ''}`}
                onClick={() => onToggle?.(item.id)}
              >
                <span className="host-icon-option__icon" aria-hidden>
                  {item.icon_url ? (
                    <img src={item.icon_url} alt="" className="host-icon-option__img" />
                  ) : (
                    <LucideIcon name={item.icon} size={18} strokeWidth={1.8} />
                  )}
                </span>
                <span className="host-icon-option__name">{item.name}</span>
                <span className="host-icon-option__check" aria-hidden>
                  {selected && <Check size={15} strokeWidth={2.6} />}
                </span>
              </button>
            )
          })
        )}
      </div>
    </div>
  )
}
