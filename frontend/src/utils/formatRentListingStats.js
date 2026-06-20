export function formatRentListingStats(stats, priceFormatter) {
  const count = Number(stats?.count ?? 0)
  if (!count) return null

  const listingLabel = count === 1 ? 'listing' : 'listings'
  let text = `${count} ${listingLabel}`

  const minPriceCents = Number(stats?.minPriceCents ?? 0)
  if (Number.isFinite(minPriceCents) && minPriceCents > 0 && priceFormatter?.formatCents) {
    const price = priceFormatter.formatCents(minPriceCents)
    const unit = stats?.priceUnit === 'night' ? 'night' : 'day'
    text += ` · from ${price}/${unit}`
  }

  return text
}
