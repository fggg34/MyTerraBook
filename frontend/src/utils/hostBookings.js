const ACTIVE_CAR_STATUSES = new Set(['pending', 'confirmed', 'stand_by'])
const ACTIVE_STAY_STATUSES = new Set(['pending', 'confirmed', 'completed'])

function parseDay(value) {
  if (!value) return null
  const raw = String(value).slice(0, 10)
  const [y, m, d] = raw.split('-').map(Number)
  if (!y || !m || !d) return null
  return new Date(y, m - 1, d)
}

function toKey(date) {
  const y = date.getFullYear()
  const m = String(date.getMonth() + 1).padStart(2, '0')
  const d = String(date.getDate()).padStart(2, '0')
  return `${y}-${m}-${d}`
}

function addDays(date, days) {
  const next = new Date(date)
  next.setDate(next.getDate() + days)
  return next
}

function eachDayKeys(start, end, inclusiveEnd = true) {
  const keys = []
  let cursor = new Date(start)
  const last = inclusiveEnd ? end : addDays(end, -1)
  while (cursor <= last) {
    keys.push(toKey(cursor))
    cursor = addDays(cursor, 1)
  }
  return keys
}

export function normalizeHostCarBookings(items) {
  return (items || [])
    .filter((item) => ACTIVE_CAR_STATUSES.has(item.order_status))
    .map((item) => {
      const start = parseDay(item.pickup_at)
      const end = parseDay(item.dropoff_at)
      if (!start || !end) return null
      return {
        id: `car-${item.id}`,
        label: item.car?.name || item.reference,
        reference: item.reference,
        status: item.order_status,
        start,
        end,
        startKey: toKey(start),
        endKey: toKey(end),
        days: eachDayKeys(start, end, true),
      }
    })
    .filter(Boolean)
}

export function normalizeHostStayBookings(items) {
  return (items || [])
    .filter((item) => ACTIVE_STAY_STATUSES.has(item.status))
    .map((item) => {
      const start = parseDay(item.check_in)
      const end = parseDay(item.check_out)
      if (!start || !end) return null
      return {
        id: `stay-${item.id}`,
        label: item.guest_house?.name || item.booking_reference,
        reference: item.booking_reference,
        status: item.status,
        start,
        end,
        startKey: toKey(start),
        endKey: toKey(end),
        days: eachDayKeys(start, end, false),
      }
    })
    .filter(Boolean)
}
