const WD = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
const MO = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']

export function fmtDisplayDate(d) {
  if (!d) return '-'
  const date = d instanceof Date ? d : new Date(d)
  return `${WD[date.getDay()]}, ${MO[date.getMonth()]} ${date.getDate()}`
}

/** Compact label for checkout summary strip (e.g. "Airport Kef"). */
export function shortLocationName(name) {
  if (!name || name === '-') return name
  const codeMatch = name.match(/\(([A-Za-z0-9]{2,6})\)\s*$/)
  if (codeMatch && /airport/i.test(name)) {
    const code = codeMatch[1]
    return `Airport ${code.charAt(0).toUpperCase()}${code.slice(1).toLowerCase()}`
  }
  if (codeMatch) return codeMatch[1].toUpperCase()
  const trimmed = name.replace(/\s*\([^)]*\)\s*$/, '').trim()
  return trimmed.length > 22 ? `${trimmed.slice(0, 20)}…` : trimmed
}

export function nightsBetween(start, end) {
  if (!start || !end) return 0
  const a = start instanceof Date ? start : new Date(start)
  const b = end instanceof Date ? end : new Date(end)
  return Math.max(1, Math.round((b - a) / 86400000))
}

export function toDateOnlyString(d) {
  if (!d) return ''
  const date = d instanceof Date ? d : new Date(String(d).slice(0, 10))
  if (Number.isNaN(date.getTime())) return ''
  const pad = (n) => String(n).padStart(2, '0')
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`
}

export function combineDateAndTime(dateStr, timeStr) {
  if (!dateStr || !timeStr) return ''
  const [h, m] = timeStr.split(':').map(Number)
  const d = new Date(`${dateStr}T00:00:00`)
  d.setHours(h || 0, m || 0, 0, 0)
  const pad = (n) => String(n).padStart(2, '0')
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`
}

export function smoothScrollTo(targetY, dur = 540) {
  const startY = window.scrollY || window.pageYOffset
  const dist = targetY - startY
  if (Math.abs(dist) < 4) return
  if (window.matchMedia?.('(prefers-reduced-motion: reduce)')?.matches) {
    window.scrollTo(0, targetY)
    return
  }
  let t0 = null
  let ticked = false
  const ease = (p) => (p < 0.5 ? 2 * p * p : 1 - (-2 * p + 2) ** 2 / 2)
  const step = (ts) => {
    ticked = true
    if (t0 === null) t0 = ts
    const p = Math.min(1, (ts - t0) / dur)
    window.scrollTo(0, startY + dist * ease(p))
    if (p < 1) requestAnimationFrame(step)
  }
  requestAnimationFrame(step)
  setTimeout(() => {
    if (!ticked) window.scrollTo(0, targetY)
  }, 260)
}

export function guideToElement(el, focusEl) {
  if (!el) return
  const y = Math.max(0, el.getBoundingClientRect().top + window.scrollY - 104)
  smoothScrollTo(y)
  el.classList.remove('guide-pulse')
  void el.offsetWidth
  el.classList.add('guide-pulse')
  setTimeout(() => el.classList.remove('guide-pulse'), 1300)
  if (focusEl) {
    setTimeout(() => {
      try {
        focusEl.focus({ preventScroll: true })
      } catch {
        /* ignore */
      }
    }, 560)
  }
}

export function parseRentalOptionIds(searchParams) {
  const raw = searchParams.getAll('rental_option_ids')
  const ids = raw.flatMap((value) =>
    String(value)
      .split(',')
      .map((part) => Number(part.trim()))
      .filter((id) => Number.isInteger(id) && id > 0),
  )
  return [...new Set(ids)]
}

export function groupCardNumber(v) {
  const digits = v.replace(/\D/g, '').slice(0, 16)
  return digits.replace(/(.{4})/g, '$1 ').trim()
}

export function formatCardExpiry(v) {
  const digits = v.replace(/\D/g, '').slice(0, 4)
  if (digits.length > 2) return `${digits.slice(0, 2)} / ${digits.slice(2)}`
  return digits
}
