export const CURRENCIES = [
  { code: 'EUR', label: '€ EUR' },
  { code: 'USD', label: '$ USD' },
  { code: 'GBP', label: '£ GBP' },
  { code: 'ISK', label: 'kr ISK' },
]

export const CUR_STORAGE_KEY = 'terrabook_currency'

export function findCurrency(value) {
  if (!value) return CURRENCIES[0]
  const normalized = String(value).trim()
  const match = CURRENCIES.find(
    (item) => item.label === normalized || item.code === normalized || normalized.includes(item.code),
  )
  if (match) return match

  try {
    const stored = localStorage.getItem(CUR_STORAGE_KEY)
    const storedMatch = CURRENCIES.find((item) => item.code === stored || item.label === stored)
    if (storedMatch) return storedMatch
  } catch {
    // ignore storage errors
  }

  return CURRENCIES[0]
}

export function readStoredCurrency(defaultLabel = '€ EUR') {
  try {
    const stored = localStorage.getItem(CUR_STORAGE_KEY)
    if (stored) return findCurrency(stored)
  } catch {
    // ignore storage errors
  }
  return findCurrency(defaultLabel)
}
