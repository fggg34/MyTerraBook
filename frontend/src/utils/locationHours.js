function normalizeTime(value) {
  if (!value) return null
  const str = String(value)
  const m = str.match(/^(\d{1,2}):(\d{2})/)
  if (!m) return null
  return `${String(m[1]).padStart(2, '0')}:${m[2]}`
}

function timeToMinutes(time) {
  const norm = normalizeTime(time)
  if (!norm) return null
  const [h, min] = norm.split(':').map(Number)
  return h * 60 + min
}

function minutesToTime(minutes) {
  const clamped = Math.max(0, Math.min(24 * 60 - 1, minutes))
  const h = Math.floor(clamped / 60)
  const m = clamped % 60
  return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`
}

export function effectiveDailyWindow(location) {
  const from = normalizeTime(location?.default_opening_time)
  const to = normalizeTime(location?.default_closing_time)
  if (!from || !to) return null
  return { from, to }
}

export function intersectionForLocations(locations) {
  if (!locations?.length) return null

  let fromMinutes = null
  let toMinutes = null

  for (const location of locations) {
    const window = effectiveDailyWindow(location)
    if (!window) return null
    const open = timeToMinutes(window.from)
    const close = timeToMinutes(window.to)
    fromMinutes = fromMinutes === null ? open : Math.max(fromMinutes, open)
    toMinutes = toMinutes === null ? close : Math.min(toMinutes, close)
  }

  if (fromMinutes === null || toMinutes === null || fromMinutes >= toMinutes) {
    return null
  }

  return { from: minutesToTime(fromMinutes), to: minutesToTime(toMinutes) }
}

export function timeOptionsForWindow(from, to) {
  const start = timeToMinutes(from)
  const end = timeToMinutes(to)
  if (start === null || end === null || start >= end) return []

  const options = []
  for (let m = start; m <= end; m += 30) {
    options.push(minutesToTime(m))
  }
  return options
}

export function formatWindowLabel(bounds) {
  if (!bounds) return 'No opening hours configured'
  return `${bounds.from}–${bounds.to}`
}
