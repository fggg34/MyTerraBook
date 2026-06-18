export function formatRentListingStats(stats, priceFormatter) {
  const count = Number(stats?.count ?? 0)
  if (!count) return null

  const listingLabel = count === 1 ? 'listing' : 'listings'
  let text = `${count} ${listingLabel}`

  const minPriceCents = Number(stats?.minPriceCents ?? 0)
  const normalizedCents =
    minPriceCents > 0 && minPriceCents < 100 ? minPriceCents * 100 : minPriceCents
  if (normalizedCents > 0 && priceFormatter?.formatCents) {
    const price = priceFormatter.formatCents(normalizedCents)
    const unit = stats?.priceUnit === 'night' ? 'night' : 'day'
    text += ` · from ${price}/${unit}`
  }

  return text
}
