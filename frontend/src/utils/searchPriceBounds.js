export function computePriceBounds(cards) {
  const prices = cards.map((c) => c.sortPrice).filter((p) => p > 0)
  if (!prices.length) return { min: 0, max: 500, step: 10 }
  const min = Math.floor(Math.min(...prices) / 10) * 10
  const max = Math.ceil(Math.max(...prices) / 10) * 10
  const step = max - min > 200 ? 10 : 5
  return { min, max: Math.max(max, min + step), step }
}

/**
 * null means the traveller has not touched the price slider, so no price
 * filter applies. Listings settle in ISK, where a daily rate is five figures,
 * so any hardcoded starting range would hide the entire fleet until the
 * real bounds arrived.
 */
export function defaultPriceFilters() {
  return { minPrice: null, maxPrice: null }
}

export function isPriceFilterActive(filters, bounds) {
  if (!bounds || !filters) return false
  const { minPrice, maxPrice } = filters
  return (
    (minPrice != null && minPrice > bounds.min)
    || (maxPrice != null && maxPrice < bounds.max)
  )
}

export function matchesPriceFilter(price, filters) {
  if (price == null || price === '') return true

  const value = Number(price)
  if (!Number.isFinite(value)) return true
  if (filters?.minPrice != null && value < filters.minPrice) return false
  if (filters?.maxPrice != null && value > filters.maxPrice) return false
  return true
}

export function clampPriceFilters(filters, bounds) {
  const minPrice = filters?.minPrice == null
    ? null
    : Math.max(bounds.min, Math.min(filters.minPrice, bounds.max))
  const maxPrice = filters?.maxPrice == null
    ? null
    : Math.max(minPrice ?? bounds.min, Math.min(filters.maxPrice, bounds.max))

  return { minPrice, maxPrice }
}
