import { useMemo } from 'react'
import { useAuth, normalizeUserRole } from '../context/AuthContext'
import { useShopConfig } from '../context/ShopConfigContext'
import { findCurrency } from '../data/localePreferences'
import { formatCurrency, formatCurrencyFromCents } from '../utils/format'

export function useHostCurrency() {
  const { user } = useAuth()
  const { baseCurrency } = useShopConfig()
  const isHost = normalizeUserRole(user) === 'host'

  const code = useMemo(() => {
    if (isHost && user?.currency) return String(user.currency).toUpperCase()
    return baseCurrency || 'EUR'
  }, [isHost, user?.currency, baseCurrency])

  const currency = useMemo(() => findCurrency(code), [code])

  const inputPrefix = currency.symbol || currency.code

  return useMemo(() => ({
    code: currency.code,
    symbol: currency.symbol,
    label: currency.label,
    name: currency.name,
    inputPrefix,
    formatAmount: (amount) => formatCurrency(amount, currency.code),
    formatCents: (cents) => formatCurrencyFromCents(cents, currency.code),
    amountLabel: (suffix = '') => {
      const base = currency.symbol ? `${currency.symbol} (${currency.code})` : currency.code
      return suffix ? `${base} ${suffix}` : base
    },
    fixedDiscountLabel: `${inputPrefix} off total rental`,
    fixedSurchargeLabel: `${inputPrefix} per day surcharge`,
  }), [currency, inputPrefix])
}
