import { useEffect, useMemo, useRef, useState } from 'react'
import { Search, Settings, X } from 'lucide-react'
import CatalogIcon from '../../utils/CatalogIcon'
import {
  defaultRentalOptionAmountEuros,
  guestRentalOptionSubLabel,
  resolveRentalOptionIsDailyCost,
} from '../../utils/rentalOptionPricing'

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
  const [draftIsDailyCost, setDraftIsDailyCost] = useState(false)
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

  const startEditing = (option, amount, isDailyCost) => {
    setEditingId(option.id)
    setDraftPrice(amount === '' || amount == null ? '' : String(amount))
    setDraftIsDailyCost(!!isDailyCost)
  }

  const cancelEditing = (optionId) => {
    if (String(pendingNewId) === String(optionId)) {
      setPendingNewId(null)
    }
    setEditingId(null)
    setDraftPrice('')
    setDraftIsDailyCost(false)
  }

  const enableOption = (option) => {
    const amount = defaultRentalOptionAmountEuros(option)
    setPendingNewId(option.id)
    startEditing(option, amount, resolveRentalOptionIsDailyCost(null, option))
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
          String(row.id) === String(optionId)
            ? { ...row, cost_euros: parsed, is_daily_cost: draftIsDailyCost }
            : row
        )),
      )
    } else {
      onChange([...enabledOptions, {
        id: optionId,
        cost_euros: parsed,
        is_daily_cost: draftIsDailyCost,
      }])
    }

    setPendingNewId(null)
    setEditingId(null)
    setDraftPrice('')
    setDraftIsDailyCost(false)
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
            const suggested = defaultRentalOptionAmountEuros(option)
            const isDailyCost = resolveRentalOptionIsDailyCost(saved, option)
            const chargeLabel = guestRentalOptionSubLabel(isDailyCost)
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
                    <div className="host-extras-card__edit-header">
                      <span className="host-extras-card__icon">
                        <CatalogIcon
                          name={option.icon}
                          iconUrl={option.icon_url}
                          size={16}
                          imgClassName="host-icon-option__img"
                        />
                      </span>
                      <span className="host-extras-card__name">{option.name}</span>
                      <button
                        type="button"
                        className="host-extras-card__action host-extras-card__action--remove"
                        aria-label={`Cancel editing ${option.name}`}
                        onClick={() => cancelEditing(option.id)}
                      >
                        <X size={15} strokeWidth={2.2} />
                      </button>
                    </div>

                    <div className="host-extras-card__edit-fields">
                      <label
                        className="host-extras-card__field"
                        htmlFor={`extra-price-${option.id}`}
                      >
                        <span className="host-extras-card__field-label">Price</span>
                        <div className="host-extras-card__price">
                          <span className="host-extras-card__price-prefix">{currency.inputPrefix}</span>
                          <input
                            ref={priceInputRef}
                            id={`extra-price-${option.id}`}
                            type="number"
                            min={0}
                            step="0.01"
                            placeholder={suggested > 0 ? String(suggested) : '0'}
                            value={draftPrice}
                            onChange={(e) => setDraftPrice(e.target.value)}
                            onKeyDown={(e) => {
                              if (e.key === 'Enter') saveEdit(option.id)
                              if (e.key === 'Escape') cancelEditing(option.id)
                            }}
                          />
                        </div>
                      </label>

                      <div className="host-extras-card__field">
                        <span className="host-extras-card__field-label" id={`extra-charge-${option.id}`}>
                          Charge
                        </span>
                        <div
                          className="host-extras-card__pricing-type"
                          role="group"
                          aria-labelledby={`extra-charge-${option.id}`}
                        >
                          <button
                            type="button"
                            className={!draftIsDailyCost ? 'is-active' : ''}
                            aria-pressed={!draftIsDailyCost}
                            onClick={() => setDraftIsDailyCost(false)}
                          >
                            {guestRentalOptionSubLabel(false)}
                          </button>
                          <button
                            type="button"
                            className={draftIsDailyCost ? 'is-active' : ''}
                            aria-pressed={draftIsDailyCost}
                            onClick={() => setDraftIsDailyCost(true)}
                          >
                            {guestRentalOptionSubLabel(true)}
                          </button>
                        </div>
                      </div>
                    </div>

                    <button
                      type="button"
                      className="host-extras-card__save"
                      onClick={() => saveEdit(option.id)}
                    >
                      Save price
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
                      {currency.formatAmount(savedAmount)} · {chargeLabel.toLowerCase()}
                    </span>
                  </div>
                  <button
                    type="button"
                    className="host-extras-card__action"
                    aria-label={`Edit price for ${option.name}`}
                    onClick={() => startEditing(
                      option,
                      saved?.cost_euros ?? suggested,
                      resolveRentalOptionIsDailyCost(saved, option),
                    )}
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
