import { useEffect, useRef, useState } from 'react'
import { CURRENCIES } from '../../data/localePreferences'
import { useLocalePreferences } from '../../context/LocalePreferencesContext'

function CurrencyPanel({ currency, onSelectCurrency }) {
  return (
    <div className="lc-section">
      <h5>Currency</h5>
      <div className="lc-opts">
        {CURRENCIES.map((item) => (
          <button
            key={item.code}
            type="button"
            className={`lc-opt ${currency.code === item.code ? 'sel' : ''}`}
            onClick={() => onSelectCurrency(item)}
          >
            {item.label}
          </button>
        ))}
      </div>
    </div>
  )
}

export default function LangCurrencyMenu({ variant = 'header' }) {
  const rootRef = useRef(null)
  const [open, setOpen] = useState(false)
  const { currency, setCurrency } = useLocalePreferences()

  useEffect(() => {
    if (!open) return undefined
    const onPointerDown = (event) => {
      if (!rootRef.current?.contains(event.target)) {
        setOpen(false)
      }
    }
    document.addEventListener('mousedown', onPointerDown)
    return () => document.removeEventListener('mousedown', onPointerDown)
  }, [open])

  useEffect(() => {
    const onKeyDown = (event) => {
      if (event.key === 'Escape') setOpen(false)
    }
    if (open) document.addEventListener('keydown', onKeyDown)
    return () => document.removeEventListener('keydown', onKeyDown)
  }, [open])

  const wrapClass = variant === 'footer' ? 'lang-cur-wrap lang-cur-wrap--footer' : 'lang-cur-wrap'

  return (
    <div className={wrapClass} ref={rootRef}>
      <button
        className={`lang-cur ${open ? 'open' : ''}`}
        type="button"
        aria-label="Currency"
        aria-haspopup="dialog"
        aria-expanded={open}
        onClick={() => setOpen((value) => !value)}
      >
        <span>{currency.label}</span>
      </button>

      <div className={`lang-cur-panel ${open ? 'show' : ''}`} role="dialog" aria-label="Currency">
        <CurrencyPanel currency={currency} onSelectCurrency={setCurrency} />
      </div>
    </div>
  )
}
