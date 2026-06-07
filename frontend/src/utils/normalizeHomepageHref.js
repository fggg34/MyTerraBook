const LEGACY_HREFS = {
  '#campervan': '/campervans',
  '#car': '/cars',
  '#cars': '/cars',
  '#guesthouse': '/guesthouses',
  '#guesthouses': '/guesthouses',
  '#host': '/become-a-host',
  '/guest-houses': '/guesthouses',
}

/** Maps legacy hash anchors from CMS seed data to real app routes. */
export function normalizeHomepageHref(href) {
  if (!href || typeof href !== 'string') return href
  return LEGACY_HREFS[href] ?? href
}

function normalizeFooterHref(href, label) {
  const normalized = normalizeHomepageHref(href)
  if (normalized?.startsWith('/')) return normalized
  const key = String(label || '').toLowerCase()
  if (key.includes('campervan')) return '/campervans'
  if (key.includes('guesthouse')) return '/guesthouses'
  if (key.includes('car') || key.includes('4×4') || key.includes('suv')) return '/cars'
  if (key.includes('become a host')) return '/become-a-host'
  return normalized
}

export function normalizeLinkList(links = []) {
  return links.map((link) => ({
    ...link,
    href: normalizeFooterHref(link.href, link.label),
  }))
}
