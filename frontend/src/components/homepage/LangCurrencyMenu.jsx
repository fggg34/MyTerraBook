import { useEffect, useRef, useState } from 'react'

const LANGUAGES = [
  { code: 'en', short: 'EN', label: 'English (UK)' },
  { code: 'is', short: 'IS', label: 'Íslenska' },
  { code: 'de', short: 'DE', label: 'Deutsch' },
  { code: 'fr', short: 'FR', label: 'Français' },
]

const CURRENCIES = [
  { code: 'EUR', label: '€ EUR' },
  { code: 'USD', label: '$ USD' },
  { code: 'GBP', label: '£ GBP' },
  { code: 'ISK', label: 'kr ISK' },
]

const LANG_KEY = 'terrabook_lang'
const CUR_KEY = 'terrabook_currency'

function findLanguage(langLabel) {
  const match = LANGUAGES.find((item) => item.short === langLabel || item.code === langLabel)
  if (match) return match
  try {
    const stored = localStorage.getItem(LANG_KEY)
    const storedMatch = LANGUAGES.find((item) => item.code === stored || item.short === stored)
    if (storedMatch) return storedMatch
  } catch {
    // ignore storage errors
  }
  return LANGUAGES[0]
}

function findCurrency(currencyLabel) {
  const match = CURRENCIES.find((item) => item.label === currencyLabel || item.code === currencyLabel)
  if (match) return match
  try {
    const stored = localStorage.getItem(CUR_KEY)
    const storedMatch = CURRENCIES.find((item) => item.code === stored || item.label === stored)
    if (storedMatch) return storedMatch
  } catch {
    // ignore storage errors
  }
  return CURRENCIES[0]
}

export default function LangCurrencyMenu({ langLabel = 'EN', currencyLabel = '€ EUR' }) {
  const rootRef = useRef(null)
  const [open, setOpen] = useState(false)
  const [language, setLanguage] = useState(() => findLanguage(langLabel))
  const [currency, setCurrency] = useState(() => findCurrency(currencyLabel))

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

  const selectLanguage = (item) => {
    setLanguage(item)
    try {
      localStorage.setItem(LANG_KEY, item.code)
    } catch {
      // ignore storage errors
    }
  }

  const selectCurrency = (item) => {
    setCurrency(item)
    try {
      localStorage.setItem(CUR_KEY, item.code)
    } catch {
      // ignore storage errors
    }
  }

  return (
    <div className="lang-cur-wrap" ref={rootRef}>
      <button
        className={`lang-cur ${open ? 'open' : ''}`}
        type="button"
        aria-label="Language and currency"
        aria-haspopup="true"
        aria-expanded={open}
        onClick={() => setOpen((value) => !value)}
      >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
          <circle cx="12" cy="12" r="9" />
          <path d="M3 12h18M12 3c2.5 2.7 2.5 15.3 0 18M12 3c-2.5 2.7-2.5 15.3 0 18" />
        </svg>
        <span>{language.short}</span>
        <span className="lc-div" />
        <span>{currency.label}</span>
      </button>

      <div className={`lang-cur-panel ${open ? 'show' : ''}`} role="dialog" aria-label="Language and currency">
        <div className="lc-section">
          <h5>Language</h5>
          <div className="lc-opts">
            {LANGUAGES.map((item) => (
              <button
                key={item.code}
                type="button"
                className={`lc-opt ${language.code === item.code ? 'sel' : ''}`}
                onClick={() => selectLanguage(item)}
              >
                {item.label}
              </button>
            ))}
          </div>
        </div>
        <div className="lc-section">
          <h5>Currency</h5>
          <div className="lc-opts">
            {CURRENCIES.map((item) => (
              <button
                key={item.code}
                type="button"
                className={`lc-opt ${currency.code === item.code ? 'sel' : ''}`}
                onClick={() => selectCurrency(item)}
              >
                {item.label}
              </button>
            ))}
          </div>
        </div>
      </div>
    </div>
  )
}
