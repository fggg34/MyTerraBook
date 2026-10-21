import { formatCurrency } from './format.js'

export function carenPriceLabel(status) {
  if (status === 'unavailable') return 'No rate for these dates'
  if (status === 'error') return 'Price temporarily unavailable'
  return 'Select dates for price'
}

export function formatCarenPrice(amount, currency, formatter) {
  return currency === formatter?.baseCurrency
    ? formatter.format(amount)
    : formatCurrency(amount, currency)
}
