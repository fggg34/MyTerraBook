import { useMemo } from 'react'
import { createPriceFormatter } from '../utils/format'
import { useLocalePreferences } from '../context/LocalePreferencesContext'
import { useShopConfig } from '../context/ShopConfigContext'

export function useFormatPrice() {
  const { currency } = useLocalePreferences()
  const { config, baseCurrency, exchangeRates } = useShopConfig()

  return useMemo(
    () =>
      createPriceFormatter({
        baseCurrency,
        displayCurrency: currency.code,
        exchangeRates,
        currencyMeta: config.currency,
      }),
    [baseCurrency, currency.code, exchangeRates, config.currency],
  )
}
