import { useMemo } from 'react'
import { useAuth, normalizeUserRole } from '../context/AuthContext'
import { useShopConfig } from '../context/ShopConfigContext'
import { findCurrency } from '../data/localePreferences'
import { convertFromBase, convertToBase, createPriceFormatter, formatCurrency } from '../utils/format'

const EXAMPLE_AMOUNTS_BY_CODE = {
  ISK: {
    dailyRate: 12000,
    enhancedCoverage: 1500,
    fullCoverage: 3500,
    extraHour: 2500,
    guesthouseNightly: 18000,
  },
  EUR: {
    dailyRate: 95,
    enhancedCoverage: 15,
    fullCoverage: 35,
    extraHour: 10,
    guesthouseNightly: 120,
  },
  USD: {
    dailyRate: 100,
    enhancedCoverage: 15,
    fullCoverage: 40,
    extraHour: 12,
    guesthouseNightly: 130,
  },
  GBP: {
    dailyRate: 85,
    enhancedCoverage: 12,
    fullCoverage: 30,
    extraHour: 10,
    guesthouseNightly: 110,
  },
}

function exampleAmountsForCode(code) {
  return EXAMPLE_AMOUNTS_BY_CODE[code] || EXAMPLE_AMOUNTS_BY_CODE.EUR
}

export function useHostCurrency() {
  const { user } = useAuth()
  const { baseCurrency, exchangeRates } = useShopConfig()
  const isHost = normalizeUserRole(user) === 'host'

  const code = useMemo(() => {
    if (isHost && user?.currency) return String(user.currency).toUpperCase()
    return baseCurrency || 'EUR'
  }, [isHost, user?.currency, baseCurrency])

  const currency = useMemo(() => findCurrency(code), [code])

  const inputPrefix = currency.symbol || currency.code
  const exampleAmounts = useMemo(() => exampleAmountsForCode(currency.code), [currency.code])

  return useMemo(() => {
    const priceFormatter = createPriceFormatter({
      baseCurrency,
      displayCurrency: currency.code,
      exchangeRates,
    })

    return {
      code: currency.code,
      symbol: currency.symbol,
      label: currency.label,
      name: currency.name,
      inputPrefix,
      exampleAmounts,
      /** Format an amount already entered in the host display currency. */
      formatAmount: (amount) => formatCurrency(amount, currency.code),
      /** Format shop-base cents in the host display currency. */
      formatCents: (cents) => priceFormatter.formatCents(cents),
      fromBaseAmount: (amountInBase) =>
        convertFromBase(amountInBase, currency.code, exchangeRates, baseCurrency),
      toBaseAmount: (amountInDisplay) =>
        convertToBase(amountInDisplay, currency.code, exchangeRates, baseCurrency),
      amountLabel: (suffix = '') => {
        const base = currency.symbol ? `${currency.symbol} (${currency.code})` : currency.code
        return suffix ? `${base} ${suffix}` : base
      },
      fixedDiscountLabel: `${inputPrefix} per day discount`,
      fixedSurchargeLabel: `${inputPrefix} per day surcharge`,
    }
  }, [currency, inputPrefix, exampleAmounts, baseCurrency, exchangeRates])
}
