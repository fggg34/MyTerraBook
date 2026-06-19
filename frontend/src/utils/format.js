export function formatCurrency(amount, currency = 'EUR') {
  const num = typeof amount === 'string' ? parseFloat(amount) : amount
  if (Number.isNaN(num)) return `${currency} 0`
  return new Intl.NumberFormat('en-GB', {
    style: 'currency',
    currency,
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
  }).format(num)
}

/** Format integer cents from API (e.g. base_price_per_night_cents). */
export function formatCurrencyFromCents(cents, currency = 'EUR') {
  const num = typeof cents === 'number' ? cents / 100 : parseFloat(cents) / 100
  if (Number.isNaN(num)) return `${currency} 0`
  return new Intl.NumberFormat('en-GB', {
    style: 'currency',
    currency,
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
  }).format(num)
}

export function convertFromBase(amount, targetCurrency, exchangeRates = {}, baseCurrency = 'EUR') {
  const base = Number(amount)
  if (Number.isNaN(base)) return 0
  const rates = exchangeRates || {}
  const targetRate = rates[targetCurrency] ?? (targetCurrency === baseCurrency ? 1 : 1)
  return base * targetRate
}

export function createPriceFormatter({
  baseCurrency = 'EUR',
  displayCurrency = 'EUR',
  exchangeRates = {},
  currencyMeta = {},
} = {}) {
  const formatConverted = (amountInBase) => {
    const converted = convertFromBase(amountInBase, displayCurrency, exchangeRates, baseCurrency)
    const maxDecimals = currencyMeta.decimals ?? 2
    const minDecimals = maxDecimals >= 2 ? 2 : maxDecimals
    try {
      return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: displayCurrency,
        minimumFractionDigits: minDecimals,
        maximumFractionDigits: maxDecimals,
      }).format(converted)
    } catch {
      const rounded = maxDecimals === 0
        ? Math.round(converted)
        : Number(converted.toFixed(maxDecimals))
      return `${displayCurrency} ${rounded.toLocaleString('en-GB')}`
    }
  }

  return {
    baseCurrency,
    displayCurrency,
    format: (amountInBase) => formatConverted(Number(amountInBase)),
    formatCents: (cents) => formatConverted(Number(cents) / 100),
    convertFromBase: (amountInBase) =>
      convertFromBase(amountInBase, displayCurrency, exchangeRates, baseCurrency),
    isConverted: displayCurrency !== baseCurrency,
  }
}

export function formatDate(iso) {
  if (!iso) return '-'
  return new Date(iso).toLocaleDateString('en-GB', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}

export function formatDateTimeLocal(iso) {
  if (!iso) return ''
  const d = iso instanceof Date ? iso : new Date(iso)
  if (Number.isNaN(d.getTime())) return ''
  const pad = (n) => String(n).padStart(2, '0')
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`
}

/** Calendar date in local time (avoids UTC day-shift from toISOString). */
export function formatDateOnly(value) {
  if (!value) return ''
  const d = value instanceof Date ? value : new Date(String(value).slice(0, 10))
  if (Number.isNaN(d.getTime())) return ''
  const pad = (n) => String(n).padStart(2, '0')
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`
}

export function parseTimeParts(time) {
  if (!time) return null
  const match = String(time).match(/^(\d{1,2}):(\d{2})/)
  if (!match) return null
  return { hours: Number(match[1]), minutes: Number(match[2]) }
}

export const TIME_INTERVAL_MINUTES = 30

export function normalizeTimeString(time) {
  if (!time) return ''
  const parts = parseTimeParts(time)
  if (!parts) return ''

  const totalMinutes = parts.hours * 60 + parts.minutes
  const snapped = Math.round(totalMinutes / TIME_INTERVAL_MINUTES) * TIME_INTERVAL_MINUTES
  const hours = Math.floor(snapped / 60) % 24
  const minutes = snapped % 60

  return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`
}

export function parseTimeValue(time) {
  const normalized = normalizeTimeString(time)
  if (!normalized) return null

  const parts = parseTimeParts(normalized)
  if (!parts) return null

  const date = new Date()
  date.setHours(parts.hours, parts.minutes, 0, 0)

  return date
}

export function formatTimeValue(date) {
  if (!date || Number.isNaN(date.getTime())) return ''

  return `${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`
}

export function formatDateTimeAt(value, hours = 10, minutes = 0) {
  if (!value) return ''
  const d = value instanceof Date ? new Date(value) : new Date(String(value).slice(0, 10))
  if (Number.isNaN(d.getTime())) return ''
  d.setHours(hours, minutes, 0, 0)
  return formatDateTimeLocal(d)
}

export function parseDateTimeLocal(value) {
  if (!value) return null
  const d = new Date(value)
  return Number.isNaN(d.getTime()) ? null : d
}

export function toApiDateTime(localValue) {
  if (!localValue) return ''
  const d = new Date(localValue)
  if (Number.isNaN(d.getTime())) return localValue
  return d.toISOString().slice(0, 19).replace('T', ' ')
}

export function capitalize(str) {
  if (!str) return ''
  return str.charAt(0).toUpperCase() + str.slice(1).replace(/_/g, ' ')
}
