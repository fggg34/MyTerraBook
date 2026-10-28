import cc from 'currency-codes'

const SUPPORTED_CURRENCY_CODES = ['EUR', 'USD', 'GBP', 'ISK']

const CURRENCY_SYMBOL_OVERRIDES = {
  ISK: 'kr',
}

function currencySymbol(code) {
  if (CURRENCY_SYMBOL_OVERRIDES[code]) {
    return CURRENCY_SYMBOL_OVERRIDES[code]
  }

  try {
    const parts = new Intl.NumberFormat('en', { style: 'currency', currency: code }).formatToParts(0)
    const symbol = parts.find((part) => part.type === 'currency')?.value?.replace(/\.$/, '') || ''
    if (!symbol || symbol.toUpperCase() === code.toUpperCase()) {
      return null
    }
    return symbol
  } catch {
    return null
  }
}

function buildCurrencyLabel(code, symbol) {
  if (symbol) return `${symbol} ${code}`
  return code
}

export const CURRENCIES = SUPPORTED_CURRENCY_CODES.map((code) => {
  const info = cc.code(code)
  const symbol = currencySymbol(code)
  const name = info?.currency || code
  const label = buildCurrencyLabel(code, symbol)

  return {
    code,
    symbol,
    label,
    name,
  }
})

export const CUR_STORAGE_KEY = 'terrabook_currency'
export const CUR_COOKIE_KEY = 'terrabook_currency'
const CUR_COOKIE_MAX_AGE = 60 * 60 * 24 * 365

export function writeCurrencyCookie(code) {
  if (typeof document === 'undefined' || !code) return
  document.cookie = `${CUR_COOKIE_KEY}=${encodeURIComponent(code)};path=/;max-age=${CUR_COOKIE_MAX_AGE};SameSite=Lax`
}

export function readCurrencyCookie() {
  if (typeof document === 'undefined') return null
  const match = document.cookie.split('; ').find((row) => row.startsWith(`${CUR_COOKIE_KEY}=`))
  if (!match) return null
  return decodeURIComponent(match.split('=').slice(1).join('='))
}

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
  const fromCookie = readCurrencyCookie()
  if (fromCookie) return findCurrency(fromCookie)
  try {
    const stored = localStorage.getItem(CUR_STORAGE_KEY)
    if (stored) return findCurrency(stored)
  } catch {
    // ignore storage errors
  }
  return findCurrency(defaultLabel)
}
