import { createContext, useContext, useEffect, useMemo, useState } from 'react'
import { api } from '../api'

const DEFAULT_CONFIG = {
  maps_api_key: '',
  currency: {
    code: 'EUR',
    symbol: '€',
    name: 'Euro',
    decimals: 2,
    decimal_separator: '.',
    thousand_separator: ',',
  },
  deposit: { allow_deposit: true, value: 15, type: 'percentage' },
  prepay_percent: 15,
  rentals_enabled: true,
  enable_coupons: true,
  payment_lock_minutes: 20,
  minimum_rental_days: 1,
  exchange_rates: { EUR: 1, USD: 1.08, GBP: 0.86, ISK: 150 },
}

const ShopConfigContext = createContext(null)

export function ShopConfigProvider({ children }) {
  const [config, setConfig] = useState(DEFAULT_CONFIG)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    api
      .get('/public-config')
      .then((res) => setConfig({ ...DEFAULT_CONFIG, ...res.data }))
      .catch(() => setConfig(DEFAULT_CONFIG))
      .finally(() => setLoading(false))
  }, [])

  const value = useMemo(
    () => ({
      config,
      loading,
      prepayPercent: config.prepay_percent ?? config.deposit?.value ?? 15,
      baseCurrency: config.currency?.code ?? 'EUR',
      exchangeRates: config.exchange_rates ?? DEFAULT_CONFIG.exchange_rates,
    }),
    [config, loading],
  )

  return <ShopConfigContext.Provider value={value}>{children}</ShopConfigContext.Provider>
}

export function useShopConfig() {
  const ctx = useContext(ShopConfigContext)
  if (!ctx) {
    throw new Error('useShopConfig must be used within ShopConfigProvider')
  }
  return ctx
}

export { DEFAULT_CONFIG }
