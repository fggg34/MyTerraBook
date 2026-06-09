export function computePriceBounds(cards) {
  const prices = cards.map((c) => c.sortPrice).filter((p) => p > 0)
  if (!prices.length) return { min: 0, max: 500, step: 10 }
  const min = Math.floor(Math.min(...prices) / 10) * 10
  const max = Math.ceil(Math.max(...prices) / 10) * 10
  const step = max - min > 200 ? 10 : 5
  return { min, max: Math.max(max, min + step), step }
}

export function isPriceFilterActive(filters, bounds) {
  if (!bounds) return false
  return filters.minPrice > bounds.min || filters.maxPrice < bounds.max
}

export function defaultPriceFilters(bounds) {
  return { minPrice: bounds.min, maxPrice: bounds.max }
}

export function clampPriceFilters(filters, bounds) {
  const minPrice = Math.max(bounds.min, Math.min(filters.minPrice ?? bounds.min, bounds.max))
  const maxPrice = Math.max(minPrice, Math.min(filters.maxPrice ?? bounds.max, bounds.max))
  return { minPrice, maxPrice }
}
