export function weekdayName(date) {
  return date.toLocaleDateString('en-US', { weekday: 'long' }).toLowerCase()
}

export function weekdayShort(date) {
  return date.toLocaleDateString('en-US', { weekday: 'short' }).toLowerCase()
}

/** YYYY-MM-DD in the user's local timezone (avoids UTC shift from toISOString). */
export function localDateKey(date) {
  if (!date) return ''
  const d = date instanceof Date ? date : new Date(date)
  if (Number.isNaN(d.getTime())) return ''
  const pad = (n) => String(n).padStart(2, '0')
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`
}

function matchesWeekdayRule(date, rules = []) {
  if (!rules.length) return false
  const long = weekdayName(date)
  const short = weekdayShort(date)
  const num = String((date.getDay() + 6) % 7)
  return rules.some((rule) => {
    const value = String(rule).toLowerCase()
    return value === long || value === short || value === num
  })
}

function activeRestrictions(date, restrictions = []) {
  const dateStr = localDateKey(date)
  return restrictions.filter((r) => {
    const from = r.date_from
    const to = r.date_to
    return from <= dateStr && dateStr <= to
  })
}

export function isClosedToArrival(date, restrictions = []) {
  return activeRestrictions(date, restrictions).some((r) =>
    matchesWeekdayRule(date, r.closed_to_arrival || []),
  )
}

export function isClosedToDeparture(date, restrictions = []) {
  return activeRestrictions(date, restrictions).some((r) =>
    matchesWeekdayRule(date, r.closed_to_departure || []),
  )
}

export function isForcedPickupOnly(date, restrictions = []) {
  const active = activeRestrictions(date, restrictions)
  if (!active.length) return false
  return active.some((r) => {
    const forced = r.forced_pickup_weekdays || []
    if (!forced.length) return false
    return matchesWeekdayRule(date, forced)
  })
}

export function expandBlockedWindows(windows = []) {
  const dates = new Set()
  for (const window of windows) {
    const startRaw = window.start || window.starts_at
    const endRaw = window.end || window.ends_at
    if (!startRaw || !endRaw) continue
    const start = new Date(startRaw)
    const end = new Date(endRaw)
    if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime())) continue
    const cur = new Date(start)
    cur.setHours(0, 0, 0, 0)
    const last = new Date(end)
    last.setHours(0, 0, 0, 0)
    while (cur <= last) {
      dates.add(localDateKey(cur))
      cur.setDate(cur.getDate() + 1)
    }
  }
  return [...dates]
}

export function rangeIncludesBlockedDate(startDate, endDate, blockedDates = []) {
  if (!startDate || !endDate || !blockedDates.length) return false
  const blockedSet = new Set(blockedDates)
  const cur = new Date(startDate)
  cur.setHours(0, 0, 0, 0)
  const last = new Date(endDate)
  last.setHours(0, 0, 0, 0)
  while (cur <= last) {
    if (blockedSet.has(localDateKey(cur))) return true
    cur.setDate(cur.getDate() + 1)
  }
  return false
}

export function buildDateDisabledChecker({ blockedDates = [], restrictions = [], role = 'pickup' } = {}) {
  const blockedSet = new Set(blockedDates)
  return (date) => {
    const dateStr = localDateKey(date)
    if (blockedSet.has(dateStr)) return true
    if (role === 'pickup' && isClosedToArrival(date, restrictions)) return true
    if (role === 'dropoff' && isClosedToDeparture(date, restrictions)) return true
    return false
  }
}
