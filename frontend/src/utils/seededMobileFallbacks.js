const SEEDED_MOBILE_FALLBACKS = new Set([
  'Iceland road trips, one booking.',
  'Campervans, 4×4s & guesthouses for the Ring Road.',
  '/images/homepage/cardcamper.jpg',
  'Become a Host — start earning today!',
  'Become a Host - start earning today!',
  'List your van',
])

export function usableMobileValue(value) {
  if (value == null) return ''
  const trimmed = String(value).trim()
  if (trimmed === '' || SEEDED_MOBILE_FALLBACKS.has(trimmed)) return ''
  return trimmed
}
