import { useEffect, useMemo, useRef, useState } from 'react'
import { Search, Settings, X } from 'lucide-react'
import CatalogIcon from '../../utils/CatalogIcon'

function defaultAmountFor(option) {
  return option.cost_euros ?? (option.cost_cents ?? option.default_cost_cents ?? 0) / 100
}

export default function HostCarExtrasPanel({
  options = [],
  enabledOptions = [],
  onChange,
  currency,
  compact = true,
}) {
  const [query, setQuery] = useState('')
  const [editingId, setEditingId] = useState(null)
  const [pendingNewId, setPendingNewId] = useState(null)
  const [draftPrice, setDraftPrice] = useState('')
  const priceInputRef = useRef(null)

  const enabledById = useMemo(
    () => Object.fromEntries(enabledOptions.map((row) => [String(row.id), row])),
    [enabledOptions],
  )

  const filtered = useMemo(() => {
    const q = query.trim().toLowerCase()
    if (!q) return options
    return options.filter((option) => {
      const haystack = `${option.name || ''} ${option.description || ''}`.toLowerCase()
      return haystack.includes(q)
    })
  }, [options, query])

  useEffect(() => {
    if (editingId == null) return undefined
    const id = window.requestAnimationFrame(() => priceInputRef.current?.focus())
    return () => window.cancelAnimationFrame(id)
  }, [editingId])

  const startEditing = (option, amount) => {
    setEditingId(option.id)
    setDraftPrice(amount === '' || amount == null ? '' : String(amount))
  }

  const cancelEditing = (optionId) => {
    if (String(pendingNewId) === String(optionId)) {
      setPendingNewId(null)
    }
    setEditingId(null)
    setDraftPrice('')
  }

  const enableOption = (option) => {
    const amount = defaultAmountFor(option)
    setPendingNewId(option.id)
    startEditing(option, amount)
  }

  const removeOption = (optionId) => {
    onChange(enabledOptions.filter((row) => String(row.id) !== String(optionId)))
    cancelEditing(optionId)
  }

  const saveEdit = (optionId) => {
    const parsed = Number(draftPrice)
    if (draftPrice === '' || Number.isNaN(parsed) || parsed < 0) return

    const exists = Boolean(enabledById[String(optionId)])
    if (exists) {
      onChange(
        enabledOptions.map((row) => (
          String(row.id) === String(optionId) ? { ...row, cost_euros: parsed } : row
        )),
      )
    } else {
      onChange([...enabledOptions, { id: optionId, cost_euros: parsed }])
    }

    setPendingNewId(null)
    setEditingId(null)
    setDraftPrice('')
  }

  const enabledCount = enabledOptions.length
  const gridClass = compact ? 'host-extras-panel__grid host-extras-panel__grid--compact' : 'host-extras-panel__grid'

  return (
    <div className="host-extras-panel">
      <div className="host-icon-select__search">
        <Search size={16} aria-hidden />
        <input
          type="text"
          value={query}
          onChange={(e) => setQuery(e.target.value)}
          placeholder="Search extras…"
          aria-label="Search optional extras"
        />
        {enabledCount > 0 && (
          <span className="host-icon-select__count">{enabledCount} offered</span>
        )}
      </div>

      {filtered.length === 0 ? (
        <p className="host-icon-select__empty">No extras match your search.</p>
      ) : (
        <div className={gridClass}>
          {filtered.map((option) => {
            const saved = enabledById[String(option.id)]
            const isPendingNew = String(pendingNewId) === String(option.id)
            const isActive = Boolean(saved) || isPendingNew
            const isEditing = isActive && String(editingId) === String(option.id)
            const suggested = defaultAmountFor(option)
            const suffix = option.is_daily_cost ? '/ day' : 'flat'
            const savedAmount = Number(saved?.cost_euros ?? 0)

            if (!isActive) {
              return (
                <button
                  key={option.id}
                  type="button"
                  className="host-extras-card host-extras-card--pick"
                  title={option.description || option.name}
                  onClick={() => enableOption(option)}
                >
                  <span className="host-extras-card__icon">
                    <CatalogIcon
                      name={option.icon}
                      iconUrl={option.icon_url}
                      size={16}
                      imgClassName="host-icon-option__img"
                    />
                  </span>
                  <span className="host-extras-card__name">{option.name}</span>
                </button>
              )
            }

            if (isEditing) {
              return (
                <div key={option.id} className="host-extras-card is-enabled is-editing">
                  <div className="host-extras-card__edit">
                    <span className="host-extras-card__icon">
                      <CatalogIcon
                        name={option.icon}
                        iconUrl={option.icon_url}
                        size={16}
                        imgClassName="host-icon-option__img"
                      />
                    </span>
                    <div className="host-extras-card__price">
                      <span className="host-extras-card__price-prefix">{currency.inputPrefix}</span>
                      <input
                        ref={priceInputRef}
                        id={`extra-price-${option.id}`}
                        type="number"
                        min={0}
                        step="0.01"
                        aria-label={`Price for ${option.name}`}
                        placeholder={suggested > 0 ? String(suggested) : '0'}
                        value={draftPrice}
                        onChange={(e) => setDraftPrice(e.target.value)}
                        onKeyDown={(e) => {
                          if (e.key === 'Enter') saveEdit(option.id)
                          if (e.key === 'Escape') {
                            if (isPendingNew) {
                              cancelEditing(option.id)
                            } else {
                              cancelEditing(option.id)
                            }
                          }
                        }}
                      />
                      <span className="host-extras-card__price-suffix">{suffix}</span>
                    </div>
                    <button
                      type="button"
                      className="host-extras-card__save"
                      onClick={() => saveEdit(option.id)}
                    >
                      Save
                    </button>
                  </div>
                </div>
              )
            }

            return (
              <div key={option.id} className="host-extras-card is-enabled">
                <div className="host-extras-card__saved">
                  <span className="host-extras-card__icon">
                    <CatalogIcon
                      name={option.icon}
                      iconUrl={option.icon_url}
                      size={16}
                      imgClassName="host-icon-option__img"
                    />
                  </span>
                  <div className="host-extras-card__saved-text">
                    <span className="host-extras-card__name">{option.name}</span>
                    <span className="host-extras-card__saved-price">
                      {currency.formatAmount(savedAmount)} {suffix}
                    </span>
                  </div>
                  <button
                    type="button"
                    className="host-extras-card__action"
                    aria-label={`Edit price for ${option.name}`}
                    onClick={() => startEditing(option, saved?.cost_euros ?? suggested)}
                  >
                    <Settings size={15} strokeWidth={2} />
                  </button>
                  <button
                    type="button"
                    className="host-extras-card__action host-extras-card__action--remove"
                    aria-label={`Remove ${option.name}`}
                    onClick={() => removeOption(option.id)}
                  >
                    <X size={15} strokeWidth={2.2} />
                  </button>
                </div>
              </div>
            )
          })}
        </div>
      )}
    </div>
  )
}
