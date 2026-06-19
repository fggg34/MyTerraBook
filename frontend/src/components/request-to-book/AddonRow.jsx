import { useEffect, useRef, useState } from 'react'
import {
  Armchair,
  BedDouble,
  ChefHat,
  Info,
  Sparkles,
  Wifi,
} from 'lucide-react'
import { formatCurrencyFromCents } from '../../utils/format'
import { calculateRentalOptionTotalCents } from '../../utils/rentalOptionPricing'

function addonIcon(name = '') {
  const n = name.toLowerCase()
  if (n.includes('chair') || n.includes('table') || n.includes('camping')) return Armchair
  if (n.includes('bed') || n.includes('bedding') || n.includes('linen')) return BedDouble
  if (n.includes('kitchen') || n.includes('gps')) return ChefHat
  if (n.includes('wifi') || n.includes('wi-fi') || n.includes('hotspot')) return Wifi
  if (n.includes('clean')) return Sparkles
  return Armchair
}

export default function AddonRow({ option, selected, nights, onToggle }) {
  const [infoOpen, setInfoOpen] = useState(false)
  const dialogRef = useRef(null)
  const unitCents = option.cost_cents || 0
  const totalCents = calculateRentalOptionTotalCents(unitCents, option.is_daily_cost, nights)
  const price = formatCurrencyFromCents(totalCents)
  const unitLabel = option.is_daily_cost ? `for trip` : 'per trip'
  const Icon = addonIcon(option.name)
  const hasInfo = Boolean(option.description) || (option.is_daily_cost && unitCents > 0)

  useEffect(() => {
    const dialog = dialogRef.current
    if (!dialog) return undefined

    if (infoOpen) {
      if (!dialog.open) dialog.showModal()
    } else if (dialog.open) {
      dialog.close()
    }

    return undefined
  }, [infoOpen])

  const handleToggle = () => onToggle(option.id)

  const handleKeyDown = (e) => {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault()
      handleToggle()
    }
  }

  const openInfo = (e) => {
    e.stopPropagation()
    setInfoOpen(true)
  }

  const closeInfo = () => setInfoOpen(false)

  return (
    <>
      <div
        className={`addon-row${selected ? ' on' : ''}`}
        role="button"
        tabIndex={0}
        aria-pressed={selected}
        onClick={handleToggle}
        onKeyDown={handleKeyDown}
      >
        <span className="aic"><Icon aria-hidden /></span>
        <span className="atx">
          <span className="an">{option.name}</span>
        </span>
        {hasInfo ? (
          <button
            type="button"
            className="addon-info-btn"
            aria-label={`More about ${option.name}`}
            onClick={openInfo}
          >
            <Info aria-hidden strokeWidth={2.2} />
          </button>
        ) : null}
        <span className="apr">
          {price}
          <small>{unitLabel}</small>
        </span>
        <button
          type="button"
          className="add-toggle"
          onClick={(e) => {
            e.stopPropagation()
            handleToggle()
          }}
        >
          {selected ? 'Added ✓' : 'Add'}
        </button>
      </div>

      {hasInfo ? (
        <dialog
          ref={dialogRef}
          className="addon-info-dialog"
          aria-labelledby={`addon-info-title-${option.id}`}
          onCancel={closeInfo}
          onClick={(e) => {
            if (e.target === dialogRef.current) closeInfo()
          }}
        >
          <div className="addon-info-dialog__panel">
            <div className="addon-info-dialog__head">
              <h4 id={`addon-info-title-${option.id}`}>{option.name}</h4>
              <button type="button" className="addon-info-dialog__close" aria-label="Close" onClick={closeInfo}>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round" aria-hidden>
                  <path d="M6 6l12 12M18 6 6 18" />
                </svg>
              </button>
            </div>
            {option.description ? <p className="addon-info-dialog__text">{option.description}</p> : null}
            {option.is_daily_cost && unitCents > 0 ? (
              <p className="addon-info-dialog__price">
                {formatCurrencyFromCents(unitCents)} / day × {nights} {nights === 1 ? 'night' : 'nights'}
              </p>
            ) : null}
          </div>
        </dialog>
      ) : null}
    </>
  )
}
