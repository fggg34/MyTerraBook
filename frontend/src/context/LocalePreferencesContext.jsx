import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react'
import {
  CUR_STORAGE_KEY,
  findCurrency,
  readStoredCurrency,
} from '../data/localePreferences'

const LocalePreferencesContext = createContext(null)

export function LocalePreferencesProvider({ currencyLabel = '€ EUR', children }) {
  const [currency, setCurrencyState] = useState(() => readStoredCurrency(currencyLabel))

  useEffect(() => {
    const onStorage = (event) => {
      if (event.key === CUR_STORAGE_KEY && event.newValue) {
        setCurrencyState(findCurrency(event.newValue))
      }
    }
    window.addEventListener('storage', onStorage)
    return () => window.removeEventListener('storage', onStorage)
  }, [])

  const setCurrency = useCallback((item) => {
    setCurrencyState(item)
    try {
      localStorage.setItem(CUR_STORAGE_KEY, item.code)
    } catch {
      // ignore storage errors
    }
  }, [])

  const value = useMemo(
    () => ({
      currency,
      setCurrency,
    }),
    [currency, setCurrency],
  )

  return <LocalePreferencesContext.Provider value={value}>{children}</LocalePreferencesContext.Provider>
}

export function useLocalePreferences() {
  const ctx = useContext(LocalePreferencesContext)
  if (!ctx) {
    throw new Error('useLocalePreferences must be used within LocalePreferencesProvider')
  }
  return ctx
}
