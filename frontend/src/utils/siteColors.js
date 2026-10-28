export const DEFAULT_SITE_COLORS = {
  navy: '#041e37',
  ink: '#041e37',
  slate: '#5c6b80',
  slateLight: '#8794a3',
  green: '#f4a83c',
  greenDark: '#e89a2e',
  blue: '#4474a4',
  blueSoft: '#e8eff7',
  line: '#e0e5ed',
  bg: '#f6f2ec',
  red: '#e23744',
  accent: '#f4a83c',
}

export const SITE_COLOR_VARS = {
  navy: '--navy',
  ink: '--ink',
  slate: '--slate',
  slateLight: '--slate-light',
  green: '--green',
  greenDark: '--green-dark',
  blue: '--blue',
  blueSoft: '--blue-soft',
  line: '--line',
  bg: '--bg',
  red: '--red',
  accent: '--accent',
}

export function normalizeHex(value) {
  if (typeof value !== 'string') return null
  const trimmed = value.trim()
  if (!trimmed) return null

  const short = trimmed.match(/^#?([0-9a-fA-F]{3})$/)
  if (short) {
    const hex = short[1].toLowerCase()
    return `#${hex[0]}${hex[0]}${hex[1]}${hex[1]}${hex[2]}${hex[2]}`
  }

  const full = trimmed.match(/^#?([0-9a-fA-F]{6})$/)
  if (full) {
    return `#${full[1].toLowerCase()}`
  }

  return null
}

export function mergeSiteColors(apiColors = {}) {
  const merged = { ...DEFAULT_SITE_COLORS }

  for (const key of Object.keys(SITE_COLOR_VARS)) {
    const hex = normalizeHex(apiColors[key])
    if (hex) merged[key] = hex
  }

  return merged
}

export function siteColorCss(colors = {}) {
  const merged = mergeSiteColors(colors)
  const rules = Object.entries(SITE_COLOR_VARS).map(
    ([key, cssVar]) => `${cssVar}: ${merged[key]} !important`,
  )

  return `:root{${rules.join(';')};}`
}

export function applySiteColors(colors = {}) {
  if (typeof document === 'undefined') return

  const merged = mergeSiteColors(colors)
  const root = document.documentElement

  for (const [key, cssVar] of Object.entries(SITE_COLOR_VARS)) {
    root.style.setProperty(cssVar, merged[key])
  }
}
