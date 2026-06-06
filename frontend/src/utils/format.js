export function formatCurrency(amount, currency = 'EUR') {
  const num = typeof amount === 'string' ? parseFloat(amount) : amount
  if (Number.isNaN(num)) return `${currency} 0.00`
  return new Intl.NumberFormat('en-GB', { style: 'currency', currency }).format(num)
}

/** Format integer cents from API (e.g. base_price_per_night_cents). */
export function formatCurrencyFromCents(cents, currency = 'EUR') {
  const num = typeof cents === 'number' ? cents / 100 : parseFloat(cents) / 100
  if (Number.isNaN(num)) return `${currency} 0.00`
  return new Intl.NumberFormat('en-GB', { style: 'currency', currency }).format(num)
}

export function formatDate(iso) {
  if (!iso) return '—'
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
