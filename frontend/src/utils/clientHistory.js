export const TYPE_LABELS = {
  car: 'Car rental',
  campervan: 'Campervan',
  guesthouse: 'Guesthouse',
}

export function getListingPath(item) {
  if (item.type === 'guesthouse') {
    if (item.listing_slug) return `/guesthouses/${item.listing_slug}`
    if (item.listing_id) return `/guesthouses/${item.listing_id}`
    return null
  }

  const base = item.type === 'campervan' ? '/campervans' : '/cars'
  if (item.listing_slug) return `${base}/${item.listing_slug}`
  if (item.listing_id) return `${base}/${item.listing_id}`
  return null
}

export function isUpcoming(item) {
  const ends = new Date(item.ends_at)
  const cancelled = item.status === 'cancelled' || item.cancelled_at
  return ends >= new Date() && !cancelled
}

export function groupHistoryItems(items) {
  const upcoming = []
  const past = []

  for (const item of items) {
    if (isUpcoming(item)) upcoming.push(item)
    else past.push(item)
  }

  return { upcoming, past }
}
